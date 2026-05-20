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

class SubscribeToSendyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    /** @var array<int, int> */
    public array $backoff = [30, 120];

    public bool $failOnTimeout = true;

    public function __construct(
        public string $email,
        public ?string $name,
        public NewsletterSubscriptionContext $ctx,
    ) {}

    public function handle(SendyService $sendy): void
    {
        $sendy->subscribe($this->email, $this->name, $this->ctx);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('SubscribeToSendyJob failed', [
            'email' => $this->email,
            'user_id' => $this->ctx->userId,
            'source' => $this->ctx->source->value,
            'error' => $exception?->getMessage(),
        ]);
    }
}
