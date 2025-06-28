<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group :heading="__('Platform')" class="grid">
                    <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
                </flux:navlist.group>

                @role('Super Admin')
                <flux:navlist.group :heading="__('Administrator')" class="grid">
                    <flux:navlist.item icon="users" :href="route('admin.users')" :current="request()->routeIs('admin.users')" wire:navigate>Manage Users</flux:navlist.item>
                    <flux:navlist.item icon="shield-check" :href="route('admin.roles')" :current="request()->routeIs('admin.roles')" wire:navigate>Manage Roles</flux:navlist.item>
                    <flux:navlist.item icon="key" :href="route('admin.permissions')" :current="request()->routeIs('admin.permissions')" wire:navigate>Manage Permissions</flux:navlist.item>
                    <flux:navlist.item icon="building-office" :href="route('admin.tenants')" :current="request()->routeIs('admin.tenants')" wire:navigate>Manage Tenants</flux:navlist.item>
                    <flux:navlist.item icon="building-storefront" :href="route('admin.stores')" :current="request()->routeIs('admin.stores')" wire:navigate>Manage Stores</flux:navlist.item>
                </flux:navlist.group>
                @endrole

                @hasrole('Admin|User')
                <flux:navlist.group :heading="__('Point of Sale')" class="grid">
                    <flux:navlist.item icon="calculator" :href="route('pos.cashier')" :current="request()->routeIs('pos.cashier')" wire:navigate>Kasir</flux:navlist.item>
                </flux:navlist.group>
                @endhasrole

                @hasrole('Admin')
                <flux:navlist.group :heading="__('Toko')" class="grid">
                    <flux:navlist.item icon="folder" :href="route('store.categories')" :current="request()->routeIs('store.categories')" wire:navigate>Kategori Produk</flux:navlist.item>
                    <flux:navlist.item icon="cube" :href="route('store.products')" :current="request()->routeIs('store.products')" wire:navigate>Produk & Inventory</flux:navlist.item>
                    <flux:navlist.item icon="users" :href="route('store.customers')" :current="request()->routeIs('store.customers')" wire:navigate>Pelanggan</flux:navlist.item>
                </flux:navlist.group>

                <flux:navlist.group :heading="__('Laporan')" class="grid">
                    <flux:navlist.item icon="document-text" :href="route('store.transactions')" :current="request()->routeIs('store.transactions')" wire:navigate>History & Report Transaksi</flux:navlist.item>
                    <flux:navlist.item icon="chart-bar" :href="route('store.sales-reports')" :current="request()->routeIs('store.sales-reports')" wire:navigate>Laporan Penjualan</flux:navlist.item>
                    <flux:navlist.item icon="cube-transparent" :href="route('store.inventory-reports')" :current="request()->routeIs('store.inventory-reports')" wire:navigate>Laporan Stok</flux:navlist.item>
                </flux:navlist.group>
                @endhasrole
            </flux:navlist>

            <flux:spacer />

            <flux:navlist variant="outline">
                <flux:navlist.item icon="folder-git-2" href="https://github.com/zachran-recodex/laravel-livewire-crud.git" target="_blank">
                {{ __('Repository') }}
                </flux:navlist.item>

                <flux:navlist.item icon="book-open-text" href="https://github.com/zachran-recodex/laravel-livewire-crud.git" target="_blank">
                {{ __('Documentation') }}
                </flux:navlist.item>
            </flux:navlist>

            <!-- Desktop User Menu -->
            <flux:dropdown class="hidden lg:block" position="bottom" align="start">
                <flux:profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    icon:trailing="chevrons-up-down"
                />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
