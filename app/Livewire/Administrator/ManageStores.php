<?php

namespace App\Livewire\Administrator;

use App\Models\Store;
use App\Models\Tenant;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class ManageStores extends Component
{
    use WithPagination;

    public $name = '';

    public $address = '';

    public $phone = '';

    public $tenant_id = '';

    public $is_active = true;

    public $editingStoreId = null;

    public $showModal = false;

    public $search = '';

    public $filterTenant = '';

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'phone' => 'nullable|string|max:20',
            'tenant_id' => 'required|exists:tenants,id',
            'is_active' => 'boolean',
        ];
    }

    #[Computed]
    public function stores()
    {
        return Store::with('tenant')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('address', 'like', '%'.$this->search.'%')
                    ->orWhere('phone', 'like', '%'.$this->search.'%')
                    ->orWhereHas('tenant', function ($q) {
                        $q->where('name', 'like', '%'.$this->search.'%');
                    });
            })
            ->when($this->filterTenant, function ($query) {
                $query->where('tenant_id', $this->filterTenant);
            })
            ->latest()
            ->paginate(10);
    }

    #[Computed]
    public function tenants()
    {
        return Tenant::active()->orderBy('name')->get();
    }

    public function create()
    {
        $this->reset(['name', 'address', 'phone', 'tenant_id', 'is_active', 'editingStoreId']);
        $this->is_active = true;
        $this->showModal = true;
    }

    public function edit($storeId)
    {
        $store = Store::with('tenant')->findOrFail($storeId);
        $this->editingStoreId = $store->id;
        $this->name = $store->name;
        $this->address = $store->address;
        $this->phone = $store->phone;
        $this->tenant_id = $store->tenant_id;
        $this->is_active = $store->is_active;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $storeData = [
            'name' => $this->name,
            'address' => $this->address,
            'phone' => $this->phone,
            'tenant_id' => $this->tenant_id,
            'is_active' => $this->is_active,
        ];

        if ($this->editingStoreId) {
            $store = Store::findOrFail($this->editingStoreId);
            $store->update($storeData);
            $message = 'Toko berhasil diperbarui!';
        } else {
            Store::create($storeData);
            $message = 'Toko berhasil dibuat!';
        }

        $this->reset(['name', 'address', 'phone', 'tenant_id', 'is_active', 'editingStoreId']);
        $this->showModal = false;

        session()->flash('message', $message);
    }

    public function delete($storeId)
    {
        Store::findOrFail($storeId)->delete();
        session()->flash('message', 'Toko berhasil dihapus!');

        $this->modal("delete-store-{$storeId}")->close();
    }

    public function resetForm()
    {
        $this->reset(['name', 'address', 'phone', 'tenant_id', 'is_active', 'editingStoreId']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.administrator.manage-stores');
    }
}
