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

    @role('Admin|User')
    <!-- POS Metrics Section -->
    <div class="grid auto-rows-min gap-6 md:grid-cols-4">
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Total Transaksi</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Transaksi yang selesai</p>
                </div>
                <div class="rounded-full bg-blue-100 p-3 dark:bg-blue-900/20">
                    <flux:icon.credit-card class="h-6 w-6 text-blue-600" />
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ number_format($this->totalTransactions) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Total Penjualan</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Revenue dari penjualan</p>
                </div>
                <div class="rounded-full bg-green-100 p-3 dark:bg-green-900/20">
                    <flux:icon.banknotes class="h-6 w-6 text-green-600" />
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($this->totalSales, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Total Produk</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Jumlah produk aktif</p>
                </div>
                <div class="rounded-full bg-purple-100 p-3 dark:bg-purple-900/20">
                    <flux:icon.cube class="h-6 w-6 text-purple-600" />
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ number_format($this->totalProducts) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Stok Menipis</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Produk dengan stok rendah</p>
                </div>
                <div class="rounded-full bg-red-100 p-3 dark:bg-red-900/20">
                    <flux:icon.exclamation-triangle class="h-6 w-6 text-red-600" />
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ number_format($this->lowStockProducts) }}</p>
            </div>
        </div>
    </div>

    <!-- Today's Sales Report Section -->
    <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Laporan Penjualan Hari Ini</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ now()->format('d F Y') }}</p>
            </div>
            <div class="rounded-full bg-blue-100 p-3 dark:bg-blue-900/20">
                <flux:icon.chart-bar-square class="h-6 w-6 text-blue-600" />
            </div>
        </div>

        <!-- Today's Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 p-4 rounded-lg border border-blue-200 dark:border-blue-700">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-medium text-blue-600 dark:text-blue-400">Transaksi Hari Ini</h4>
                        <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ number_format($this->todayTransactions) }}</p>
                    </div>
                    <div class="rounded-full bg-blue-200 p-2 dark:bg-blue-800">
                        <flux:icon.credit-card class="h-5 w-5 text-blue-600" />
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 p-4 rounded-lg border border-green-200 dark:border-green-700">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-medium text-green-600 dark:text-green-400">Penjualan Hari Ini</h4>
                        <p class="text-2xl font-bold text-green-900 dark:text-green-100">Rp {{ number_format($this->todaySales, 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-full bg-green-200 p-2 dark:bg-green-800">
                        <flux:icon.banknotes class="h-5 w-5 text-green-600" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Charts and Details -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Payment Methods Today -->
            <div>
                <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Metode Pembayaran Hari Ini</h4>
                @if(count($this->todayPaymentMethods['labels']) > 0)
                    <div class="space-y-3">
                        @foreach($this->todayPaymentMethods['labels'] as $index => $method)
                            <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    @php
                                        $colors = ['bg-blue-500', 'bg-green-500', 'bg-yellow-500', 'bg-purple-500', 'bg-red-500'];
                                        $color = $colors[$index % count($colors)];
                                    @endphp
                                    <div class="w-3 h-3 rounded-full {{ $color }}"></div>
                                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $method }}</span>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($this->todayPaymentMethods['data'][$index], 0, ',', '.') }}</p>
                                    <p class="text-xs text-zinc-600 dark:text-zinc-400">{{ $this->todayPaymentMethods['counts'][$index] }} transaksi</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <flux:icon.chart-pie class="mx-auto h-12 w-12 text-zinc-400" />
                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Belum ada transaksi hari ini</p>
                    </div>
                @endif
            </div>

            <!-- Top Products Today -->
            <div>
                <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Produk Terlaris Hari Ini</h4>
                @forelse($this->todayTopProducts as $index => $product)
                    <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg mb-3">
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center justify-center h-8 w-8 bg-yellow-100 rounded-full text-xs font-medium text-yellow-800">
                                {{ $index + 1 }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $product->name }}</p>
                                <p class="text-xs text-zinc-600 dark:text-zinc-400">{{ $product->sku }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-green-600">Rp {{ number_format($product->total_revenue, 0, ',', '.') }}</p>
                            <p class="text-xs text-zinc-600 dark:text-zinc-400">{{ $product->total_qty }} terjual</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <flux:icon.cube class="mx-auto h-12 w-12 text-zinc-400" />
                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Belum ada penjualan produk hari ini</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Charts Section - Sales Reports -->
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-zinc-100">Laporan Penjualan Harian (7 Hari)</h3>
            <div class="h-64">
                <canvas id="dailySalesChart" data-sales-data="{{ json_encode($this->dailySales) }}"></canvas>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-zinc-100">Penjualan per Metode Pembayaran</h3>
            <div class="h-64">
                <canvas id="paymentMethodChart" data-payment-data="{{ json_encode($this->salesByPaymentMethod) }}"></canvas>
            </div>
        </div>
    </div>

    <!-- Info Section - Transaction History & Stock Report -->
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-zinc-100">History & Report Transaksi Terbaru</h3>
            <div class="space-y-3">
                @foreach($this->recentTransactions as $transaction)
                    <div class="flex items-center justify-between rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800/50">
                        <div class="flex items-center space-x-3">
                            <div class="rounded-full bg-blue-100 p-2 dark:bg-blue-900/20">
                                <flux:icon.credit-card class="h-4 w-4 text-blue-600" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $transaction->transaction_number }}</p>
                                <p class="text-xs text-zinc-600 dark:text-zinc-400">
                                    {{ $transaction->customer->name ?? 'Walk-in Customer' }} -
                                    {{ ucfirst($transaction->payment_method) }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-green-600">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $transaction->transaction_date->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-zinc-100">Laporan Stok - Produk Stok Menipis</h3>
            <div class="space-y-3">
                @forelse($this->lowStockProductsList as $product)
                    <div class="flex items-center justify-between rounded-lg bg-red-50 p-3 dark:bg-red-900/10 border border-red-200 dark:border-red-800">
                        <div class="flex items-center space-x-3">
                            <div class="rounded-full bg-red-100 p-2 dark:bg-red-900/20">
                                <flux:icon.exclamation-triangle class="h-4 w-4 text-red-600" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $product->name }}</p>
                                <p class="text-xs text-zinc-600 dark:text-zinc-400">{{ $product->category->name ?? 'No Category' }} - {{ $product->sku }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-red-600">Stok: {{ $product->stock }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Min: {{ $product->min_stock }}</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <flux:icon.check-circle class="mx-auto h-12 w-12 text-green-500" />
                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Semua produk memiliki stok yang cukup</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
    @endrole
</div>
