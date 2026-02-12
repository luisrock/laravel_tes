<?php

use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

/**
 * Testes de Middleware — verificam que cada middleware funciona isoladamente.
 *
 * Usa rotas temporárias registradas no setUp para testar middlewares
 * sem depender das rotas reais da aplicação.
 */

// ==========================================
// AdminMiddleware (admin_access)
// ==========================================

describe('AdminMiddleware', function () {

    beforeEach(function () {
        Route::middleware(['web', 'admin_access:manage_all'])->get('/__test/admin', function () {
            return response('admin-ok');
        });
    });

    it('retorna 403 para usuário não autenticado', function () {
        $this->get('/__test/admin')
            ->assertForbidden();
    });

    it('retorna 403 para usuário sem permissão', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/__test/admin')
            ->assertForbidden();
    });

    it('permite acesso para admin com permissão manage_all', function () {
        $admin = createAdminUser();

        $this->actingAs($admin)
            ->get('/__test/admin')
            ->assertOk()
            ->assertSee('admin-ok');
    });

});

// ==========================================
// BearerTokenMiddleware (bearer.token)
// ==========================================

describe('BearerTokenMiddleware', function () {

    beforeEach(function () {
        Route::middleware(['api', 'bearer.token'])->get('/__test/api', function () {
            return response()->json(['status' => 'ok']);
        });
    });

    it('retorna 401 sem header Authorization', function () {
        $this->getJson('/__test/api')
            ->assertUnauthorized()
            ->assertJson(['success' => false]);
    });

    it('retorna 401 com token inválido', function () {
        $this->getJson('/__test/api', ['Authorization' => 'Bearer token-invalido'])
            ->assertUnauthorized()
            ->assertJson(['success' => false]);
    });

    it('permite acesso com token válido', function () {
        $validToken = env('API_TOKEN', 'your-secret-token-here');

        $this->getJson('/__test/api', ['Authorization' => "Bearer {$validToken}"])
            ->assertOk()
            ->assertJson(['status' => 'ok']);
    });

});

// ==========================================
// EnsureUserIsSubscribed (subscribed)
// ==========================================

describe('EnsureUserIsSubscribed', function () {

    beforeEach(function () {
        Route::middleware(['web', 'subscribed'])->get('/__test/subscribed', function () {
            return response('subscriber-ok');
        });
    });

    it('redireciona para login sem autenticação', function () {
        $this->get('/__test/subscribed')
            ->assertRedirect(route('login'));
    });

    it('redireciona para planos sem assinatura ativa', function () {
        Config::set('cashier.key', 'pk_test_fake');
        Config::set('cashier.secret', 'sk_test_fake');
        Config::set('subscription.tier_product_ids', ['prod_test']);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/__test/subscribed')
            ->assertRedirect(route('subscription.plans'));
    });

    it('permite acesso com assinatura ativa', function () {
        Config::set('cashier.key', 'pk_test_fake');
        Config::set('cashier.secret', 'sk_test_fake');
        Config::set('subscription.tier_product_ids', ['prod_test']);

        $user = createSubscribedUser();

        $this->actingAs($user)
            ->get('/__test/subscribed')
            ->assertOk()
            ->assertSee('subscriber-ok');
    });

});

// ==========================================
// EnsureUserHasFeature (feature)
// ==========================================

describe('EnsureUserHasFeature', function () {

    beforeEach(function () {
        Route::middleware(['web', 'feature:no_ads'])->get('/__test/feature', function () {
            return response('feature-ok');
        });
    });

    it('redireciona para login sem autenticação', function () {
        $this->get('/__test/feature')
            ->assertRedirect(route('login'));
    });

    it('redireciona para planos sem a feature', function () {
        Config::set('cashier.key', 'pk_test_fake');
        Config::set('cashier.secret', 'sk_test_fake');
        Config::set('subscription.tier_product_ids', ['prod_test']);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/__test/feature')
            ->assertRedirect(route('subscription.plans'));
    });

    it('permite acesso com feature habilitada', function () {
        Config::set('cashier.key', 'pk_test_fake');
        Config::set('cashier.secret', 'sk_test_fake');
        Config::set('subscription.tier_product_ids', ['prod_test']);

        $user = createSubscribedUser('prod_test');

        // Criar PlanFeature para o produto
        \App\Models\PlanFeature::create([
            'stripe_product_id' => 'prod_test',
            'feature_key' => 'no_ads',
            'feature_value' => '1',
        ]);

        $this->actingAs($user)
            ->get('/__test/feature')
            ->assertOk()
            ->assertSee('feature-ok');
    });

});

// ==========================================
// EnsureSubscriptionConfigured (subscription.configured)
// ==========================================

describe('EnsureSubscriptionConfigured', function () {

    beforeEach(function () {
        Route::middleware(['web', 'subscription.configured'])->get('/__test/configured', function () {
            return response('configured-ok');
        });
    });

    it('redireciona quando tier_product_ids está vazio', function () {
        Config::set('subscription.tier_product_ids', []);
        Config::set('cashier.key', 'pk_test');
        Config::set('cashier.secret', 'sk_test');

        $this->get('/__test/configured')
            ->assertRedirect(route('searchpage'));
    });

    it('retorna 503 JSON quando não configurado e request é JSON', function () {
        Config::set('subscription.tier_product_ids', []);
        Config::set('cashier.key', 'pk_test');
        Config::set('cashier.secret', 'sk_test');

        $this->getJson('/__test/configured')
            ->assertStatus(503);
    });

    it('permite acesso quando configurado', function () {
        Config::set('subscription.tier_product_ids', ['prod_test']);
        Config::set('cashier.key', 'pk_test');
        Config::set('cashier.secret', 'sk_test');

        $this->get('/__test/configured')
            ->assertOk()
            ->assertSee('configured-ok');
    });

});
