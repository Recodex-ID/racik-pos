<div>
    <header class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">Tenant Management</flux:heading>
            <flux:subheading>Kelola tenant dan informasi mereka</flux:subheading>
        </div>

        <flux:button wire:click="create" variant="primary" icon="plus">
            Buat
        </flux:button>
    </header>

    @if (session()->has('message'))
        <flux:callout variant="success" icon="check-circle" heading="{{ session('message') }}" class="mb-6" />
    @endif

    <div class="mb-6">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari tenant..." icon="magnifying-glass" />
    </div>

    <div class="border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Telepon</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Dibuat</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Aksi</th>
                </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($this->tenants as $tenant)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $tenant->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ $tenant->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ $tenant->phone ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            <flux:badge variant="{{ $tenant->is_active ? 'primary' : 'outline' }}" size="sm">
                                {{ $tenant->is_active ? 'Aktif' : 'Tidak Aktif' }}
                            </flux:badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $tenant->created_at->format('d F Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <flux:button wire:click="edit({{ $tenant->id }})" size="sm" variant="primary" color="blue" icon="pencil" />
                                <flux:modal.trigger name="delete-tenant-{{ $tenant->id }}">
                                    <flux:button size="sm" variant="primary" color="red" icon="trash" />
                                </flux:modal.trigger>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-zinc-500 dark:text-zinc-400">
                            Tidak ada tenant ditemukan
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $this->tenants->links('custom.pagination') }}
    </div>

    <!-- Modal Form -->
    <flux:modal wire:model.self="showModal" name="tenant-form" class="min-w-2xl max-w-3xl" wire:close="resetForm">
        <form wire:submit.prevent="save">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">
                        {{ $editingTenantId ? 'Edit Tenant' : 'Tambah Tenant Baru' }}
                    </flux:heading>
                    <flux:text class="mt-2">
                        {{ $editingTenantId ? 'Ubah informasi tenant.' : 'Buat tenant baru dengan informasi yang lengkap.' }}
                    </flux:text>
                </div>

                <flux:field>
                    <flux:label>Nama</flux:label>
                    <flux:input wire:model="name" placeholder="Masukkan nama..." />
                    <flux:error name="name" />
                </flux:field>

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

                <flux:field>
                    <flux:label>Alamat</flux:label>
                    <flux:textarea wire:model="address" placeholder="Masukkan alamat..." rows="3" />
                    <flux:error name="address" />
                </flux:field>

                <flux:field>
                    <flux:label>Status</flux:label>
                    <flux:checkbox wire:model="is_active" label="Aktif" />
                    <flux:error name="is_active" />
                    <flux:description>
                        Centang jika tenant aktif
                    </flux:description>
                </flux:field>

                <div class="flex gap-2">
                    <flux:spacer />

                    <flux:modal.close>
                        <flux:button variant="ghost">Batal</flux:button>
                    </flux:modal.close>

                    <flux:button type="submit" variant="primary">
                        {{ $editingTenantId ? 'Perbarui' : 'Simpan' }}
                    </flux:button>
                </div>
            </div>
        </form>
    </flux:modal>

    <!-- Delete Confirmation Modals -->
    @foreach ($this->tenants as $tenant)
        <flux:modal name="delete-tenant-{{ $tenant->id }}" class="min-w-[22rem]">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Hapus Tenant?</flux:heading>
                    <flux:text class="mt-2">
                        <p>Anda akan menghapus tenant "{{ $tenant->name }}" ({{ $tenant->email }}).</p>
                        <p>Tindakan ini tidak dapat dibatalkan.</p>
                    </flux:text>
                </div>

                <div class="flex gap-2">
                    <flux:spacer />

                    <flux:modal.close>
                        <flux:button variant="ghost">Batal</flux:button>
                    </flux:modal.close>

                    <flux:button wire:click="delete({{ $tenant->id }})" variant="danger">
                        Hapus
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endforeach
</div>