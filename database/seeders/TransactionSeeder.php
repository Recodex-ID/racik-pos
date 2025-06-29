<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();
        $users = User::all();

        foreach ($tenants as $tenant) {
            $customers = Customer::where('tenant_id', $tenant->id)->get();
            $tenantUsers = $users->where('tenant_id', $tenant->id);

            if ($tenantUsers->isEmpty()) {
                $tenantUsers = $users->take(1); // Fallback ke user pertama jika belum ada relasi tenant
            }

            // Buat 10 transaksi per toko
            for ($i = 1; $i <= 10; $i++) {
                $transactionDate = Carbon::now()->subDays(rand(0, 30));
                $customer = $customers->random();
                $user = $tenantUsers->random();

                $subtotal = rand(50000, 500000);
                $discountAmount = rand(0, $subtotal * 0.1);
                $totalAmount = $subtotal - $discountAmount;
                $paymentAmount = $totalAmount + rand(0, 50000); // Bisa lebih untuk kembalian
                $changeAmount = $paymentAmount - $totalAmount;

                Transaction::create([
                    'tenant_id' => $tenant->id,
                    'customer_id' => $customer->id,
                    'user_id' => $user->id,
                    'transaction_number' => 'TRX-'.$tenant->id.'-'.str_pad($i, 4, '0', STR_PAD_LEFT).'-'.$transactionDate->format('Ymd'),
                    'transaction_date' => $transactionDate,
                    'subtotal' => $subtotal,
                    'discount_amount' => $discountAmount,
                    'total_amount' => $totalAmount,
                    'payment_method' => ['cash', 'card', 'transfer', 'qris'][rand(0, 3)],
                    'payment_amount' => $paymentAmount,
                    'change_amount' => $changeAmount,
                    'status' => 'completed',
                    'notes' => 'Transaksi contoh untuk '.$tenant->name,
                ]);
            }
        }
    }
}
