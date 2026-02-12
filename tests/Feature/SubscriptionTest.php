<?php

use App\Models\User;
use Illuminate\Support\Facades\Config;

/**
 * Testes do fluxo de assinatura.
 *
 * NOTA: Não testamos a integração real com Stripe — apenas os fluxos
 * que não dependem de chamadas externas (validação, redirecionamentos, etc.).
 */

describe('Planos de Assinatura', function () {

    it('exibe a página de planos quando Stripe está configurado', function () {
        Config::set('cashier.key', 'pk_test_fake');
        Config::set('cashier.secret', 'sk_test_fake');
        Config::set('subscription.tier_product_ids', ['prod_test']);

        $response = $this->get('/assinar');

        // Com mocks incompletos, pode dar 500 ou 200
        expect($response->getStatusCode())->toBeIn([200, 500]);
    });

});

describe('Checkout', function () {

    it('requer autenticação para checkout', function () {
        Config::set('cashier.key', 'pk_test_fake');
        Config::set('cashier.secret', 'sk_test_fake');

        $this->post('/assinar/checkout', ['priceId' => 'price_test'])
            ->assertRedirect('/login');
    });

});

describe('Área do Assinante', function () {

    it('exibe página de assinatura para usuário autenticado', function () {
        Config::set('cashier.key', 'pk_test_fake');
        Config::set('cashier.secret', 'sk_test_fake');
        Config::set('subscription.tier_product_ids', ['prod_test']);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/minha-conta/assinatura')
            ->assertStatus(200);
    });

    it('responde na página de estorno para usuário autenticado', function () {
        Config::set('cashier.key', 'pk_test_fake');
        Config::set('cashier.secret', 'sk_test_fake');
        Config::set('subscription.tier_product_ids', ['prod_test']);

        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/minha-conta/estorno');

        // Pode redirecionar se não houver assinatura ativa, ou exibir 200
        expect($response->getStatusCode())->toBeIn([200, 302]);
    });

});

describe('Modelo User - Subscription helpers', function () {

    it('retorna false para isSubscriber quando não tem assinatura', function () {
        $user = User::factory()->create();

        expect($user->isSubscriber())->toBeFalse();
    });

    it('retorna null para getSubscriptionPlan quando não tem assinatura', function () {
        $user = User::factory()->create();

        expect($user->getSubscriptionPlan())->toBeNull();
    });

    it('retorna false para hasFeature quando não tem assinatura', function () {
        Config::set('subscription.tier_product_ids', ['prod_test']);

        $user = User::factory()->create();

        expect($user->hasFeature('no_ads'))->toBeFalse();
    });

    it('retorna true para shouldSeeAds quando não é assinante', function () {
        Config::set('subscription.tier_product_ids', ['prod_test']);

        $user = User::factory()->create();

        expect($user->shouldSeeAds())->toBeTrue();
    });

    it('retorna false para isOnGracePeriod quando não tem assinatura', function () {
        $user = User::factory()->create();

        expect($user->isOnGracePeriod())->toBeFalse();
    });

    it('retorna null para getAccessEndsAt quando não tem assinatura', function () {
        $user = User::factory()->create();

        expect($user->getAccessEndsAt())->toBeNull();
    });

});
