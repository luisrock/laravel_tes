<?php

namespace App\Http\Controllers;

use App\Enums\NewsletterEventAction;
use App\Enums\NewsletterEventSource;
use App\Http\Requests\NewsletterSubscribeRequest;
use App\Models\NewsletterSubscriptionEvent;
use App\Services\Sendy\NewsletterSubscriptionContext;
use App\Services\Sendy\SendyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

final class NewsletterSubscriptionController extends Controller
{
    public function __construct(private SendyService $sendy) {}

    public function subscribe(NewsletterSubscribeRequest $request): JsonResponse
    {
        if (! $this->sendy->isEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Inscrições temporariamente indisponíveis. Tente em alguns minutos.',
            ], 503);
        }

        $email = strtolower(trim($request->user()?->email ?? $request->string('email')->toString()));
        $lockKey = 'newsletter-subscribe:'.hash('sha256', $email);

        /** @var JsonResponse $response */
        $response = Cache::lock($lockKey, 15)->block(5, function () use ($request, $email): JsonResponse {
            if ($this->hasRecentSuccessfulSubscription($email)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Inscrição realizada! Verifique seu email.',
                    'already_subscribed' => false,
                ]);
            }

            return $this->performSubscribe($request, $email);
        });

        return $response;
    }

    private function performSubscribe(NewsletterSubscribeRequest $request, string $email): JsonResponse
    {
        $userId = $request->user()?->id;
        $silent = $userId
            ? (bool) config('services.sendy.silent_authenticated', true)
            : (bool) config('services.sendy.silent_visitor', false);

        $fromPopup = $request->boolean('from_popup');
        $source = $fromPopup
            ? NewsletterEventSource::Popup
            : NewsletterEventSource::NewslettersForm;

        $ctx = NewsletterSubscriptionContext::fromRequest($source, $request, $userId);
        $ctx->silent = $silent;

        if ($fromPopup) {
            $variant = $request->input('popup_variant');
            $ctx->popupVariant = is_string($variant) && in_array($variant, ['A', 'B'], true) ? $variant : null;
            $trigger = $request->input('popup_trigger');
            $ctx->popupTrigger = is_string($trigger) && in_array($trigger, ['timer', 'exit_intent', 'scroll'], true)
                ? $trigger
                : null;
        }

        $result = $this->sendy->subscribe(
            email: $email,
            name: $request->string('name')->toString(),
            ctx: $ctx,
        );

        return response()->json([
            'success' => $result->success,
            'message' => $result->alreadySubscribed
                ? 'Você já está inscrito!'
                : ($result->success ? 'Inscrição realizada! Verifique seu email.' : 'Não foi possível inscrever agora. Tente novamente em instantes.'),
            'already_subscribed' => $result->alreadySubscribed,
        ]);
    }

    private function hasRecentSuccessfulSubscription(string $email): bool
    {
        return NewsletterSubscriptionEvent::query()
            ->where('email', $email)
            ->whereIn('action', [
                NewsletterEventAction::Subscribed->value,
                NewsletterEventAction::AlreadySubscribed->value,
            ])
            ->where('created_at', '>=', now()->subSeconds(60))
            ->exists();
    }

    public function trackEvent(Request $request): JsonResponse
    {
        if (! $this->sendy->isEnabled()) {
            return response()->json(['ok' => false], 503);
        }

        $validated = $request->validate([
            'action' => ['required', Rule::in([
                NewsletterEventAction::Impression->value,
                NewsletterEventAction::Dismissed->value,
            ])],
            'variant' => ['nullable', Rule::in(['A', 'B'])],
            'trigger' => ['nullable', Rule::in(['timer', 'exit_intent', 'scroll'])],
        ]);

        $referrer = $request->headers->get('referer');
        $userAgent = $request->userAgent();

        NewsletterSubscriptionEvent::query()->create([
            'email' => '',
            'action' => $validated['action'],
            'source' => NewsletterEventSource::Popup->value,
            'popup_variant' => $validated['variant'] ?? null,
            'popup_trigger' => $validated['trigger'] ?? null,
            'ip' => $request->ip(),
            'user_agent' => $userAgent !== null ? substr($userAgent, 0, 512) : null,
            'referrer' => $referrer !== null ? substr($referrer, 0, 1024) : null,
            'page_url' => $referrer !== null ? substr($referrer, 0, 512) : null,
        ]);

        return response()->json(['ok' => true]);
    }
}
