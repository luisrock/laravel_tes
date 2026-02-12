<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Cria roles, permissões e o usuário admin.
     */
    public function run(): void
    {
        $permission = Permission::findOrCreate('manage_all', 'web');
        $roleAdmin = Role::findOrCreate('admin', 'web');
        $roleAdmin->givePermissionTo($permission);

        Role::findOrCreate('registered', 'web');

        $user = User::updateOrCreate(
            ['email' => 'trator70@gmail.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('Tratortes70!'),
                'email_verified_at' => now(),
            ]
        );

        $user->syncRoles([$roleAdmin]);
    }
}
