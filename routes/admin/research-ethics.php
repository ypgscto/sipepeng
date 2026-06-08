<?php

use App\Http\Controllers\Admin\ResearchEthics\ResearchEthicsApplicationController;
use App\Http\Controllers\Admin\ResearchEthics\ResearchEthicsReviewController;
use Illuminate\Support\Facades\Route;

$allRoles = 'super_admin|admin_lppm|ketua_lppm|pimpinan|dosen|ketua_prodi|reviewer';
$proposerRoles = 'super_admin|admin_lppm|dosen';
$manageRoles = 'super_admin|admin_lppm';
$decisionRoles = 'super_admin|admin_lppm|ketua_lppm';

Route::middleware("role:{$allRoles}")->group(function () use ($proposerRoles, $manageRoles, $decisionRoles): void {
    Route::get('/', [ResearchEthicsApplicationController::class, 'index'])->name('index');
    Route::get('/antrian/komite', [ResearchEthicsReviewController::class, 'queue'])->middleware("role:{$manageRoles}")->name('queues.committee');

    Route::middleware("role:{$proposerRoles}")->group(function (): void {
        Route::get('/buat', [ResearchEthicsApplicationController::class, 'create'])->name('create');
        Route::post('/', [ResearchEthicsApplicationController::class, 'store'])->name('store');
    });

    Route::get('/{ethicsApplication}', [ResearchEthicsApplicationController::class, 'show'])->name('show');
    Route::get('/{ethicsApplication}/unduh/{field}', [ResearchEthicsApplicationController::class, 'download'])
        ->where('field', 'file_protocol|file_ethics_application|file_consent_form|file_approval_letter')
        ->name('download');

    Route::middleware("role:{$proposerRoles}")->group(function (): void {
        Route::get('/{ethicsApplication}/edit', [ResearchEthicsApplicationController::class, 'edit'])->name('edit');
        Route::put('/{ethicsApplication}', [ResearchEthicsApplicationController::class, 'update'])->name('update');
        Route::delete('/{ethicsApplication}', [ResearchEthicsApplicationController::class, 'destroy'])->name('destroy');
        Route::post('/{ethicsApplication}/ajukan', [ResearchEthicsApplicationController::class, 'submit'])->name('submit');
    });

    Route::middleware("role:{$manageRoles}")->group(function (): void {
        Route::post('/{ethicsApplication}/tugaskan-reviewer', [ResearchEthicsReviewController::class, 'assign'])->name('review.assign');
    });

    Route::middleware("role:{$decisionRoles}")->group(function (): void {
        Route::post('/{ethicsApplication}/penetapan', [ResearchEthicsReviewController::class, 'decide'])->name('decision.store');
    });
});
