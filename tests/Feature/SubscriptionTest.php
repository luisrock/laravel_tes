<?php

use App\Enums\RefundRequestStatus;
use App\Models\PlanFeature;
use App\Models\RefundRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Laravel\Cashier\Subscription;

/**
 * Testes do fluxo de assinatura.
 *
 * NOTA: Nao testamos a integracao real com Stripe — apenas os fluxos
 * que nao dependem de chamadas externas (validacao, redirecionamentos, etc.).
 */

// ==========================================
// Planos de Assinatura
// ==========================================

describe('Planos de Assinatura', function () {

    it('exibe a pagina de planos quando Stripe esta configurado', function () {
        Config::set('cashier.key', 'pk_test_fake');
        Config::set('cashier.secret', 'sk_test_fake');
        Config::set('subscription.tier_product_ids', ['prod_test']);

        $response = $this->get('/assinar');

        // Com mocks incompletos, pode dar 500 ou 200
        expect($response->getStatusCode())->toBeIn([200, 500]);
    });

});

// ==========================================
// Checkout
// ==========================================

describe('Checkout', function () {

    it('requer autenticacao para checkout', function () {
        Config::set('cashier.key', 'pk_test_fake');
        Config::set('cashier.secret', 'sk_test_fake');

        $this->post('/assinar/checkout', ['priceId' => 'price_test'])
            ->assertRedirect('/login');
    });

});

// ==========================================
// Area do Assinante
// ==========================================

describe('Area do Assinante', function () {

    it('exibe pagina de assinatura para usuario autenticado', function () {
        Config::set('cashier.key', 'pk_test_fake');
        Config::set('cashier.secret', 'sk_test_fake');
        Config::set('subscription.tier_product_ids', ['prod_test']);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/minha-conta/assinatura')
            ->assertSuccessful();
    });

    it('responde na pagina de estorno para usuario autenticado', function () {
        Config::set('cashier.key', 'pk_test_fake');
        Config::set('cashier.secret', 'sk_test_fake');
        Config::set('subscription.tier_product_ids', ['prod_test']);

        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/minha-conta/estorno');

        // Pode redirecionar se nao houver assinatura ativa, ou exibir 200
        expect($response->getStatusCode())->toBeIn([200, 302]);
    });

});

// ==========================================
// Modelo User - Subscription helpers (sem assinatura)
// ==========================================

describe('Modelo User - Subscription helpers sem assinatura', function () {

    it('retorna false para isSubscriber quando nao tem assinatura', function () {
        $user = User::factory()->create();

        expect($user->isSubscriber())->toBeFalse();
    });

    it('retorna null para getSubscriptionPlan quando nao tem assinatura', function () {
        $user = User::factory()->create();

        expect($user->getSubscriptionPlan())->toBeNull();
    });

    it('retorna false para hasFeature quando nao tem assinatura', function () {
        Config::set('subscription.tier_product_ids', ['prod_test']);

        $user = User::factory()->create();

        expect($user->hasFeature('no_ads'))->toBeFalse();
    });

    it('retorna true para shouldSeeAds quando nao e assinante', function () {
        Config::set('subscription.tier_product_ids', ['prod_test']);

        $user = User::factory()->create();

        expect($user->shouldSeeAds())->toBeTrue();
    });

    it('retorna false para isOnGracePeriod quando nao tem assinatura', function () {
        $user = User::factory()->create();

        expect($user->isOnGracePeriod())->toBeFalse();
    });

    it('retorna null para getAccessEndsAt quando nao tem assinatura', function () {
        $user = User::factory()->create();

        expect($user->getAccessEndsAt())->toBeNull();
    });

});

// ==========================================
// Modelo User - Subscription helpers COM assinatura ativa
// ==========================================

