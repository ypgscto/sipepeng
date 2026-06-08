<?php

use App\Http\Controllers\Admin\Research\ResearchAdminQueueController;
use App\Http\Controllers\Admin\Research\ResearchAdminVerificationController;
use App\Http\Controllers\Admin\Research\ResearchProposalController;
use App\Http\Controllers\Admin\Research\ResearchReviewController;
use Illuminate\Support\Facades\Route;

$allRoles = 'super_admin|admin_lppm|ketua_lppm|dosen|reviewer';
$proposerRoles = 'super_admin|admin_lppm|dosen';
$manageRoles = 'super_admin|admin_lppm';
$decisionRoles = 'super_admin|admin_lppm|ketua_lppm';

Route::middleware("role:{$allRoles}")->group(function () use ($proposerRoles, $manageRoles, $decisionRoles): void {
    Route::get('/', [ResearchProposalController::class, 'index'])->name('index');
    Route::get('/antrian/verifikasi-admin', ResearchAdminQueueController::class)
        ->middleware("role:{$manageRoles}")
        ->name('queues.admin');
    Route::get('/antrian/review', [ResearchReviewController::class, 'queue'])
        ->middleware("role:{$manageRoles}|reviewer")
        ->name('queues.review');

    Route::middleware("role:{$proposerRoles}")->group(function (): void {
        Route::get('/buat', [ResearchProposalController::class, 'create'])->name('create');
        Route::post('/', [ResearchProposalController::class, 'store'])->name('store');
    });

    Route::get('/{proposal}', [ResearchProposalController::class, 'show'])->name('show');
    Route::get('/{proposal}/unduh/{field}', [ResearchProposalController::class, 'download'])
        ->where('field', 'file_proposal|file_pengesahan|file_pernyataan')
        ->name('download');

    Route::middleware("role:{$proposerRoles}")->group(function (): void {
        Route::get('/{proposal}/edit', [ResearchProposalController::class, 'edit'])->name('edit');
        Route::put('/{proposal}', [ResearchProposalController::class, 'update'])->name('update');
        Route::delete('/{proposal}', [ResearchProposalController::class, 'destroy'])->name('destroy');
        Route::post('/{proposal}/ajukan', [ResearchProposalController::class, 'submit'])->name('submit');
    });

    Route::middleware("role:{$manageRoles}")->group(function (): void {
        Route::post('/{proposal}/verifikasi-admin', [ResearchAdminVerificationController::class, 'store'])->name('admin-verification.store');
        Route::post('/{proposal}/tugaskan-reviewer', [ResearchReviewController::class, 'assign'])->name('review.assign');
    });

    Route::middleware('role:reviewer|super_admin|admin_lppm')->group(function (): void {
        Route::post('/{proposal}/review', [ResearchReviewController::class, 'submit'])->name('review.submit');
    });

    Route::middleware("role:{$decisionRoles}")->group(function (): void {
        Route::post('/{proposal}/penetapan', [ResearchReviewController::class, 'decide'])->name('decision.store');
    });
});
