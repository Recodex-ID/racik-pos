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
                'address' => 'Jl. Sudirman No. 123, Jakarta Pusat',
                'is_active' => true,
            ],
            [
                'name' => 'Warung Berkah',
                'address' => 'Jl. Gatot Subroto No. 456, Jakarta Selatan',
                'is_active' => true,
            ],
            [
                'name' => 'Minimarket Sejahtera',
                'address' => 'Jl. Thamrin No. 789, Jakarta Pusat',
                'is_active' => true,
            ],
        ];

        foreach ($tenants as $tenant) {
            Tenant::create($tenant);
        }
    }
}