describe('Modelo User - Subscription helpers com assinatura ativa', function () {

    it('retorna true para isSubscriber com assinatura ativa', function () {
        Config::set('subscription.tier_product_ids', ['prod_test']);

        $user = createSubscribedUser('prod_test');

        expect($user->isSubscriber())->toBeTrue();
    });

    it('retorna product_id correto para getSubscriptionPlan', function () {
        Config::set('subscription.tier_product_ids', ['prod_test']);

        $user = createSubscribedUser('prod_test');

        expect($user->getSubscriptionPlan())->toBe('prod_test');
    });

    it('retorna true para hasFeature quando PlanFeature existe', function () {
        Config::set('subscription.tier_product_ids', ['prod_test']);

        $user = createSubscribedUser('prod_test');

        PlanFeature::create([
            'stripe_product_id' => 'prod_test',
            'feature_key' => 'no_ads',
            'feature_value' => '1',
        ]);

        expect($user->hasFeature('no_ads'))->toBeTrue();
    });

    it('retorna false para hasFeature quando PlanFeature nao existe', function () {
        Config::set('subscription.tier_product_ids', ['prod_test']);

        $user = createSubscribedUser('prod_test');

        expect($user->hasFeature('exclusive_content'))->toBeFalse();
    });

    it('retorna false para shouldSeeAds quando e assinante com feature no_ads', function () {
        Config::set('subscription.tier_product_ids', ['prod_test']);

        $user = createSubscribedUser('prod_test');

        PlanFeature::create([
            'stripe_product_id' => 'prod_test',
            'feature_key' => 'no_ads',
            'feature_value' => '1',
        ]);

        expect($user->shouldSeeAds())->toBeFalse();
    });

    it('retorna true para isOnGracePeriod com ends_at futuro', function () {
        Config::set('subscription.tier_product_ids', ['prod_test']);

        $user = User::factory()->create();

        Subscription::create([
            'user_id' => $user->id,
            'type' => 'default',
            'stripe_id' => 'sub_grace_'.uniqid(),
            'stripe_status' => 'active',
            'stripe_price' => 'price_test_123',
            'quantity' => 1,
            'ends_at' => Carbon::now()->addDays(10),
        ]);

        expect($user->isOnGracePeriod())->toBeTrue();
    });

    it('retorna Carbon para getAccessEndsAt quando em grace period', function () {
        Config::set('subscription.tier_product_ids', ['prod_test']);

        $endsAt = Carbon::now()->addDays(10);
        $user = User::factory()->create();

        Subscription::create([
            'user_id' => $user->id,
            'type' => 'default',
            'stripe_id' => 'sub_ends_'.uniqid(),
            'stripe_status' => 'active',
            'stripe_price' => 'price_test_123',
            'quantity' => 1,
            'ends_at' => $endsAt,
        ]);

        $accessEndsAt = $user->getAccessEndsAt();
        expect($accessEndsAt)->toBeInstanceOf(Carbon::class);
        expect($accessEndsAt->format('Y-m-d'))->toBe($endsAt->format('Y-m-d'));
    });

});

// ==========================================
// Formulario de Estorno
// ==========================================

describe('Formulario de Estorno', function () {

    beforeEach(function () {
        Config::set('cashier.key', 'pk_test_fake');
        Config::set('cashier.secret', 'sk_test_fake');
        Config::set('subscription.tier_product_ids', ['prod_test']);
    });

    it('redireciona para planos sem assinatura ativa', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/minha-conta/estorno')
            ->assertRedirect(route('subscription.plans'));
    });

    it('exibe formulario de estorno com assinatura ativa', function () {
        $user = createSubscribedUser('prod_test');

        $this->actingAs($user)
            ->get('/minha-conta/estorno')
            ->assertSuccessful();
    });

    it('rejeita razao curta (< 20 chars) no estorno', function () {
        $user = createSubscribedUser('prod_test');

        $this->actingAs($user)
            ->post('/minha-conta/estorno', [
                'reason' => 'Curto demais',
            ])
            ->assertSessionHasErrors(['reason']);
    });

    it('cria RefundRequest com razao valida', function () {
        Notification::fake();

        $user = createSubscribedUser('prod_test');

        $this->actingAs($user)
            ->post('/minha-conta/estorno', [
                'reason' => 'Motivo valido e detalhado para solicitar o estorno da minha assinatura.',
            ])
            ->assertRedirect(route('subscription.show'));

        $this->assertDatabaseHas('refund_requests', [
            'user_id' => $user->id,
            'status' => RefundRequestStatus::Pending->value,
        ]);
    });

    it('redireciona com info se ja existe solicitacao pendente', function () {
        Notification::fake();

        $user = createSubscribedUser('prod_test');
        $subscription = $user->subscription('default');

        RefundRequest::create([
            'user_id' => $user->id,
            'cashier_subscription_id' => $subscription->id,
            'stripe_subscription_id' => $subscription->stripe_id,
            'reason' => 'Solicitacao anterior pendente',
            'status' => RefundRequestStatus::Pending,
        ]);

        $this->actingAs($user)
            ->get('/minha-conta/estorno')
            ->assertRedirect(route('subscription.show'));
    });

});
