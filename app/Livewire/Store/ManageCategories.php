<?php

namespace App\Livewire\Store;

use App\Models\Category;
use App\Models\Store;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class ManageCategories extends Component
{
    use WithPagination;

    public $name = '';

    public $description = '';

    public $is_active = true;

    public $editingCategoryId = null;

    public $showModal = false;

    public $search = '';

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ];
    }

    #[Computed]
    public function categories()
    {
        $currentStore = $this->getCurrentStore();

        return Category::byStore($currentStore->id)
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            })
            ->withCount('products')
            ->latest()
            ->paginate(10);
    }

    #[Computed]
    public function currentStore()
    {
        return $this->getCurrentStore();
    }

    private function getCurrentStore()
    {
        // Try to get store from app instance first
        try {
            return app('current_store');
        } catch (\Exception $e) {
            // Fallback to getting from request attributes or user
            $store = request()->attributes->get('current_store');
            if ($store) {
                return $store;
            }

            // Ultimate fallback: get user's store or first active store in tenant
            $user = auth()->user();
            if ($user->store_id) {
                return Store::find($user->store_id);
            }

            // For Admin users, get first active store in their tenant
            if ($user->tenant_id) {
                return Store::where('tenant_id', $user->tenant_id)
                    ->where('is_active', true)
                    ->first();
            }

            throw new \Exception('No store context available');
        }
    }

    public function create()
    {
        $this->reset(['name', 'description', 'is_active', 'editingCategoryId']);
        $this->is_active = true;
        $this->showModal = true;
    }

    public function edit($categoryId)
    {
        $currentStore = $this->getCurrentStore();

        $category = Category::byStore($currentStore->id)->findOrFail($categoryId);
        $this->editingCategoryId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description;
        $this->is_active = $category->is_active;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $currentStore = $this->getCurrentStore();

        $categoryData = [
            'store_id' => $currentStore->id,
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ];

        if ($this->editingCategoryId) {
            $category = Category::byStore($currentStore->id)->findOrFail($this->editingCategoryId);
            $category->update($categoryData);
            $message = 'Kategori berhasil diperbarui!';
        } else {
            Category::create($categoryData);
            $message = 'Kategori berhasil dibuat!';
        }

        $this->reset(['name', 'description', 'is_active', 'editingCategoryId']);
        $this->showModal = false;

        session()->flash('message', $message);
    }

    public function delete($categoryId)
    {
        $currentStore = $this->getCurrentStore();

        $category = Category::byStore($currentStore->id)->findOrFail($categoryId);

        // Check if category has products
        if ($category->products()->count() > 0) {
            session()->flash('error', 'Kategori tidak dapat dihapus karena masih memiliki produk!');
            $this->modal("delete-category-{$categoryId}")->close();

            return;
        }

        $category->delete();
        session()->flash('message', 'Kategori berhasil dihapus!');

        $this->modal("delete-category-{$categoryId}")->close();
    }

    public function resetForm()
    {
        $this->reset(['name', 'description', 'is_active', 'editingCategoryId']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.store.manage-categories');
    }
}
