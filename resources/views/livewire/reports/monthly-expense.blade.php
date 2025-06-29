<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
    {{-- Header Section --}}
    <header class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">Laporan Pengeluaran Bulanan</flux:heading>
            <flux:subheading>
                Laporan detail pengeluaran untuk bulan {{ Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->translatedFormat('F') }} {{ $selectedYear }}
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

            {{-- Category Filter --}}
            <flux:select wire:model.live="selectedCategory" placeholder="Semua Kategori">
                <flux:select.option value="">Semua Kategori</flux:select.option>
                @foreach ($this->categories as $category)
                    <flux:select.option value="{{ $category }}">{{ $category }}</flux:select.option>
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

    @if (session()->has('info'))
        <flux:callout variant="info" icon="information-circle" heading="{{ session('info') }}" class="mb-6" />
    @endif

    {{-- Comparison Section --}}
    <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Perbandingan Bulan Sebelumnya</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    {{ Carbon\Carbon::create($selectedYear, $selectedMonth)->subMonth()->translatedFormat('F Y') }} vs {{ Carbon\Carbon::create($selectedYear, $selectedMonth)->translatedFormat('F Y') }}
                </p>
            </div>
            <div class="text-right">
                <div class="flex items-center gap-2">
                    @if($this->comparisonData['difference'] > 0)
                        <flux:icon name="arrow-trending-up" class="w-5 h-5 text-red-500" />
                        <span class="text-red-600 font-medium">+{{ number_format(abs($this->comparisonData['percentage_change']), 1) }}%</span>
                    @elseif($this->comparisonData['difference'] < 0)
                        <flux:icon name="arrow-trending-down" class="w-5 h-5 text-green-500" />
                        <span class="text-green-600 font-medium">-{{ number_format(abs($this->comparisonData['percentage_change']), 1) }}%</span>
                    @else
                        <span class="text-zinc-500">0%</span>
                    @endif
                </div>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    Rp {{ number_format(abs($this->comparisonData['difference']), 0, ',', '.') }}
                </p>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid auto-rows-min gap-6 md:grid-cols-4">
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Total Pengeluaran</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Semua pengeluaran bulan ini</p>
                </div>
                <div class="rounded-full bg-red-100 p-3 dark:bg-red-900/20">
                    <flux:icon.currency-dollar class="h-6 w-6 text-red-600" />
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($this->monthlyStats['total_expenses'], 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Total Transaksi</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Jumlah transaksi pengeluaran</p>
                </div>
                <div class="rounded-full bg-blue-100 p-3 dark:bg-blue-900/20">
                    <flux:icon.document-text class="h-6 w-6 text-blue-600" />
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ number_format($this->monthlyStats['total_transactions']) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Rata-rata</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Per transaksi</p>
                </div>
                <div class="rounded-full bg-green-100 p-3 dark:bg-green-900/20">
                    <flux:icon.chart-bar class="h-6 w-6 text-green-600" />
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($this->monthlyStats['average_expense'], 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Kategori Terbanyak</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Pengeluaran tertinggi</p>
                </div>
                <div class="rounded-full bg-purple-100 p-3 dark:bg-purple-900/20">
                    <flux:icon.tag class="h-6 w-6 text-purple-600" />
                </div>
            </div>
            <div class="mt-4">
                @if($this->monthlyStats['category_stats']->isNotEmpty())
                    <p class="text-lg font-bold text-zinc-900 dark:text-zinc-100">{{ $this->monthlyStats['category_stats']->first()->category }}</p>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Rp {{ number_format($this->monthlyStats['category_stats']->first()->total_amount, 0, ',', '.') }}</p>
                @else
                    <p class="text-lg font-bold text-zinc-500">-</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Charts Section --}}
    <div class="grid auto-rows-min gap-6 md:grid-cols-2">
        {{-- Daily Expense Chart --}}
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Pengeluaran Harian</h3>
            <div class="h-64">
                <canvas id="dailyExpenseChart"></canvas>
            </div>
        </div>

        {{-- Category Breakdown Chart --}}
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Kategori Pengeluaran</h3>
            <div class="h-64">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Category Stats Table --}}
    <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Statistik per Kategori</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Kategori</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Jumlah Transaksi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Total Pengeluaran</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Persentase</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse ($this->monthlyStats['category_stats'] as $category)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <flux:badge variant="primary" size="sm">{{ $category->category }}</flux:badge>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                                    {{ number_format($category->count) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                    Rp {{ number_format($category->total_amount, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $this->monthlyStats['total_expenses'] > 0 ? number_format(($category->total_amount / $this->monthlyStats['total_expenses']) * 100, 1) : 0 }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-zinc-500 dark:text-zinc-400">
                                    Tidak ada data pengeluaran untuk periode ini
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Top Expenses --}}
    <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Pengeluaran Terbesar</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Judul</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Kategori</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Jumlah</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">User</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse ($this->topExpenses as $expense)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $expense->title }}</div>
                                    @if($expense->description)
                                        <div class="text-sm text-zinc-500 dark:text-zinc-400 truncate max-w-xs">{{ Str::limit($expense->description, 50) }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <flux:badge variant="primary" size="sm">{{ $expense->category }}</flux:badge>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $expense->expense_date->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                    Rp {{ number_format($expense->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $expense->user->name }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-zinc-500 dark:text-zinc-400">
                                    Tidak ada data pengeluaran untuk periode ini
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Detailed Expenses Table --}}
    <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Detail Semua Pengeluaran</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Judul</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Kategori</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Jumlah</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Struk</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse ($this->monthlyExpenses as $expense)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $expense->expense_date->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $expense->title }}</div>
                                    @if($expense->description)
                                        <div class="text-sm text-zinc-500 dark:text-zinc-400 truncate max-w-xs">{{ Str::limit($expense->description, 50) }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <flux:badge variant="primary" size="sm">{{ $expense->category }}</flux:badge>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                    Rp {{ number_format($expense->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $expense->user->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                    @if($expense->receipt_file)
                                        <a href="{{ Storage::url($expense->receipt_file) }}" target="_blank" class="text-blue-600 hover:text-blue-900">
                                            <flux:icon name="document" class="w-4 h-4" />
                                        </a>
                                    @else
                                        <span class="text-zinc-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-zinc-500 dark:text-zinc-400">
                                    Tidak ada data pengeluaran untuk periode ini
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $this->monthlyExpenses->links('custom.pagination') }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Global chart instances
window.expenseCharts = { dailyChart: null, categoryChart: null };

function initExpenseCharts() {
    try {
        // Check if required elements exist
        const dailyCanvas = document.getElementById('dailyExpenseChart');
        const categoryCanvas = document.getElementById('categoryChart');
        
        if (!dailyCanvas || !categoryCanvas) {
            return false;
        }

        // Destroy existing charts
        if (window.expenseCharts.dailyChart) {
            window.expenseCharts.dailyChart.destroy();
        }
        if (window.expenseCharts.categoryChart) {
            window.expenseCharts.categoryChart.destroy();
        }

        // Get data from Livewire component
        const dailyData = @json($this->dailyChart);
        const categoryData = @json($this->categoryChart);

        // Daily Expense Chart
        const dailyCtx = dailyCanvas.getContext('2d');
        window.expenseCharts.dailyChart = new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: dailyData.labels,
                datasets: [{
                    label: 'Pengeluaran (Rp)',
                    data: dailyData.amounts,
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });

        // Category Chart
        if (categoryData.labels.length > 0) {
            const categoryCtx = categoryCanvas.getContext('2d');
            window.expenseCharts.categoryChart = new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: categoryData.labels,
                    datasets: [{
                        data: categoryData.amounts,
                        backgroundColor: [
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(139, 92, 246, 0.8)',
                            'rgba(236, 72, 153, 0.8)',
                            'rgba(34, 197, 94, 0.8)'
                        ],
                        borderWidth: 2,
                        borderColor: 'rgba(255, 255, 255, 1)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': Rp ' + context.parsed.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });
        }

        return true;
    } catch (error) {
        console.error('Error initializing expense charts:', error);
        return false;
    }
}

// Initialize charts on page load and Livewire navigation
document.addEventListener('DOMContentLoaded', initExpenseCharts);
document.addEventListener('livewire:navigated', () => setTimeout(initExpenseCharts, 300));

// Retry mechanism
function retryExpenseCharts() {
    [500, 1000, 2000].forEach(delay => {
        setTimeout(() => {
            if (!window.expenseCharts.dailyChart || !window.expenseCharts.categoryChart) {
                initExpenseCharts();
            }
        }, delay);
    });
}

document.addEventListener('DOMContentLoaded', retryExpenseCharts);
document.addEventListener('livewire:navigated', retryExpenseCharts);
</script>
@endpush