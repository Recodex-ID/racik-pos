<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
    {{-- Header Section --}}
    <header class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">Laporan Transaksi Bulanan</flux:heading>
            <flux:subheading>
                Laporan detail transaksi untuk bulan {{ Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->translatedFormat('F') }} {{ $selectedYear }}
            </flux:subheading>
        </div>

        <div class="flex items-center gap-4">
            {{-- Year Selector --}}
            <flux:select wire:model.live="selectedYear" placeholder="Pilih Tahun">
                @for ($year = now()->year - 5; $year <= now()->year + 1; $year++)
                    <flux:select.option value="{{ $year }}">{{ $year }}</flux:select.option>
                @endfor
            </flux:select>

            {{-- Month Selector --}}
            <flux:select wire:model.live="selectedMonth" placeholder="Pilih Bulan">
                @foreach (range(1, 12) as $month)
                    <flux:select.option value="{{ $month }}">{{ Carbon\Carbon::create(null, $month, 1)->translatedFormat('F') }}</flux:select.option>
                @endforeach
            </flux:select>

            {{-- Export Buttons --}}
            <flux:button wire:click="exportToPdf" icon="document-arrow-down" variant="outline">
                PDF
            </flux:button>

            <flux:button wire:click="exportToExcel" icon="table-cells" variant="outline">
                Excel
            </flux:button>
        </div>
    </header>

    {{-- Stats Cards --}}
    <div class="grid auto-rows-min gap-6 md:grid-cols-4">
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Total Transaksi</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Semua transaksi bulan ini</p>
                </div>
                <div class="rounded-full bg-blue-100 p-3 dark:bg-blue-900/20">
                    <flux:icon.shopping-cart class="h-6 w-6 text-blue-600" />
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ number_format($this->monthlyStats['total_transactions']) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Transaksi Selesai</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Transaksi yang berhasil</p>
                </div>
                <div class="rounded-full bg-green-100 p-3 dark:bg-green-900/20">
                    <flux:icon.check-circle class="h-6 w-6 text-green-600" />
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ number_format($this->monthlyStats['completed_transactions']) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Total Pendapatan</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Pendapatan bulan ini</p>
                </div>
                <div class="rounded-full bg-emerald-100 p-3 dark:bg-emerald-900/20">
                    <flux:icon.currency-dollar class="h-6 w-6 text-emerald-600" />
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($this->monthlyStats['total_revenue'], 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Rata-rata Transaksi</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Per transaksi</p>
                </div>
                <div class="rounded-full bg-purple-100 p-3 dark:bg-purple-900/20">
                    <flux:icon.calculator class="h-6 w-6 text-purple-600" />
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($this->monthlyStats['average_transaction'], 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    {{-- Charts Section --}}
    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Daily Revenue Chart --}}
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-zinc-100">Tren Harian {{ Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->translatedFormat('F') }} {{ $selectedYear }}</h3>
            <div class="h-80">
                <canvas id="dailyRevenueChart" data-chart-data="{{ json_encode($this->dailyChart) }}"></canvas>
            </div>
        </div>

        {{-- Payment Method Stats --}}
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-zinc-100">Metode Pembayaran</h3>
            <div class="space-y-4">
                @forelse ($this->paymentMethodStats as $payment)
                    <div class="flex justify-between items-center p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg">
                        <div>
                            <div class="font-semibold text-zinc-900 dark:text-zinc-100">{{ ucfirst($payment->payment_method) }}</div>
                            <div class="text-sm text-zinc-600 dark:text-zinc-400">{{ $payment->count }} transaksi</div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-green-600">Rp {{ number_format($payment->total_amount, 0, ',', '.') }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-zinc-500 dark:text-zinc-400">
                        Belum ada data metode pembayaran
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Top Products Section --}}
    <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
        <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-zinc-100">Produk Terlaris</h3>

        @if($this->topProducts->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700">
                            <th class="text-left py-3 px-4 text-sm font-medium text-zinc-600 dark:text-zinc-400">Produk</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-zinc-600 dark:text-zinc-400">Jumlah Terjual</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-zinc-600 dark:text-zinc-400">Total Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->topProducts as $product)
                            <tr class="border-b border-zinc-100 dark:border-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $product->name }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <flux:badge color="blue">{{ number_format($product->total_quantity) }} item</flux:badge>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-green-600">Rp {{ number_format($product->total_revenue, 0, ',', '.') }}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8 text-zinc-500 dark:text-zinc-400">
                Belum ada data produk untuk bulan ini
            </div>
        @endif
    </div>

    {{-- Transactions Table --}}
    <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Detail Transaksi</h3>
            <flux:badge color="blue" size="sm">{{ $this->monthlyTransactions->total() }} transaksi</flux:badge>
        </div>

        @if($this->monthlyTransactions->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700">
                            <th class="text-left py-3 px-4 text-sm font-medium text-zinc-600 dark:text-zinc-400">No. Transaksi</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-zinc-600 dark:text-zinc-400">Tanggal</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-zinc-600 dark:text-zinc-400">Pelanggan</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-zinc-600 dark:text-zinc-400">Kasir</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-zinc-600 dark:text-zinc-400">Items</th>
                            <th class="text-right py-3 px-4 text-sm font-medium text-zinc-600 dark:text-zinc-400">Total</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-zinc-600 dark:text-zinc-400">Status</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-zinc-600 dark:text-zinc-400">Pembayaran</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->monthlyTransactions as $transaction)
                            <tr class="border-b border-zinc-100 dark:border-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="py-3 px-4">
                                    <div class="font-mono text-sm text-zinc-900 dark:text-zinc-100">{{ $transaction->transaction_number }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="text-sm text-zinc-600 dark:text-zinc-400">{{ $transaction->transaction_date->translatedFormat('d/m/Y H:i') }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="text-sm text-zinc-900 dark:text-zinc-100">{{ $transaction->customer->name ?? 'Walk-in' }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="text-sm text-zinc-900 dark:text-zinc-100">{{ $transaction->user->name }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="text-sm text-zinc-600 dark:text-zinc-400">{{ $transaction->transactionItems->count() }} item</div>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    @if ($transaction->status === App\Models\Transaction::STATUS_COMPLETED)
                                        <flux:badge color="green" size="sm">Selesai</flux:badge>
                                    @elseif ($transaction->status === App\Models\Transaction::STATUS_PENDING)
                                        <flux:badge color="orange" size="sm">Pending</flux:badge>
                                    @else
                                        <flux:badge color="red" size="sm">Dibatalkan</flux:badge>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="text-sm text-zinc-900 dark:text-zinc-100">{{ ucfirst($transaction->payment_method) }}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $this->monthlyTransactions->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <flux:icon.shopping-cart class="h-12 w-12 text-zinc-400 mx-auto mb-4" />
                <h4 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">Belum Ada Transaksi</h4>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Belum ada transaksi untuk bulan {{ Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->translatedFormat('F') }} {{ $selectedYear }}.</p>
            </div>
        @endif
    </div>

    {{-- Chart Script --}}
    @script
    <script>
        // Chart initialization will be handled by existing chart.js
        window.addEventListener('livewire:navigated', function() {
            setTimeout(() => {
                initializeMonthlyReportChart();
            }, 300);
        });

        function initializeMonthlyReportChart() {
            const chartEl = document.getElementById('dailyRevenueChart');
            if (!chartEl) return;

            const chartData = JSON.parse(chartEl.dataset.chartData || '{"labels":[],"revenue":[],"transactions":[]}');

            // Destroy existing chart if exists
            if (window.monthlyReportChart) {
                window.monthlyReportChart.destroy();
            }

            const ctx = chartEl.getContext('2d');
            window.monthlyReportChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        {
                            label: 'Pendapatan (Rp)',
                            data: chartData.revenue,
                            borderColor: '#16A34A',
                            backgroundColor: 'rgba(22, 163, 74, 0.1)',
                            tension: 0.4,
                            fill: true,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Jumlah Transaksi',
                            data: chartData.transactions,
                            borderColor: '#2563EB',
                            backgroundColor: 'rgba(37, 99, 235, 0.1)',
                            tension: 0.4,
                            fill: false,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Hari'
                            }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Pendapatan (Rp)'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Jumlah Transaksi'
                            },
                            grid: {
                                drawOnChartArea: false
                            }
                        }
                    }
                }
            });
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initializeMonthlyReportChart, 100);
        });
    </script>
    @endscript
</div>
