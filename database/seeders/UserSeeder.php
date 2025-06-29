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
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            // Buat Admin untuk setiap tenant
            $admin = User::create([
                'name' => 'Admin '.$tenant->name,
                'username' => 'admin'.$tenant->id,
                'email' => 'admin'.$tenant->id.'@'.strtolower(str_replace(' ', '', $tenant->name)).'.com',
                'email_verified_at' => now(),
                'password' => Hash::make('admin123'),
                'tenant_id' => $tenant->id,
                'is_active' => true,
            ]);
            $admin->assignRole('Admin');

            // Buat Staff untuk tenant
            // Kasir 1
            $cashier1 = User::create([
                'name' => 'Kasir 1 '.$tenant->name,
                'username' => 'kasir1tenant'.$tenant->id,
                'email' => 'kasir1@tenant'.$tenant->id.'.com',
                'email_verified_at' => now(),
                'password' => Hash::make('kasir123'),
                'tenant_id' => $tenant->id,
                'is_active' => true,
            ]);
            $cashier1->assignRole('Cashier');

            // Kasir 2
            $cashier2 = User::create([
                'name' => 'Kasir 2 '.$tenant->name,
                'username' => 'kasir2tenant'.$tenant->id,
                'email' => 'kasir2@tenant'.$tenant->id.'.com',
                'email_verified_at' => now(),
                'password' => Hash::make('kasir123'),
                'tenant_id' => $tenant->id,
                'is_active' => true,
            ]);
            $cashier2->assignRole('Cashier');
        }
    }
}
