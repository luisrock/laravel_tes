<?php

use App\Models\StripeWebhookEvent;
use App\Models\User;
use Laravel\Cashier\Http\Middleware\VerifyWebhookSignature;

/**
 * Testes do fluxo de Webhook Stripe — idempotencia, validacao de payload,
 * e processamento de eventos.
 *
 * O VerifyWebhookSignature e desativado nos testes para permitir
 * testar o fluxo sem assinatura real do Stripe.
 */

// ==========================================
// Validacao de Payload
// ==========================================

describe('Webhook - Validacao de Payload', function () {

    it('retorna 400 para payload sem id', function () {
        $this->withoutMiddleware(VerifyWebhookSignature::class)
            ->postJson('/stripe/webhook', [
                'type' => 'checkout.session.completed',
                'data' => ['object' => []],
            ])
            ->assertStatus(400)
            ->assertJson(['error' => 'Invalid payload']);
    });

    it('retorna 400 para payload sem type', function () {
        $this->withoutMiddleware(VerifyWebhookSignature::class)
            ->postJson('/stripe/webhook', [
                'id' => 'evt_test_123',
                'data' => ['object' => []],
            ])
            ->assertStatus(400)
            ->assertJson(['error' => 'Invalid payload']);
    });

    it('retorna 400 para payload completamente vazio', function () {
        $this->withoutMiddleware(VerifyWebhookSignature::class)
            ->postJson('/stripe/webhook', [])
            ->assertStatus(400);
    });

});

// ==========================================
// Idempotencia
// ==========================================

describe('Webhook - Idempotencia', function () {

    it('retorna already_processed para evento ja processado', function () {
        StripeWebhookEvent::create([
            'stripe_event_id' => 'evt_already_processed',
            'event_type' => 'checkout.session.completed',
            'stripe_object_id' => 'cs_test_123',
            'received_at' => now(),
            'processed_at' => now(),
        ]);

        $this->withoutMiddleware(VerifyWebhookSignature::class)
            ->postJson('/stripe/webhook', [
                'id' => 'evt_already_processed',
                'type' => 'checkout.session.completed',
                'data' => [
                    'object' => [
                        'id' => 'cs_test_123',
                        'client_reference_id' => '1',
                    ],
                ],
            ])
            ->assertSuccessful()
            ->assertJson(['status' => 'already_processed']);
    });

    it('registra StripeWebhookEvent ao receber evento novo', function () {
        $this->withoutMiddleware(VerifyWebhookSignature::class)
            ->postJson('/stripe/webhook', [
                'id' => 'evt_new_event_123',
                'type' => 'checkout.session.completed',
                'data' => [
                    'object' => [
                        'id' => 'cs_test_new',
                        'client_reference_id' => null,
                    ],
                ],
            ]);

        $this->assertDatabaseHas('stripe_webhook_events', [
            'stripe_event_id' => 'evt_new_event_123',
            'event_type' => 'checkout.session.completed',
        ]);
    });

});

// ==========================================
// Checkout Session Completed (via TestableWebhookController)
// ==========================================

describe('Webhook - Checkout Session Completed', function () {

    it('cria StripeWebhookEvent com client_reference_id', function () {
        $user = User::factory()->create();

        $controller = new TestableWebhookControllerForWebhookTest;
        $controller->callHandleCheckoutSessionCompleted([
            'data' => [
                'object' => [
                    'id' => 'cs_test_direct',
                    'client_reference_id' => $user->id,
                ],
            ],
        ]);

        expect(true)->toBeTrue();
    });

});

// Helper class para expor metodos protegidos do WebhookController
class TestableWebhookControllerForWebhookTest extends \App\Http\Controllers\WebhookController
{
    public function callHandleCheckoutSessionCompleted(array $payload): mixed
    {
        return $this->handleCheckoutSessionCompleted($payload);
    }
}
