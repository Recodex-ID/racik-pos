<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('index');
})->name('home');

Route::get('/dashboard', \App\Livewire\Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    // Administrator Routes
    Route::middleware(['role:Super Admin'])->prefix('administrator')->name('admin.')->group(function () {
        Route::get('/users', \App\Livewire\Administrator\ManageUsers::class)->name('users');
        Route::get('/roles', \App\Livewire\Administrator\ManageRoles::class)->name('roles');
        Route::get('/tenants', \App\Livewire\Administrator\ManageTenants::class)->name('tenants');
    });

    // Tenant Management Routes (untuk staff toko)
    Route::middleware(['role:Admin'])->prefix('tenant')->name('tenant.')->group(function () {
        Route::get('/categories', \App\Livewire\Tenant\ManageCategories::class)->name('categories');
        Route::get('/products', \App\Livewire\Tenant\ManageProducts::class)->name('products');
        Route::get('/customers', \App\Livewire\Tenant\ManageCustomers::class)->name('customers');    });

    // POS Routes (untuk kasir)
    Route::middleware(['role:Admin|Cashier'])->prefix('pos')->name('pos.')->group(function () {
        Route::get('/cashier', \App\Livewire\Pos\Cashier::class)->name('cashier');
    });

    // Reports Routes (untuk admin dan kasir)
    Route::middleware(['role:Admin|Cashier'])->prefix('reports')->name('reports.')->group(function () {
        Route::get('/monthly-transaction', \App\Livewire\Reports\MonthlyTransaction::class)->name('monthly-transaction');
    });

    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
