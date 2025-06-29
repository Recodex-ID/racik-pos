<?php

namespace App\Livewire;

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
    public function todayTransactions()
    {
        return Transaction::with(['user', 'customer', 'transactionItems.product'])
            ->whereDate('transaction_date', today())
            ->latest('transaction_date')
            ->get();
    }

    #[Computed]
    public function todayTransactionStats()
    {
        $completedTransactions = Transaction::whereDate('transaction_date', today())->completed();
        $pendingTransactions = Transaction::whereDate('transaction_date', today())->where('status', 'pending');

        return [
            'total_transactions' => $completedTransactions->count(),
            'total_revenue' => $completedTransactions->sum('total_amount'),
            'average_transaction' => $completedTransactions->avg('total_amount') ?? 0,
            'pending_transactions' => $pendingTransactions->count(),
        ];
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
