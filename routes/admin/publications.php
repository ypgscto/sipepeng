<?php

use App\Http\Controllers\Admin\Publication\PublicationAdminQueueController;
use App\Http\Controllers\Admin\Publication\PublicationController;
use App\Http\Controllers\Admin\Publication\PublicationVerificationController;
use Illuminate\Support\Facades\Route;

$allRoles = 'super_admin|admin_lppm|ketua_lppm|pimpinan|dosen|ketua_prodi';
$proposerRoles = 'super_admin|admin_lppm|dosen';
$manageRoles = 'super_admin|admin_lppm';

Route::middleware("role:{$allRoles}")->group(function () use ($proposerRoles, $manageRoles): void {
    Route::get('/', [PublicationController::class, 'index'])->name('index');
    Route::get('/antrian/verifikasi', PublicationAdminQueueController::class)->middleware("role:{$manageRoles}")->name('queues.admin');

    Route::middleware("role:{$proposerRoles}")->group(function (): void {
        Route::get('/buat', [PublicationController::class, 'create'])->name('create');
        Route::post('/', [PublicationController::class, 'store'])->name('store');
    });

    Route::get('/{publication}', [PublicationController::class, 'show'])->name('show');
    Route::get('/{publication}/unduh/{field}', [PublicationController::class, 'download'])
        ->where('field', 'file_manuscript|file_acceptance_letter|file_published|file_other')
        ->name('download');

    Route::middleware("role:{$proposerRoles}")->group(function (): void {
        Route::get('/{publication}/edit', [PublicationController::class, 'edit'])->name('edit');
        Route::put('/{publication}', [PublicationController::class, 'update'])->name('update');
        Route::delete('/{publication}', [PublicationController::class, 'destroy'])->name('destroy');
        Route::post('/{publication}/ajukan', [PublicationController::class, 'submit'])->name('submit');
    });

    Route::middleware("role:{$manageRoles}")->group(function (): void {
        Route::post('/{publication}/verifikasi', [PublicationVerificationController::class, 'store'])->name('verification.store');
    });
});
