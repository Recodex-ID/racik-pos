<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Store;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $stores = Store::all();

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

        foreach ($stores as $store) {
            foreach ($categories as $categoryName) {
                Category::create([
                    'store_id' => $store->id,
                    'name' => $categoryName,
                    'description' => 'Kategori ' . $categoryName . ' untuk ' . $store->name,
                    'is_active' => true,
                ]);
            }
        }
    }
}
