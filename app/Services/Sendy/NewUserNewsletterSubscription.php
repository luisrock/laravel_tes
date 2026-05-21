<?php

namespace App\Services\Sendy;

use App\Enums\NewsletterEventSource;
use App\Models\User;

final class NewUserNewsletterSubscription
{
    public function __construct(private SendyService $sendy) {}

    public function subscribeNewUser(User $user, NewsletterEventSource $source): void
    {
        if (! $this->sendy->isEnabled()) {
            return;
        }

        $ctx = NewsletterSubscriptionContext::fromRequest($source, request(), $user->id);
        $ctx->silent = (bool) config('services.sendy.silent_authenticated', true);

        $result = $this->sendy->subscribe($user->email, $user->name, $ctx);

        $toast = ($result->success || $result->alreadySubscribed) ? 'subscribed' : 'invite';

        session(['newsletter.registration_toast' => $toast]);
    }
}
