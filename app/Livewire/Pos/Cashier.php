<?php

namespace App\Livewire\Pos;

use App\Models\Category;
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

    public $selectedCategoryId = null;

    public $selectedCustomerId = null;

    public $cart = [];

    public $subtotal = 0;

    public $discountType = 'percentage'; // percentage or amount

    public $discountValue = 0;

    public $discountAmount = 0;

    public $totalAmount = 0;

    public $paymentMethod = 'cash';

    public $paymentAmount = 0;

    public $changeAmount = 0;

    public $notes = '';

    public $showCustomerModal = false;

    public $showPaymentModal = false;

    public $showCartModal = false;

    public $showDraftsModal = false;

    public $currentDraftId = null;

    public $newCustomerName = '';

    public $newCustomerPhone = '';

    public $newCustomerEmail = '';

    public $transactionNumber = '';

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
            ->with('category');

        // Filter berdasarkan kategori yang dipilih
        if ($this->selectedCategoryId) {
            $query->where('category_id', $this->selectedCategoryId);
        }

        // Jika ada pencarian, filter berdasarkan pencarian
        if (strlen($this->productSearch) > 0) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->productSearch.'%')
                    ->orWhere('description', 'like', '%'.$this->productSearch.'%');
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
    public function categories()
    {
        $currentTenant = $this->getCurrentTenant();

        return Category::byTenant($currentTenant->id)
            ->active()
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function drafts()
    {
        $currentTenant = $this->getCurrentTenant();

        return Transaction::byTenant($currentTenant->id)
            ->drafts()
            ->where('user_id', auth()->id())
            ->with(['customer', 'transactionItems.product'])
            ->orderBy('updated_at', 'desc')
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

    public function selectCategory($categoryId = null)
    {
        $this->selectedCategoryId = $categoryId;
        $this->productSearch = '';
    }

    public function saveToDraft()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Keranjang masih kosong!');

            return;
        }

        try {
            DB::beginTransaction();

            $currentTenant = $this->getCurrentTenant();

            // If this is an update to existing draft
            if ($this->currentDraftId) {
                $transaction = Transaction::find($this->currentDraftId);
                if ($transaction) {
                    // Delete existing transaction items
                    $transaction->transactionItems()->delete();

                    // Update transaction data
                    $transaction->update([
                        'customer_id' => $this->selectedCustomerId,
                        'subtotal' => $this->subtotal,
                        'discount_amount' => $this->discountAmount,
                        'total_amount' => $this->totalAmount,
                        'notes' => $this->notes,
                    ]);
                }
            } else {
                // Create new draft transaction
                $transaction = Transaction::create([
                    'tenant_id' => $currentTenant->id,
                    'customer_id' => $this->selectedCustomerId,
                    'user_id' => auth()->id(),
                    'transaction_number' => $this->transactionNumber,
                    'transaction_date' => now(),
                    'subtotal' => $this->subtotal,
                    'discount_amount' => $this->discountAmount,
                    'total_amount' => $this->totalAmount,
                    'payment_method' => 'cash', // Default for draft
                    'payment_amount' => 0, // 0 indicates draft
                    'change_amount' => 0,
                    'status' => Transaction::STATUS_PENDING, // Use pending status for draft
                    'notes' => $this->notes,
                ]);

                $this->currentDraftId = $transaction->id;
            }

            // Create transaction items
            foreach ($this->cart as $item) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['price'] * $item['quantity'],
                ]);
            }

            DB::commit();

            session()->flash('success', 'Draft berhasil disimpan!');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan saat menyimpan draft: '.$e->getMessage());
        }
    }

    public function loadDraft($draftId)
    {
        $currentTenant = $this->getCurrentTenant();

        $draft = Transaction::byTenant($currentTenant->id)
            ->drafts()
            ->where('user_id', auth()->id())
            ->with(['customer', 'transactionItems.product'])
            ->find($draftId);

        if (! $draft) {
            session()->flash('error', 'Draft tidak ditemukan!');

            return;
        }

        // Load draft data
        $this->currentDraftId = $draft->id;
        $this->selectedCustomerId = $draft->customer_id;
        $this->discountValue = $this->discountType === 'percentage'
            ? (float) (($draft->discount_amount / $draft->subtotal) * 100)
            : (float) $draft->discount_amount;
        $this->notes = $draft->notes;
        $this->transactionNumber = $draft->transaction_number;

        // Load cart from transaction items
        $this->cart = [];
        foreach ($draft->transactionItems as $item) {
            $cartKey = 'product_'.$item->product_id;
            $this->cart[$cartKey] = [
                'product_id' => $item->product_id,
                'name' => $item->product->name,
                'price' => $item->unit_price,
                'quantity' => $item->quantity,
            ];
        }

        $this->calculateTotals();
        $this->showDraftsModal = false;
        $this->showCartModal = true;

        session()->flash('success', 'Draft berhasil dimuat!');
    }

    public function deleteDraft($draftId)
    {
        $currentTenant = $this->getCurrentTenant();

        $draft = Transaction::byTenant($currentTenant->id)
            ->drafts()
            ->where('user_id', auth()->id())
            ->find($draftId);

        if ($draft) {
            $draft->delete();
            session()->flash('success', 'Draft berhasil dihapus!');
        } else {
            session()->flash('error', 'Draft tidak ditemukan!');
        }
    }

    public function addToCart($productId)
    {
        $currentTenant = $this->getCurrentTenant();
        $product = Product::byTenant($currentTenant->id)->find($productId);

        if (! $product || ! $product->is_active) {
            session()->flash('error', 'Produk tidak tersedia!');

            return;
        }

        $cartItemKey = 'product_'.$productId;

        if (isset($this->cart[$cartItemKey])) {
            $this->cart[$cartItemKey]['quantity']++;
        } else {
            $this->cart[$cartItemKey] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
            ];
        }

        $this->calculateTotals();
        $this->productSearch = '';
        session()->flash('success', "Produk {$product->name} ditambahkan ke keranjang!");
    }

    public function updateQuantity($cartItemKey, $quantity)
    {
        if ($quantity <= 0) {
            $this->removeFromCart($cartItemKey);

            return;
        }

        if (isset($this->cart[$cartItemKey])) {
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
            $this->subtotal += (float) $item['price'] * (int) $item['quantity'];
        }

        // Convert input values to numeric
        $discountValue = (float) ($this->discountValue ?: 0);
        $paymentAmount = (float) ($this->paymentAmount ?: 0);

        // Calculate discount
        if ($this->discountType === 'percentage') {
            $this->discountAmount = ($this->subtotal * $discountValue) / 100;
        } else {
            $this->discountAmount = $discountValue;
        }

        // Ensure discount doesn't exceed subtotal
        $this->discountAmount = min($this->discountAmount, $this->subtotal);

        // Calculate total
        $this->totalAmount = $this->subtotal - $this->discountAmount;

        // Calculate change
        if ($paymentAmount > 0) {
            $this->changeAmount = max(0, $paymentAmount - $this->totalAmount);
        } else {
            $this->changeAmount = 0;
        }
    }

    public function updated($property)
    {
        if (in_array($property, ['discountType', 'discountValue', 'paymentAmount'])) {
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
                'total_amount' => $this->totalAmount,
                'payment_method' => $this->paymentMethod,
                'payment_amount' => $this->paymentAmount,
                'change_amount' => $this->changeAmount,
                'status' => Transaction::STATUS_COMPLETED,
                'notes' => $this->notes,
            ]);

            // Create transaction items
            foreach ($this->cart as $item) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['price'] * $item['quantity'],
                ]);
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
            'currentDraftId',
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
