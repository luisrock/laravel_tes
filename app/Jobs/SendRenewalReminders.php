<?php

namespace App\Jobs;

use App\Notifications\SubscriptionRenewingSoonNotification;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Subscription;

class SendRenewalReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $daysAhead = 7;
        $targetDate = Carbon::now()->addDays($daysAhead)->startOfDay();
        $endOfTargetDate = $targetDate->copy()->endOfDay();

        $subscriptions = Subscription::where('stripe_status', 'active')
            ->whereBetween('current_period_end', [$targetDate, $endOfTargetDate])
            ->with('user')
            ->get();

        foreach ($subscriptions as $subscription) {
            if (! $subscription->user) {
                continue;
            }

            try {
                $renewsAt = $subscription->current_period_end instanceof Carbon
                    ? $subscription->current_period_end
                    : Carbon::parse($subscription->current_period_end);

                $subscription->user->notify(
                    new SubscriptionRenewingSoonNotification($renewsAt)
                );

                Log::info('Renewal reminder sent', [
                    'user_id' => $subscription->user->id,
                    'renews_at' => $renewsAt,
                ]);
            } catch (Exception $e) {
                Log::error('Failed to send renewal reminder', [
                    'user_id' => $subscription->user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
