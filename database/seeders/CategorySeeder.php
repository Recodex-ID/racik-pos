<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();

        $categories = [
            'Makanan & Minuman',
            'Sembako',
            'Peralatan Rumah Tangga',
            'Kesehatan & Kecantikan',
            'Elektronik',
            'Pakaian',
            'Mainan',
            'Alat Tulis',
        ];

        foreach ($tenants as $tenant) {
            foreach ($categories as $categoryName) {
                Category::create([
                    'tenant_id' => $tenant->id,
                    'name' => $categoryName,
                    'description' => 'Kategori '.$categoryName.' untuk '.$tenant->name,
                    'is_active' => true,
                ]);
            }
        }
    }
}
