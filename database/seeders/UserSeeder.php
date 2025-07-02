<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('name', 'Recodex ID')->first();

        if ($tenant) {
            // Akun Admin
            $admin = User::create([
                'name' => 'Admin',
                'username' => 'admin_recodex',
                'email' => 'admin@recodex.id',
                'email_verified_at' => now(),
                'password' => Hash::make('admin123'),
                'tenant_id' => $tenant->id,
                'is_active' => true,
            ]);
            $admin->assignRole('Admin');

            // Akun Kasir
            $cashier = User::create([
                'name' => 'Kasir',
                'username' => 'kasir_recodex',
                'email' => 'kasir@recodex.id',
                'email_verified_at' => now(),
                'password' => Hash::make('kasir123'),
                'tenant_id' => $tenant->id,
                'is_active' => true,
            ]);
            $cashier->assignRole('Cashier');
        }
    }
}
