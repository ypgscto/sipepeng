<?php

use App\Http\Controllers\Admin\Settings\SettingsBackupController;
use App\Http\Controllers\Admin\Settings\SettingsController;
use App\Http\Controllers\Admin\Settings\SettingsGeneralController;
use App\Http\Controllers\Admin\Settings\SettingsLogoController;
use App\Http\Controllers\Admin\Settings\SettingsRoleController;
use App\Http\Controllers\Admin\Settings\SettingsSiakadController;
use App\Http\Controllers\Admin\Settings\SettingsTemplateController;
use App\Http\Controllers\Admin\Settings\SettingsUsersController;
use App\Http\Controllers\Admin\Settings\SettingsUserSyncController;
use Illuminate\Support\Facades\Route;

Route::middleware('settings.access:view')->group(function (): void {
    Route::get('/', [SettingsController::class, 'index'])->name('index');
    Route::get('/template', [SettingsTemplateController::class, 'index'])->name('templates.index');
});

Route::middleware('settings.access:manage')->group(function (): void {
    Route::get('/profil', [SettingsGeneralController::class, 'edit'])->name('general.edit');
    Route::put('/profil', [SettingsGeneralController::class, 'update'])->name('general.update');

    Route::get('/logo', [SettingsLogoController::class, 'edit'])->name('logo.edit');
    Route::post('/logo', [SettingsLogoController::class, 'update'])->name('logo.update');

    Route::get('/siakad-api', [SettingsSiakadController::class, 'edit'])->name('siakad.edit');
    Route::put('/siakad-api', [SettingsSiakadController::class, 'update'])->name('siakad.update');

    Route::get('/mapping-role', [SettingsRoleController::class, 'index'])->name('roles.index');
    Route::put('/mapping-role/{role}', [SettingsRoleController::class, 'update'])->name('roles.update');

    Route::get('/sinkronisasi-user', [SettingsUserSyncController::class, 'index'])->name('user-sync.index');
    Route::post('/sinkronisasi-user', [SettingsUserSyncController::class, 'run'])
        ->middleware('throttle:3,10')
        ->name('user-sync.run');

    Route::get('/pengguna', [SettingsUsersController::class, 'index'])->name('users.index');
    Route::get('/pengguna/{user}/edit', [SettingsUsersController::class, 'edit'])->name('users.edit');
    Route::put('/pengguna/{user}', [SettingsUsersController::class, 'update'])->name('users.update');
});

Route::middleware('settings.access:backup')->group(function (): void {
    Route::get('/backup', [SettingsBackupController::class, 'index'])->name('backup.index');
    Route::post('/backup', [SettingsBackupController::class, 'store'])
        ->middleware('throttle:3,10')
        ->name('backup.store');
    Route::get('/backup/{backup}/download', [SettingsBackupController::class, 'download'])->name('backup.download');
});
