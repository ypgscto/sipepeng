<?php

use App\Http\Controllers\Admin\Letter\LetterAdminQueueController;
use App\Http\Controllers\Admin\Letter\LetterApprovalController;
use App\Http\Controllers\Admin\Letter\LetterController;
use App\Http\Controllers\Admin\Letter\LetterIssueController;
use Illuminate\Support\Facades\Route;

$allRoles = 'super_admin|admin_lppm|ketua_lppm|pimpinan|dosen|ketua_prodi';
$proposerRoles = 'super_admin|admin_lppm|dosen';
$manageRoles = 'super_admin|admin_lppm';
$approveRoles = 'super_admin|admin_lppm|ketua_lppm';

Route::middleware("role:{$allRoles}")->group(function () use ($proposerRoles, $manageRoles, $approveRoles): void {
    Route::get('/', [LetterController::class, 'index'])->name('index');
    Route::get('/antrian/persetujuan', LetterAdminQueueController::class)
        ->middleware("role:{$approveRoles}")
        ->name('queues.approval');

    Route::middleware("role:{$proposerRoles}")->group(function (): void {
        Route::get('/buat', [LetterController::class, 'create'])->name('create');
        Route::get('/buat/penelitian/{proposal}/{type}', [LetterController::class, 'createFromResearch'])->name('create.from-research');
        Route::get('/buat/pkm/{proposal}/{type}', [LetterController::class, 'createFromPkm'])->name('create.from-pkm');
        Route::post('/', [LetterController::class, 'store'])->name('store');
    });

    Route::get('/{letter}', [LetterController::class, 'show'])->name('show');
    Route::get('/{letter}/preview-pdf', [LetterController::class, 'previewPdf'])->name('preview.pdf');
    Route::get('/{letter}/unduh-pdf', [LetterController::class, 'downloadPdf'])->name('download.pdf');
    Route::get('/{letter}/unduh-scan', [LetterController::class, 'downloadSigned'])->name('download.signed');

    Route::middleware("role:{$proposerRoles}")->group(function (): void {
        Route::get('/{letter}/edit', [LetterController::class, 'edit'])->name('edit');
        Route::put('/{letter}', [LetterController::class, 'update'])->name('update');
        Route::delete('/{letter}', [LetterController::class, 'destroy'])->name('destroy');
        Route::post('/{letter}/ajukan', [LetterController::class, 'submit'])->name('submit');
    });

    Route::middleware("role:{$approveRoles}")->group(function (): void {
        Route::post('/{letter}/persetujuan', [LetterApprovalController::class, 'store'])->name('approval.store');
    });

    Route::middleware("role:{$manageRoles}")->group(function (): void {
        Route::post('/{letter}/terbitkan', [LetterIssueController::class, 'store'])->name('issue');
        Route::post('/{letter}/upload-scan', [LetterController::class, 'uploadSigned'])->name('upload.signed');
    });
});
