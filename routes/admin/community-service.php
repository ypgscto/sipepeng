<?php

use App\Http\Controllers\Admin\CommunityService\CommunityServiceProposalController;
use App\Http\Controllers\Admin\CommunityService\PkmAdminQueueController;
use App\Http\Controllers\Admin\CommunityService\PkmAdminVerificationController;
use App\Http\Controllers\Admin\CommunityService\PkmReviewController;
use Illuminate\Support\Facades\Route;

$allRoles = 'super_admin|admin_lppm|ketua_lppm|dosen|reviewer';
$proposerRoles = 'super_admin|admin_lppm|dosen';
$manageRoles = 'super_admin|admin_lppm';
$decisionRoles = 'super_admin|admin_lppm|ketua_lppm';

Route::middleware("role:{$allRoles}")->group(function () use ($proposerRoles, $manageRoles, $decisionRoles): void {
    Route::get('/', [CommunityServiceProposalController::class, 'index'])->name('index');
    Route::get('/antrian/verifikasi-admin', PkmAdminQueueController::class)
        ->middleware("role:{$manageRoles}")
        ->name('queues.admin');
    Route::get('/antrian/review', [PkmReviewController::class, 'queue'])
        ->middleware("role:{$manageRoles}|reviewer")
        ->name('queues.review');

    Route::middleware("role:{$proposerRoles}")->group(function (): void {
        Route::get('/buat', [CommunityServiceProposalController::class, 'create'])->name('create');
        Route::post('/', [CommunityServiceProposalController::class, 'store'])->name('store');
    });

    Route::get('/{proposal}', [CommunityServiceProposalController::class, 'show'])->name('show');
    Route::get('/{proposal}/unduh/{field}', [CommunityServiceProposalController::class, 'download'])
        ->where('field', 'file_proposal|file_surat_mitra|file_pengesahan')
        ->name('download');

    Route::middleware("role:{$proposerRoles}")->group(function (): void {
        Route::get('/{proposal}/edit', [CommunityServiceProposalController::class, 'edit'])->name('edit');
        Route::put('/{proposal}', [CommunityServiceProposalController::class, 'update'])->name('update');
        Route::delete('/{proposal}', [CommunityServiceProposalController::class, 'destroy'])->name('destroy');
        Route::post('/{proposal}/ajukan', [CommunityServiceProposalController::class, 'submit'])->name('submit');
    });

    Route::middleware("role:{$manageRoles}")->group(function (): void {
        Route::post('/{proposal}/verifikasi-admin', [PkmAdminVerificationController::class, 'store'])->name('admin-verification.store');
        Route::post('/{proposal}/tugaskan-reviewer', [PkmReviewController::class, 'assign'])->name('review.assign');
    });

    Route::middleware('role:reviewer|super_admin|admin_lppm')->group(function (): void {
        Route::post('/{proposal}/review', [PkmReviewController::class, 'submit'])->name('review.submit');
    });

    Route::middleware("role:{$decisionRoles}")->group(function (): void {
        Route::post('/{proposal}/penetapan', [PkmReviewController::class, 'decide'])->name('decision.store');
    });
});
