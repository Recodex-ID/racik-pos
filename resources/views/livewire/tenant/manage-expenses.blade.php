<div>
    <header class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">Kelola Pengeluaran</flux:heading>
            <flux:subheading>Kelola data pengeluaran dan biaya operasional</flux:subheading>
        </div>

        <flux:button wire:click="create" variant="primary" icon="plus">
            Tambah Pengeluaran
        </flux:button>
    </header>

    @if (session()->has('message'))
        <flux:callout variant="success" icon="check-circle" heading="{{ session('message') }}" class="mb-6" />
    @endif

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-zinc-800 rounded-lg p-6 border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center">
                <div class="p-2 bg-red-100 dark:bg-red-900 rounded-lg">
                    <flux:icon name="currency-dollar" class="w-6 h-6 text-red-600 dark:text-red-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Total Pengeluaran</p>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                        Rp {{ number_format($this->totalExpenses, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-zinc-800 rounded-lg p-6 border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                    <flux:icon name="document-text" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Total Transaksi</p>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                        {{ $this->expenses->total() }}
                    </p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-zinc-800 rounded-lg p-6 border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                    <flux:icon name="chart-bar" class="w-6 h-6 text-green-600 dark:text-green-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Rata-rata per Transaksi</p>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                        Rp {{ $this->expenses->total() > 0 ? number_format($this->totalExpenses / $this->expenses->total(), 0, ',', '.') : '0' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari pengeluaran..." icon="magnifying-glass" />

        <flux:select wire:model.live="selectedCategory" placeholder="Semua Kategori">
            <flux:select.option value="">Semua Kategori</flux:select.option>
            @foreach ($this->categories as $category)
                <flux:select.option value="{{ $category }}">{{ $category }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:button wire:click="resetFilters" variant="outline" icon="x-mark">
            Reset Filter
        </flux:button>
    </div>

    <div class="border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Judul</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Kategori</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Jumlah</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Struk</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Aksi</th>
                </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($this->expenses as $expense)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $expense->title }}</div>
                            @if($expense->description)
                                <div class="text-sm text-zinc-500 dark:text-zinc-400 truncate max-w-xs">{{ $expense->description }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <flux:badge variant="primary" size="sm">
                                {{ $expense->category }}
                            </flux:badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            Rp {{ number_format($expense->amount, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $expense->expense_date->format('d M Y') }}
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <flux:button wire:click="edit({{ $expense->id }})" size="sm" variant="primary" color="blue" icon="pencil" />
                                <flux:modal.trigger name="delete-expense-{{ $expense->id }}">
                                    <flux:button size="sm" variant="primary" color="red" icon="trash" />
                                </flux:modal.trigger>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-zinc-500 dark:text-zinc-400">
                            Tidak ada data pengeluaran
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $this->expenses->links('custom.pagination') }}
    </div>

    <!-- Modal Form -->
    <flux:modal wire:model.self="showModal" name="expense-form" class="min-w-2xl max-w-3xl" wire:close="resetForm">
        <form wire:submit.prevent="save">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">
                        {{ $editingExpenseId ? 'Edit Pengeluaran' : 'Tambah Pengeluaran Baru' }}
                    </flux:heading>
                    <flux:text class="mt-2">
                        {{ $editingExpenseId ? 'Modifikasi informasi pengeluaran.' : 'Catat pengeluaran baru untuk operasional.' }}
                    </flux:text>
                </div>

                <flux:field>
                    <flux:label>Judul</flux:label>
                    <flux:input wire:model="title" placeholder="Masukkan judul pengeluaran..." />
                    <flux:error name="title" />
                </flux:field>

                <flux:field>
                    <flux:label>Deskripsi</flux:label>
                    <flux:textarea wire:model="description" placeholder="Deskripsi pengeluaran (opsional)..." rows="3" />
                    <flux:error name="description" />
                </flux:field>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Jumlah (Rp)</flux:label>
                        <flux:input wire:model="amount" type="number" step="0.01" placeholder="0" />
                        <flux:error name="amount" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Tanggal Pengeluaran</flux:label>
                        <flux:input wire:model="expense_date" type="date" />
                        <flux:error name="expense_date" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Kategori</flux:label>

                    <flux:switch wire:model.live="isManualCategory" label="Input kategori manual" align="left" class="mb-2" />

                    @if($isManualCategory)
                        <flux:input wire:model="category" placeholder="Masukkan kategori baru..." />
                    @else
                        <flux:select wire:model="category" placeholder="Pilih kategori">
                            @foreach ($this->categories as $cat)
                                <flux:select.option value="{{ $cat }}">{{ $cat }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    @endif

                    <flux:error name="category" />
                </flux:field>

                <flux:field>
                    <flux:label>File Struk/Bukti (Opsional)</flux:label>
                    <flux:input wire:model="receipt_file" type="file" accept="image/*,application/pdf" />
                    <flux:error name="receipt_file" />
                    <flux:description>
                        Upload gambar atau PDF maksimal 2MB
                    </flux:description>
                </flux:field>

                <div class="flex gap-2">
                    <flux:spacer />

                    <flux:modal.close>
                        <flux:button variant="ghost">Batal</flux:button>
                    </flux:modal.close>

                    <flux:button type="submit" variant="primary">
                        {{ $editingExpenseId ? 'Perbarui' : 'Simpan' }}
                    </flux:button>
                </div>
            </div>
        </form>
    </flux:modal>

    <!-- Delete Confirmation Modals -->
    @foreach ($this->expenses as $expense)
        <flux:modal name="delete-expense-{{ $expense->id }}" class="min-w-[22rem]">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Hapus Pengeluaran?</flux:heading>
                    <flux:text class="mt-2">
                        <p>Anda akan menghapus pengeluaran "{{ $expense->title }}" sebesar Rp {{ number_format($expense->amount, 0, ',', '.') }}.</p>
                        <p>Tindakan ini tidak dapat dibatalkan.</p>
                    </flux:text>
                </div>

                <div class="flex gap-2">
                    <flux:spacer />

                    <flux:modal.close>
                        <flux:button variant="ghost">Batal</flux:button>
                    </flux:modal.close>

                    <flux:button wire:click="delete({{ $expense->id }})" variant="danger">
                        Hapus
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endforeach
</div>
