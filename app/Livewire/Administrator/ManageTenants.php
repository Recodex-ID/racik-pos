<?php

namespace App\Livewire\Administrator;

use App\Models\Tenant;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class ManageTenants extends Component
{
    use WithPagination;

    public $name = '';

    public $email = '';

    public $phone = '';

    public $address = '';

    public $is_active = true;

    public $editingTenantId = null;

    public $showModal = false;

    public $search = '';

    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ];

        if ($this->editingTenantId) {
            $rules['email'] .= '|unique:tenants,email,'.$this->editingTenantId;
        } else {
            $rules['email'] .= '|unique:tenants,email';
        }

        return $rules;
    }

    #[Computed]
    public function tenants()
    {
        return Tenant::when($this->search, function ($query) {
            $query->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('email', 'like', '%'.$this->search.'%')
                ->orWhere('phone', 'like', '%'.$this->search.'%');
        })
            ->latest()
            ->paginate(10);
    }

    public function create()
    {
        $this->reset(['name', 'email', 'phone', 'address', 'is_active', 'editingTenantId']);
        $this->is_active = true;
        $this->showModal = true;
    }

    public function edit($tenantId)
    {
        $tenant = Tenant::findOrFail($tenantId);
        $this->editingTenantId = $tenant->id;
        $this->name = $tenant->name;
        $this->email = $tenant->email;
        $this->phone = $tenant->phone;
        $this->address = $tenant->address;
        $this->is_active = $tenant->is_active;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $tenantData = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'is_active' => $this->is_active,
        ];

        if ($this->editingTenantId) {
            $tenant = Tenant::findOrFail($this->editingTenantId);
            $tenant->update($tenantData);
            $message = 'Tenant berhasil diperbarui!';
        } else {
            Tenant::create($tenantData);
            $message = 'Tenant berhasil dibuat!';
        }

        $this->reset(['name', 'email', 'phone', 'address', 'is_active', 'editingTenantId']);
        $this->showModal = false;

        session()->flash('message', $message);
    }

    public function delete($tenantId)
    {
        Tenant::findOrFail($tenantId)->delete();
        session()->flash('message', 'Tenant berhasil dihapus!');

        $this->modal("delete-tenant-{$tenantId}")->close();
    }

    public function resetForm()
    {
        $this->reset(['name', 'email', 'phone', 'address', 'is_active', 'editingTenantId']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.administrator.manage-tenants');
    }
}
