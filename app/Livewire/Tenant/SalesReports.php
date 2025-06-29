<?php

namespace App\Livewire\Tenant;

use App\Models\Transaction;
use App\Models\Tenant;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;

class SalesReports extends Component
{
    public $reportType = 'daily'; // daily, weekly, monthly, yearly, custom
    public $filterDateFrom = '';
    public $filterDateTo = '';
    public $customDateFrom = '';
    public $customDateTo = '';

    public function mount()
    {
        $this->filterDateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->filterDateTo = now()->format('Y-m-d');
        $this->customDateFrom = now()->subDays(30)->format('Y-m-d');
        $this->customDateTo = now()->format('Y-m-d');
    }

    public function rules(): array
    {
        return [
            'filterDateFrom' => 'nullable|date',
            'filterDateTo' => 'nullable|date|after_or_equal:filterDateFrom',
            'customDateFrom' => 'nullable|date',
            'customDateTo' => 'nullable|date|after_or_equal:customDateFrom',
        ];
    }

    #[Computed]
    public function salesSummary()
    {
        $query = $this->getBaseQuery();

        return [
            'total_transactions' => $query->count(),
            'total_revenue' => $query->sum('transactions.total_amount'),
            'total_discount' => $query->sum('transactions.discount_amount'),
            'average_transaction' => $query->count() > 0 ? $query->sum('transactions.total_amount') / $query->count() : 0,
        ];
    }

    #[Computed]
    public function salesByPaymentMethod()
    {
        $data = $this->getBaseQuery()
            ->selectRaw('transactions.payment_method, COUNT(*) as count, SUM(transactions.total_amount) as total')
            ->groupBy('transactions.payment_method')
            ->get();

        return [
            'labels' => $data->pluck('payment_method')->map(fn($method) => ucfirst($method))->toArray(),
            'data' => $data->pluck('total')->toArray(),
            'counts' => $data->pluck('count')->toArray(),
        ];
    }

