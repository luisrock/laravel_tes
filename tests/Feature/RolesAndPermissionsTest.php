<?php

use App\Models\User;
use Filament\Panel;
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
    $registeredRole->givePermissionTo(['search', 'ad_free', 'view_ai_analysis']);
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
        expect($user->hasPermissionTo('view_ai_analysis'))->toBeTrue();

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

        expect($user->canAccessPanel($panel))->toBeFalse();
    });

    it('canAccessPanel() retorna true para administradores com role admin', function () {
        $admin = createAdminUser();
        $panel = mock(Panel::class);

        // Simulamos ambiente de producao
        app()->detectEnvironment(fn () => 'production');

        expect($admin->canAccessPanel($panel))->toBeTrue();
    });

    it('role registered tem view_ai_analysis (registerwall ativo)', function () {
        $role = Role::findByName('registered', 'web');
        expect($role->hasPermissionTo('view_ai_analysis'))->toBeTrue();
    });

    it('usuario sem role nao tem view_ai_analysis', function () {
        $user = User::factory()->create();
        expect($user->hasPermissionTo('view_ai_analysis'))->toBeFalse();
    });

    it('usuario com role registered tem view_ai_analysis', function () {
        $user = User::factory()->create();
        $user->assignRole('registered');
        expect($user->hasPermissionTo('view_ai_analysis'))->toBeTrue();
    });

    it('usuario com role subscriber tem view_ai_analysis', function () {
        $user = User::factory()->create();
        $user->assignRole('subscriber');
        expect($user->hasPermissionTo('view_ai_analysis'))->toBeTrue();
    });

    it('usuario com role premium tem view_ai_analysis', function () {
        $user = User::factory()->create();
        $user->assignRole('premium');
        expect($user->hasPermissionTo('view_ai_analysis'))->toBeTrue();
    });

    it('remover view_ai_analysis do registered simula paywall', function () {
        $role = Role::findByName('registered', 'web');
        $role->revokePermissionTo('view_ai_analysis');

        // Agora registered não tem mais a permissão
        expect($role->hasPermissionTo('view_ai_analysis'))->toBeFalse();

        // Usuário com role registered não tem acesso
        $user = User::factory()->create();
        $user->assignRole('registered');
        // Limpar cache para pegar estado atualizado
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $user = $user->fresh();
        expect($user->hasPermissionTo('view_ai_analysis'))->toBeFalse();

        // Mas subscriber continua com acesso
        $sub = User::factory()->create();
        $sub->assignRole('subscriber');
        expect($sub->hasPermissionTo('view_ai_analysis'))->toBeTrue();
    });

    it('registro publico esta habilitado no Fortify', function () {
        $features = config('fortify.features');
        expect($features)->toContain(\Laravel\Fortify\Features::registration());
    });

});
