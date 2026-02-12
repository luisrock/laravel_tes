<?php

use App\Models\User;
use App\Services\StripeService;
use Illuminate\Support\Facades\Cache;

/**
 * Testes de integracao com a API real do Stripe (modo teste).
 *
 * Estes testes usam as chaves de teste do .env para validar
 * a integracao real com o Stripe, sem mocks.
 *
 * NOTA: Sao mais lentos que testes unitarios (~1-3s cada).
 */
beforeEach(function () {
    Cache::flush();
});

// ==========================================
// StripeService - Integracao com API real
// ==========================================

describe('StripeService - Integracao com API real', function () {

    it('retorna planos formatados do Stripe com produtos reais', function () {
        $stripeService = app(StripeService::class);
        $plans = $stripeService->getFormattedPlans();

        expect($plans)->toBeArray()->not->toBeEmpty();

        foreach ($plans as $tier => $plan) {
            expect($plan)
                ->toHaveKeys(['product_id', 'name', 'description', 'prices'])
                ->and($plan['product_id'])->toStartWith('prod_')
                ->and($plan['name'])->toBeString()->not->toBeEmpty()
                ->and($plan['prices'])->toBeArray()->not->toBeEmpty();

            foreach ($plan['prices'] as $interval => $price) {
                expect($price)
                    ->toHaveKeys(['id', 'amount', 'currency', 'interval'])
                    ->and($price['id'])->toStartWith('price_')
                    ->and($price['amount'])->toBeGreaterThan(0);
            }
        }
    });

    it('retorna price IDs validos dos produtos configurados', function () {
        $stripeService = app(StripeService::class);
        $allowedIds = $stripeService->getAllowedPriceIds();

        expect($allowedIds)->toBeArray()->not->toBeEmpty();

        foreach ($allowedIds as $priceId) {
            expect($priceId)->toStartWith('price_');
        }
    });

    it('valida price ID existente como valido', function () {
        $stripeService = app(StripeService::class);
        $allowedIds = $stripeService->getAllowedPriceIds();

        expect($stripeService->isValidPriceId($allowedIds[0]))->toBeTrue();
    });

    it('rejeita price ID inexistente como invalido', function () {
        $stripeService = app(StripeService::class);

        expect($stripeService->isValidPriceId('price_invalido_xyz'))->toBeFalse();
    });

    it('limpa cache corretamente', function () {
        $stripeService = app(StripeService::class);

        $stripeService->getFormattedPlans();
        expect(Cache::has('stripe_formatted_plans'))->toBeTrue();

        $stripeService->clearCache();
        expect(Cache::has('stripe_formatted_plans'))->toBeFalse();
    });

});

// ==========================================
// Pagina de Planos - Integracao com Stripe
// ==========================================

describe('Pagina de Planos - Integracao com Stripe', function () {

    it('exibe pagina de planos com dados reais do Stripe', function () {
        $response = $this->get('/assinar');

        $response->assertSuccessful();
        $response->assertViewHas('plans');

        $plans = $response->viewData('plans');
        expect($plans)->toBeArray()->not->toBeEmpty();
    });

});

// ==========================================
// Checkout - Integracao com Stripe
// ==========================================

