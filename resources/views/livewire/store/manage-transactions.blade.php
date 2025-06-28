<div>
    <!-- Header -->
    <header class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">History & Report Transaksi</flux:heading>
            <flux:subheading>Kelola dan lihat riwayat transaksi toko</flux:subheading>
        </div>
    </header>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <flux:callout variant="success" class="mb-6">
            {{ session('message') }}
        </flux:callout>
    @endif

    <!-- Summary Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Total Transaksi</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Berdasarkan filter</p>
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
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Berdasarkan filter</p>
                </div>
                <div class="rounded-full bg-green-100 p-3 dark:bg-green-900/20">
                    <flux:icon.banknotes class="h-6 w-6 text-green-600" />
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($this->totalAmount, 0, ',', '.') }}</p>
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
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($this->averageAmount, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari transaksi..."
                    icon="magnifying-glass"
                />
            </div>

            <div>
                <flux:select wire:model.live="filterStatus" placeholder="Semua Status">
                    @foreach($this->statuses as $value => $label)
                        <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div>
                <flux:select wire:model.live="filterPaymentMethod" placeholder="Semua Pembayaran">
                    @foreach($this->paymentMethods as $value => $label)
                        <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

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
        </div>

        <div class="mt-4">
            <flux:button wire:click="clearFilters" variant="outline" size="sm" icon="x-mark">
                Clear Filters
            </flux:button>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">No. Transaksi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Kasir</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Pembayaran</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($this->transactions as $transaction)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $transaction->transaction_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $transaction->transaction_date->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $transaction->customer->name ?? 'Walk-in Customer' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $transaction->user->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                                Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <flux:badge
                                    size="sm"
                                    color="{{ $transaction->payment_method === 'cash' ? 'green' : ($transaction->payment_method === 'card' ? 'blue' : 'purple') }}"
                                >
                                    {{ ucfirst($transaction->payment_method) }}
                                </flux:badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <flux:badge
                                    size="sm"
                                    color="{{ $transaction->status === 'completed' ? 'green' : ($transaction->status === 'pending' ? 'yellow' : 'red') }}"
                                >
                                    {{ ucfirst($transaction->status) }}
                                </flux:badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <flux:button
                                    wire:click="viewDetail({{ $transaction->id }})"
                                    variant="outline"
                                    size="sm"
                                    icon="eye"
                                >
                                    Detail
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                <flux:icon.document-text class="mx-auto h-12 w-12 text-zinc-400 mb-4" />
                                <div>Tidak ada transaksi ditemukan</div>
                                <div class="mt-1">Coba ubah filter pencarian</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $this->transactions->links('custom.pagination') }}
    </div>

    <!-- Transaction Detail Modal -->
    <flux:modal wire:model="showDetailModal" class="md:w-2xl">
        @if($selectedTransaction)
            <div class="p-6">
                <flux:heading size="lg" class="mb-4">Detail Transaksi</flux:heading>

                <!-- Transaction Info -->
                <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-4 mb-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-sm text-zinc-500 dark:text-zinc-400">No. Transaksi</div>
                            <div class="font-medium">{{ $selectedTransaction->transaction_number }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-zinc-500 dark:text-zinc-400">Tanggal</div>
                            <div class="font-medium">{{ $selectedTransaction->transaction_date->format('d/m/Y H:i') }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-zinc-500 dark:text-zinc-400">Customer</div>
                            <div class="font-medium">{{ $selectedTransaction->customer->name ?? 'Walk-in Customer' }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-zinc-500 dark:text-zinc-400">Kasir</div>
                            <div class="font-medium">{{ $selectedTransaction->user->name }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-zinc-500 dark:text-zinc-400">Status</div>
                            <flux:badge color="{{ $selectedTransaction->status === 'completed' ? 'green' : ($selectedTransaction->status === 'pending' ? 'yellow' : 'red') }}">
                                {{ ucfirst($selectedTransaction->status) }}
                            </flux:badge>
                        </div>
                    </div>
                </div>

                <!-- Transaction Items -->
                <div class="mb-6">
                    <flux:subheading class="mb-3">Item Transaksi</flux:subheading>
                    <div class="border rounded-lg overflow-hidden">
                        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                            <thead class="bg-zinc-50 dark:bg-zinc-800">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Produk</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Qty</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Harga</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach($selectedTransaction->transactionItems as $item)
                                    <tr>
                                        <td class="px-4 py-2">
                                            <div class="font-medium">{{ $item->product->name }}</div>
                                            <div class="text-sm text-zinc-500">{{ $item->product->category->name ?? 'No Category' }}</div>
                                        </td>
                                        <td class="px-4 py-2">{{ $item->quantity }}</td>
                                        <td class="px-4 py-2">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                        <td class="px-4 py-2 font-medium">Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Payment Summary -->
                <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-4">
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span>Rp {{ number_format($selectedTransaction->subtotal, 0, ',', '.') }}</span>
                        </div>
                        @if($selectedTransaction->discount_amount > 0)
                            <div class="flex justify-between text-red-600">
                                <span>Diskon:</span>
                                <span>-Rp {{ number_format($selectedTransaction->discount_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        @if($selectedTransaction->tax_amount > 0)
                            <div class="flex justify-between">
                                <span>Pajak:</span>
                                <span>Rp {{ number_format($selectedTransaction->tax_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <hr class="my-2">
                        <div class="flex justify-between font-bold text-lg">
                            <span>Total:</span>
                            <span>Rp {{ number_format($selectedTransaction->total_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Metode Pembayaran:</span>
                            <span class="font-medium">{{ ucfirst($selectedTransaction->payment_method) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Bayar:</span>
                            <span>Rp {{ number_format($selectedTransaction->payment_amount, 0, ',', '.') }}</span>
                        </div>
                        @if($selectedTransaction->change_amount > 0)
                            <div class="flex justify-between">
                                <span>Kembalian:</span>
                                <span>Rp {{ number_format($selectedTransaction->change_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                @if($selectedTransaction->notes)
                    <div class="mt-4">
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">Catatan:</div>
                        <div class="mt-1">{{ $selectedTransaction->notes }}</div>
                    </div>
                @endif

                <div class="flex justify-end mt-6">
                    <flux:button wire:click="closeDetailModal" variant="primary">
                        Tutup
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
