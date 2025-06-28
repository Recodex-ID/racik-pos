<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Database\Seeder;

class TransactionItemSeeder extends Seeder
{
    public function run(): void
    {
        $transactions = Transaction::all();

        foreach ($transactions as $transaction) {
            $products = Product::where('store_id', $transaction->store_id)->get();

            if ($products->isEmpty()) {
                continue; // Skip jika tidak ada produk untuk toko ini
            }

            // Buat 2-5 item per transaksi
            $itemCount = rand(2, 5);
            $selectedProducts = $products->random(min($itemCount, $products->count()));

            $calculatedSubtotal = 0;

            foreach ($selectedProducts as $product) {
                $quantity = rand(1, 3);
                $unitPrice = $product->price;
                $totalPrice = $quantity * $unitPrice;
                $calculatedSubtotal += $totalPrice;

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ]);
            }

            // Update subtotal transaksi berdasarkan item yang dibuat
            $discountAmount = $transaction->discount_amount;
            $taxAmount = ($calculatedSubtotal - $discountAmount) * 0.11;
            $totalAmount = $calculatedSubtotal - $discountAmount + $taxAmount;
            $paymentAmount = $totalAmount + rand(0, 50000);
            $changeAmount = $paymentAmount - $totalAmount;

            $transaction->update([
                'subtotal' => $calculatedSubtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'payment_amount' => $paymentAmount,
                'change_amount' => $changeAmount,
            ]);
        }
    }
}
