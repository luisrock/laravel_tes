<?php

namespace App\Jobs\Newsletter;

use App\Services\Sendy\NewsletterSubscriptionContext;
use App\Services\Sendy\SendyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class UnsubscribeFromSendyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    /** @var array<int, int> */
    public array $backoff = [30, 120];

    public bool $failOnTimeout = true;

    public function __construct(
        public string $email,
        public NewsletterSubscriptionContext $ctx,
    ) {}

    public function handle(SendyService $sendy): void
    {
        $sendy->unsubscribe($this->email, $this->ctx);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('UnsubscribeFromSendyJob failed', [
            'email' => $this->email,
            'user_id' => $this->ctx->userId,
            'source' => $this->ctx->source->value,
            'error' => $exception?->getMessage(),
        ]);
    }
}
