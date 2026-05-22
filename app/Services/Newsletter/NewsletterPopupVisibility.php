<?php

namespace App\Services\Newsletter;

use App\Contracts\NewsletterSubscriptionChecker;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

final class NewsletterPopupVisibility
{
    public function __construct(
        private NewsletterSubscriptionChecker $sendy,
    ) {}

    public function shouldRender(): bool
    {
        if (! $this->popupFlagsEnabled()) {
            return false;
        }

        if (! Auth::check()) {
            return true;
        }

        /** @var User $user */
        $user = Auth::user();

        return $this->authenticatedUserEligible($user);
    }

    private function popupFlagsEnabled(): bool
    {
        return SiteSetting::getAsBool('newsletter_integration_enabled', false)
            && SiteSetting::getAsBool('newsletter_popup_enabled', false);
    }

    private function authenticatedUserEligible(User $user): bool
    {
        try {
            if (! $this->sendy->isEnabled()) {
                return ! $user->wantsNewsletter();
            }

            return ! $this->sendy->isSubscribed($user->email);
        } catch (Throwable $e) {
            Log::warning('Newsletter popup eligibility: Sendy check failed, using cache fallback', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return ! $user->wantsNewsletter();
        }
    }
}
