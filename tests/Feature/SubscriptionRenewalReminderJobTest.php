<?php

use App\Jobs\SendRenewalReminders;
use App\Models\User;
use App\Notifications\SubscriptionRenewingSoonNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use Laravel\Cashier\Subscription;

afterEach(function () {
    Carbon::setTestNow();
});

it('envia lembrete para assinaturas ativas com renovação em 7 dias', function () {
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
});

it('ignora assinaturas não ativas', function () {
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
});

it('não filtra por ends_at', function () {
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
});
