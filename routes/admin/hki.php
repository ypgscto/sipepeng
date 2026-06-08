<?php

use App\Http\Controllers\Admin\IntellectualProperty\IpAdminQueueController;
use App\Http\Controllers\Admin\IntellectualProperty\IpRegistrationController;
use App\Http\Controllers\Admin\IntellectualProperty\IpVerificationController;
use Illuminate\Support\Facades\Route;

$allRoles = 'super_admin|admin_lppm|ketua_lppm|pimpinan|dosen|ketua_prodi';
$proposerRoles = 'super_admin|admin_lppm|dosen';
$manageRoles = 'super_admin|admin_lppm';

Route::middleware("role:{$allRoles}")->group(function () use ($proposerRoles, $manageRoles): void {
    Route::get('/', [IpRegistrationController::class, 'index'])->name('index');
    Route::get('/antrian/verifikasi', IpAdminQueueController::class)->middleware("role:{$manageRoles}")->name('queues.admin');

    Route::middleware("role:{$proposerRoles}")->group(function (): void {
        Route::get('/buat', [IpRegistrationController::class, 'create'])->name('create');
        Route::post('/', [IpRegistrationController::class, 'store'])->name('store');
    });

    Route::get('/{ipRegistration}', [IpRegistrationController::class, 'show'])->name('show');
    Route::get('/{ipRegistration}/unduh/{field}', [IpRegistrationController::class, 'download'])
        ->where('field', 'file_application|file_statement|file_certificate|file_supporting')
        ->name('download');

    Route::middleware("role:{$proposerRoles}")->group(function (): void {
        Route::get('/{ipRegistration}/edit', [IpRegistrationController::class, 'edit'])->name('edit');
        Route::put('/{ipRegistration}', [IpRegistrationController::class, 'update'])->name('update');
        Route::delete('/{ipRegistration}', [IpRegistrationController::class, 'destroy'])->name('destroy');
        Route::post('/{ipRegistration}/ajukan', [IpRegistrationController::class, 'submit'])->name('submit');
    });

    Route::middleware("role:{$manageRoles}")->group(function (): void {
        Route::post('/{ipRegistration}/verifikasi', [IpVerificationController::class, 'store'])->name('verification.store');
    });
});
