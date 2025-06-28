<?php

namespace App\Livewire\Store;

use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class ManageProducts extends Component
{
    use WithPagination;

    public $name = '';

    public $description = '';

    public $sku = '';

    public $price = '';

    public $cost = '';

    public $stock = 0;

    public $min_stock = 5;

    public $category_id = '';

    public $is_active = true;

    public $editingProductId = null;

    public $showModal = false;

    public $showStockModal = false;

    public $stockAdjustment = 0;

    public $stockNote = '';

    public $selectedProductId = null;

    public $search = '';

    public $filterCategory = '';

    public $filterStock = '';

    public function rules(): array
    {
        $currentStore = app('current_store');

        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'is_active' => 'boolean',
        ];

        if ($this->editingProductId) {
            $rules['sku'] = 'required|string|max:50|unique:products,sku,'.$this->editingProductId.',id,store_id,'.$currentStore->id;
        } else {
            $rules['sku'] = 'required|string|max:50|unique:products,sku,NULL,id,store_id,'.$currentStore->id;
        }

        return $rules;
    }

    #[Computed]
    public function products()
    {
        $currentStore = app('current_store');

        $query = Product::with(['category'])
            ->byStore($currentStore->id)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('sku', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filterCategory, function ($query) {
                $query->where('category_id', $this->filterCategory);
            });

        // Filter berdasarkan stock status
        if ($this->filterStock === 'low') {
            $query->lowStock();
        } elseif ($this->filterStock === 'out') {
            $query->where('stock', 0);
        } elseif ($this->filterStock === 'available') {
            $query->where('stock', '>', 0);
        }

        return $query->latest()->paginate(10);
    }

    #[Computed]
    public function categories()
    {
        $currentStore = app('current_store');

        return Category::byStore($currentStore->id)
            ->active()
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function currentStore()
    {
        return app('current_store');
    }

    #[Computed]
    public function lowStockCount()
    {
        $currentStore = app('current_store');

        return Product::byStore($currentStore->id)->lowStock()->count();
    }

    public function create()
    {
        $this->reset(['name', 'description', 'sku', 'price', 'cost', 'stock', 'min_stock', 'category_id', 'is_active', 'editingProductId']);
        $this->is_active = true;
        $this->stock = 0;
        $this->min_stock = 5;
        $this->showModal = true;
    }

    public function edit($productId)
    {
        $currentStore = app('current_store');

        $product = Product::byStore($currentStore->id)->findOrFail($productId);
        $this->editingProductId = $product->id;
        $this->name = $product->name;
        $this->description = $product->description;
        $this->sku = $product->sku;
        $this->price = $product->price;
        $this->cost = $product->cost;
        $this->stock = $product->stock;
        $this->min_stock = $product->min_stock;
        $this->category_id = $product->category_id;
        $this->is_active = $product->is_active;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $currentStore = app('current_store');

        $productData = [
            'store_id' => $currentStore->id,
            'name' => $this->name,
            'description' => $this->description,
            'sku' => $this->sku,
            'price' => $this->price,
            'cost' => $this->cost,
            'stock' => $this->stock,
            'min_stock' => $this->min_stock,
            'category_id' => $this->category_id,
            'is_active' => $this->is_active,
        ];

        if ($this->editingProductId) {
            $product = Product::byStore($currentStore->id)->findOrFail($this->editingProductId);
            $product->update($productData);
            $message = 'Produk berhasil diperbarui!';
        } else {
            Product::create($productData);
            $message = 'Produk berhasil dibuat!';
        }

        $this->reset(['name', 'description', 'sku', 'price', 'cost', 'stock', 'min_stock', 'category_id', 'is_active', 'editingProductId']);
        $this->showModal = false;

        session()->flash('message', $message);
    }

    public function openStockModal($productId)
    {
        $this->selectedProductId = $productId;
        $this->stockAdjustment = 0;
        $this->stockNote = '';
        $this->showStockModal = true;
    }

    public function adjustStock()
    {
        $this->validate([
            'stockAdjustment' => 'required|integer|not_in:0',
            'stockNote' => 'required|string|max:255',
        ]);

        $currentStore = app('current_store');
        $product = Product::byStore($currentStore->id)->findOrFail($this->selectedProductId);

        $newStock = $product->stock + $this->stockAdjustment;

        if ($newStock < 0) {
            $this->addError('stockAdjustment', 'Stok tidak boleh kurang dari 0');

            return;
        }

        $product->update(['stock' => $newStock]);

        $adjustmentType = $this->stockAdjustment > 0 ? 'masuk' : 'keluar';
        $message = "Stok {$adjustmentType} berhasil: {$this->stockAdjustment}. Stok sekarang: {$newStock}";

        $this->reset(['selectedProductId', 'stockAdjustment', 'stockNote']);
        $this->showStockModal = false;

        session()->flash('message', $message);
    }

    public function delete($productId)
    {
        $currentStore = app('current_store');

        $product = Product::byStore($currentStore->id)->findOrFail($productId);

        // Check if product has transactions
        if ($product->transactionItems()->count() > 0) {
            session()->flash('error', 'Produk tidak dapat dihapus karena sudah memiliki riwayat transaksi!');
            $this->modal("delete-product-{$productId}")->close();

            return;
        }

        $product->delete();
        session()->flash('message', 'Produk berhasil dihapus!');

        $this->modal("delete-product-{$productId}")->close();
    }

    public function generateSku()
    {
        $currentStore = app('current_store');
        $prefix = strtoupper(substr($currentStore->name, 0, 3));
        $timestamp = now()->format('ymdHis');
        $this->sku = $prefix.'-'.$timestamp;
    }

    public function resetForm()
    {
        $this->reset(['name', 'description', 'sku', 'price', 'cost', 'stock', 'min_stock', 'category_id', 'is_active', 'editingProductId']);
        $this->resetValidation();
    }

    public function resetStockForm()
    {
        $this->reset(['selectedProductId', 'stockAdjustment', 'stockNote']);
        $this->resetValidation(['stockAdjustment', 'stockNote']);
    }

    public function render()
    {
        return view('livewire.store.manage-products');
    }
}
