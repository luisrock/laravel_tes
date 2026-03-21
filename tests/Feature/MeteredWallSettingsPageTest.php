<?php

use App\Models\SiteSetting;
use App\Models\User;

describe('Acesso à página MeteredWallSettings', function () {

    it('redireciona visitante não autenticado', function () {
        $this->get('/admin/painel/metered-wall-settings')
            ->assertRedirect();
    });

    it('retorna 403 para usuário comum', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/painel/metered-wall-settings')
            ->assertForbidden();
    });

    it('admin consegue acessar a página', function () {
        $admin = createAdminUser();

        $response = $this->actingAs($admin)->get('/admin/painel/metered-wall-settings');

        expect($response->getStatusCode())->toBeIn([200, 302]);
    });

});

describe('Salvamento das configurações', function () {

    it('salva metered_wall_enabled corretamente', function () {
        SiteSetting::set('metered_wall_enabled', '0');

        SiteSetting::set('metered_wall_enabled', '1');

        expect(SiteSetting::get('metered_wall_enabled'))->toBe('1');
    });

    it('salva metered_wall_daily_limit corretamente', function () {
        SiteSetting::set('metered_wall_daily_limit', '5');

        expect(SiteSetting::get('metered_wall_daily_limit'))->toBe('5');
    });

    it('atualiza valor existente', function () {
        SiteSetting::set('metered_wall_daily_limit', '3');
        SiteSetting::set('metered_wall_daily_limit', '10');

        expect(SiteSetting::get('metered_wall_daily_limit'))->toBe('10');
    });

});
