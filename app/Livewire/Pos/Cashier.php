<?php

namespace App\Livewire\Pos;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Cashier extends Component
{
    public $productSearch = '';

    public $selectedCustomerId = null;

    public $cart = [];

    public $subtotal = 0;

    public $discountType = 'percentage'; // percentage or amount

    public $discountValue = 0;

    public $discountAmount = 0;

    public $taxRate = 11; // PPN 11%

    public $taxAmount = 0;

    public $totalAmount = 0;

    public $paymentMethod = 'cash';

    public $paymentAmount = 0;

    public $changeAmount = 0;

    public $notes = '';

    public $showCustomerModal = false;

    public $showPaymentModal = false;

    public $newCustomerName = '';

    public $newCustomerPhone = '';

    public $newCustomerEmail = '';

    public $transactionNumber = '';

    protected $listeners = ['productScanned' => 'addProductByBarcode'];

    public function rules(): array
    {
        return [
            'paymentAmount' => 'required|numeric|min:'.$this->totalAmount,
            'selectedCustomerId' => 'nullable|exists:customers,id',
            'discountValue' => 'numeric|min:0',
            'notes' => 'nullable|string|max:255',
        ];
    }

    public function mount()
    {
        $this->calculateTotals();
        $this->generateTransactionNumber();
    }

