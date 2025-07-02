<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InitialDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::create([
            'tenant_id' => 1, // Assuming tenant with ID 1 exists
            'name' => 'Web Development',
        ]);

        Product::create([
            'tenant_id' => 1, // Assuming tenant with ID 1 exists
            'name' => 'Company Profile',
            'price' => 3000000,
            'cost' => 0,
            'category_id' => 1, // Assuming category with ID 1 exists
        ]);

        Customer::create([
            'tenant_id' => 1, // Assuming tenant with ID 1 exists
            'name' => 'Tung Tung Sahur',
            'phone' => '081234567890',
            'address' => 'Jl. Shubuh No. 123',
        ]);
    }
}
