<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Store;
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
                'name' => 'Admin ' . $tenant->name,
                'username' => 'admin' . $tenant->id,
                'email' => 'admin' . $tenant->id . '@' . strtolower(str_replace(' ', '', $tenant->name)) . '.com',
                'email_verified_at' => now(),
                'password' => Hash::make('admin123'),
                'tenant_id' => $tenant->id,
                'store_id' => null, // Admin bisa akses semua store dalam tenant
                'is_active' => true,
            ]);
            $admin->assignRole('Admin');

            // Buat Staff untuk setiap store dalam tenant
            $stores = Store::where('tenant_id', $tenant->id)->get();

            foreach ($stores as $store) {
                // Kasir 1
                $cashier1 = User::create([
                    'name' => 'Kasir 1 ' . $store->name,
                    'username' => 'kasir1store' . $store->id,
                    'email' => 'kasir1@store' . $store->id . '.com',
                    'email_verified_at' => now(),
                    'password' => Hash::make('kasir123'),
                    'tenant_id' => $tenant->id,
                    'store_id' => $store->id,
                    'is_active' => true,
                ]);
                $cashier1->assignRole('User');

                // Kasir 2
                $cashier2 = User::create([
                    'name' => 'Kasir 2 ' . $store->name,
                    'username' => 'kasir2store' . $store->id,
                    'email' => 'kasir2@store' . $store->id . '.com',
                    'email_verified_at' => now(),
                    'password' => Hash::make('kasir123'),
                    'tenant_id' => $tenant->id,
                    'store_id' => $store->id,
                    'is_active' => true,
                ]);
                $cashier2->assignRole('User');
            }
        }
    }
}
