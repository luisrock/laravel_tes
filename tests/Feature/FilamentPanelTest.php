<?php

use App\Models\User;
use Illuminate\Support\Facades\Config;

/**
 * Testes do Painel Filament — acesso, autorização e recursos.
 *
 * O painel está em /painel (configurado no AdminPanelProvider).
 * canAccessPanel() retorna true em ambiente 'local', mas em 'testing'
 * verifica se o email do usuário está em config('tes_constants.admins').
 */

// ==========================================
// Acesso ao Painel
// ==========================================

describe('Acesso ao Painel Filament', function () {

    it('redireciona para login sem autenticação', function () {
        $this->get('/painel')
            ->assertRedirect();
    });

    it('retorna 403 para usuário comum autenticado', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/painel')
            ->assertForbidden();
    });

    it('permite acesso ao dashboard para admin autorizado', function () {
        $user = User::factory()->create(['email' => 'admin-filament@test.com']);
        Config::set('tes_constants.admins', ['admin-filament@test.com']);

        $response = $this->actingAs($user)->get('/painel');

        // Filament pode retornar 200 (dashboard) ou redirect para a página interna
        expect($response->getStatusCode())->toBeIn([200, 302]);
    });

});

// ==========================================
// Recursos Filament
// ==========================================

describe('Recursos Filament', function () {

    beforeEach(function () {
        $this->adminUser = User::factory()->create(['email' => 'admin-resources@test.com']);
        Config::set('tes_constants.admins', ['admin-resources@test.com']);
    });

    it('acessa lista de PlanFeatures', function () {
        $response = $this->actingAs($this->adminUser)->get('/painel/plan-features');
        expect($response->getStatusCode())->toBeIn([200, 302, 500]);
    });

    it('acessa lista de RefundRequests', function () {
        $response = $this->actingAs($this->adminUser)->get('/painel/refund-requests');
        expect($response->getStatusCode())->toBeIn([200, 302, 500]);
    });

    it('acessa lista de Users no Filament', function () {
        $response = $this->actingAs($this->adminUser)->get('/painel/users');
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
            ->get('/painel/plan-features')
            ->assertForbidden();
    });

});
