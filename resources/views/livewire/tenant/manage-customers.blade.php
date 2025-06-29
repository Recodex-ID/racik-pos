<div>
    <header class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">Pelanggan</flux:heading>
            <flux:subheading>Kelola data pelanggan untuk {{ $this->currentTenant->name }}</flux:subheading>
        </div>

        <flux:button wire:click="create" variant="primary" icon="plus">
            Tambah Pelanggan
        </flux:button>
    </header>

    @if (session()->has('message'))
        <flux:callout variant="success" icon="check-circle" heading="{{ session('message') }}" class="mb-6" />
    @endif

    @if (session()->has('error'))
        <flux:callout variant="danger" icon="exclamation-triangle" heading="{{ session('error') }}" class="mb-6" />
    @endif

    <!-- Customer Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-zinc-800 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
                    <flux:icon name="users" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Total Pelanggan</p>
                    <p class="text-xl font-bold text-zinc-900 dark:text-zinc-100">{{ $this->customerStats['total'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-800 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center">
                    <flux:icon name="check-circle" class="w-5 h-5 text-green-600 dark:text-green-400" />
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Aktif</p>
                    <p class="text-xl font-bold text-zinc-900 dark:text-zinc-100">{{ $this->customerStats['active'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-800 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-red-100 dark:bg-red-900/20 rounded-lg flex items-center justify-center">
                    <flux:icon name="x-circle" class="w-5 h-5 text-red-600 dark:text-red-400" />
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Tidak Aktif</p>
                    <p class="text-xl font-bold text-zinc-900 dark:text-zinc-100">{{ $this->customerStats['inactive'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-800 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/20 rounded-lg flex items-center justify-center">
                    <flux:icon name="credit-card" class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Pernah Transaksi</p>
                    <p class="text-xl font-bold text-zinc-900 dark:text-zinc-100">{{ $this->customerStats['with_transactions'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari pelanggan..." icon="magnifying-glass" />

        <flux:select wire:model.live="filterStatus" placeholder="Filter status...">
            <flux:select.option value="">Semua Status</flux:select.option>
            <flux:select.option value="1">Aktif</flux:select.option>
            <flux:select.option value="0">Tidak Aktif</flux:select.option>
        </flux:select>

        <flux:select wire:model.live="sortBy" placeholder="Urutkan berdasarkan...">
            <flux:select.option value="created_at">Tanggal Dibuat</flux:select.option>
            <flux:select.option value="name">Nama</flux:select.option>
            <flux:select.option value="total_spent">Total Pembelian</flux:select.option>
            <flux:select.option value="transaction_count">Jumlah Transaksi</flux:select.option>
        </flux:select>
    </div>

    <!-- Customer Table -->
    <div class="border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                        <button wire:click="sortBy('name')" class="flex items-center space-x-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                            <span>Nama</span>
                            @if($sortBy === 'name')
                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-3 h-3" />
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Kontak</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                        <button wire:click="sortBy('transaction_count')" class="flex items-center space-x-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                            <span>Transaksi</span>
                            @if($sortBy === 'transaction_count')
                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-3 h-3" />
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                        <button wire:click="sortBy('total_spent')" class="flex items-center space-x-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                            <span>Total Pembelian</span>
                            @if($sortBy === 'total_spent')
                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-3 h-3" />
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                        <button wire:click="sortBy('created_at')" class="flex items-center space-x-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                            <span>Bergabung</span>
                            @if($sortBy === 'created_at')
                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-3 h-3" />
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Aksi</th>
                </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($this->customers as $customer)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                        <td class="px-6 py-4">
                            <div>
                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $customer->name }}</div>
                                @if($customer->address)
                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ Str::limit($customer->address, 30) }}</div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                @if($customer->email)
                                    <div>{{ $customer->email }}</div>
                                @endif
                                @if($customer->phone)
                                    <div class="text-zinc-500 dark:text-zinc-400">{{ $customer->phone }}</div>
                                @endif
                                @if(!$customer->email && !$customer->phone)
                                    <span class="text-zinc-400">-</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <flux:badge variant="outline" size="sm">
                                {{ $customer->transactions_count }} transaksi
                            </flux:badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            @if($customer->transactions_sum_total_amount)
                                Rp {{ number_format($customer->transactions_sum_total_amount, 0, ',', '.') }}
                            @else
                                <span class="text-zinc-400">Rp 0</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <flux:badge variant="{{ $customer->is_active ? 'primary' : 'outline' }}" size="sm">
                                {{ $customer->is_active ? 'Aktif' : 'Tidak Aktif' }}
                            </flux:badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $customer->created_at->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <flux:button wire:click="showDetail({{ $customer->id }})" size="sm" variant="primary" color="gray" icon="eye" />
                                <flux:button wire:click="edit({{ $customer->id }})" size="sm" variant="primary" color="blue" icon="pencil" />
                                <flux:button wire:click="toggleStatus({{ $customer->id }})" size="sm" variant="primary" color="{{ $customer->is_active ? 'orange' : 'green' }}" icon="{{ $customer->is_active ? 'x-circle' : 'check-circle' }}" />
                                @if($customer->transactions_count === 0)
                                    <flux:modal.trigger name="delete-customer-{{ $customer->id }}">
                                        <flux:button size="sm" variant="primary" color="red" icon="trash" />
                                    </flux:modal.trigger>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center">
                            <div class="flex flex-col items-center justify-center space-y-3">
                                <div class="w-12 h-12 bg-zinc-100 dark:bg-zinc-800 rounded-lg flex items-center justify-center">
                                    <flux:icon name="users" class="w-6 h-6 text-zinc-400" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Belum ada pelanggan</p>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Mulai dengan menambahkan pelanggan pertama</p>
                                </div>
                                <flux:button wire:click="create" size="sm" variant="primary" icon="plus">
                                    Tambah Pelanggan Pertama
                                </flux:button>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $this->customers->links('custom.pagination') }}
    </div>

    <!-- Customer Form Modal -->
    <flux:modal wire:model.self="showModal" name="customer-form" class="min-w-2xl max-w-3xl" wire:close="resetForm">
        <form wire:submit.prevent="save">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">
                        {{ $editingCustomerId ? 'Edit Pelanggan' : 'Tambah Pelanggan Baru' }}
                    </flux:heading>
                    <flux:text class="mt-2">
                        {{ $editingCustomerId ? 'Ubah informasi pelanggan.' : 'Tambahkan pelanggan baru ke database toko.' }}
                    </flux:text>
                </div>

                <flux:field>
                    <flux:label>Nama Pelanggan</flux:label>
                    <flux:input wire:model="name" placeholder="Masukkan nama pelanggan..." />
                    <flux:error name="name" />
                </flux:field>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Email</flux:label>
                        <flux:input wire:model="email" type="email" placeholder="Masukkan email..." />
                        <flux:error name="email" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Telepon</flux:label>
                        <flux:input wire:model="phone" placeholder="Masukkan nomor telepon..." />
                        <flux:error name="phone" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Alamat</flux:label>
                    <flux:textarea wire:model="address" placeholder="Masukkan alamat lengkap..." rows="3" />
                    <flux:error name="address" />
                </flux:field>

                <flux:field>
                    <flux:label>Status</flux:label>
                    <flux:checkbox wire:model="is_active" label="Pelanggan Aktif" />
                    <flux:error name="is_active" />
                    <flux:description>
                        Pelanggan aktif dapat dipilih dalam transaksi
                    </flux:description>
                </flux:field>

                <div class="flex gap-2">
                    <flux:spacer />

                    <flux:modal.close>
                        <flux:button variant="ghost">Batal</flux:button>
                    </flux:modal.close>

                    <flux:button type="submit" variant="primary">
                        {{ $editingCustomerId ? 'Perbarui' : 'Simpan' }}
                    </flux:button>
                </div>
            </div>
        </form>
    </flux:modal>

    <!-- Customer Detail Modal -->
    <flux:modal wire:model.self="showDetailModal" name="customer-detail" class="min-w-3xl max-w-4xl" wire:close="resetDetailModal">
        @if($this->selectedCustomer)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Detail Pelanggan</flux:heading>
                    <flux:text class="mt-2">Informasi lengkap dan riwayat transaksi pelanggan</flux:text>
                </div>

                <!-- Customer Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-3">Informasi Pelanggan</h3>
                            <div class="bg-zinc-50 dark:bg-zinc-800 p-4 rounded-lg space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-zinc-600 dark:text-zinc-400">Nama:</span>
                                    <span class="font-medium">{{ $this->selectedCustomer->name }}</span>
                                </div>
                                @if($this->selectedCustomer->email)
                                    <div class="flex justify-between">
                                        <span class="text-zinc-600 dark:text-zinc-400">Email:</span>
                                        <span class="font-medium">{{ $this->selectedCustomer->email }}</span>
                                    </div>
                                @endif
                                @if($this->selectedCustomer->phone)
                                    <div class="flex justify-between">
                                        <span class="text-zinc-600 dark:text-zinc-400">Telepon:</span>
                                        <span class="font-medium">{{ $this->selectedCustomer->phone }}</span>
                                    </div>
                                @endif
                                @if($this->selectedCustomer->address)
                                    <div class="flex justify-between items-start">
                                        <span class="text-zinc-600 dark:text-zinc-400">Alamat:</span>
                                        <span class="font-medium text-right max-w-xs">{{ $this->selectedCustomer->address }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between">
                                    <span class="text-zinc-600 dark:text-zinc-400">Status:</span>
                                    <flux:badge variant="{{ $this->selectedCustomer->is_active ? 'primary' : 'outline' }}" size="sm">
                                        {{ $this->selectedCustomer->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                    </flux:badge>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-zinc-600 dark:text-zinc-400">Bergabung:</span>
                                    <span class="font-medium">{{ $this->selectedCustomer->created_at->format('d F Y') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-3">Statistik Transaksi</h3>
                            <div class="bg-zinc-50 dark:bg-zinc-800 p-4 rounded-lg space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-zinc-600 dark:text-zinc-400">Total Transaksi:</span>
                                    <span class="font-bold text-lg">{{ $this->selectedCustomer->transactions_count }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-zinc-600 dark:text-zinc-400">Total Pembelian:</span>
                                    <span class="font-bold text-lg text-green-600 dark:text-green-400">
                                        Rp {{ number_format($this->selectedCustomer->transactions_sum_total_amount ?? 0, 0, ',', '.') }}
                                    </span>
                                </div>
                                @if($this->selectedCustomer->transactions_count > 0)
                                    <div class="flex justify-between">
                                        <span class="text-zinc-600 dark:text-zinc-400">Rata-rata per Transaksi:</span>
                                        <span class="font-medium">
                                            Rp {{ number_format(($this->selectedCustomer->transactions_sum_total_amount ?? 0) / $this->selectedCustomer->transactions_count, 0, ',', '.') }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions -->
                @if($this->selectedCustomer->transactions->count() > 0)
                    <div>
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-3">Transaksi Terakhir</h3>
                        <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden">
                            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                <thead class="bg-zinc-50 dark:bg-zinc-800">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">No. Transaksi</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Tanggal</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Total</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Status</th>
                                </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach($this->selectedCustomer->transactions as $transaction)
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $transaction->transaction_number }}</td>
                                        <td class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">{{ $transaction->transaction_date->format('d M Y H:i') }}</td>
                                        <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <flux:badge variant="{{ $transaction->status === 'completed' ? 'primary' : 'outline' }}" size="sm">
                                                {{ ucfirst($transaction->status) }}
                                            </flux:badge>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <div class="flex gap-2">
                    <flux:spacer />
                    <flux:modal.close>
                        <flux:button variant="primary">Tutup</flux:button>
                    </flux:modal.close>
                </div>
            </div>
        @endif
    </flux:modal>

    <!-- Delete Confirmation Modals -->
    @foreach ($this->customers as $customer)
        @if($customer->transactions_count === 0)
            <flux:modal name="delete-customer-{{ $customer->id }}" class="min-w-[22rem]">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">Hapus Pelanggan?</flux:heading>
                        <flux:text class="mt-2">
                            <p>Anda akan menghapus pelanggan "{{ $customer->name }}".</p>
                            <p>Tindakan ini tidak dapat dibatalkan.</p>
                        </flux:text>
                    </div>

                    <div class="flex gap-2">
                        <flux:spacer />

                        <flux:modal.close>
                            <flux:button variant="ghost">Batal</flux:button>
                        </flux:modal.close>

                        <flux:button wire:click="delete({{ $customer->id }})" variant="danger">
                            Hapus
                        </flux:button>
                    </div>
                </div>
            </flux:modal>
        @endif
    @endforeach
</div>
