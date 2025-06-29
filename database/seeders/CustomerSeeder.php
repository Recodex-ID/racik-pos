<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();

        $customers = [
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@example.com',
                'phone' => '081234567890',
                'address' => 'Jl. Merdeka No. 123, Jakarta',
            ],
            [
                'name' => 'Siti Rahayu',
                'email' => 'siti@example.com',
                'phone' => '081234567891',
                'address' => 'Jl. Pahlawan No. 456, Bogor',
            ],
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad@example.com',
                'phone' => '081234567892',
                'address' => 'Jl. Kemerdekaan No. 789, Depok',
            ],
            [
                'name' => 'Dewi Sartika',
                'email' => 'dewi@example.com',
                'phone' => '081234567893',
                'address' => 'Jl. Proklamasi No. 321, Tangerang',
            ],
            [
                'name' => 'Eko Prasetyo',
                'email' => 'eko@example.com',
                'phone' => '081234567894',
                'address' => 'Jl. Diponegoro No. 654, Bekasi',
            ],
        ];

        foreach ($tenants as $tenant) {
            foreach ($customers as $customerData) {
                Customer::create([
                    'tenant_id' => $tenant->id,
                    'name' => $customerData['name'],
                    'email' => $customerData['email'],
                    'phone' => $customerData['phone'],
                    'address' => $customerData['address'],
                    'is_active' => true,
                ]);
            }
        }
    }
}
