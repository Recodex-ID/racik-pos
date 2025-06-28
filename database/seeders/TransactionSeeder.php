<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $stores = Store::all();
        $users = User::all();

        foreach ($stores as $store) {
            $customers = Customer::where('store_id', $store->id)->get();
            $storeUsers = $users->where('store_id', $store->id);

            if ($storeUsers->isEmpty()) {
                $storeUsers = $users->take(1); // Fallback ke user pertama jika belum ada relasi store
            }

            // Buat 10 transaksi per toko
            for ($i = 1; $i <= 10; $i++) {
                $transactionDate = Carbon::now()->subDays(rand(0, 30));
                $customer = $customers->random();
                $user = $storeUsers->random();

                $subtotal = rand(50000, 500000);
                $discountAmount = rand(0, $subtotal * 0.1);
                $taxAmount = ($subtotal - $discountAmount) * 0.11; // PPN 11%
                $totalAmount = $subtotal - $discountAmount + $taxAmount;
                $paymentAmount = $totalAmount + rand(0, 50000); // Bisa lebih untuk kembalian
                $changeAmount = $paymentAmount - $totalAmount;

                Transaction::create([
                    'store_id' => $store->id,
                    'customer_id' => $customer->id,
                    'user_id' => $user->id,
                    'transaction_number' => 'TRX-'.$store->id.'-'.str_pad($i, 4, '0', STR_PAD_LEFT).'-'.$transactionDate->format('Ymd'),
                    'transaction_date' => $transactionDate,
                    'subtotal' => $subtotal,
                    'discount_amount' => $discountAmount,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $totalAmount,
                    'payment_method' => ['cash', 'card', 'transfer', 'qris'][rand(0, 3)],
                    'payment_amount' => $paymentAmount,
                    'change_amount' => $changeAmount,
                    'status' => 'completed',
                    'notes' => 'Transaksi contoh untuk '.$store->name,
                ]);
            }
        }
    }
}
