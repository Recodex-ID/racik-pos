<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
    @role('Super Admin')
    <!-- Cards Section -->
    <div class="grid auto-rows-min gap-6 md:grid-cols-3">
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Total Users</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Number of registered users</p>
                </div>
                <div class="rounded-full bg-blue-100 p-3 dark:bg-blue-900/20">
                    <flux:icon.users class="h-6 w-6 text-blue-600" />
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100" data-users-count="{{ $this->totalUsers }}">{{ $this->totalUsers }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Total Roles</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Number of system roles</p>
                </div>
                <div class="rounded-full bg-green-100 p-3 dark:bg-green-900/20">
                    <flux:icon.shield-check class="h-6 w-6 text-green-600" />
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100" data-roles-count="{{ $this->totalRoles }}">{{ $this->totalRoles }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Total Permissions</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Number of system permissions</p>
                </div>
                <div class="rounded-full bg-purple-100 p-3 dark:bg-purple-900/20">
                    <flux:icon.key class="h-6 w-6 text-purple-600" />
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100" data-permissions-count="{{ $this->totalPermissions }}">{{ $this->totalPermissions }}</p>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-zinc-100">System Overview</h3>
            <div class="h-64">
                <canvas id="systemOverviewChart"></canvas>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-zinc-100">User Registration Trend</h3>
            <div class="h-64">
                <canvas id="userTrendChart" data-trend-data="{{ json_encode($this->userRegistrationTrend) }}"></canvas>
            </div>
        </div>
    </div>

    <!-- Info Section -->
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-zinc-100">Recent Users</h3>
            <div class="space-y-3">
                @foreach($this->recentUsers as $user)
                    <div class="flex items-center justify-between rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800/50">
                        <div class="flex items-center space-x-3">
                            <flux:avatar circle initials="{{ $user->initials() }}" />
                            <div>
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $user->name }}</p>
                                <p class="text-xs text-zinc-600 dark:text-zinc-400">{{ $user->email }}</p>
                            </div>
                        </div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                            {{ $user->created_at->diffForHumans() }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-zinc-100">System Statistics</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-zinc-600 dark:text-zinc-400">Active Users</span>
                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $this->activeUsers }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-zinc-600 dark:text-zinc-400">Available Roles</span>
                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $this->totalRoles }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-zinc-600 dark:text-zinc-400">Registered Permissions</span>
                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $this->totalPermissions }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-zinc-600 dark:text-zinc-400">New Users (7 days)</span>
                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $this->newUsersThisWeek }}</span>
                </div>
            </div>
        </div>
    </div>
    @endrole

    @role('Admin|Cashier')
    <!-- Transaction Statistics Cards -->
    <div class="grid auto-rows-min gap-6 md:grid-cols-4">
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Total Transaksi</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Transaksi hari ini</p>
                </div>
                <div class="rounded-full bg-blue-100 p-3 dark:bg-blue-900/20">
                    <flux:icon.shopping-cart class="h-6 w-6 text-blue-600" />
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $this->todayTransactionStats['total_transactions'] }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Total Pendapatan</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Pendapatan hari ini</p>
                </div>
                <div class="rounded-full bg-green-100 p-3 dark:bg-green-900/20">
                    <flux:icon.currency-dollar class="h-6 w-6 text-green-600" />
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($this->todayTransactionStats['total_revenue'], 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Rata-rata Transaksi</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Per transaksi hari ini</p>
                </div>
                <div class="rounded-full bg-yellow-100 p-3 dark:bg-yellow-900/20">
                    <flux:icon.calculator class="h-6 w-6 text-yellow-600" />
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($this->todayTransactionStats['average_transaction'], 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Transaksi Pending</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Menunggu pembayaran</p>
                </div>
                <div class="rounded-full bg-orange-100 p-3 dark:bg-orange-900/20">
                    <flux:icon.clock class="h-6 w-6 text-orange-600" />
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $this->todayTransactionStats['pending_transactions'] }}</p>
            </div>
        </div>
    </div>

    <!-- Transaction Charts -->
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-zinc-100">Tren Transaksi Hari Ini</h3>
            <div class="h-64">
                <canvas id="todayTransactionChart" data-chart-data="{{ json_encode($this->todayTransactionChart) }}"></canvas>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-zinc-100">Tren Transaksi 7 Hari</h3>
            <div class="h-64">
                <canvas id="weeklyTransactionChart" data-chart-data="{{ json_encode($this->weeklyTransactionChart) }}"></canvas>
            </div>
        </div>
    </div>

    <!-- Today's Transactions List -->
    <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Transaksi Hari Ini</h3>
            <flux:badge color="blue" size="sm">{{ $this->todayTransactions->count() }} transaksi</flux:badge>
        </div>

        @if($this->todayTransactions->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700">
                            <th class="text-left py-3 px-4 text-sm font-medium text-zinc-600 dark:text-zinc-400">No. Transaksi</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-zinc-600 dark:text-zinc-400">Waktu</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-zinc-600 dark:text-zinc-400">Kasir</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-zinc-600 dark:text-zinc-400">Customer</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-zinc-600 dark:text-zinc-400">Items</th>
                            <th class="text-right py-3 px-4 text-sm font-medium text-zinc-600 dark:text-zinc-400">Total</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-zinc-600 dark:text-zinc-400">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($this->todayTransactions as $transaction)
                            <tr class="border-b border-zinc-100 dark:border-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="py-3 px-4">
                                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $transaction->transaction_number }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $transaction->transaction_date->format('H:i') }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-sm text-zinc-900 dark:text-zinc-100">{{ $transaction->user->name }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-sm text-zinc-900 dark:text-zinc-100">{{ $transaction->customer->name ?? 'Guest' }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex flex-col space-y-1">
                                        @foreach($transaction->transactionItems->take(2) as $item)
                                            <span class="text-xs text-zinc-600 dark:text-zinc-400">
                                                {{ $item->quantity }}x {{ $item->product->name ?? 'Produk' }}
                                            </span>
                                        @endforeach
                                        @if($transaction->transactionItems->count() > 2)
                                            <span class="text-xs text-zinc-500 dark:text-zinc-500">
                                                +{{ $transaction->transactionItems->count() - 2 }} lainnya
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <span class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                        Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    @if($transaction->status === \App\Models\Transaction::STATUS_COMPLETED)
                                        <flux:badge color="green" size="sm">Selesai</flux:badge>
                                    @elseif($transaction->status === \App\Models\Transaction::STATUS_PENDING)
                                        <flux:badge color="orange" size="sm">Pending</flux:badge>
                                    @else
                                        <flux:badge color="zinc" size="sm">{{ ucfirst($transaction->status) }}</flux:badge>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-12">
                <flux:icon.shopping-cart class="h-12 w-12 text-zinc-400 mx-auto mb-4" />
                <h4 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">Belum Ada Transaksi</h4>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Belum ada transaksi yang dilakukan hari ini.</p>
            </div>
        @endif
    </div>
    @endrole
</div>
