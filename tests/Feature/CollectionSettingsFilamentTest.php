<?php

use App\Models\SiteSetting;
use App\Models\User;

/**
 * Testes da página Filament CollectionSettings.
 *
 * Segue o padrão de FilamentPanelTest: testa acesso e persistência de dados.
 */

// ==========================================
// Acesso à página
// ==========================================

describe('CollectionSettings — acesso', function () {

    it('redireciona visitante não autenticado', function () {
        $this->get('/admin/painel/collection-settings')
            ->assertRedirect();
    });

    it('retorna 403 para usuário comum', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/painel/collection-settings')
            ->assertForbidden();
    });

    it('permite acesso ao admin', function () {
        $admin = createAdminUser();

        $response = $this->actingAs($admin)->get('/admin/painel/collection-settings');

        expect($response->getStatusCode())->toBeIn([200, 302]);
    });

});

// ==========================================
// Persistência dos settings
// ==========================================

describe('CollectionSettings — persistência via SiteSetting', function () {

    it('CollectionSettingsSeeder insere os valores default', function () {
        $this->artisan('db:seed', ['--class' => 'CollectionSettingsSeeder', '--force' => true])
            ->assertSuccessful();

        expect(SiteSetting::get('collections_registered_max'))->toBe('3')
            ->and(SiteSetting::get('collections_registered_items_max'))->toBe('15')
            ->and(SiteSetting::get('collections_pro_max'))->toBe('10')
            ->and(SiteSetting::get('collections_pro_items_max'))->toBe('50')
            ->and(SiteSetting::get('collections_premium_max'))->toBe('-1')
            ->and(SiteSetting::get('collections_premium_items_max'))->toBe('-1');
    });

    it('CollectionSettingsSeeder não sobrescreve valores já existentes', function () {
        SiteSetting::set('collections_registered_max', '99');

        $this->artisan('db:seed', ['--class' => 'CollectionSettingsSeeder', '--force' => true])
            ->assertSuccessful();

        // firstOrCreate: não deve sobrescrever
        expect(SiteSetting::get('collections_registered_max'))->toBe('99');
    });

    it('SiteSetting::set persiste e SiteSetting::get recupera os valores', function () {
        SiteSetting::set('collections_registered_max', '5');
        SiteSetting::set('collections_pro_max', '20');
        SiteSetting::set('collections_premium_max', '0');

        expect(SiteSetting::get('collections_registered_max'))->toBe('5')
            ->and(SiteSetting::get('collections_pro_max'))->toBe('20')
            ->and(SiteSetting::get('collections_premium_max'))->toBe('0');
    });

});
