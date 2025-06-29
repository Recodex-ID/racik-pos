<?php

namespace App\Livewire\Tenant;

use App\Models\Customer;
use App\Models\Tenant;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class ManageCustomers extends Component
{
    use WithPagination;

    public $name = '';

    public $email = '';

    public $phone = '';

    public $address = '';

    public $is_active = true;

    public $editingCustomerId = null;

    public $showModal = false;

    public $showDetailModal = false;

    public $selectedCustomerId = null;

    public $search = '';

    public $filterStatus = '';

    public $sortBy = 'created_at';

    public $sortDirection = 'desc';

    public function rules(): array
    {
        $currentTenant = $this->getCurrentTenant();

        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ];

        if ($this->editingCustomerId) {
            $rules['email'] = 'nullable|email|max:255|unique:customers,email,'.$this->editingCustomerId.',id,tenant_id,'.$currentTenant->id;
        } else {
            $rules['email'] = 'nullable|email|max:255|unique:customers,email,NULL,id,tenant_id,'.$currentTenant->id;
        }

        return $rules;
    }

    #[Computed]
    public function customers()
    {
        $currentTenant = $this->getCurrentTenant();

        $query = Customer::byTenant($currentTenant->id)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%')
                        ->orWhere('phone', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filterStatus !== '', function ($query) {
                $query->where('is_active', $this->filterStatus);
            })
            ->withCount('transactions')
            ->withSum('transactions', 'total_amount');

        // Apply sorting
        if ($this->sortBy === 'total_spent') {
            $query->orderBy('transactions_sum_total_amount', $this->sortDirection);
        } elseif ($this->sortBy === 'transaction_count') {
            $query->orderBy('transactions_count', $this->sortDirection);
        } else {
            $query->orderBy($this->sortBy, $this->sortDirection);
        }

        return $query->paginate(10);
    }

    #[Computed]
    public function selectedCustomer()
    {
        if (! $this->selectedCustomerId) {
            return null;
        }

        $currentTenant = $this->getCurrentTenant();

        return Customer::byTenant($currentTenant->id)
            ->withCount('transactions')
            ->withSum('transactions', 'total_amount')
            ->with(['transactions' => function ($query) {
                $query->latest()->limit(10);
            }])
            ->find($this->selectedCustomerId);
    }

    #[Computed]
    public function customerStats()
    {
        $currentTenant = $this->getCurrentTenant();

        return [
            'total' => Customer::byTenant($currentTenant->id)->count(),
            'active' => Customer::byTenant($currentTenant->id)->active()->count(),
            'inactive' => Customer::byTenant($currentTenant->id)->where('is_active', false)->count(),
            'with_transactions' => Customer::byTenant($currentTenant->id)
                ->whereHas('transactions')
                ->count(),
        ];
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
        $this->reset(['name', 'email', 'phone', 'address', 'is_active', 'editingCustomerId']);
        $this->is_active = true;
        $this->showModal = true;
    }

    public function edit($customerId)
    {
        $currentTenant = $this->getCurrentTenant();

        $customer = Customer::byTenant($currentTenant->id)->findOrFail($customerId);
        $this->editingCustomerId = $customer->id;
        $this->name = $customer->name;
        $this->email = $customer->email;
        $this->phone = $customer->phone;
        $this->address = $customer->address;
        $this->is_active = $customer->is_active;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $currentTenant = $this->getCurrentTenant();

        $customerData = [
            'tenant_id' => $currentTenant->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'is_active' => $this->is_active,
        ];

        if ($this->editingCustomerId) {
            $customer = Customer::byTenant($currentTenant->id)->findOrFail($this->editingCustomerId);
            $customer->update($customerData);
            $message = 'Data pelanggan berhasil diperbarui!';
        } else {
            Customer::create($customerData);
            $message = 'Pelanggan baru berhasil ditambahkan!';
        }

        $this->reset(['name', 'email', 'phone', 'address', 'is_active', 'editingCustomerId']);
        $this->showModal = false;

        session()->flash('message', $message);
    }

    public function showDetail($customerId)
    {
        $this->selectedCustomerId = $customerId;
        $this->showDetailModal = true;
    }

    public function delete($customerId)
    {
        $currentTenant = $this->getCurrentTenant();

        $customer = Customer::byTenant($currentTenant->id)->findOrFail($customerId);

        // Check if customer has transactions
        if ($customer->transactions()->count() > 0) {
            session()->flash('error', 'Pelanggan tidak dapat dihapus karena memiliki riwayat transaksi!');
            $this->modal("delete-customer-{$customerId}")->close();

            return;
        }

        $customer->delete();
        session()->flash('message', 'Data pelanggan berhasil dihapus!');

        $this->modal("delete-customer-{$customerId}")->close();
    }

    public function toggleStatus($customerId)
    {
        $currentTenant = $this->getCurrentTenant();

        $customer = Customer::byTenant($currentTenant->id)->findOrFail($customerId);
        $customer->update(['is_active' => ! $customer->is_active]);

        $status = $customer->is_active ? 'diaktifkan' : 'dinonaktifkan';
        session()->flash('message', "Pelanggan berhasil {$status}!");
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function resetForm()
    {
        $this->reset(['name', 'email', 'phone', 'address', 'is_active', 'editingCustomerId']);
        $this->resetValidation();
    }

    public function resetDetailModal()
    {
        $this->reset(['selectedCustomerId']);
    }

    public function render()
    {
        return view('livewire.tenant.manage-customers');
    }
}
