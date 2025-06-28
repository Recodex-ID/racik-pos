<div>
    <header class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">Store Management</flux:heading>
            <flux:subheading>Kelola toko dan informasi mereka</flux:subheading>
        </div>

        <flux:button wire:click="create" variant="primary" icon="plus">
            Buat Toko
        </flux:button>
    </header>

    @if (session()->has('message'))
        <flux:callout variant="success" icon="check-circle" heading="{{ session('message') }}" class="mb-6" />
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari toko..." icon="magnifying-glass" />

        <flux:select wire:model.live="filterTenant" placeholder="Filter berdasarkan tenant...">
            <flux:select.option value="">Semua Tenant</flux:select.option>
            @foreach ($this->tenants as $tenant)
                <flux:select.option value="{{ $tenant->id }}">{{ $tenant->name }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <div class="border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Nama Toko</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Tenant</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Alamat</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Telepon</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Dibuat</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Aksi</th>
                </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($this->stores as $store)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $store->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            <flux:badge variant="outline" size="sm">
                                {{ $store->tenant->name }}
                            </flux:badge>
                        </td>
                        <td class="px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400 max-w-xs truncate">{{ $store->address }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ $store->phone ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            <flux:badge variant="{{ $store->is_active ? 'primary' : 'outline' }}" size="sm">
                                {{ $store->is_active ? 'Aktif' : 'Tidak Aktif' }}
                            </flux:badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $store->created_at->format('d F Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <flux:button wire:click="edit({{ $store->id }})" size="sm" variant="primary" color="blue" icon="pencil" />
                                <flux:modal.trigger name="delete-store-{{ $store->id }}">
                                    <flux:button size="sm" variant="primary" color="red" icon="trash" />
                                </flux:modal.trigger>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-zinc-500 dark:text-zinc-400">
                            Tidak ada toko ditemukan
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $this->stores->links('custom.pagination') }}
    </div>

    <!-- Modal Form -->
    <flux:modal wire:model.self="showModal" name="store-form" class="min-w-2xl max-w-3xl" wire:close="resetForm">
        <form wire:submit.prevent="save">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">
                        {{ $editingStoreId ? 'Edit Toko' : 'Tambah Toko Baru' }}
                    </flux:heading>
                    <flux:text class="mt-2">
                        {{ $editingStoreId ? 'Ubah informasi toko.' : 'Buat toko baru dengan informasi yang lengkap.' }}
                    </flux:text>
                </div>

                <flux:field>
                    <flux:label>Nama Toko</flux:label>
                    <flux:input wire:model="name" placeholder="Masukkan nama toko..." />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Tenant</flux:label>
                    <flux:select wire:model="tenant_id" placeholder="Pilih tenant...">
                        @foreach ($this->tenants as $tenant)
                            <flux:select.option value="{{ $tenant->id }}">{{ $tenant->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="tenant_id" />
                    <flux:description>
                        Pilih tenant pemilik toko ini
                    </flux:description>
                </flux:field>

                <flux:field>
                    <flux:label>Alamat</flux:label>
                    <flux:textarea wire:model="address" placeholder="Masukkan alamat lengkap..." rows="3" />
                    <flux:error name="address" />
                </flux:field>

                <flux:field>
                    <flux:label>Telepon</flux:label>
                    <flux:input wire:model="phone" placeholder="Masukkan nomor telepon..." />
                    <flux:error name="phone" />
                </flux:field>

                <flux:field>
                    <flux:label>Status</flux:label>
                    <flux:checkbox wire:model="is_active" label="Aktif" />
                    <flux:error name="is_active" />
                    <flux:description>
                        Centang jika toko aktif dan dapat beroperasi
                    </flux:description>
                </flux:field>

                <div class="flex gap-2">
                    <flux:spacer />

                    <flux:modal.close>
                        <flux:button variant="ghost">Batal</flux:button>
                    </flux:modal.close>

                    <flux:button type="submit" variant="primary">
                        {{ $editingStoreId ? 'Perbarui' : 'Simpan' }}
                    </flux:button>
                </div>
            </div>
        </form>
    </flux:modal>

    <!-- Delete Confirmation Modals -->
    @foreach ($this->stores as $store)
        <flux:modal name="delete-store-{{ $store->id }}" class="min-w-[22rem]">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Hapus Toko?</flux:heading>
                    <flux:text class="mt-2">
                        <p>Anda akan menghapus toko "{{ $store->name }}" milik {{ $store->tenant->name }}.</p>
                        <p class="text-red-600 dark:text-red-400 font-medium">Tindakan ini akan menghapus semua data terkait seperti produk, kategori, pelanggan, dan transaksi!</p>
                        <p>Tindakan ini tidak dapat dibatalkan.</p>
                    </flux:text>
                </div>

                <div class="flex gap-2">
                    <flux:spacer />

                    <flux:modal.close>
                        <flux:button variant="ghost">Batal</flux:button>
                    </flux:modal.close>

                    <flux:button wire:click="delete({{ $store->id }})" variant="danger">
                        Hapus
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endforeach
</div>
