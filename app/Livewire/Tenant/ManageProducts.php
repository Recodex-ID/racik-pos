<?php

namespace App\Livewire\Tenant;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ManageProducts extends Component
{
    use WithFileUploads, WithPagination;

    public $name = '';

    public $description = '';

    public $image = null;

    public $existingImage = '';

    public $price = '';

    public $cost = '';

    public $category_id = '';

    public $is_active = true;

    public $editingProductId = null;

    public $showModal = false;

    public $search = '';

    public $filterCategory = '';

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'image' => 'nullable|image|max:2048',
            'price' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'is_active' => 'boolean',
        ];
    }

    #[Computed]
    public function products()
    {
        $currentTenant = $this->getCurrentTenant();

        $query = Product::with(['category'])
            ->byTenant($currentTenant->id)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filterCategory, function ($query) {
                $query->where('category_id', $this->filterCategory);
            });

        return $query->latest()->paginate(10);
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

            // Ultimate fallback: get user's tenant or first active tenant in tenant
            $user = auth()->user();
            if ($user->tenant_id) {
                return Tenant::find($user->tenant_id);
            }

            // For Admin users, get first active tenant in their tenant
            if ($user->tenant_id) {
                return Tenant::where('tenant_id', $user->tenant_id)
                    ->where('is_active', true)
                    ->first();
            }

            throw new \Exception('No tenant context available');
        }
    }

    public function create()
    {
        $this->reset(['name', 'description', 'image', 'existingImage', 'price', 'cost', 'category_id', 'is_active', 'editingProductId']);
        $this->is_active = true;
        $this->showModal = true;
    }

    public function edit($productId)
    {
        $currentTenant = $this->getCurrentTenant();

        $product = Product::byTenant($currentTenant->id)->findOrFail($productId);
        $this->editingProductId = $product->id;
        $this->name = $product->name;
        $this->description = $product->description;
        $this->existingImage = $product->image;
        $this->price = $product->price;
        $this->cost = $product->cost;
        $this->category_id = $product->category_id;
        $this->is_active = $product->is_active;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $currentTenant = $this->getCurrentTenant();

        // Handle image upload
        $imagePath = $this->existingImage;
        if ($this->image) {
            $imagePath = $this->image->store('products', 'public');
        }

        $productData = [
            'tenant_id' => $currentTenant->id,
            'name' => $this->name,
            'description' => $this->description,
            'image' => $imagePath,
            'price' => $this->price,
            'cost' => $this->cost,
            'category_id' => $this->category_id,
            'is_active' => $this->is_active,
        ];

        if ($this->editingProductId) {
            $product = Product::byTenant($currentTenant->id)->findOrFail($this->editingProductId);
            $product->update($productData);
            $message = 'Produk berhasil diperbarui!';
        } else {
            Product::create($productData);
            $message = 'Produk berhasil dibuat!';
        }

        $this->reset(['name', 'description', 'image', 'existingImage', 'price', 'cost', 'category_id', 'is_active', 'editingProductId']);
        $this->showModal = false;

        session()->flash('message', $message);
    }

    public function delete($productId)
    {
        $currentTenant = $this->getCurrentTenant();

        $product = Product::byTenant($currentTenant->id)->findOrFail($productId);

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

    public function resetForm()
    {
        $this->reset(['name', 'description', 'image', 'existingImage', 'price', 'cost', 'category_id', 'is_active', 'editingProductId']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.tenant.manage-products');
    }
}