describe('Checkout - Integracao com Stripe', function () {

    it('redireciona usuario nao autenticado para login', function () {
        $stripeService = app(StripeService::class);
        $priceId = $stripeService->getAllowedPriceIds()[0];

        $this->post('/assinar/checkout', ['priceId' => $priceId])
            ->assertRedirect('/login');
    });

    it('rejeita checkout com price ID invalido', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/assinar/checkout', ['priceId' => 'price_invalido_xyz']);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    });

    it('rejeita checkout sem price ID', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/assinar/checkout', [])
            ->assertSessionHasErrors(['priceId']);
    });

    it('inicia checkout com price ID valido e redireciona para Stripe', function () {
        $stripeService = app(StripeService::class);
        $priceId = $stripeService->getAllowedPriceIds()[0];

        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/assinar/checkout', ['priceId' => $priceId]);

        expect($response->getStatusCode())->toBe(303);

        $location = $response->headers->get('Location');
        expect($location)->toStartWith('https://checkout.stripe.com');

        expect($user->fresh()->stripe_id)->not->toBeNull();
    });

    it('redireciona assinante existente para billing portal em vez de checkout', function () {
        $stripeService = app(StripeService::class);
        $priceId = $stripeService->getAllowedPriceIds()[0];

        $user = User::factory()->create();
        $user->createAsStripeCustomer();

        $subscription = \Laravel\Cashier\Subscription::create([
            'user_id' => $user->id,
            'type' => config('subscription.default_subscription_name', 'default'),
            'stripe_id' => 'sub_test_'.uniqid(),
            'stripe_status' => 'active',
            'stripe_price' => $priceId,
            'quantity' => 1,
        ]);

        $subscription->items()->create([
            'stripe_id' => 'si_test_'.uniqid(),
            'stripe_product' => config('subscription.tier_product_ids')[0],
            'stripe_price' => $priceId,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($user)
            ->post('/assinar/checkout', ['priceId' => $priceId]);

        $response->assertRedirect();

        $location = $response->headers->get('Location');
        expect($location)->toContain('billing.stripe.com');
    });

});

// ==========================================
// Billing Portal - Integracao com Stripe
// ==========================================

describe('Billing Portal - Integracao com Stripe', function () {

    it('redireciona para Stripe Billing Portal com customer real', function () {
        $user = User::factory()->create();
        $user->createAsStripeCustomer();

        $response = $this->actingAs($user)
            ->get('/minha-conta/assinatura/portal');

        $response->assertRedirect();

        $location = $response->headers->get('Location');
        expect($location)->toContain('billing.stripe.com');
    });

});

// ==========================================
// Paginas de callback (logica local)
// ==========================================

describe('Paginas de callback e status', function () {

    it('redireciona para planos quando success nao tem session_id', function () {
        $this->get('/assinar/sucesso')
            ->assertRedirect(route('subscription.plans'));
    });

    it('exibe pagina de sucesso com session_id', function () {
        $response = $this->get('/assinar/sucesso?session_id=cs_test_fake123');

        $response->assertSuccessful();
        $response->assertViewHas('sessionId', 'cs_test_fake123');
        $response->assertViewHas('isProcessed', false);
    });

    it('exibe pagina de cancelamento', function () {
        $this->get('/assinar/cancelado')
            ->assertSuccessful()
            ->assertSee('Checkout Cancelado');
    });

    it('retorna erro 400 sem session_id no check-status', function () {
        $this->getJson('/assinar/status')
            ->assertStatus(400)
            ->assertJson(['status' => 'error']);
    });

    it('retorna status processing com session_id no check-status', function () {
        $this->getJson('/assinar/status?session_id=cs_test_fake123')
            ->assertSuccessful()
            ->assertJson(['status' => 'processing']);
    });

    it('retorna status completed quando webhook ja processou a sessao', function () {
        \App\Models\StripeWebhookEvent::create([
            'stripe_event_id' => 'evt_test_'.uniqid(),
            'event_type' => 'checkout.session.completed',
            'stripe_object_id' => 'cs_test_processed_123',
            'received_at' => now(),
            'processed_at' => now(),
        ]);

        $this->getJson('/assinar/status?session_id=cs_test_processed_123')
            ->assertSuccessful()
            ->assertJson(['status' => 'completed']);
    });

});

// ==========================================
// Pagina de status da assinatura
// ==========================================

describe('Pagina de status da assinatura', function () {

    it('exibe pagina de assinatura para usuario sem assinatura', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/minha-conta/assinatura')
            ->assertSuccessful()
            ->assertViewHas('isSubscriber', false);
    });

    it('exibe pagina de assinatura para usuario com assinatura ativa', function () {
        $user = createSubscribedUser(config('subscription.tier_product_ids')[0]);

        $this->actingAs($user)
            ->get('/minha-conta/assinatura')
            ->assertSuccessful()
            ->assertViewHas('isSubscriber', true);
    });

    it('requer autenticacao para pagina de assinatura', function () {
        $this->get('/minha-conta/assinatura')
            ->assertRedirect('/login');
    });

});
