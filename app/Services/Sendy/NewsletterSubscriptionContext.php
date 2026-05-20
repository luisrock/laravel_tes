<?php

namespace App\Services\Sendy;

use App\Enums\NewsletterEventSource;
use Illuminate\Http\Request;

final class NewsletterSubscriptionContext
{
    public function __construct(
        public NewsletterEventSource $source,
        public ?int $userId = null,
        public ?string $ip = null,
        public ?string $userAgent = null,
        public ?string $referrer = null,
        public ?string $pageUrl = null,
        public ?string $popupVariant = null,
        public ?string $popupTrigger = null,
        public bool $silent = false,
    ) {}

    public static function fromRequest(
        NewsletterEventSource $source,
        Request $request,
        ?int $userId = null,
    ): self {
        $referrer = $request->headers->get('referer');
        $userAgent = $request->userAgent();

        return new self(
            source: $source,
            userId: $userId,
            ip: $request->ip(),
            userAgent: $userAgent !== null ? substr($userAgent, 0, 512) : null,
            referrer: $referrer !== null ? substr($referrer, 0, 1024) : null,
            pageUrl: $referrer !== null ? substr($referrer, 0, 512) : null,
        );
    }
}
