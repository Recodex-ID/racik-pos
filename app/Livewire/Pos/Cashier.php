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

    public function mount($transactionId = null)
    {
        if ($transactionId) {
            $this->loadPendingTransaction($transactionId);
        } else {
            $this->generateTransactionNumber();
        }
        $this->calculateTotals();
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

    public function loadPendingTransaction($transactionId)
    {
        $currentTenant = $this->getCurrentTenant();

        $transaction = Transaction::byTenant($currentTenant->id)
            ->where('status', Transaction::STATUS_PENDING)
            ->where('user_id', auth()->id())
            ->with(['customer', 'transactionItems.product'])
            ->find($transactionId);

        if (! $transaction) {
            session()->flash('error', 'Transaksi pending tidak ditemukan atau tidak dapat dimuat!');

            return;
        }

        // Load draft data
        $this->currentDraftId = $transaction->id;
        $this->selectedCustomerId = $transaction->customer_id;
        $this->discountValue = $this->discountType === 'percentage'
            ? (float) (($transaction->discount_amount / $transaction->subtotal) * 100)
            : (float) $transaction->discount_amount;
        $this->notes = $transaction->notes;
        $this->transactionNumber = $transaction->transaction_number;

        // Load cart from transaction items
        $this->cart = [];
        foreach ($transaction->transactionItems as $item) {
            $productName = $item->product ? $item->product->name : 'Produk Tidak Ditemukan';
            $productPrice = (float) $item->unit_price; // Explicitly cast to float

            $cartKey = 'product_'.$item->product_id;
            $this->cart[$cartKey] = [
                'product_id' => $item->product_id,
                'name' => $productName,
                'price' => $productPrice,
                'quantity' => $item->quantity,
                'image' => $item->product ? $item->product->image : null,
                'initials' => $item->product ? $item->product->getInitials() : '??',
            ];
        }

        $this->calculateTotals();
        $this->showDraftsModal = false;

        session()->flash('success', 'Transaksi pending berhasil dimuat!');
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
                'image' => $product->image,
                'initials' => $product->getInitials(),
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

            $transaction = null; // Initialize transaction variable

            if ($this->currentDraftId) {
                // Update existing draft transaction
                $transaction = Transaction::find($this->currentDraftId);
                if (! $transaction) {
                    throw new \Exception('Draft transaction not found.');
                }

                // Update transaction data
                $transaction->update([
                    'customer_id' => $this->selectedCustomerId,
                    // Keep existing transaction_number from draft
                    'transaction_date' => now(),
                    'subtotal' => $this->subtotal,
                    'discount_amount' => $this->discountAmount,
                    'total_amount' => $this->totalAmount,
                    'payment_method' => $this->paymentMethod,
                    'payment_amount' => $this->paymentAmount,
                    'change_amount' => $this->changeAmount,
                    'status' => Transaction::STATUS_COMPLETED, // Change status to completed
                    'notes' => $this->notes,
                ]);

                // Delete existing transaction items and re-create them
                $transaction->transactionItems()->delete();

            } else {
                // Create new transaction
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
            }

            // Create transaction items (for both new and updated transactions)
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
        $tenantInitials = $currentTenant->getInitials();
        $today = now()->format('Ymd');
        $tenantId = $currentTenant->id;
        
        // Get today's transaction count for this tenant to ensure uniqueness
        $todayTransactionCount = Transaction::where('tenant_id', $currentTenant->id)
            ->whereDate('transaction_date', now()->toDateString())
            ->count();
        
        // Generate unique transaction number using tenant initials with sequential number
        // Format: {tenant_initials}-{date}-{tenant_id}-{sequential_number}
        $sequentialNumber = str_pad($todayTransactionCount + 1, 3, '0', STR_PAD_LEFT);
        
        $this->transactionNumber = sprintf(
            '%s-%s-%d-%s',
            $tenantInitials,
            $today,
            $tenantId,
            $sequentialNumber
        );
        
        // Double-check for uniqueness and increment if needed
        $attempts = 1;
        $baseSequentialNumber = $todayTransactionCount + 1;
        
        while ($attempts <= 999) {
            $existingTransaction = Transaction::where('transaction_number', $this->transactionNumber)->first();
            
            if (!$existingTransaction) {
                break; // Unique number found
            }
            
            // Increment sequential number for uniqueness
            $newSequentialNumber = str_pad($baseSequentialNumber + $attempts, 3, '0', STR_PAD_LEFT);
            $this->transactionNumber = sprintf(
                '%s-%s-%d-%s',
                $tenantInitials,
                $today,
                $tenantId,
                $newSequentialNumber
            );
            
            $attempts++;
        }
    }

    

    public function render()
    {
        return view('livewire.pos.cashier')
            ->layout('components.layouts.pos');
    }
}
