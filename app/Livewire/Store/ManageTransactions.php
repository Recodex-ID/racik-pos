<?php

namespace App\Livewire\Store;

use App\Models\Transaction;
use App\Models\Store;
use App\Models\Customer;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class ManageTransactions extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatus = '';
    public $filterPaymentMethod = '';
    public $filterDateFrom = '';
    public $filterDateTo = '';
    public $showDetailModal = false;
    public $selectedTransaction = null;

    public function mount()
    {
        $this->filterDateFrom = now()->subDays(30)->format('Y-m-d');
        $this->filterDateTo = now()->format('Y-m-d');
    }

    public function rules(): array
    {
        return [
            'filterDateFrom' => 'nullable|date',
            'filterDateTo' => 'nullable|date|after_or_equal:filterDateFrom',
        ];
    }

    #[Computed]
    public function transactions()
    {
        $query = Transaction::with(['customer', 'user', 'store', 'transactionItems.product'])
            ->byStore($this->getCurrentStore()->id)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('transaction_number', 'like', '%' . $this->search . '%')
                      ->orWhereHas('customer', function ($customer) {
                          $customer->where('name', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('user', function ($user) {
                          $user->where('name', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->filterStatus, function ($query) {
                $query->where('status', $this->filterStatus);
            })
            ->when($this->filterPaymentMethod, function ($query) {
                $query->where('payment_method', $this->filterPaymentMethod);
            })
            ->when($this->filterDateFrom, function ($query) {
                $query->whereDate('transaction_date', '>=', $this->filterDateFrom);
            })
            ->when($this->filterDateTo, function ($query) {
                $query->whereDate('transaction_date', '<=', $this->filterDateTo);
            })
            ->latest('transaction_date')
            ->paginate(15);

        return $query;
    }

    #[Computed]
    public function paymentMethods()
    {
        return [
            'cash' => 'Cash',
            'card' => 'Card',
            'transfer' => 'Transfer',
            'qris' => 'QRIS',
        ];
    }

    #[Computed]
    public function statuses()
    {
        return [
            'pending' => 'Pending',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
    }

    #[Computed]
    public function totalTransactions()
    {
        return $this->getFilteredQuery()->count();
    }

    #[Computed]
    public function totalAmount()
    {
        return $this->getFilteredQuery()->sum('total_amount');
    }

    #[Computed]
    public function averageAmount()
    {
        $total = $this->getFilteredQuery()->count();
        return $total > 0 ? $this->totalAmount / $total : 0;
    }

    private function getFilteredQuery()
    {
        return Transaction::query()
            ->byStore($this->getCurrentStore()->id)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('transaction_number', 'like', '%' . $this->search . '%')
                      ->orWhereHas('customer', function ($customer) {
                          $customer->where('name', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('user', function ($user) {
                          $user->where('name', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->filterStatus, function ($query) {
                $query->where('status', $this->filterStatus);
            })
            ->when($this->filterPaymentMethod, function ($query) {
                $query->where('payment_method', $this->filterPaymentMethod);
            })
            ->when($this->filterDateFrom, function ($query) {
                $query->whereDate('transaction_date', '>=', $this->filterDateFrom);
            })
            ->when($this->filterDateTo, function ($query) {
                $query->whereDate('transaction_date', '<=', $this->filterDateTo);
            });
    }

    public function viewDetail($transactionId)
    {
        $this->selectedTransaction = Transaction::with([
            'customer', 
            'user', 
            'store', 
            'transactionItems.product.category'
        ])->find($transactionId);
        
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedTransaction = null;
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->filterStatus = '';
        $this->filterPaymentMethod = '';
        $this->filterDateFrom = now()->subDays(30)->format('Y-m-d');
        $this->filterDateTo = now()->format('Y-m-d');
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    public function updatedFilterPaymentMethod()
    {
        $this->resetPage();
    }

    public function updatedFilterDateFrom()
    {
        $this->resetPage();
    }

    public function updatedFilterDateTo()
    {
        $this->resetPage();
    }

    private function getCurrentStore()
    {
        // Assuming user has store_id or using a method to get current store
        return auth()->user()->store ?? Store::first();
    }

    public function render()
    {
        return view('livewire.store.manage-transactions');
    }
}