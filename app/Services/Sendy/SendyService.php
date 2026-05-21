<?php

namespace App\Services\Sendy;

use App\Enums\NewsletterEventAction;
use App\Enums\SendyStatus;
use App\Models\NewsletterSubscriptionEvent;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class SendyService
{
    public function __construct(
        private HttpFactory $http,
        private DatabaseManager $db,
    ) {}

    public function isEnabled(): bool
    {
        return SiteSetting::getAsBool('newsletter_integration_enabled', false);
    }

    public function subscribe(string $email, ?string $name, NewsletterSubscriptionContext $ctx): SendyResult
    {
        if (! $this->isEnabled()) {
            Log::debug('Sendy subscribe skipped: integration disabled');

            return SendyResult::failure('Integration disabled');
        }

        try {
            $response = $this->http->asForm()->post($this->apiUrl('/subscribe'), [
                'api_key' => $this->apiToken(),
                'name' => $name ?? '',
                'email' => $email,
                'list' => $this->listId(),
                'ipaddress' => $ctx->ip ?? '',
                'referrer' => $ctx->referrer ?? '',
                'gdpr' => 'true',
                'silent' => $ctx->silent ? 'true' : 'false',
                'boolean' => 'true',
            ]);

            $body = trim($response->body());

            if (strcasecmp($body, 'Already subscribed.') === 0) {
                $this->persistSubscriptionOutcome($email, $ctx, NewsletterEventAction::AlreadySubscribed);

                return SendyResult::alreadySubscribed();
            }

            if ($this->isTruthyApiResponse($body)) {
                $this->persistSubscriptionOutcome($email, $ctx, NewsletterEventAction::Subscribed);

                return SendyResult::success($body);
            }

            $this->persistSubscriptionOutcome($email, $ctx, NewsletterEventAction::Failed, $body);
            Log::warning('Sendy subscribe failed', ['email' => $email, 'response' => $body]);

            return SendyResult::failure($body !== '' ? $body : 'Subscribe failed');
        } catch (Throwable $e) {
            $this->persistSubscriptionOutcome($email, $ctx, NewsletterEventAction::Failed, $e->getMessage());
            Log::error('Sendy subscribe exception', ['email' => $email, 'error' => $e->getMessage()]);

            return SendyResult::failure('Subscribe failed');
        }
    }

    public function unsubscribe(string $email, NewsletterSubscriptionContext $ctx): SendyResult
    {
        if (! $this->isEnabled()) {
            Log::debug('Sendy unsubscribe skipped: integration disabled');

            return SendyResult::failure('Integration disabled');
        }

        try {
            $response = $this->http->asForm()->post($this->apiUrl('/unsubscribe'), [
                'email' => $email,
                'list' => $this->listId(),
                'boolean' => 'true',
            ]);

            $body = trim($response->body());

            if ($this->isTruthyApiResponse($body)) {
                $this->persistSubscriptionOutcome($email, $ctx, NewsletterEventAction::Unsubscribed);

                return SendyResult::success($body);
            }

            $this->persistSubscriptionOutcome($email, $ctx, NewsletterEventAction::Failed, $body);
            Log::warning('Sendy unsubscribe failed', ['email' => $email, 'response' => $body]);

            return SendyResult::failure($body !== '' ? $body : 'Unsubscribe failed');
        } catch (Throwable $e) {
            $this->persistSubscriptionOutcome($email, $ctx, NewsletterEventAction::Failed, $e->getMessage());
            Log::error('Sendy unsubscribe exception', ['email' => $email, 'error' => $e->getMessage()]);

            return SendyResult::failure('Unsubscribe failed');
        }
    }

    public function getStatus(string $email): SendyStatus
    {
        if (! $this->isEnabled()) {
            Log::debug('Sendy getStatus skipped: integration disabled');

            return SendyStatus::NotFound;
        }

        $fromDb = $this->getStatusFromDb($email);

        if ($fromDb !== null) {
            return $fromDb;
        }

        return $this->getStatusFromApi($email);
    }

    public function getStatusFromDb(string $email): ?SendyStatus
    {
        if (! $this->usesSendyDb() || $this->listInternalId() === null) {
            return null;
        }

        try {
            $row = $this->db->connection('sendy')
                ->table('subscribers')
                ->where('email', $email)
                ->where('list', $this->listInternalId())
                ->first();

            if ($row === null) {
                return SendyStatus::NotFound;
            }

            return $this->mapSubscriberRowToStatus($row);
        } catch (Throwable $e) {
            Log::error('Sendy getStatusFromDb exception', ['email' => $email, 'error' => $e->getMessage()]);

            return SendyStatus::NotFound;
        }
    }

    public function getStatusFromApi(string $email): SendyStatus
    {
        if (! $this->isEnabled()) {
            return SendyStatus::NotFound;
        }

        try {
            $response = $this->http->asForm()->post($this->apiUrl('/api/subscribers/subscription-status.php'), [
                'api_key' => $this->apiToken(),
                'email' => $email,
                'list_id' => $this->listId(),
            ]);

            return $this->mapApiStatusResponse(trim($response->body()));
        } catch (Throwable $e) {
            Log::error('Sendy getStatusFromApi exception', ['email' => $email, 'error' => $e->getMessage()]);

            return SendyStatus::NotFound;
        }
    }

    public function isSubscribed(string $email): bool
    {
        return $this->getStatus($email) === SendyStatus::Subscribed;
    }

    public function syncUserSubscriptionState(User $user): bool
    {
        if (! $this->isEnabled()) {
            return $user->wantsNewsletter();
        }

        try {
            $subscribed = $this->getStatus($user->email) === SendyStatus::Subscribed;

            if (Schema::hasColumn('users', 'newsletter_subscribed_at')) {
                $user->forceFill([
                    'newsletter_subscribed_at' => $subscribed ? ($user->newsletter_subscribed_at ?? now()) : null,
                    'newsletter_synced_at' => now(),
                ])->save();
            }

            return $subscribed;
        } catch (Throwable $e) {
            Log::error('Sendy syncUserSubscriptionState exception', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return $user->wantsNewsletter();
        }
    }

    public function activeSubscriberCount(): ?int
    {
        if (! $this->usesSendyDb() || $this->listInternalId() === null) {
            return null;
        }

        try {
            return $this->db->connection('sendy')
                ->table('subscribers')
                ->where('list', $this->listInternalId())
                ->where('unsubscribed', 0)
                ->where('bounced', 0)
                ->where('complaint', 0)
                ->count();
        } catch (Throwable $e) {
            Log::error('Sendy activeSubscriberCount exception', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * @param  iterable<User>  $users
     */
    public function syncUsersFromSendyDb(iterable $users): int
    {
        if (! Schema::hasColumn('users', 'newsletter_subscribed_at')) {
            return 0;
        }

        $userList = $users instanceof Collection ? $users : collect($users);

        if ($userList->isEmpty()) {
            return 0;
        }

        $subscribedEmails = $this->subscribedEmailsFromDb(
            $userList->pluck('email')->filter()->unique()->values()->all(),
        );

        if ($subscribedEmails === null) {
            $synced = 0;
            foreach ($userList as $user) {
                $status = $this->getStatus($user->email);
                $user->forceFill([
                    'newsletter_subscribed_at' => $status === SendyStatus::Subscribed ? now() : null,
                    'newsletter_synced_at' => now(),
                ])->save();
                $synced++;
            }

            return $synced;
        }

        $synced = 0;

        foreach ($userList as $user) {
            $isSubscribed = in_array($user->email, $subscribedEmails, true);

            $user->forceFill([
                'newsletter_subscribed_at' => $isSubscribed ? now() : null,
                'newsletter_synced_at' => now(),
            ])->save();

            $synced++;
        }

        return $synced;
    }

    /**
     * @param  array<int, string>  $emails
     * @return array<int, string>|null null quando DB Sendy indisponível (fallback por user)
     */
    private function subscribedEmailsFromDb(array $emails): ?array
    {
        if ($emails === [] || ! $this->usesSendyDb() || $this->listInternalId() === null) {
            return null;
        }

        try {
            $subscribed = [];

            $rows = $this->db->connection('sendy')
                ->table('subscribers')
                ->where('list', $this->listInternalId())
                ->whereIn('email', $emails)
                ->get();

            foreach ($rows as $row) {
                if ($this->mapSubscriberRowToStatus($row) === SendyStatus::Subscribed) {
                    $subscribed[] = $row->email;
                }
            }

            return $subscribed;
        } catch (Throwable $e) {
            Log::error('Sendy subscribedEmailsFromDb exception', ['error' => $e->getMessage()]);

            return null;
        }
    }

    private function persistSubscriptionOutcome(
        string $email,
        NewsletterSubscriptionContext $ctx,
        NewsletterEventAction $action,
        ?string $failureMessage = null,
    ): void {
        $this->recordEvent($email, $ctx, $action, $failureMessage);

        if ($ctx->userId === null) {
            return;
        }

        if ($action === NewsletterEventAction::Subscribed || $action === NewsletterEventAction::AlreadySubscribed) {
            $this->updateUserNewsletterCache($ctx->userId, subscribed: true);
        } elseif ($action === NewsletterEventAction::Unsubscribed) {
            $this->updateUserNewsletterCache($ctx->userId, subscribed: false);
        }
    }

    private function recordEvent(
        string $email,
        NewsletterSubscriptionContext $ctx,
        NewsletterEventAction $action,
        ?string $failureMessage = null,
    ): void {
        if (! Schema::hasTable('newsletter_subscription_events')) {
            return;
        }

        try {
            NewsletterSubscriptionEvent::query()->create([
                'user_id' => $ctx->userId,
                'email' => $email,
                'action' => $action->value,
                'source' => $ctx->source->value,
                'popup_variant' => $ctx->popupVariant,
                'popup_trigger' => $ctx->popupTrigger,
                'ip' => $ctx->ip,
                'user_agent' => $ctx->userAgent,
                'referrer' => $ctx->referrer,
                'page_url' => $ctx->pageUrl,
                'meta' => $failureMessage !== null ? ['error' => $failureMessage] : null,
            ]);
        } catch (Throwable $e) {
            Log::error('Sendy recordEvent exception', ['email' => $email, 'error' => $e->getMessage()]);
        }
    }

    private function updateUserNewsletterCache(int $userId, bool $subscribed): void
    {
        if (! Schema::hasColumn('users', 'newsletter_subscribed_at')) {
            return;
        }

        try {
            $user = User::query()->find($userId);

            if ($user === null) {
                return;
            }

            $user->forceFill([
                'newsletter_subscribed_at' => $subscribed ? now() : null,
                'newsletter_synced_at' => now(),
            ])->save();
        } catch (Throwable $e) {
            Log::error('Sendy updateUserNewsletterCache exception', ['user_id' => $userId, 'error' => $e->getMessage()]);
        }
    }

    private function mapSubscriberRowToStatus(object $row): SendyStatus
    {
        if ((int) ($row->complaint ?? 0) === 1) {
            return SendyStatus::Complained;
        }

        if ((int) ($row->bounced ?? 0) === 1) {
            return SendyStatus::Bounced;
        }

        if ((int) ($row->unsubscribed ?? 0) === 1) {
            return SendyStatus::Unsubscribed;
        }

        if (isset($row->confirmed) && (int) $row->confirmed === 0) {
            return SendyStatus::Unconfirmed;
        }

        return SendyStatus::Subscribed;
    }

    private function mapApiStatusResponse(string $body): SendyStatus
    {
        return match (strtolower($body)) {
            'subscribed' => SendyStatus::Subscribed,
            'unsubscribed' => SendyStatus::Unsubscribed,
            'unconfirmed' => SendyStatus::Unconfirmed,
            'bounced' => SendyStatus::Bounced,
            'soft bounced', 'soft_bounced' => SendyStatus::SoftBounced,
            'complained' => SendyStatus::Complained,
            'email does not exist in list', 'email not found' => SendyStatus::NotFound,
            default => SendyStatus::NotFound,
        };
    }

    private function isTruthyApiResponse(string $body): bool
    {
        $normalized = strtolower(trim($body));

        return in_array($normalized, ['true', '1', 'success'], true);
    }

    private function apiUrl(string $path): string
    {
        return rtrim((string) config('services.sendy.api_base_url'), '/').$path;
    }

    private function apiToken(): string
    {
        return (string) config('services.sendy.api_token');
    }

    private function listId(): string
    {
        return (string) config('services.sendy.list_id');
    }

    private function listInternalId(): ?int
    {
        $id = config('services.sendy.list_internal_id');

        if ($id === null || $id === '') {
            return null;
        }

        return (int) $id;
    }

    private function usesSendyDb(): bool
    {
        return (bool) config('services.sendy.db_enabled', true);
    }
}
