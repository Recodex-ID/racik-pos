<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\Store;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Dashboard extends Component
{
    #[Computed]
    public function totalUsers()
    {
        return User::count();
    }

    #[Computed]
    public function totalRoles()
    {
        return Role::count();
    }

    #[Computed]
    public function totalPermissions()
    {
        return Permission::count();
    }

    #[Computed]
    public function activeUsers()
    {
        return User::whereNotNull('email_verified_at')->count();
    }

    #[Computed]
    public function newUsersThisWeek()
    {
        return User::where('created_at', '>=', now()->subDays(7))->count();
    }

    #[Computed]
    public function recentUsers()
    {
        return User::latest()->take(5)->get();
    }

    #[Computed]
    public function userRegistrationTrend()
    {
        $last7Days = [];
        $userCounts = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateFormatted = $date->format('M j');

            $userCount = User::whereDate('created_at', $date->toDateString())->count();

            $last7Days[] = $dateFormatted;
            $userCounts[] = $userCount;
        }

        return [
            'labels' => $last7Days,
            'data' => $userCounts,
        ];
    }

    #[Computed]
    public function totalTransactions()
    {
        return Transaction::where('status', 'completed')->count();
    }

    #[Computed]
    public function totalSales()
    {
        return Transaction::where('status', 'completed')->sum('total_amount');
    }

    #[Computed]
    public function todaySales()
    {
        return Transaction::where('status', 'completed')
            ->whereDate('transaction_date', today())
            ->sum('total_amount');
    }

    #[Computed]
    public function todayTransactions()
    {
        return Transaction::where('status', 'completed')
            ->whereDate('transaction_date', today())
            ->count();
    }

    #[Computed]
    public function totalProducts()
    {
        return Product::count();
    }

    #[Computed]
    public function lowStockProducts()
    {
        return Product::whereColumn('stock', '<=', 'min_stock')->count();
    }

    #[Computed]
    public function recentTransactions()
    {
        return Transaction::with(['customer', 'user', 'store'])
            ->where('status', 'completed')
            ->latest()
            ->take(5)
            ->get();
    }

    #[Computed]
    public function dailySales()
    {
        $last7Days = [];
        $salesData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateFormatted = $date->format('M j');

            $salesAmount = Transaction::where('status', 'completed')
                ->whereDate('transaction_date', $date->toDateString())
                ->sum('total_amount');

            $last7Days[] = $dateFormatted;
            $salesData[] = floatval($salesAmount);
        }

        return [
            'labels' => $last7Days,
            'data' => $salesData,
        ];
    }

    #[Computed]
    public function salesByPaymentMethod()
    {
        $paymentMethods = Transaction::where('status', 'completed')
            ->selectRaw('payment_method, SUM(total_amount) as total')
            ->groupBy('payment_method')
            ->get();

        return [
            'labels' => $paymentMethods->pluck('payment_method')->map(function($method) {
                return ucfirst($method);
            })->toArray(),
            'data' => $paymentMethods->pluck('total')->map(function($total) {
                return floatval($total);
            })->toArray(),
        ];
    }

    #[Computed]
    public function lowStockProductsList()
    {
        return Product::with('category')
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock', 'asc')
            ->take(5)
            ->get();
    }

    #[Computed]
    public function todayTopProducts()
    {
        return Transaction::where('transactions.status', 'completed')
            ->whereDate('transactions.transaction_date', today())
            ->join('transaction_items', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->selectRaw('products.name, products.sku, SUM(transaction_items.quantity) as total_qty, SUM(transaction_items.total_price) as total_revenue')
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function todayPaymentMethods()
    {
        $paymentMethods = Transaction::where('status', 'completed')
            ->whereDate('transaction_date', today())
            ->selectRaw('payment_method, SUM(total_amount) as total, COUNT(*) as count')
            ->groupBy('payment_method')
            ->get();

        return [
            'labels' => $paymentMethods->pluck('payment_method')->map(function($method) {
                return ucfirst($method);
            })->toArray(),
            'data' => $paymentMethods->pluck('total')->map(function($total) {
                return floatval($total);
            })->toArray(),
            'counts' => $paymentMethods->pluck('count')->toArray(),
        ];
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
