<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

$roles = implode('|', config('sipepeng_access.dashboard.roles', []));

Route::middleware(["role:{$roles}"])->group(function (): void {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::patch('/read-all', [NotificationController::class, 'markAllRead'])->name('read-all');
    Route::patch('/{notification}/read', [NotificationController::class, 'markRead'])->name('read');
    Route::patch('/{notification}/dismiss', [NotificationController::class, 'dismiss'])->name('dismiss');
});
