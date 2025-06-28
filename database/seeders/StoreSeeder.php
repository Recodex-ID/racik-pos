<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            Store::create([
                'tenant_id' => $tenant->id,
                'name' => $tenant->name.' - Cabang Utama',
                'address' => $tenant->address,
                'phone' => $tenant->phone,
                'is_active' => true,
            ]);

            if ($tenant->id === 1) {
                Store::create([
                    'tenant_id' => $tenant->id,
                    'name' => $tenant->name.' - Cabang Kelapa Gading',
                    'address' => 'Jl. Boulevard Raya No. 45, Kelapa Gading',
                    'phone' => '021-45566778',
                    'is_active' => true,
                ]);
            }
        }
    }
}
