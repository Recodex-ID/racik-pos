<div>
    <!-- Header -->
    <header class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">Laporan Penjualan</flux:heading>
            <flux:subheading>Analisis dan laporan penjualan toko</flux:subheading>
        </div>
        <flux:button wire:click="exportData" variant="filled" icon="arrow-down-tray">
            Export Data
        </flux:button>
    </header>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <flux:callout variant="success" class="mb-6">
            {{ session('message') }}
        </flux:callout>
    @endif

    <!-- Report Type Selection -->
    <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 mb-6">
        <div class="flex items-center justify-between mb-4">
            <flux:subheading>Jenis Laporan</flux:subheading>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-4">
            <flux:button
                wire:click="setReportType('daily')"
                variant="{{ $reportType === 'daily' ? 'primary' : 'subtle' }}"
                class="w-full"
            >
                Harian
            </flux:button>
            <flux:button
                wire:click="setReportType('weekly')"
                variant="{{ $reportType === 'weekly' ? 'primary' : 'subtle' }}"
                class="w-full"
            >
                Mingguan
            </flux:button>
            <flux:button
                wire:click="setReportType('monthly')"
                variant="{{ $reportType === 'monthly' ? 'primary' : 'subtle' }}"
                class="w-full"
            >
                Bulanan
            </flux:button>
            <flux:button
                wire:click="setReportType('yearly')"
                variant="{{ $reportType === 'yearly' ? 'primary' : 'subtle' }}"
                class="w-full"
            >
                Tahunan
            </flux:button>
            <flux:button
                wire:click="setReportType('custom')"
                variant="{{ $reportType === 'custom' ? 'primary' : 'subtle' }}"
                class="w-full"
            >
                Custom
            </flux:button>
        </div>

        <!-- Date Filters -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @if($reportType === 'custom')
                <div>
                    <flux:input
                        type="date"
                        wire:model.live="customDateFrom"
                        placeholder="Dari Tanggal"
                    />
                </div>
                <div>
                    <flux:input
                        type="date"
                        wire:model.live="customDateTo"
                        placeholder="Sampai Tanggal"
                    />
                </div>
            @else
                <div>
                    <flux:input
                        type="date"
                        wire:model.live="filterDateFrom"
                        placeholder="Dari Tanggal"
                    />
                </div>
                <div>
                    <flux:input
                        type="date"
                        wire:model.live="filterDateTo"
                        placeholder="Sampai Tanggal"
                    />
                </div>
            @endif
        </div>
    </div>

    <!-- Sales Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Total Transaksi</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Jumlah transaksi</p>
                </div>
                <div class="rounded-full bg-blue-100 p-3 dark:bg-blue-900/20">
                    <flux:icon.credit-card class="h-6 w-6 text-blue-600" />
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ number_format($this->salesSummary['total_transactions']) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Total Revenue</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Pendapatan kotor</p>
                </div>
                <div class="rounded-full bg-green-100 p-3 dark:bg-green-900/20">
                    <flux:icon.banknotes class="h-6 w-6 text-green-600" />
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($this->salesSummary['total_revenue'], 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Total Diskon</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Diskon diberikan</p>
                </div>
                <div class="rounded-full bg-red-100 p-3 dark:bg-red-900/20">
                    <flux:icon.receipt-percent class="h-6 w-6 text-red-600" />
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($this->salesSummary['total_discount'], 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Rata-rata</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Per transaksi</p>
                </div>
                <div class="rounded-full bg-yellow-100 p-3 dark:bg-yellow-900/20">
                    <flux:icon.chart-bar class="h-6 w-6 text-yellow-600" />
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($this->salesSummary['average_transaction'], 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid gap-6 lg:grid-cols-2 mb-6">
        <!-- Sales Trend Chart -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-zinc-100">Trend Penjualan</h3>
            <div class="h-64">
                <canvas id="salesTrendChart"
                        data-trend-data="{{ json_encode($this->salesTrend) }}"></canvas>
            </div>
        </div>

        <!-- Payment Method Chart -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-zinc-100">Penjualan per Metode Pembayaran</h3>
            <div class="h-64">
                <canvas id="paymentMethodChart" data-payment-data="{{ json_encode($this->salesByPaymentMethod) }}"></canvas>
            </div>
        </div>
    </div>

    <!-- Hourly Distribution Chart -->
    <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 mb-6">
        <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-zinc-100">Distribusi Penjualan per Jam</h3>
        <div class="h-64">
            <canvas id="hourlyChart" data-hourly-data="{{ json_encode($this->hourlyDistribution) }}"></canvas>
        </div>
    </div>

    <!-- Top Products Table -->
    <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden mb-6">
        <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Top 10 Produk Terlaris</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Rank</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Produk</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Qty Terjual</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Revenue</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($this->topProducts as $index => $product)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center justify-center h-8 w-8 bg-gray-200 rounded-full text-xs font-medium">
                                    {{ $index + 1 }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $product->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                {{ number_format($product->total_qty) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                                Rp {{ number_format($product->total_revenue, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                Tidak ada data produk
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
