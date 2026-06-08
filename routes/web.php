<?php

use App\Http\Controllers\Admin\DataReferensiSiakadController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ModulePlaceholderController;
use App\Http\Controllers\Public\PublicDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:120,1')->group(function () {
    Route::get('/', [PublicDashboardController::class, 'landing'])->name('public.landing');
    Route::get('/dashboard-umum', [PublicDashboardController::class, 'dashboard'])->name('public.dashboard');
});

require __DIR__.'/auth.php';

$dashboardRoles = implode('|', config('sipepeng_access.dashboard.roles', []));

Route::middleware(['auth', 'sipepeng.access'])->group(function () use ($dashboardRoles): void {
    Route::view('/akses-ditolak', 'auth.access-denied')->name('access.denied');

    Route::get('/dashboard', DashboardController::class)
        ->middleware("role:{$dashboardRoles}")
        ->name('dashboard');

    Route::prefix('notifications')
        ->name('notifications.')
        ->group(base_path('routes/notifications.php'));

    Route::prefix('admin')->name('admin.')->middleware('role:super_admin|admin_lppm|ketua_lppm|pimpinan|dosen|ketua_prodi|reviewer')->group(function (): void {
        Route::get('/referensi-siakad', [DataReferensiSiakadController::class, 'index'])
            ->middleware('role:super_admin|admin_lppm|ketua_lppm')
            ->name('siakad-reference.index');
        Route::post('/referensi-siakad/refresh', [DataReferensiSiakadController::class, 'refresh'])
            ->middleware(['role:super_admin|admin_lppm|ketua_lppm', 'throttle:10,1'])
            ->name('siakad-reference.refresh');
        Route::prefix('master')
            ->name('master.')
            ->middleware('role:super_admin|admin_lppm|ketua_lppm')
            ->group(base_path('routes/admin/master.php'));
        Route::prefix('penelitian')
            ->name('research.')
            ->group(base_path('routes/admin/research.php'));
        Route::prefix('pengabdian')
            ->name('community-service.')
            ->group(base_path('routes/admin/community-service.php'));
        Route::prefix('publikasi')
            ->name('publications.')
            ->group(base_path('routes/admin/publications.php'));
        Route::prefix('hki')
            ->name('hki.')
            ->group(base_path('routes/admin/hki.php'));
        Route::prefix('etik-penelitian')
            ->name('research-ethics.')
            ->group(base_path('routes/admin/research-ethics.php'));
        Route::prefix('surat')
            ->name('letters.')
            ->group(base_path('routes/admin/letters.php'));
        Route::get('/mitra', ModulePlaceholderController::class)->name('partners.index');
        Route::get('/roadmap', ModulePlaceholderController::class)->name('roadmap.index');
        Route::get('/arsip', ModulePlaceholderController::class)->name('archives.index');
        Route::prefix('laporan')
            ->name('reports.')
            ->middleware('role:super_admin|admin_lppm|ketua_lppm|pimpinan|ketua_prodi|dosen')
            ->group(base_path('routes/admin/reports.php'));
        Route::prefix('pengaturan')
            ->name('settings.')
            ->middleware('role:super_admin|admin_lppm')
            ->group(base_path('routes/admin/settings.php'));
    });

    Route::get('/kalender', ModulePlaceholderController::class)->name('calendar.index');
    Route::get('/pengumuman', ModulePlaceholderController::class)->name('announcements.index');
});
