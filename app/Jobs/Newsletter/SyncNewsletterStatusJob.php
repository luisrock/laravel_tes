<?php

namespace App\Jobs\Newsletter;

use App\Enums\SendyStatus;
use App\Models\User;
use App\Services\Sendy\SendyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SyncNewsletterStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    /** @var array<int, int> */
    public array $backoff = [30, 120];

    public bool $failOnTimeout = true;

    public function __construct(
        public int $userId,
    ) {}

    public function handle(SendyService $sendy): void
    {
        if (! Schema::hasColumn('users', 'newsletter_subscribed_at')) {
            return;
        }

        $user = User::query()->find($this->userId);

        if ($user === null) {
            return;
        }

        $status = $sendy->getStatus($user->email);

        $subscribedAt = ($status === SendyStatus::Subscribed) ? now() : null;

        $user->forceFill([
            'newsletter_subscribed_at' => $subscribedAt,
            'newsletter_synced_at' => now(),
        ])->save();
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('SyncNewsletterStatusJob failed', [
            'user_id' => $this->userId,
            'error' => $exception?->getMessage(),
        ]);
    }
}
