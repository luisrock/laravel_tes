<?php

namespace App\Services\Sendy;

final class SendyResult
{
    public function __construct(
        public bool $success,
        public string $message,
        public bool $alreadySubscribed = false,
    ) {}

    public static function success(string $message = 'true'): self
    {
        return new self(success: true, message: $message);
    }

    public static function alreadySubscribed(): self
    {
        return new self(
            success: true,
            message: 'Already subscribed.',
            alreadySubscribed: true,
        );
    }

    public static function failure(string $message): self
    {
        return new self(success: false, message: $message);
    }
}
