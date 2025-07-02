<?php

namespace App\Livewire;

use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Dashboard extends Component
{
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

            // Get user and check for Super Admin
            $user = auth()->user();

            // If Super Admin, return first active tenant or create default
            if ($user->hasRole('Super Admin')) {
                $firstTenant = Tenant::where('is_active', true)->first();
                if ($firstTenant) {
                    return $firstTenant;
                }

                // Create default tenant if none exists
                return Tenant::create([
                    'name' => 'Default Tenant',
                    'address' => 'Default Address',
                    'is_active' => true,
                ]);
            }

            // For regular users, get their assigned tenant
            if ($user->tenant_id) {
                return Tenant::find($user->tenant_id);
            }

            throw new \Exception('No tenant context available');
        }
    }

    #[Computed]
    public function totalUsers()
    {
        $user = auth()->user();

        // Super Admin dapat melihat semua user
        if ($user->hasRole('Super Admin')) {
            return User::count();
        }

        $currentTenant = $this->getCurrentTenant();

        return User::where('tenant_id', $currentTenant->id)->count();
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
        $user = auth()->user();

        // Super Admin dapat melihat semua active user
        if ($user->hasRole('Super Admin')) {
            return User::whereNotNull('email_verified_at')->count();
        }

        $currentTenant = $this->getCurrentTenant();

        return User::where('tenant_id', $currentTenant->id)
            ->whereNotNull('email_verified_at')
            ->count();
    }

    #[Computed]
    public function newUsersThisWeek()
    {
        $user = auth()->user();

        // Super Admin dapat melihat semua new user
        if ($user->hasRole('Super Admin')) {
            return User::where('created_at', '>=', now()->subDays(7))->count();
        }

        $currentTenant = $this->getCurrentTenant();

        return User::where('tenant_id', $currentTenant->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
    }

    #[Computed]
    public function recentUsers()
    {
        $user = auth()->user();

        // Super Admin dapat melihat semua recent user
        if ($user->hasRole('Super Admin')) {
            return User::latest()->take(5)->get();
        }

        $currentTenant = $this->getCurrentTenant();

        return User::where('tenant_id', $currentTenant->id)
            ->latest()
            ->take(5)
            ->get();
    }

    #[Computed]
    public function userRegistrationTrend()
    {
        $user = auth()->user();
        $last7Days = [];
        $userCounts = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateFormatted = $date->format('M j');

            if ($user->hasRole('Super Admin')) {
                // Super Admin melihat semua user registration
                $userCount = User::whereDate('created_at', $date->toDateString())->count();
            } else {
                // Regular user melihat hanya tenant mereka
                $currentTenant = $this->getCurrentTenant();
                $userCount = User::where('tenant_id', $currentTenant->id)
                    ->whereDate('created_at', $date->toDateString())
                    ->count();
            }

            $last7Days[] = $dateFormatted;
            $userCounts[] = $userCount;
        }

        return [
            'labels' => $last7Days,
            'data' => $userCounts,
        ];
    }

    #[Computed]
    public function todayTransactions()
    {
        $user = auth()->user();

        if ($user->hasRole('Super Admin')) {
            // Super Admin melihat semua transaksi hari ini
            return Transaction::with(['user', 'customer', 'transactionItems.product'])
                ->whereDate('transaction_date', today())
                ->latest('transaction_date')
                ->get();
        }

        $currentTenant = $this->getCurrentTenant();

        return Transaction::byTenant($currentTenant->id)
            ->with(['user', 'customer', 'transactionItems.product'])
            ->whereDate('transaction_date', today())
            ->latest('transaction_date')
            ->get();
    }

    #[Computed]
    public function todayTransactionStats()
    {
        $user = auth()->user();

        if ($user->hasRole('Super Admin')) {
            // Super Admin melihat semua transaction stats
            $completedTransactions = Transaction::whereDate('transaction_date', today())
                ->completed();
            $pendingTransactions = Transaction::whereDate('transaction_date', today())
                ->where('status', Transaction::STATUS_PENDING);
        } else {
            $currentTenant = $this->getCurrentTenant();
            $completedTransactions = Transaction::byTenant($currentTenant->id)
                ->whereDate('transaction_date', today())
                ->completed();
            $pendingTransactions = Transaction::byTenant($currentTenant->id)
                ->whereDate('transaction_date', today())
                ->where('status', Transaction::STATUS_PENDING);
        }

        return [
            'total_transactions' => $completedTransactions->count(),
            'total_revenue' => $completedTransactions->sum('total_amount'),
            'average_transaction' => $completedTransactions->avg('total_amount') ?? 0,
            'pending_transactions' => $pendingTransactions->count(),
        ];
    }

    #[Computed]
    public function todayTransactionChart()
    {
        $user = auth()->user();
        $hours = [];
        $revenues = [];
        $counts = [];

        // Generate hourly data for today (24 hours: 00:00 to 23:59)
        for ($hour = 0; $hour <= 23; $hour++) {
            $startTime = today()->setHour($hour)->setMinute(0)->setSecond(0);
            $endTime = today()->setHour($hour)->setMinute(59)->setSecond(59);

            if ($user->hasRole('Super Admin')) {
                // Super Admin melihat semua transactions
                $hourlyTransactions = Transaction::whereDate('transaction_date', today())
                    ->completed()
                    ->whereBetween('transaction_date', [$startTime, $endTime]);
            } else {
                $currentTenant = $this->getCurrentTenant();
                $hourlyTransactions = Transaction::byTenant($currentTenant->id)
                    ->whereDate('transaction_date', today())
                    ->completed()
                    ->whereBetween('transaction_date', [$startTime, $endTime]);
            }

            $hours[] = $startTime->format('H:i');
            $revenues[] = $hourlyTransactions->sum('total_amount');
            $counts[] = $hourlyTransactions->count();
        }

        return [
            'labels' => $hours,
            'revenue' => $revenues,
            'transactions' => $counts,
        ];
    }

    #[Computed]
    public function weeklyTransactionChart()
    {
        $user = auth()->user();
        $last7Days = [];
        $revenues = [];
        $counts = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateFormatted = $date->format('M j');

            if ($user->hasRole('Super Admin')) {
                // Super Admin melihat semua transactions
                $dailyTransactions = Transaction::whereDate('transaction_date', $date->toDateString())
                    ->completed();
            } else {
                $currentTenant = $this->getCurrentTenant();
                $dailyTransactions = Transaction::byTenant($currentTenant->id)
                    ->whereDate('transaction_date', $date->toDateString())
                    ->completed();
            }

            $last7Days[] = $dateFormatted;
            $revenues[] = $dailyTransactions->sum('total_amount');
            $counts[] = $dailyTransactions->count();
        }

        return [
            'labels' => $last7Days,
            'revenue' => $revenues,
            'transactions' => $counts,
        ];
    }

    public function render()
    {
        return view('livewire.dashboard');
    }

    public function loadPendingTransaction($transactionId)
    {
        // Redirect to cashier page with the transaction ID
        return redirect()->route('pos.cashier', ['transactionId' => $transactionId]);
    }
}
