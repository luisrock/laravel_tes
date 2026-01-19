<?php

namespace Tests\Feature;

use App\Jobs\SendRenewalReminders;
use App\Models\User;
use App\Notifications\SubscriptionRenewingSoonNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Cashier\Subscription;
use Tests\TestCase;

class SubscriptionRenewalReminderJobTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_job_sends_reminders_for_active_subscriptions_7_days_out(): void
    {
        Notification::fake();
        Carbon::setTestNow(Carbon::create(2026, 1, 19, 9, 0, 0));

        $user = User::factory()->create();
        $renewsAt = Carbon::now()->addDays(7)->startOfDay()->addHours(12);

        Subscription::create([
            'user_id' => $user->id,
            'name' => 'default',
            'stripe_id' => 'sub_active_1',
            'stripe_status' => 'active',
            'stripe_price' => 'price_test_1',
            'quantity' => 1,
            'current_period_end' => $renewsAt,
        ]);

        (new SendRenewalReminders())->handle();

        Notification::assertSentTo($user, SubscriptionRenewingSoonNotification::class);
    }

    public function test_job_ignores_non_active_subscriptions(): void
    {
        Notification::fake();
        Carbon::setTestNow(Carbon::create(2026, 1, 19, 9, 0, 0));

        $user = User::factory()->create();
        $renewsAt = Carbon::now()->addDays(7)->startOfDay()->addHours(12);

        Subscription::create([
            'user_id' => $user->id,
            'name' => 'default',
            'stripe_id' => 'sub_past_due',
            'stripe_status' => 'past_due',
            'stripe_price' => 'price_test_2',
            'quantity' => 1,
            'current_period_end' => $renewsAt,
        ]);

        (new SendRenewalReminders())->handle();

        Notification::assertNotSentTo($user, SubscriptionRenewingSoonNotification::class);
    }

    public function test_job_does_not_filter_by_ends_at(): void
    {
        Notification::fake();
        Carbon::setTestNow(Carbon::create(2026, 1, 19, 9, 0, 0));

        $user = User::factory()->create();
        $renewsAt = Carbon::now()->addDays(7)->startOfDay()->addHours(12);

        Subscription::create([
            'user_id' => $user->id,
            'name' => 'default',
            'stripe_id' => 'sub_active_with_ends_at',
            'stripe_status' => 'active',
            'stripe_price' => 'price_test_3',
            'quantity' => 1,
            'current_period_end' => $renewsAt,
            'ends_at' => Carbon::now()->addDays(1),
        ]);

        (new SendRenewalReminders())->handle();

        Notification::assertSentTo($user, SubscriptionRenewingSoonNotification::class);
    }
}
