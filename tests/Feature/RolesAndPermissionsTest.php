<?php

use App\Models\User;
use Filament\Panel;
use Illuminate\Support\Facades\Config;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Testes focados no Spatie Laravel Permission e suas ramificacoes
 * no Model User (shouldSeeAds, canAccessPanel, e acesso a rotas).
 */
beforeEach(function () {
    // Limpar cache de permissoes
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    // Criar permissoes basicas
    $permissions = [
        'view_ai_analysis',
        'download_acordaos',
        'search',
        'use_ai',
        'manage_all',
        'manage_users',
        'ad_free',
    ];

    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, 'web');
    }

    // Criar roles e associar permissoes
    // 1. Admin
    $adminRole = Role::findOrCreate('admin', 'web');
    $adminRole->givePermissionTo(Permission::all());

    // 2. Premium
    $premiumRole = Role::findOrCreate('premium', 'web');
    $premiumRole->givePermissionTo(['search', 'view_ai_analysis', 'download_acordaos', 'use_ai', 'ad_free']);

    // 3. Subscriber
    $subscriberRole = Role::findOrCreate('subscriber', 'web');
    $subscriberRole->givePermissionTo(['search', 'view_ai_analysis', 'download_acordaos', 'ad_free']);

    // 4. Registered
    $registeredRole = Role::findOrCreate('registered', 'web');
    $registeredRole->givePermissionTo(['search', 'ad_free']);
});

describe('User Model Roles and Permissions', function () {

    it('shouldSeeAds() retorna true se nao tem permissao ad_free nem feature do cashier', function () {
        $user = User::factory()->create();
        // Nao assinamos nenhuma role
        expect($user->shouldSeeAds())->toBeTrue();
    });

    it('shouldSeeAds() retorna false se o usuario tem permissao ad_free via role registered', function () {
        $user = User::factory()->create();
        $user->assignRole('registered');

        // Verifica as permissoes
        expect($user->hasPermissionTo('ad_free'))->toBeTrue();
        expect($user->hasPermissionTo('view_ai_analysis'))->toBeFalse();

        // Com ad_free ativado (via Spatie), deve retornar false
        expect($user->shouldSeeAds())->toBeFalse();
    });

    it('shouldSeeAds() retorna false se o usuario tem permissao ad_free via role subscriber', function () {
        $user = User::factory()->create();
        $user->assignRole('subscriber');

        // Verifica as permissoes
        expect($user->hasPermissionTo('ad_free'))->toBeTrue();
        expect($user->hasPermissionTo('view_ai_analysis'))->toBeTrue();
        expect($user->hasPermissionTo('use_ai'))->toBeFalse();

        expect($user->shouldSeeAds())->toBeFalse();
    });

    it('shouldSeeAds() retorna false se o usuario tem permissao ad_free via role premium', function () {
        $user = User::factory()->create();
        $user->assignRole('premium');

        // Verifica as permissoes
        expect($user->hasPermissionTo('ad_free'))->toBeTrue();
        expect($user->hasPermissionTo('use_ai'))->toBeTrue();

        expect($user->shouldSeeAds())->toBeFalse();
    });

    it('shouldSeeAds() retorna false se o usuario tem permissao ad_free via role admin', function () {
        $user = User::factory()->create();
        $user->assignRole('admin');

        expect($user->hasPermissionTo('ad_free'))->toBeTrue();
        expect($user->hasPermissionTo('manage_all'))->toBeTrue();

        expect($user->shouldSeeAds())->toBeFalse();
    });

    it('canAccessPanel() retorna false para usuarios sem configuracao local e nao listados no config', function () {
        $user = User::factory()->create(['email' => 'comum@teste.com']);
        $panel = mock(Panel::class);

        // Simulamos ambiente de producao
        app()->detectEnvironment(fn () => 'production');

        Config::set('tes_constants.admins', ['admin@tesesesumulas.com.br']);

        expect($user->canAccessPanel($panel))->toBeFalse();
    });

    it('canAccessPanel() retorna true para administradores listados no config', function () {
        $user = User::factory()->create(['email' => 'admin@tesesesumulas.com.br']);
        $panel = mock(Panel::class);

        // Simulamos ambiente de producao
        app()->detectEnvironment(fn () => 'production');

        Config::set('tes_constants.admins', ['admin@tesesesumulas.com.br']);

        expect($user->canAccessPanel($panel))->toBeTrue();
    });

});
