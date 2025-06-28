<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use App\Models\Store;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $stores = Store::all();

        foreach ($stores as $store) {
            $categories = Category::where('store_id', $store->id)->get();

            foreach ($categories as $category) {
                $products = $this->getProductsByCategory($category->name);

                foreach ($products as $productData) {
                    Product::create([
                        'store_id' => $store->id,
                        'category_id' => $category->id,
                        'name' => $productData['name'],
                        'description' => $productData['description'],
                        'sku' => $store->id . '-' . strtoupper(substr($productData['name'], 0, 3)) . '-' . rand(1000, 9999),
                        'price' => $productData['price'],
                        'cost' => $productData['cost'],
                        'stock' => rand(10, 100),
                        'min_stock' => 5,
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
                ['name' => 'Nasi Uduk', 'description' => 'Nasi uduk dengan lauk lengkap', 'price' => 15000, 'cost' => 8000],
                ['name' => 'Teh Botol', 'description' => 'Minuman teh manis dalam kemasan botol', 'price' => 5000, 'cost' => 3000],
                ['name' => 'Keripik Singkong', 'description' => 'Keripik singkong rasa original', 'price' => 8000, 'cost' => 5000],
            ],
            'Sembako' => [
                ['name' => 'Beras Premium 5kg', 'description' => 'Beras berkualitas premium', 'price' => 75000, 'cost' => 60000],
                ['name' => 'Minyak Goreng 1L', 'description' => 'Minyak goreng kemasan 1 liter', 'price' => 18000, 'cost' => 15000],
                ['name' => 'Gula Pasir 1kg', 'description' => 'Gula pasir putih kemasan 1kg', 'price' => 15000, 'cost' => 12000],
            ],
            'Peralatan Rumah Tangga' => [
                ['name' => 'Piring Melamin', 'description' => 'Piring melamin warna-warni', 'price' => 12000, 'cost' => 8000],
                ['name' => 'Sendok Stainless', 'description' => 'Sendok makan stainless steel', 'price' => 5000, 'cost' => 3000],
            ],
            'Kesehatan & Kecantikan' => [
                ['name' => 'Sabun Mandi', 'description' => 'Sabun mandi antibakteri', 'price' => 8000, 'cost' => 5000],
                ['name' => 'Shampoo Anti Ketombe', 'description' => 'Shampoo untuk mengatasi ketombe', 'price' => 25000, 'cost' => 18000],
            ],
        ];

        return $products[$categoryName] ?? [
            ['name' => 'Produk Default', 'description' => 'Produk default untuk kategori ' . $categoryName, 'price' => 10000, 'cost' => 7000]
        ];
    }
}
