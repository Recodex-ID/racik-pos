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
        $tenant = Tenant::where('name', 'Machine Coffee SMI')->first();

        if ($tenant) {
            // Akun Admin
            $admin = User::create([
                'name' => 'Machine',
                'username' => 'machine_coffee_',
                'email' => 'pengkorstyle48@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('Machine24'),
                'tenant_id' => $tenant->id,
                'is_active' => true,
            ]);
            $admin->assignRole('Admin');

            // Akun Kasir
            $cashier = User::create([
                'name' => 'Mesin',
                'username' => 'mesinkopi',
                'email' => 'kasir.pengkorstyle48@gmail.com', // Modified to avoid duplicate email
                'email_verified_at' => now(),
                'password' => Hash::make('Mesinkopi24'),
                'tenant_id' => $tenant->id,
                'is_active' => true,
            ]);
            $cashier->assignRole('Cashier');
        }
    }
}
