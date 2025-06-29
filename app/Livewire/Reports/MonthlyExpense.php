<?php

namespace App\Livewire\Reports;

use App\Models\Expense;
use App\Models\Tenant;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class MonthlyExpense extends Component
{
    use WithPagination;

    public $selectedYear;

    public $selectedMonth;

    public $selectedCategory = '';

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
        try {
            return app('current_tenant');
        } catch (\Exception $e) {
            $tenant = request()->attributes->get('current_tenant');
            if ($tenant) {
                return $tenant;
            }

            $user = auth()->user();
            if ($user->tenant_id) {
                return Tenant::find($user->tenant_id);
            }

            throw new \Exception('No tenant context available');
        }
    }

    #[Computed]
    public function monthlyExpenses()
    {
        return Expense::with('user')
            ->whereHas('user', function ($query) {
                $query->where('tenant_id', $this->getCurrentTenant()->id);
            })
            ->whereYear('expense_date', $this->selectedYear)
            ->whereMonth('expense_date', $this->selectedMonth)
            ->when($this->selectedCategory, function ($query) {
                $query->where('category', $this->selectedCategory);
            })
            ->latest('expense_date')
            ->paginate(20);
    }

    #[Computed]
    public function monthlyStats()
    {
        $currentTenant = $this->getCurrentTenant();

        $expenses = Expense::whereHas('user', function ($query) use ($currentTenant) {
            $query->where('tenant_id', $currentTenant->id);
        })
            ->whereYear('expense_date', $this->selectedYear)
            ->whereMonth('expense_date', $this->selectedMonth);

        $totalExpenses = $expenses->sum('amount');
        $totalTransactions = $expenses->count();
        $averageExpense = $totalTransactions > 0 ? $totalExpenses / $totalTransactions : 0;

        // Get category breakdown
        $categoryStats = $expenses->selectRaw('category, COUNT(*) as count, SUM(amount) as total_amount')
            ->groupBy('category')
            ->orderByDesc('total_amount')
            ->get();

        return [
            'total_expenses' => $totalExpenses,
            'total_transactions' => $totalTransactions,
            'average_expense' => $averageExpense,
            'category_stats' => $categoryStats,
        ];
    }

    #[Computed]
    public function dailyChart()
    {
        $currentTenant = $this->getCurrentTenant();
        $daysInMonth = Carbon::create($this->selectedYear, $this->selectedMonth)->daysInMonth;

        $days = [];
        $amounts = [];
        $counts = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($this->selectedYear, $this->selectedMonth, $day);

            $dailyExpenses = Expense::whereHas('user', function ($query) use ($currentTenant) {
                $query->where('tenant_id', $currentTenant->id);
            })
                ->whereDate('expense_date', $date->toDateString())
                ->when($this->selectedCategory, function ($query) {
                    $query->where('category', $this->selectedCategory);
                });

            $days[] = $day;
            $amounts[] = $dailyExpenses->sum('amount');
            $counts[] = $dailyExpenses->count();
        }

        return [
            'labels' => $days,
            'amounts' => $amounts,
            'transactions' => $counts,
        ];
    }

    #[Computed]
    public function categoryChart()
    {
        $currentTenant = $this->getCurrentTenant();

        $categoryData = Expense::whereHas('user', function ($query) use ($currentTenant) {
            $query->where('tenant_id', $currentTenant->id);
        })
            ->whereYear('expense_date', $this->selectedYear)
            ->whereMonth('expense_date', $this->selectedMonth)
            ->selectRaw('category, SUM(amount) as total_amount')
            ->groupBy('category')
            ->orderByDesc('total_amount')
            ->get();

        return [
            'labels' => $categoryData->pluck('category')->toArray(),
            'amounts' => $categoryData->pluck('total_amount')->toArray(),
        ];
    }

    #[Computed]
    public function topExpenses()
    {
        $currentTenant = $this->getCurrentTenant();

        return Expense::whereHas('user', function ($query) use ($currentTenant) {
            $query->where('tenant_id', $currentTenant->id);
        })
            ->whereYear('expense_date', $this->selectedYear)
            ->whereMonth('expense_date', $this->selectedMonth)
            ->when($this->selectedCategory, function ($query) {
                $query->where('category', $this->selectedCategory);
            })
            ->orderByDesc('amount')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function categories()
    {
        return ['Operasional', 'Inventaris', 'Gaji', 'Utilitas', 'Pemasaran', 'Transportasi', 'Konsumsi'];
    }

    #[Computed]
    public function comparisonData()
    {
        $currentTenant = $this->getCurrentTenant();
        $currentDate = Carbon::create($this->selectedYear, $this->selectedMonth);
        $previousMonth = $currentDate->copy()->subMonth();

        $currentTotal = Expense::whereHas('user', function ($query) use ($currentTenant) {
            $query->where('tenant_id', $currentTenant->id);
        })
            ->whereYear('expense_date', $this->selectedYear)
            ->whereMonth('expense_date', $this->selectedMonth)
            ->sum('amount');

        $previousTotal = Expense::whereHas('user', function ($query) use ($currentTenant) {
            $query->where('tenant_id', $currentTenant->id);
        })
            ->whereYear('expense_date', $previousMonth->year)
            ->whereMonth('expense_date', $previousMonth->month)
            ->sum('amount');

        $difference = $currentTotal - $previousTotal;
        $percentageChange = $previousTotal > 0 ? ($difference / $previousTotal) * 100 : 0;

        return [
            'current_total' => $currentTotal,
            'previous_total' => $previousTotal,
            'difference' => $difference,
            'percentage_change' => $percentageChange,
        ];
    }

    public function updatedSelectedYear()
    {
        $this->resetPage();
    }

    public function updatedSelectedMonth()
    {
        $this->resetPage();
    }

    public function updatedSelectedCategory()
    {
        $this->resetPage();
    }

    public function exportToPdf()
    {
        session()->flash('info', 'Fitur export PDF akan segera tersedia.');
    }

    public function exportToExcel()
    {
        session()->flash('info', 'Fitur export Excel akan segera tersedia.');
    }

    public function render()
    {
        return view('livewire.reports.monthly-expense');
    }
}
