<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = [
            [
                'name' => 'Toko Maju Jaya',
                'email' => 'majujaya@example.com',
                'phone' => '021-12345678',
                'address' => 'Jl. Sudirman No. 123, Jakarta Pusat',
                'is_active' => true,
            ],
            [
                'name' => 'Warung Berkah',
                'email' => 'berkah@example.com',
                'phone' => '021-87654321',
                'address' => 'Jl. Gatot Subroto No. 456, Jakarta Selatan',
                'is_active' => true,
            ],
            [
                'name' => 'Minimarket Sejahtera',
                'email' => 'sejahtera@example.com',
                'phone' => '021-11223344',
                'address' => 'Jl. Thamrin No. 789, Jakarta Pusat',
                'is_active' => true,
            ],
        ];

        foreach ($tenants as $tenant) {
            Tenant::create($tenant);
        }
    }
}
