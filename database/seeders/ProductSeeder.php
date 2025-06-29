<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $categories = Category::where('tenant_id', $tenant->id)->get();

            foreach ($categories as $category) {
                $products = $this->getProductsByCategory($category->name);

                foreach ($products as $productData) {
                    Product::create([
                        'tenant_id' => $tenant->id,
                        'category_id' => $category->id,
                        'name' => $productData['name'],
                        'description' => $productData['description'],
                        'image' => $productData['image'] ?? null,
                        'price' => $productData['price'],
                        'cost' => $productData['cost'],
                        'is_active' => true,
                    ]);
                }
            }
        }
    }

    private function getProductsByCategory($categoryName): array
    {
        $products = [
            'Makanan & Minuman' => [
                ['name' => 'Nasi Uduk', 'description' => 'Nasi uduk dengan lauk lengkap', 'price' => 15000, 'cost' => 8000, 'image' => null],
                ['name' => 'Teh Botol', 'description' => 'Minuman teh manis dalam kemasan botol', 'price' => 5000, 'cost' => 3000, 'image' => null],
                ['name' => 'Keripik Singkong', 'description' => 'Keripik singkong rasa original', 'price' => 8000, 'cost' => 5000, 'image' => null],
            ],
            'Sembako' => [
                ['name' => 'Beras Premium 5kg', 'description' => 'Beras berkualitas premium', 'price' => 75000, 'cost' => 60000, 'image' => null],
                ['name' => 'Minyak Goreng 1L', 'description' => 'Minyak goreng kemasan 1 liter', 'price' => 18000, 'cost' => 15000, 'image' => null],
                ['name' => 'Gula Pasir 1kg', 'description' => 'Gula pasir putih kemasan 1kg', 'price' => 15000, 'cost' => 12000, 'image' => null],
            ],
            'Peralatan Rumah Tangga' => [
                ['name' => 'Piring Melamin', 'description' => 'Piring melamin warna-warni', 'price' => 12000, 'cost' => 8000, 'image' => null],
                ['name' => 'Sendok Stainless', 'description' => 'Sendok makan stainless steel', 'price' => 5000, 'cost' => 3000, 'image' => null],
            ],
            'Kesehatan & Kecantikan' => [
                ['name' => 'Sabun Mandi', 'description' => 'Sabun mandi antibakteri', 'price' => 8000, 'cost' => 5000, 'image' => null],
                ['name' => 'Shampoo Anti Ketombe', 'description' => 'Shampoo untuk mengatasi ketombe', 'price' => 25000, 'cost' => 18000, 'image' => null],
            ],
        ];

        return $products[$categoryName] ?? [
            ['name' => 'Produk Default', 'description' => 'Produk default untuk kategori '.$categoryName, 'price' => 10000, 'cost' => 7000, 'image' => null],
        ];
    }
}
