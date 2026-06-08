<?php

use App\Http\Controllers\Admin\Report\ReportController;
use App\Http\Controllers\Admin\Report\ReportExportController;
use Illuminate\Support\Facades\Route;

$roles = 'super_admin|admin_lppm|ketua_lppm|pimpinan|ketua_prodi|dosen';

Route::middleware("role:{$roles}")->group(function (): void {
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('/{type}', [ReportController::class, 'show'])->name('show');
    Route::get('/{type}/export/excel', [ReportExportController::class, 'excel'])->name('export.excel');
    Route::get('/{type}/export/pdf', [ReportExportController::class, 'pdf'])->name('export.pdf');
});
