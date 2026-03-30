<?php

use App\Models\User;
use Illuminate\Support\Facades\Config;

describe('Subscription Global Toggle', function () {

    it('bloqueia o acesso a /assinar quando desabilitado', function () {
        Config::set('subscription.enabled', false);

        $response = $this->get('/assinar');

        $response->assertNotFound();
    });

    it('bloqueia o acesso a painel de assinatura quando desabilitado', function () {
        Config::set('subscription.enabled', false);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/minha-conta/assinatura');

        $response->assertNotFound();
    });

    it('bloqueia webhooks do stripe quando desabilitado', function () {
        Config::set('subscription.enabled', false);

        $response = $this->postJson('/stripe/webhook', []);

        $response->assertNotFound();
    });

    it('nao exibe links de assinatura no footer quando desabilitado', function () {
        Config::set('subscription.enabled', false);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('searchpage'));

        $response->assertOk();
        $response->assertDontSee('Minha assinatura', false);
        $response->assertDontSee('Gerenciar pagamento', false);
        $response->assertDontSee('/minha-conta/assinatura', false);
    });

    it('exibe links de assinatura no footer quando habilitado', function () {
        Config::set('subscription.enabled', true);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('searchpage'));

        $response->assertOk();
        $response->assertSee('Minha assinatura', false);
        $response->assertSee('Gerenciar pagamento', false);
    });

    it('permite o acesso a /assinar quando habilitado', function () {
        Config::set('subscription.enabled', true);
        Config::set('cashier.key', 'pk_test_fake');
        Config::set('cashier.secret', 'sk_test_fake');
        Config::set('subscription.tier_product_ids', ['prod_test']);

        $response = $this->get('/assinar');

        // It might be 200 or 500 depending on Stripe mocks, but it should NOT be 404
        expect($response->getStatusCode())->not->toBe(404);
    });
});
