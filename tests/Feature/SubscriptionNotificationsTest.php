<?php

use App\Http\Controllers\WebhookController;
use App\Models\RefundRequest;
use App\Models\User;
use App\Notifications\RefundRequestReceivedNotification;
use App\Notifications\SubscriptionCanceledNotification;
use App\Notifications\WelcomeSubscriberNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use Laravel\Cashier\Subscription;

it('envia notificação de boas-vindas ao completar checkout', function () {
    Notification::fake();

    $user = User::factory()->create();
    $controller = new TestableWebhookController;

    $controller->callHandleCheckoutSessionCompleted([
        'data' => [
            'object' => [
                'id' => 'cs_test_123',
                'client_reference_id' => $user->id,
            ],
        ],
    ]);

    Notification::assertSentTo($user, WelcomeSubscriberNotification::class);
});

it('envia notificação de cancelamento quando cancel_at_period_end é ativado', function () {
    Notification::fake();

    $user = User::factory()->create();
    $user->forceFill(['stripe_id' => 'cus_123'])->save();

    $endsAt = Carbon::now()->addDays(10);
    $controller = new TestableWebhookController;

    expect($controller->callExtractUserId('customer.subscription.updated', [
        'customer' => 'cus_123',
    ]))->toBe($user->id);

    $controller->callHandleCustomerSubscriptionUpdated([
        'data' => [
            'object' => [
                'id' => 'sub_123',
                'customer' => 'cus_123',
                'cancel_at_period_end' => true,
                'current_period_end' => $endsAt->timestamp,
                'status' => 'active',
                'items' => [
                    'data' => [
                        [
                            'id' => 'si_test_123',
                            'price' => [
                                'id' => 'price_test_123',
                                'product' => 'prod_test_123',
                            ],
                            'quantity' => 1,
                        ],
                    ],
                ],
            ],
            'previous_attributes' => [
                'cancel_at_period_end' => false,
            ],
        ],
    ]);

    Notification::assertSentTo($user, SubscriptionCanceledNotification::class);
});

it('envia notificação de estorno ao criar solicitação', function () {
    Notification::fake();

    $user = User::factory()->create();

    $subscription = Subscription::create([
        'user_id' => $user->id,
        'type' => config('subscription.default_subscription_name', 'default'),
        'stripe_id' => 'sub_test_123',
        'stripe_status' => 'active',
        'stripe_price' => 'price_test_123',
        'quantity' => 1,
    ]);

    $this->app['config']->set('subscription.tier_product_ids', ['prod_test']);
    $this->app['config']->set('cashier.key', 'pk_test');
    $this->app['config']->set('cashier.secret', 'sk_test');

    $this->actingAs($user)
        ->post(route('refund.store'), [
            'reason' => str_repeat('Motivo válido. ', 2),
        ])
        ->assertRedirect(route('subscription.show'));

    Notification::assertSentTo($user, RefundRequestReceivedNotification::class);

    $this->assertDatabaseHas('refund_requests', [
        'user_id' => $user->id,
        'cashier_subscription_id' => $subscription->id,
        'status' => RefundRequest::STATUS_PENDING,
    ]);
});

// Helper class para expor métodos protegidos do WebhookController
class TestableWebhookController extends WebhookController
{
    public function callHandleCheckoutSessionCompleted(array $payload)
    {
        return $this->handleCheckoutSessionCompleted($payload);
    }

    public function callHandleCustomerSubscriptionUpdated(array $payload)
    {
        return $this->handleCustomerSubscriptionUpdated($payload);
    }

    public function callExtractUserId(string $eventType, array $data): ?int
    {
        return $this->extractUserId($eventType, $data);
    }
}
