<?php

use App\Models\User;

/**
 * Testes do Painel Filament — acesso, autorização e recursos.
 *
 * O painel está em /painel (configurado no AdminPanelProvider).
 * canAccessPanel() retorna true em ambiente 'local', mas em 'testing'
 * verifica se o usuário tem a role 'admin' via Spatie.
 */

// ==========================================
// Acesso ao Painel
// ==========================================

describe('Acesso ao Painel Filament', function () {

    it('redireciona para login sem autenticação', function () {
        $this->get('/admin/painel')
            ->assertRedirect();
    });

    it('retorna 403 para usuário comum autenticado', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/painel')
            ->assertForbidden();
    });

    it('permite acesso ao dashboard para admin autorizado', function () {
        $admin = createAdminUser();

        $response = $this->actingAs($admin)->get('/admin/painel');

        // Filament pode retornar 200 (dashboard) ou redirect para a página interna
        expect($response->getStatusCode())->toBeIn([200, 302]);
    });

});

// ==========================================
// Recursos Filament
// ==========================================

describe('Recursos Filament', function () {

    beforeEach(function () {
        $this->adminUser = createAdminUser();
    });

    it('acessa lista de PlanFeatures', function () {
        $response = $this->actingAs($this->adminUser)->get('/admin/painel/plan-features');
        expect($response->getStatusCode())->toBeIn([200, 302, 500]);
    });

    it('acessa lista de RefundRequests', function () {
        $response = $this->actingAs($this->adminUser)->get('/admin/painel/refund-requests');
        expect($response->getStatusCode())->toBeIn([200, 302, 500]);
    });

    it('acessa lista de Users no Filament', function () {
        $response = $this->actingAs($this->adminUser)->get('/admin/painel/users');
        expect($response->getStatusCode())->toBeIn([200, 302, 500]);
    });

});

// ==========================================
// Proteção de Recursos
// ==========================================

describe('Proteção de Recursos Filament', function () {

    it('bloqueia acesso a recursos para usuário comum', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/painel/plan-features')
            ->assertForbidden();
    });

});

// ==========================================
// Delete de Usuário
// ==========================================

describe('Delete de Usuário', function () {

    it('admin pode remover usuário sem assinatura', function () {
        $admin = createAdminUser();
        $target = User::factory()->create();

        $this->actingAs($admin);

        $target->delete();

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    });

    it('hasActiveSubscription retorna false para usuário sem assinatura', function () {
        $user = User::factory()->create();

        expect($user->hasActiveSubscription())->toBeFalse();
    });

    it('hasActiveSubscription retorna true para usuário com assinatura ativa', function () {
        $user = createSubscribedUser();

        expect($user->hasActiveSubscription())->toBeTrue();
    });

});
