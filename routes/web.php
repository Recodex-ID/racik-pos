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
        Route::get('/permissions', \App\Livewire\Administrator\ManagePermissions::class)->name('permissions');
        Route::get('/tenants', \App\Livewire\Administrator\ManageTenants::class)->name('tenants');
        Route::get('/stores', \App\Livewire\Administrator\ManageStores::class)->name('stores');
    });

    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
