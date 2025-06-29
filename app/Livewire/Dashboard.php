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

            // Ultimate fallback: get user's tenant
            $user = auth()->user();
            if ($user->tenant_id) {
                return Tenant::find($user->tenant_id);
            }

            throw new \Exception('No tenant context available');
        }
    }

    #[Computed]
    public function totalUsers()
    {
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
        $currentTenant = $this->getCurrentTenant();
        return User::where('tenant_id', $currentTenant->id)
            ->whereNotNull('email_verified_at')
            ->count();
    }

    #[Computed]
    public function newUsersThisWeek()
    {
        $currentTenant = $this->getCurrentTenant();
        return User::where('tenant_id', $currentTenant->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
    }

    #[Computed]
    public function recentUsers()
    {
        $currentTenant = $this->getCurrentTenant();
        return User::where('tenant_id', $currentTenant->id)
            ->latest()
            ->take(5)
            ->get();
    }

    #[Computed]
    public function userRegistrationTrend()
    {
        $currentTenant = $this->getCurrentTenant();
        $last7Days = [];
        $userCounts = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateFormatted = $date->format('M j');

            $userCount = User::where('tenant_id', $currentTenant->id)
                ->whereDate('created_at', $date->toDateString())
                ->count();

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
        $currentTenant = $this->getCurrentTenant();
        $completedTransactions = Transaction::byTenant($currentTenant->id)
            ->whereDate('transaction_date', today())
            ->completed();
        $pendingTransactions = Transaction::byTenant($currentTenant->id)
            ->whereDate('transaction_date', today())
            ->where('status', Transaction::STATUS_PENDING);

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
        $currentTenant = $this->getCurrentTenant();
        $hours = [];
        $revenues = [];
        $counts = [];

        // Generate hourly data for today (24 hours: 00:00 to 23:59)
        for ($hour = 0; $hour <= 23; $hour++) {
            $startTime = today()->setHour($hour)->setMinute(0)->setSecond(0);
            $endTime = today()->setHour($hour)->setMinute(59)->setSecond(59);

            $hourlyTransactions = Transaction::byTenant($currentTenant->id)
                ->whereDate('transaction_date', today())
                ->completed()
                ->whereBetween('transaction_date', [$startTime, $endTime]);

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
        $currentTenant = $this->getCurrentTenant();
        $last7Days = [];
        $revenues = [];
        $counts = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateFormatted = $date->format('M j');

            $dailyTransactions = Transaction::byTenant($currentTenant->id)
                ->whereDate('transaction_date', $date->toDateString())
                ->completed();

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
}
