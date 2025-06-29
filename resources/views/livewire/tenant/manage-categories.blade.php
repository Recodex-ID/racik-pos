<div>
    <header class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">Kategori Produk</flux:heading>
            <flux:subheading>Kelola kategori produk untuk {{ $this->currentTenant->name }}</flux:subheading>
        </div>

        <flux:button wire:click="create" variant="primary" icon="plus">
            Buat Kategori
        </flux:button>
    </header>

    @if (session()->has('message'))
        <flux:callout variant="success" icon="check-circle" heading="{{ session('message') }}" class="mb-6" />
    @endif

    @if (session()->has('error'))
        <flux:callout variant="danger" icon="exclamation-triangle" heading="{{ session('error') }}" class="mb-6" />
    @endif

    <div class="mb-6">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari kategori..." icon="magnifying-glass" />
    </div>

    <div class="border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Nama Kategori</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Deskripsi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Jumlah Produk</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Dibuat</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Aksi</th>
                </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($this->categories as $category)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $category->name }}</td>
                        <td class="px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400 max-w-xs">
                            {{ $category->description ? Str::limit($category->description, 50) : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            <flux:badge variant="outline" size="sm">
                                {{ $category->products_count }} produk
                            </flux:badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            <flux:badge color="{{ $category->is_active ? 'green' : 'red' }}" size="sm">
                                {{ $category->is_active ? 'Aktif' : 'Tidak Aktif' }}
                            </flux:badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $category->created_at->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <flux:button wire:click="edit({{ $category->id }})" size="sm" variant="primary" color="blue" icon="pencil" />
                                <flux:modal.trigger name="delete-category-{{ $category->id }}">
                                    <flux:button size="sm" variant="primary" color="red" icon="trash" />
                                </flux:modal.trigger>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center">
                            <div class="flex flex-col items-center justify-center space-y-3">
                                <div class="w-12 h-12 bg-zinc-100 dark:bg-zinc-800 rounded-lg flex items-center justify-center">
                                    <flux:icon name="folder" class="w-6 h-6 text-zinc-400" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Belum ada kategori</p>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Mulai dengan membuat kategori produk pertama</p>
                                </div>
                                <flux:button wire:click="create" size="sm" variant="primary" icon="plus">
                                    Buat Kategori Pertama
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
        {{ $this->categories->links('custom.pagination') }}
    </div>

    <!-- Modal Form -->
    <flux:modal wire:model.self="showModal" name="category-form" class="min-w-2xl max-w-3xl" wire:close="resetForm">
        <form wire:submit.prevent="save">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">
                        {{ $editingCategoryId ? 'Edit Kategori' : 'Tambah Kategori Baru' }}
                    </flux:heading>
                    <flux:text class="mt-2">
                        {{ $editingCategoryId ? 'Ubah informasi kategori produk.' : 'Buat kategori baru untuk mengorganisasi produk.' }}
                    </flux:text>
                </div>

                <flux:field>
                    <flux:label>Nama Kategori</flux:label>
                    <flux:input wire:model="name" placeholder="Masukkan nama kategori..." />
                    <flux:error name="name" />
                    <flux:description>
                        Nama kategori akan digunakan untuk mengelompokkan produk
                    </flux:description>
                </flux:field>

                <flux:field>
                    <flux:label>Deskripsi</flux:label>
                    <flux:textarea wire:model="description" placeholder="Masukkan deskripsi kategori (opsional)..." rows="3" />
                    <flux:error name="description" />
                    <flux:description>
                        Deskripsi singkat tentang kategori ini
                    </flux:description>
                </flux:field>

                <flux:field>
                    <flux:label>Status</flux:label>
                    <flux:checkbox wire:model="is_active" label="Aktif" />
                    <flux:error name="is_active" />
                    <flux:description>
                        Kategori aktif dapat digunakan untuk produk baru
                    </flux:description>
                </flux:field>

                <div class="flex gap-2">
                    <flux:spacer />

                    <flux:modal.close>
                        <flux:button variant="ghost">Batal</flux:button>
                    </flux:modal.close>

                    <flux:button type="submit" variant="primary">
                        {{ $editingCategoryId ? 'Perbarui' : 'Simpan' }}
                    </flux:button>
                </div>
            </div>
        </form>
    </flux:modal>

    <!-- Delete Confirmation Modals -->
    @foreach ($this->categories as $category)
        <flux:modal name="delete-category-{{ $category->id }}" class="min-w-[22rem]">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Hapus Kategori?</flux:heading>
                    <flux:text class="mt-2">
                        <p>Anda akan menghapus kategori "{{ $category->name }}".</p>
                        @if($category->products_count > 0)
                            <p class="text-red-600 dark:text-red-400 font-medium">
                                Kategori ini memiliki {{ $category->products_count }} produk dan tidak dapat dihapus!
                            </p>
                            <p>Pindahkan semua produk ke kategori lain terlebih dahulu.</p>
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

                    @if($category->products_count == 0)
                        <flux:button wire:click="delete({{ $category->id }})" variant="danger">
                            Hapus
                        </flux:button>
                    @endif
                </div>
            </div>
        </flux:modal>
    @endforeach
</div>
