<div>
    <header class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">
                {{ $showSuperAdmins ? 'Super Admin Management' : 'User Management' }}
            </flux:heading>
            <flux:subheading>
                {{ $showSuperAdmins ? 'Manage super administrator accounts' : 'Manage regular users and their roles' }}
            </flux:subheading>
        </div>

        <div class="flex items-center space-x-2">
            <flux:button 
                wire:click="toggleSuperAdminView" 
                variant="outline" 
                icon="{{ $showSuperAdmins ? 'users' : 'shield-check' }}">
                {{ $showSuperAdmins ? 'Show Regular Users' : 'Show Super Admins' }}
            </flux:button>
            
            @if(!$showSuperAdmins)
                <flux:button wire:click="create" variant="primary" icon="plus">
                    Create User
                </flux:button>
            @endif
        </div>
    </header>

    @if (session()->has('message'))
        <flux:callout variant="success" icon="check-circle" heading="{{ session('message') }}" class="mb-6" />
    @endif

    <div class="mb-6">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search users..." icon="magnifying-glass" />
    </div>

    <div class="border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Username</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Tenant</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Roles</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Created</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Actions</th>
                </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                @if($showSuperAdmins)
                    @forelse ($this->superAdmins as $user)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                <div class="flex items-center">
                                    <flux:icon name="shield-check" class="w-4 h-4 text-red-500 mr-2" />
                                    {{ $user->name }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ $user->username }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ $user->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                <flux:badge variant="danger" size="sm">
                                    System Wide
                                </flux:badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                @foreach ($user->roles as $role)
                                    <flux:badge variant="danger" size="sm" class="mr-1">
                                        {{ $role->name }}
                                    </flux:badge>
                                @endforeach
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $user->created_at->format('d F Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <flux:button wire:click="edit({{ $user->id }})" size="sm" variant="primary" color="blue" icon="pencil" />
                                    <flux:modal.trigger name="delete-user-{{ $user->id }}">
                                        <flux:button size="sm" variant="primary" color="red" icon="trash" />
                                    </flux:modal.trigger>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-zinc-500 dark:text-zinc-400">
                                No super administrators found
                            </td>
                        </tr>
                    @endforelse
                @else
                    @forelse ($this->users as $user)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $user->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ $user->username }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ $user->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                @if($user->tenant)
                                    <flux:badge variant="primary" size="sm">
                                        {{ $user->tenant->name }}
                                    </flux:badge>
                                @else
                                    <span class="text-zinc-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                @foreach ($user->roles as $role)
                                    <flux:badge variant="primary" size="sm" class="mr-1">
                                        {{ $role->name }}
                                    </flux:badge>
                                @endforeach
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $user->created_at->format('d F Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <flux:button wire:click="edit({{ $user->id }})" size="sm" variant="primary" color="blue" icon="pencil" />
                                    <flux:modal.trigger name="delete-user-{{ $user->id }}">
                                        <flux:button size="sm" variant="primary" color="red" icon="trash" />
                                    </flux:modal.trigger>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-zinc-500 dark:text-zinc-400">
                                No users found
                            </td>
                        </tr>
                    @endforelse
                @endif
                </tbody>
            </table>
        </div>
    </div>

    @if(!$showSuperAdmins)
        <div class="mt-4">
            {{ $this->users->links('custom.pagination') }}
        </div>
    @endif

    <!-- Modal Form -->
    <flux:modal wire:model.self="showModal" name="user-form" class="min-w-2xl max-w-3xl" wire:close="resetForm">
        <form wire:submit.prevent="save">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">
                        {{ $editingUserId ? 'Edit User' : 'Add New User' }}
                    </flux:heading>
                    <flux:text class="mt-2">
                        {{ $editingUserId ? 'Modify user information and selected roles.' : 'Create a new user with appropriate roles.' }}
                    </flux:text>
                </div>

                <flux:field>
                    <flux:label>Name</flux:label>
                    <flux:input wire:model="name" placeholder="Enter name..." />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Username</flux:label>
                    <flux:input wire:model="username" placeholder="Enter username..." />
                    <flux:error name="username" />
                </flux:field>

                <flux:field>
                    <flux:label>Email</flux:label>
                    <flux:input wire:model="email" type="email" placeholder="Enter email..." />
                    <flux:error name="email" />
                </flux:field>

                <flux:field>
                    <flux:label>Password</flux:label>
                    <flux:input wire:model="password" type="password" placeholder="Enter password..." />
                    <flux:error name="password" />
                    @if($editingUserId)
                        <flux:description>
                            Leave empty if you don't want to change
                        </flux:description>
                    @endif
                </flux:field>

                <flux:field>
                    <flux:label>Tenant</flux:label>
                    <flux:select wire:model="tenant_id" placeholder="Pilih Tenant (Opsional)">
                        <option value="">Tidak ada tenant (Super Admin)</option>
                        @foreach ($this->tenants as $tenant)
                            <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="tenant_id" />
                    <flux:description>
                        Pilih tenant untuk user ini. Kosongkan jika Super Admin.
                    </flux:description>
                </flux:field>

                <flux:field>
                    <flux:label>Roles</flux:label>
                    <div class="space-y-2 max-h-32 overflow-y-auto border border-zinc-200 dark:border-zinc-700 rounded-lg p-3">
                        @foreach ($this->roles as $role)
                            <flux:checkbox
                                wire:model="selectedRoles"
                                value="{{ $role->name }}"
                                :label="$role->name"
                            />
                        @endforeach
                    </div>
                    <flux:error name="selectedRoles" />
                    <flux:description>
                        Select roles that this user will have
                    </flux:description>
                </flux:field>

                <div class="flex gap-2">
                    <flux:spacer />

                    <flux:modal.close>
                        <flux:button variant="ghost">Cancel</flux:button>
                    </flux:modal.close>

                    <flux:button type="submit" variant="primary">
                        {{ $editingUserId ? 'Update' : 'Save' }}
                    </flux:button>
                </div>
            </div>
        </form>
    </flux:modal>

    <!-- Delete Confirmation Modals -->
    @if($showSuperAdmins)
        @foreach ($this->superAdmins as $user)
            <flux:modal name="delete-user-{{ $user->id }}" class="min-w-[22rem]">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">Delete Super Admin?</flux:heading>
                        <flux:text class="mt-2">
                            <p>You are about to delete Super Admin "{{ $user->name }}" ({{ $user->email }}).</p>
                            <p class="text-red-600 font-semibold">Warning: This will remove system-wide administrative access!</p>
                            <p>This action cannot be undone.</p>
                        </flux:text>
                    </div>

                    <div class="flex gap-2">
                        <flux:spacer />

                        <flux:modal.close>
                            <flux:button variant="ghost">Cancel</flux:button>
                        </flux:modal.close>

                        <flux:button wire:click="delete({{ $user->id }})" variant="danger">
                            Delete Super Admin
                        </flux:button>
                    </div>
                </div>
            </flux:modal>
        @endforeach
    @else
        @foreach ($this->users as $user)
            <flux:modal name="delete-user-{{ $user->id }}" class="min-w-[22rem]">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">Delete User?</flux:heading>
                        <flux:text class="mt-2">
                            <p>You are about to delete user "{{ $user->name }}" ({{ $user->email }}).</p>
                            <p>This action cannot be undone.</p>
                        </flux:text>
                    </div>

                    <div class="flex gap-2">
                        <flux:spacer />

                        <flux:modal.close>
                            <flux:button variant="ghost">Cancel</flux:button>
                        </flux:modal.close>

                        <flux:button wire:click="delete({{ $user->id }})" variant="danger">
                            Delete
                        </flux:button>
                    </div>
                </div>
            </flux:modal>
        @endforeach
    @endif
</div>
