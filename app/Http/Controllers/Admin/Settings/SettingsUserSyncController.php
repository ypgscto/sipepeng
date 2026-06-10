<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Admin\Concerns\PreparesLongRunningSiakadSync;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Siakad\SiakadUserSyncService;
use App\Support\Siakad\SiakadConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class SettingsUserSyncController extends Controller
{
    use PreparesLongRunningSiakadSync;

    public function index(): View
    {
        $stats = [
            'total' => User::query()->count(),
            'siakad' => User::query()->siakadSourced()->count(),
            'local' => User::query()->localOnly()->count(),
            'allowed' => User::query()->where('is_allowed_login', true)->where('is_active', true)->count(),
            'pending' => User::query()->siakadSourced()->where('is_allowed_login', false)->count(),
        ];

        $recent = User::query()
            ->siakadSourced()
            ->with('activeRoles')
            ->orderByDesc('synced_at')
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();

        return view('admin.settings.user-sync.index', [
            'stats' => $stats,
            'recent' => $recent,
            'connectionLabel' => SiakadConfig::isConfigured()
                ? SiakadConfig::baseUrl()
                : 'Belum dikonfigurasi',
        ]);
    }

    public function run(SiakadUserSyncService $sync): RedirectResponse
    {
        $this->prepareLongRunningSync();

        try {
            $result = $sync->syncAll();
            $message = sprintf(
                'Sinkronisasi user selesai: %d baru, %d diperbarui, %d dilewati. Aktifkan login dan peran di Pengaturan Pengguna untuk akun yang belum diizinkan.',
                $result['created'],
                $result['updated'],
                $result['skipped'],
            );

            if ($result['errors'] !== []) {
                $message .= ' Beberapa baris gagal: '.implode('; ', array_slice($result['errors'], 0, 3));
            }

            return redirect()
                ->route('admin.settings.users.index', ['login_access' => 'blocked', 'source' => 'siakad'])
                ->with($result['errors'] === [] ? 'sync_success' : 'sync_warning', $message);
        } catch (Throwable $e) {
            return redirect()
                ->route('admin.settings.user-sync.index')
                ->with('sync_error', 'Sinkronisasi user gagal: '.$e->getMessage());
        }
    }
}