    #[Computed]
    public function products()
    {
        $currentTenant = $this->getCurrentTenant();

        $query = Product::byTenant($currentTenant->id)
            ->active()
            ->where('stock', '>', 0)
            ->with('category');

        // Jika ada pencarian, filter berdasarkan pencarian
        if (strlen($this->productSearch) > 0) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->productSearch.'%')
                    ->orWhere('sku', 'like', '%'.$this->productSearch.'%');
            });
        }

        return $query->orderBy('name')
            ->limit(20)
            ->get();
    }

    #[Computed]
    public function customers()
    {
        $currentTenant = $this->getCurrentTenant();

        return Customer::byTenant($currentTenant->id)
            ->active()
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function selectedCustomer()
    {
        if (! $this->selectedCustomerId) {
            return null;
        }

        $currentTenant = $this->getCurrentTenant();

        return Customer::byTenant($currentTenant->id)->find($this->selectedCustomerId);
    }

    #[Computed]
    public function currentTenant()
    {
        return $this->getCurrentTenant();
    }

    private function getCurrentTenant()
    {
        // Try to get tenant from app instance first
        try {
            return app('current_tenant');
        } catch (\Exception $e) {
            // Fallback to getting from request attributes or user
            $tenant = request()->attributes->get('current_tenant');
            if ($tenant) {
                return $tenant;
            }

            // Ultimate fallback: get user's tenant
            $user = auth()->user();
            if ($user->tenant_id) {
                return Tenant::find($user->tenant_id);
            }

            throw new \Exception('No tenant context available');
        }
    }

    public function addToCart($productId)
    {
        $currentTenant = $this->getCurrentTenant();
        $product = Product::byTenant($currentTenant->id)->find($productId);

        if (! $product || ! $product->is_active || $product->stock <= 0) {
            session()->flash('error', 'Produk tidak tersedia atau stok habis!');

            return;
        }

        $cartItemKey = 'product_'.$productId;

        if (isset($this->cart[$cartItemKey])) {
            // Check if adding one more exceeds stock
            if ($this->cart[$cartItemKey]['quantity'] + 1 > $product->stock) {
                session()->flash('error', "Stok tidak mencukupi! Stok tersedia: {$product->stock}");

                return;
            }
            $this->cart[$cartItemKey]['quantity']++;
        } else {
            $this->cart[$cartItemKey] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
                'quantity' => 1,
                'stock' => $product->stock,
            ];
        }

        $this->calculateTotals();
        $this->productSearch = '';
        session()->flash('success', "Produk {$product->name} ditambahkan ke keranjang!");
    }

    public function addProductByBarcode($barcode)
    {
        $currentTenant = $this->getCurrentTenant();
        $product = Product::byTenant($currentTenant->id)
            ->where('sku', $barcode)
            ->active()
            ->first();

        if ($product) {
            $this->addToCart($product->id);
        } else {
            session()->flash('error', 'Produk dengan barcode tersebut tidak ditemukan!');
        }
    }

    public function updateQuantity($cartItemKey, $quantity)
    {
        if ($quantity <= 0) {
            $this->removeFromCart($cartItemKey);

            return;
        }

        if (isset($this->cart[$cartItemKey])) {
            $maxStock = $this->cart[$cartItemKey]['stock'];

            if ($quantity > $maxStock) {
                session()->flash('error', "Stok tidak mencukupi! Stok tersedia: {$maxStock}");

                return;
            }

            $this->cart[$cartItemKey]['quantity'] = $quantity;
            $this->calculateTotals();
        }
    }

    public function removeFromCart($cartItemKey)
    {
        if (isset($this->cart[$cartItemKey])) {
            $productName = $this->cart[$cartItemKey]['name'];
            unset($this->cart[$cartItemKey]);
            $this->calculateTotals();
            session()->flash('success', "Produk {$productName} dihapus dari keranjang!");
        }
    }

    public function clearCart()
    {
        $this->cart = [];
        $this->calculateTotals();
        session()->flash('success', 'Keranjang berhasil dikosongkan!');
    }

    public function calculateTotals()
    {
        $this->subtotal = 0;

        foreach ($this->cart as $item) {
            $this->subtotal += $item['price'] * $item['quantity'];
        }

        // Calculate discount
        if ($this->discountType === 'percentage') {
            $this->discountAmount = ($this->subtotal * $this->discountValue) / 100;
        } else {
            $this->discountAmount = $this->discountValue;
        }

        // Ensure discount doesn't exceed subtotal
        $this->discountAmount = min($this->discountAmount, $this->subtotal);

        // Calculate tax (after discount)
        $taxableAmount = $this->subtotal - $this->discountAmount;
        $this->taxAmount = ($taxableAmount * $this->taxRate) / 100;

        // Calculate total
        $this->totalAmount = $this->subtotal - $this->discountAmount + $this->taxAmount;

        // Calculate change
        if ($this->paymentAmount > 0) {
            $this->changeAmount = max(0, $this->paymentAmount - $this->totalAmount);
        } else {
            $this->changeAmount = 0;
        }
    }

    public function updated($property)
    {
        if (in_array($property, ['discountType', 'discountValue', 'taxRate', 'paymentAmount'])) {
            $this->calculateTotals();
        }
    }

    public function openPaymentModal()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Keranjang masih kosong!');

            return;
        }

        $this->paymentAmount = $this->totalAmount;
        $this->calculateTotals();
        $this->showPaymentModal = true;
    }

    public function processTransaction()
    {
        $this->validate();

        if (empty($this->cart)) {
            session()->flash('error', 'Keranjang masih kosong!');

            return;
        }

        try {
            DB::beginTransaction();

            $currentTenant = $this->getCurrentTenant();

            // Create transaction
            $transaction = Transaction::create([
                'tenant_id' => $currentTenant->id,
                'customer_id' => $this->selectedCustomerId,
                'user_id' => auth()->id(),
                'transaction_number' => $this->transactionNumber,
                'transaction_date' => now(),
                'subtotal' => $this->subtotal,
                'discount_amount' => $this->discountAmount,
                'tax_amount' => $this->taxAmount,
                'total_amount' => $this->totalAmount,
                'payment_method' => $this->paymentMethod,
                'payment_amount' => $this->paymentAmount,
                'change_amount' => $this->changeAmount,
                'status' => 'completed',
                'notes' => $this->notes,
            ]);

            // Create transaction items and update stock
            foreach ($this->cart as $item) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['price'] * $item['quantity'],
                ]);

                // Update product stock
                $product = Product::find($item['product_id']);
                $product->decrement('stock', $item['quantity']);
            }

            DB::commit();

            // Reset form
            $this->resetTransaction();

            session()->flash('success', "Transaksi berhasil! No: {$transaction->transaction_number}");

            // Could redirect to receipt or continue with new transaction
            return redirect()->route('pos.cashier');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan saat memproses transaksi: '.$e->getMessage());
        }
    }

    public function addQuickAmount($amount)
    {
        $this->paymentAmount = $this->totalAmount + $amount;
        $this->calculateTotals();
    }

    public function setExactAmount()
    {
        $this->paymentAmount = $this->totalAmount;
        $this->calculateTotals();
    }

    public function createCustomer()
    {
        $this->validate([
            'newCustomerName' => 'required|string|max:255',
            'newCustomerPhone' => 'nullable|string|max:20',
            'newCustomerEmail' => 'nullable|email|max:255',
        ]);

        $currentTenant = $this->getCurrentTenant();

        $customer = Customer::create([
            'tenant_id' => $currentTenant->id,
            'name' => $this->newCustomerName,
            'phone' => $this->newCustomerPhone,
            'email' => $this->newCustomerEmail,
            'is_active' => true,
        ]);

        $this->selectedCustomerId = $customer->id;
        $this->reset(['newCustomerName', 'newCustomerPhone', 'newCustomerEmail']);
        $this->showCustomerModal = false;

        session()->flash('success', 'Pelanggan baru berhasil ditambahkan!');
    }

    public function resetTransaction()
    {
        $this->reset([
            'cart',
            'selectedCustomerId',
            'discountValue',
            'discountAmount',
            'paymentAmount',
            'changeAmount',
            'notes',
            'showPaymentModal',
        ]);

        $this->discountType = 'percentage';
        $this->paymentMethod = 'cash';
        $this->calculateTotals();
        $this->generateTransactionNumber();
    }

    private function generateTransactionNumber()
    {
        $currentTenant = $this->getCurrentTenant();
        $today = now()->format('Ymd');
        $prefix = 'TRX-'.$currentTenant->id.'-';

        // Get last transaction number for today
        $lastTransaction = Transaction::where('transaction_number', 'like', $prefix.'%'.$today)
            ->orderBy('transaction_number', 'desc')
            ->first();

        if ($lastTransaction) {
            $lastNumber = (int) substr($lastTransaction->transaction_number, -8, 4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        $this->transactionNumber = $prefix.$newNumber.'-'.$today;
    }

    public function render()
    {
        return view('livewire.pos.cashier');
    }
}
