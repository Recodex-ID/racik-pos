<?php

namespace App\Livewire\Reports;

use App\Models\Tenant;
use App\Models\Transaction;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class MonthlyTransaction extends Component
{
    use WithPagination;

    public $selectedYear;
    public $selectedMonth;

    public function mount()
    {
        $this->selectedYear = now()->year;
        $this->selectedMonth = now()->month;
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

    #[Computed]
    public function monthlyTransactions()
    {
        $currentTenant = $this->getCurrentTenant();
        
        return Transaction::byTenant($currentTenant->id)
            ->with(['user', 'customer', 'transactionItems.product'])
            ->whereYear('transaction_date', $this->selectedYear)
            ->whereMonth('transaction_date', $this->selectedMonth)
            ->latest('transaction_date')
            ->paginate(20);
    }

    #[Computed]
    public function monthlyStats()
    {
        $currentTenant = $this->getCurrentTenant();
        
        $transactions = Transaction::byTenant($currentTenant->id)
            ->whereYear('transaction_date', $this->selectedYear)
            ->whereMonth('transaction_date', $this->selectedMonth);

        $completedTransactions = $transactions->completed();
        $pendingTransactions = $transactions->where('status', Transaction::STATUS_PENDING);
        $cancelledTransactions = $transactions->where('status', Transaction::STATUS_CANCELLED);

        return [
            'total_transactions' => $transactions->count(),
            'completed_transactions' => $completedTransactions->count(),
            'pending_transactions' => $pendingTransactions->count(),
            'cancelled_transactions' => $cancelledTransactions->count(),
            'total_revenue' => $completedTransactions->sum('total_amount'),
            'average_transaction' => $completedTransactions->avg('total_amount') ?? 0,
            'total_items_sold' => $completedTransactions->join('transaction_items', 'transactions.id', '=', 'transaction_items.transaction_id')
                ->sum('transaction_items.quantity'),
        ];
    }

    #[Computed]
    public function dailyChart()
    {
        $currentTenant = $this->getCurrentTenant();
        $daysInMonth = Carbon::create($this->selectedYear, $this->selectedMonth)->daysInMonth;
        
        $days = [];
        $revenues = [];
        $counts = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($this->selectedYear, $this->selectedMonth, $day);
            
            $dailyTransactions = Transaction::byTenant($currentTenant->id)
                ->whereDate('transaction_date', $date->toDateString())
                ->completed();

            $days[] = $day;
            $revenues[] = $dailyTransactions->sum('total_amount');
            $counts[] = $dailyTransactions->count();
        }

        return [
            'labels' => $days,
            'revenue' => $revenues,
            'transactions' => $counts,
        ];
    }

    #[Computed]
    public function topProducts()
    {
        $currentTenant = $this->getCurrentTenant();
        
        return Transaction::where('transactions.tenant_id', $currentTenant->id)
            ->whereYear('transaction_date', $this->selectedYear)
            ->whereMonth('transaction_date', $this->selectedMonth)
            ->completed()
            ->join('transaction_items', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->where('products.tenant_id', $currentTenant->id) // Tambahkan filter tenant untuk products juga
            ->selectRaw('products.name, SUM(transaction_items.quantity) as total_quantity, SUM(transaction_items.total_price) as total_revenue')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function paymentMethodStats()
    {
        $currentTenant = $this->getCurrentTenant();
        
        return Transaction::byTenant($currentTenant->id)
            ->whereYear('transaction_date', $this->selectedYear)
            ->whereMonth('transaction_date', $this->selectedMonth)
            ->completed()
            ->selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as total_amount')
            ->groupBy('payment_method')
            ->orderByDesc('total_amount')
            ->get();
    }

    public function updatedSelectedYear()
    {
        $this->resetPage();
    }

    public function updatedSelectedMonth()
    {
        $this->resetPage();
    }

    public function exportToPdf()
    {
        // TODO: Implement PDF export functionality
        session()->flash('info', 'Fitur export PDF akan segera tersedia.');
    }

    public function exportToExcel()
    {
        // TODO: Implement Excel export functionality
        session()->flash('info', 'Fitur export Excel akan segera tersedia.');
    }

    public function render()
    {
        return view('livewire.reports.monthly-transaction');
    }
}