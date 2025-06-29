<div>
    <header class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">Produk</flux:heading>
            <flux:subheading>Kelola produk dan inventory untuk {{ $this->currentTenant->name }}</flux:subheading>
        </div>

        <div class="flex gap-2">
            @if($this->lowStockCount > 0)
                <flux:badge variant="danger" size="sm" icon="exclamation-triangle">
                    {{ $this->lowStockCount }} produk stok rendah
                </flux:badge>
            @endif
            <flux:button wire:click="create" variant="primary" icon="plus">
                Tambah Produk
            </flux:button>
        </div>
    </header>

    @if (session()->has('message'))
        <flux:callout variant="success" icon="check-circle" heading="{{ session('message') }}" class="mb-6" />
    @endif

    @if (session()->has('error'))
        <flux:callout variant="danger" icon="exclamation-triangle" heading="{{ session('error') }}" class="mb-6" />
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari produk (nama, SKU)..." icon="magnifying-glass" />

        <flux:select wire:model.live="filterCategory" placeholder="Filter kategori...">
            <flux:select.option value="">Semua Kategori</flux:select.option>
            @foreach ($this->categories as $category)
                <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="filterStock" placeholder="Filter stok...">
            <flux:select.option value="">Semua Stok</flux:select.option>
            <flux:select.option value="available">Tersedia</flux:select.option>
            <flux:select.option value="low">Stok Rendah</flux:select.option>
            <flux:select.option value="out">Stok Habis</flux:select.option>
        </flux:select>
    </div>

    <div class="border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Produk</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">SKU</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Kategori</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Harga</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Stok</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Aksi</th>
                </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($this->products as $product)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                        <td class="px-6 py-4">
                            <div>
                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $product->name }}</div>
                                @if($product->description)
                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ Str::limit($product->description, 40) }}</div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-zinc-500 dark:text-zinc-400">{{ $product->sku }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            <flux:badge variant="outline" size="sm">
                                {{ $product->category->name }}
                            </flux:badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">HPP: Rp {{ number_format($product->cost, 0, ',', '.') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-medium {{ $product->stock <= $product->min_stock ? 'text-red-600 dark:text-red-400' : 'text-zinc-900 dark:text-zinc-100' }}">
                                    {{ $product->stock }}
                                </span>
                                @if($product->stock <= $product->min_stock)
                                    <flux:icon name="exclamation-triangle" class="w-4 h-4 text-red-500" />
                                @endif
                            </div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">Min: {{ $product->min_stock }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <flux:badge variant="{{ $product->is_active ? 'primary' : 'outline' }}" size="sm">
                                {{ $product->is_active ? 'Aktif' : 'Tidak Aktif' }}
                            </flux:badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <flux:button wire:click="edit({{ $product->id }})" size="sm" variant="primary" color="blue" icon="pencil" />
                                <flux:button wire:click="openStockModal({{ $product->id }})" size="sm" variant="primary" color="green" icon="arrows-up-down" />
                                <flux:modal.trigger name="delete-product-{{ $product->id }}">
                                    <flux:button size="sm" variant="primary" color="red" icon="trash" />
                                </flux:modal.trigger>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center">
                            <div class="flex flex-col items-center justify-center space-y-3">
                                <div class="w-12 h-12 bg-zinc-100 dark:bg-zinc-800 rounded-lg flex items-center justify-center">
                                    <flux:icon name="cube" class="w-6 h-6 text-zinc-400" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Belum ada produk</p>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Mulai dengan menambahkan produk pertama</p>
                                </div>
                                <flux:button wire:click="create" size="sm" variant="primary" icon="plus">
                                    Tambah Produk Pertama
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
        {{ $this->products->links('custom.pagination') }}
    </div>

    <!-- Modal Form Product -->
    <flux:modal wire:model.self="showModal" name="product-form" class="min-w-4xl max-w-5xl" wire:close="resetForm">
        <form wire:submit.prevent="save">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">
                        {{ $editingProductId ? 'Edit Produk' : 'Tambah Produk Baru' }}
                    </flux:heading>
                    <flux:text class="mt-2">
                        {{ $editingProductId ? 'Ubah informasi produk dan inventory.' : 'Tambahkan produk baru ke inventory toko.' }}
                    </flux:text>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <flux:heading size="md">Informasi Produk</flux:heading>

                        <flux:field>
                            <flux:label>Nama Produk</flux:label>
                            <flux:input wire:model="name" placeholder="Masukkan nama produk..." />
                            <flux:error name="name" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Deskripsi</flux:label>
                            <flux:textarea wire:model="description" placeholder="Deskripsi produk (opsional)..." rows="3" />
                            <flux:error name="description" />
                        </flux:field>

                        <flux:field>
                            <flux:label>SKU (Kode Produk)</flux:label>
                            <div class="flex gap-2">
                                <flux:input wire:model="sku" placeholder="Masukkan SKU..." class="flex-1" />
                                <flux:button type="button" wire:click="generateSku" size="sm" variant="outline" icon="refresh">
                                    Generate
                                </flux:button>
                            </div>
                            <flux:error name="sku" />
                            <flux:description>
                                SKU harus unik untuk setiap produk
                            </flux:description>
                        </flux:field>

                        <flux:field>
                            <flux:label>Kategori</flux:label>
                            <flux:select wire:model="category_id" placeholder="Pilih kategori...">
                                @foreach ($this->categories as $category)
                                    <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="category_id" />
                        </flux:field>
                    </div>

                    <div class="space-y-4">
                        <flux:heading size="md">Harga & Inventory</flux:heading>

                        <div class="grid grid-cols-2 gap-4">
                            <flux:field>
                                <flux:label>Harga Jual</flux:label>
                                <flux:input wire:model="price" type="number" step="0.01" placeholder="0" />
                                <flux:error name="price" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Harga Pokok (HPP)</flux:label>
                                <flux:input wire:model="cost" type="number" step="0.01" placeholder="0" />
                                <flux:error name="cost" />
                            </flux:field>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <flux:field>
                                <flux:label>Stok</flux:label>
                                <flux:input wire:model="stock" type="number" placeholder="0" />
                                <flux:error name="stock" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Stok Minimum</flux:label>
                                <flux:input wire:model="min_stock" type="number" placeholder="5" />
                                <flux:error name="min_stock" />
                                <flux:description>
                                    Peringatan jika stok di bawah nilai ini
                                </flux:description>
                            </flux:field>
                        </div>

                        <flux:field>
                            <flux:label>Status</flux:label>
                            <flux:checkbox wire:model="is_active" label="Produk Aktif" />
                            <flux:error name="is_active" />
                            <flux:description>
                                Produk aktif dapat dijual di kasir
                            </flux:description>
                        </flux:field>

                        @if($price && $cost)
                            <div class="p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100 mb-2">Analisis Margin</div>
                                <div class="space-y-1 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-zinc-600 dark:text-zinc-400">Margin:</span>
                                        <span class="font-medium">Rp {{ number_format($price - $cost, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-zinc-600 dark:text-zinc-400">Margin %:</span>
                                        <span class="font-medium">{{ $cost > 0 ? round((($price - $cost) / $cost) * 100, 1) : 0 }}%</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex gap-2">
                    <flux:spacer />

                    <flux:modal.close>
                        <flux:button variant="ghost">Batal</flux:button>
                    </flux:modal.close>

                    <flux:button type="submit" variant="primary">
                        {{ $editingProductId ? 'Perbarui' : 'Simpan' }}
                    </flux:button>
                </div>
            </div>
        </form>
    </flux:modal>

    <!-- Modal Stock Adjustment -->
    <flux:modal wire:model.self="showStockModal" name="stock-adjustment" class="min-w-md max-w-lg" wire:close="resetStockForm">
        <form wire:submit.prevent="adjustStock">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Penyesuaian Stok</flux:heading>
                    <flux:text class="mt-2">
                        Tambah atau kurangi stok produk
                    </flux:text>
                </div>

                <flux:field>
                    <flux:label>Jumlah Penyesuaian</flux:label>
                    <flux:input wire:model="stockAdjustment" type="number" placeholder="+10 atau -5" />
                    <flux:error name="stockAdjustment" />
                    <flux:description>
                        Gunakan angka positif untuk menambah, negatif untuk mengurangi stok
                    </flux:description>
                </flux:field>

                <flux:field>
                    <flux:label>Catatan</flux:label>
                    <flux:input wire:model="stockNote" placeholder="Alasan penyesuaian stok..." />
                    <flux:error name="stockNote" />
                </flux:field>

                <div class="flex gap-2">
                    <flux:spacer />

                    <flux:modal.close>
                        <flux:button variant="ghost">Batal</flux:button>
                    </flux:modal.close>

                    <flux:button type="submit" variant="primary">
                        Sesuaikan Stok
                    </flux:button>
                </div>
            </div>
        </form>
    </flux:modal>

    <!-- Delete Confirmation Modals -->
    @foreach ($this->products as $product)
        <flux:modal name="delete-product-{{ $product->id }}" class="min-w-[22rem]">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Hapus Produk?</flux:heading>
                    <flux:text class="mt-2">
                        <p>Anda akan menghapus produk "{{ $product->name }}" ({{ $product->sku }}).</p>
                        @if($product->transactionItems()->count() > 0)
                            <p class="text-red-600 dark:text-red-400 font-medium">
                                Produk ini memiliki riwayat transaksi dan tidak dapat dihapus!
                            </p>
                        @else
                            <p>Tindakan ini tidak dapat dibatalkan.</p>
                        @endif
                    </flux:text>
                </div>

                <div class="flex gap-2">
                    <flux:spacer />

                    <flux:modal.close>
                        <flux:button variant="ghost">Batal</flux:button>
                    </flux:modal.close>

                    @if($product->transactionItems()->count() == 0)
                        <flux:button wire:click="delete({{ $product->id }})" variant="danger">
                            Hapus
                        </flux:button>
                    @endif
                </div>
            </div>
        </flux:modal>
    @endforeach
</div>
