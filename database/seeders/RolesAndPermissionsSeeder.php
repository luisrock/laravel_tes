<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpar o cache do Spatie guardado antes de rodar as insercoes
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Criacao das Permissoes Exatas
        $permissions = [
            'view_ai_analysis',
            'download_acordaos',
            'search',
            'use_ai',
            'manage_all',
            'manage_users',
            'ad_free',
        ];

        foreach ($permissions as $perm) {
            Permission::findOrCreate($perm, 'web');
        }

        // 2. Criacao e Sincronizacao das Roles

        // Admin
        $roleAdmin = Role::findOrCreate('admin', 'web');
        $roleAdmin->syncPermissions([
            'manage_all',
            'manage_users',
            'view_ai_analysis',
            'download_acordaos',
            'search',
            'use_ai',
            'ad_free',
        ]);

        // Registered (usuario base)
        $roleRegistered = Role::findOrCreate('registered', 'web');
        $roleRegistered->syncPermissions([
            'search',
            'ad_free',
        ]);

        // Subscriber (Assinante do plano minimo / Pro / Premium)
        $roleSubscriber = Role::findOrCreate('subscriber', 'web');
        $roleSubscriber->syncPermissions([
            'search',
            'view_ai_analysis',
            'download_acordaos',
            'ad_free',
        ]);

        // Premium (Assinante de plano maximo / Premium)
        $rolePremium = Role::findOrCreate('premium', 'web');
        $rolePremium->syncPermissions([
            'search',
            'view_ai_analysis',
            'download_acordaos',
            'use_ai',
            'ad_free',
        ]);

        // Remove 'download_pdf' antigo caso ele exista
        $oldPermission = Permission::where('name', 'download_pdf')->first();
        if ($oldPermission) {
            $oldPermission->delete();
        }
    }
}