    #[Computed]
    public function salesTrend()
    {
        $query = $this->getBaseQuery();
        $data = [];
        $labels = [];

        switch ($this->reportType) {
            case 'daily':
                $period = now()->subDays(29);
                for ($i = 29; $i >= 0; $i--) {
                    $date = now()->subDays($i);
                    $labels[] = $date->format('d/m');
                    $data[] = (clone $query)->whereDate('transactions.transaction_date', $date)->sum('transactions.total_amount');
                }
                break;

            case 'weekly':
                for ($i = 11; $i >= 0; $i--) {
                    $startWeek = now()->subWeeks($i)->startOfWeek();
                    $endWeek = now()->subWeeks($i)->endOfWeek();
                    $labels[] = 'Week ' . $startWeek->weekOfMonth . ' ' . $startWeek->format('M');
                    $data[] = (clone $query)->whereBetween('transactions.transaction_date', [$startWeek, $endWeek])->sum('transactions.total_amount');
                }
                break;

            case 'monthly':
                for ($i = 11; $i >= 0; $i--) {
                    $month = now()->subMonths($i);
                    $labels[] = $month->format('M Y');
                    $startMonth = $month->startOfMonth();
                    $endMonth = $month->copy()->endOfMonth();
                    $data[] = (clone $query)->whereBetween('transactions.transaction_date', [$startMonth, $endMonth])->sum('transactions.total_amount');
                }
                break;

            case 'yearly':
                for ($i = 4; $i >= 0; $i--) {
                    $year = now()->subYears($i);
                    $labels[] = $year->format('Y');
                    $startYear = $year->startOfYear();
                    $endYear = $year->copy()->endOfYear();
                    $data[] = (clone $query)->whereBetween('transactions.transaction_date', [$startYear, $endYear])->sum('transactions.total_amount');
                }
                break;

            case 'custom':
                $startDate = Carbon::parse($this->customDateFrom);
                $endDate = Carbon::parse($this->customDateTo);
                $diffInDays = $startDate->diffInDays($endDate);

                if ($diffInDays <= 31) {
                    // Daily breakdown for <= 31 days
                    while ($startDate <= $endDate) {
                        $labels[] = $startDate->format('d/m');
                        $data[] = (clone $query)->whereDate('transactions.transaction_date', $startDate)->sum('transactions.total_amount');
                        $startDate->addDay();
                    }
                } else {
                    // Monthly breakdown for > 31 days
                    $currentMonth = $startDate->copy()->startOfMonth();
                    while ($currentMonth <= $endDate) {
                        $labels[] = $currentMonth->format('M Y');
                        $monthStart = $currentMonth->copy()->startOfMonth();
                        $monthEnd = $currentMonth->copy()->endOfMonth();
                        $data[] = (clone $query)->whereBetween('transactions.transaction_date', [$monthStart, $monthEnd])->sum('transactions.total_amount');
                        $currentMonth->addMonth();
                    }
                }
                break;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    #[Computed]
    public function topProducts()
    {
        $query = $this->getBaseQuery();

        return $query->join('transaction_items', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->selectRaw('products.name, products.sku, SUM(transaction_items.quantity) as total_qty, SUM(transaction_items.total_price) as total_revenue')
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function hourlyDistribution()
    {
        $data = $this->getBaseQuery()
            ->selectRaw('HOUR(transactions.transaction_date) as hour, COUNT(*) as count, SUM(transactions.total_amount) as total')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $hours = [];
        $counts = [];
        $totals = [];

        for ($i = 0; $i < 24; $i++) {
            $hourData = $data->firstWhere('hour', $i);
            $hours[] = sprintf('%02d:00', $i);
            $counts[] = $hourData ? $hourData->count : 0;
            $totals[] = $hourData ? $hourData->total : 0;
        }

        return [
            'labels' => $hours,
            'counts' => $counts,
            'totals' => $totals,
        ];
    }

    private function getBaseQuery()
    {
        $query = Transaction::where('transactions.status', 'completed')
            ->where('transactions.tenant_id', $this->getCurrentTenant()->id);

        if ($this->reportType === 'custom') {
            if ($this->customDateFrom) {
                $query->whereDate('transactions.transaction_date', '>=', $this->customDateFrom);
            }
            if ($this->customDateTo) {
                $query->whereDate('transactions.transaction_date', '<=', $this->customDateTo);
            }
        } else {
            if ($this->filterDateFrom) {
                $query->whereDate('transactions.transaction_date', '>=', $this->filterDateFrom);
            }
            if ($this->filterDateTo) {
                $query->whereDate('transactions.transaction_date', '<=', $this->filterDateTo);
            }
        }

        return $query;
    }

    public function setReportType($type)
    {
        $this->reportType = $type;

        // Set default date ranges based on report type
        switch ($type) {
            case 'daily':
                $this->filterDateFrom = now()->subDays(29)->format('Y-m-d');
                $this->filterDateTo = now()->format('Y-m-d');
                break;
            case 'weekly':
                $this->filterDateFrom = now()->subWeeks(11)->startOfWeek()->format('Y-m-d');
                $this->filterDateTo = now()->format('Y-m-d');
                break;
            case 'monthly':
                $this->filterDateFrom = now()->subMonths(11)->startOfMonth()->format('Y-m-d');
                $this->filterDateTo = now()->format('Y-m-d');
                break;
            case 'yearly':
                $this->filterDateFrom = now()->subYears(4)->startOfYear()->format('Y-m-d');
                $this->filterDateTo = now()->format('Y-m-d');
                break;
        }

        // Dispatch browser event to refresh charts
        $this->dispatch('charts-refresh');
    }

    public function exportData()
    {
        // Export functionality can be implemented here
        session()->flash('message', 'Export feature will be implemented soon.');
    }

    private function getCurrentTenant()
    {
        // Assuming user has tenant_id or using a method to get current tenant
        return auth()->user()->tenant ?? Tenant::first();
    }

    public function render()
    {
        return view('livewire.tenant.sales-reports');
    }
}
