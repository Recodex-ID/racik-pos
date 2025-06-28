<div>
    <!-- Header -->
    <header class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">Laporan Stok</flux:heading>
            <flux:subheading>Analisis dan laporan inventori toko</flux:subheading>
        </div>
        <flux:button wire:click="exportInventory" variant="filled" icon="arrow-down-tray">
            Export Data
        </flux:button>
    </header>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <flux:callout variant="success" class="mb-6">
            {{ session('message') }}
        </flux:callout>
    @endif

    <!-- Inventory Summary -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-6">
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Total Produk</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Produk aktif</p>
                </div>
                <div class="rounded-full bg-blue-100 p-3 dark:bg-blue-900/20">
                    <flux:icon.cube class="h-6 w-6 text-blue-600" />
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ number_format($this->inventorySummary['total_products']) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Nilai Stok</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Total nilai inventori</p>
                </div>
                <div class="rounded-full bg-green-100 p-3 dark:bg-green-900/20">
                    <flux:icon.banknotes class="h-6 w-6 text-green-600" />
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($this->inventorySummary['total_stock_value'], 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Stok Menipis</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Perlu restock</p>
                </div>
                <div class="rounded-full bg-yellow-100 p-3 dark:bg-yellow-900/20">
                    <flux:icon.exclamation-triangle class="h-6 w-6 text-yellow-600" />
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ number_format($this->inventorySummary['low_stock_count']) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Stok Habis</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Stok kosong</p>
                </div>
                <div class="rounded-full bg-red-100 p-3 dark:bg-red-900/20">
                    <flux:icon.x-circle class="h-6 w-6 text-red-600" />
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ number_format($this->inventorySummary['out_of_stock_count']) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Kategori</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Total kategori</p>
                </div>
                <div class="rounded-full bg-purple-100 p-3 dark:bg-purple-900/20">
                    <flux:icon.tag class="h-6 w-6 text-purple-600" />
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ number_format($this->inventorySummary['total_categories']) }}</p>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid gap-6 lg:grid-cols-2 mb-6">
        <!-- Stock Value by Category Chart -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-zinc-100">Nilai Stok per Kategori</h3>
            <div class="h-64">
                <canvas id="stockValueChart" data-stock-data="{{ json_encode($this->stockValueByCategory) }}"></canvas>
            </div>
        </div>

        <!-- Stock Movement Trend Chart -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-zinc-100">Pergerakan Stok (7 Hari)</h3>
            <div class="h-64">
                <canvas id="stockMovementChart" data-movement-data="{{ json_encode($this->stockMovementTrend) }}"></canvas>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari produk..."
                    icon="magnifying-glass"
                />
            </div>

            <div>
                <flux:select wire:model.live="filterCategory" placeholder="Semua Kategori">
                    @foreach($this->categories as $category)
                        <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div>
                <flux:select wire:model.live="filterStockStatus" placeholder="Status Stok">
                    <flux:select.option value="all">Semua Status</flux:select.option>
                    <flux:select.option value="in_stock">Stok Aman</flux:select.option>
                    <flux:select.option value="low_stock">Stok Menipis</flux:select.option>
                    <flux:select.option value="out_of_stock">Stok Habis</flux:select.option>
                </flux:select>
            </div>

            <div>
                <flux:button wire:click="clearFilters" variant="subtle" class="w-full">
                    <flux:icon.x-mark class="w-4 h-4 mr-2" />
                    Clear Filters
                </flux:button>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            <button wire:click="sortBy('name')" class="flex items-center space-x-1 hover:text-zinc-700">
                                <span>Produk</span>
                                @if($sortBy === 'name')
                                    <flux:icon.chevron-up class="w-3 h-3 {{ $sortDirection === 'desc' ? 'rotate-180' : '' }}" />
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Kategori</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            <button wire:click="sortBy('stock')" class="flex items-center space-x-1 hover:text-zinc-700">
                                <span>Stok</span>
                                @if($sortBy === 'stock')
                                    <flux:icon.chevron-up class="w-3 h-3 {{ $sortDirection === 'desc' ? 'rotate-180' : '' }}" />
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Min Stok</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            <button wire:click="sortBy('price')" class="flex items-center space-x-1 hover:text-zinc-700">
                                <span>Harga</span>
                                @if($sortBy === 'price')
                                    <flux:icon.chevron-up class="w-3 h-3 {{ $sortDirection === 'desc' ? 'rotate-180' : '' }}" />
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Nilai Stok</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($this->products as $product)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $product->name }}</div>
                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $product->sku }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $product->category->name ?? 'No Category' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                {{ number_format($product->stock) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                {{ number_format($product->min_stock) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                                Rp {{ number_format($product->stock * $product->cost, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($product->stock == 0)
                                    <flux:badge size="sm" color="red">Habis</flux:badge>
                                @elseif($product->stock <= $product->min_stock)
                                    <flux:badge size="sm" color="yellow">Menipis</flux:badge>
                                @else
                                    <flux:badge size="sm" color="green">Aman</flux:badge>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                <flux:icon.cube class="mx-auto h-12 w-12 text-zinc-400 mb-4" />
                                <div>Tidak ada produk ditemukan</div>
                                <div class="mt-1">Coba ubah filter pencarian</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mb-6">
        {{ $this->products->links('custom.pagination') }}
    </div>

    <!-- Additional Reports Section -->
    <div class="grid gap-6 lg:grid-cols-2">
        <!-- Low Stock Products -->
        <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
            <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Produk Stok Menipis</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Produk</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Stok</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Min</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse($this->lowStockProducts as $product)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $product->name }}</div>
                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $product->category->name ?? 'No Category' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600">
                                    {{ number_format($product->stock) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ number_format($product->min_stock) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                    Semua produk memiliki stok yang cukup
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Most Sold Products (Last 30 Days) -->
        <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
            <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Produk Terlaris (30 Hari)</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Produk</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Terjual</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Sisa</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse($this->mostSoldProducts as $product)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $product->name }}</div>
                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $product->sku }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                                    {{ number_format($product->total_sold) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ number_format($product->stock) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                    Tidak ada data penjualan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
