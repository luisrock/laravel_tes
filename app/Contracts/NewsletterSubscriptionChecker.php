<?php

namespace App\Contracts;

interface NewsletterSubscriptionChecker
{
    public function isEnabled(): bool;

    public function isSubscribed(string $email): bool;
}
