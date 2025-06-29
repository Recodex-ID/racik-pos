<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Update cache after creating permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles and assign permissions
        $superAdmin = Role::create(['name' => 'Super Admin']);

        // Create user and assign Super Admin role
        $user = User::create([
            'name' => 'Zachran Razendra',
            'username' => 'zachranraze',
            'email' => 'zachranraze@recodex.id',
            'email_verified_at' => now(),
            'password' => Hash::make('admin123'),
            'tenant_id' => null, // Super Admin tidak terikat tenant
            'is_active' => true,
        ]);
        $user->assignRole('Super Admin');

        Role::create(['name' => 'Admin']);

        Role::create(['name' => 'Cashier']);
    }
}
