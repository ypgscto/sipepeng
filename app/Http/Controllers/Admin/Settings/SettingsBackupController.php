<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\BackupLog;
use App\Services\ActivityLogger;
use App\Services\DatabaseBackupService;
use App\Support\Settings\SettingsPermissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SettingsBackupController extends Controller
{
    public function index(): View
    {
        $logs = BackupLog::query()
            ->with('creator:id,name')
            ->orderByDesc('id')
            ->paginate(15);

        return view('admin.settings.backup', [
            'logs' => $logs,
        ]);
    }

    public function store(
        Request $request,
        DatabaseBackupService $backupService,
        ActivityLogger $logger,
    ): RedirectResponse {
        try {
            $result = $backupService->create($request->user());

            $logger->logAudit(
                'backup_created',
                $result['log'],
                'Backup database dibuat.',
                [
                    'filename' => $result['log']->filename,
                    'size_bytes' => $result['log']->size_bytes,
                ],
                $request,
                logName: 'security',
            );

            return redirect()
                ->route('admin.settings.backup.index')
                ->with('success', "Backup {$result['log']->filename} berhasil dibuat.");
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->route('admin.settings.backup.index')
                ->with('error', 'Backup gagal. Periksa log server atau pastikan mysqldump/sqlite tersedia.');
        }
    }

    public function download(
        Request $request,
        BackupLog $backup,
        DatabaseBackupService $backupService,
        ActivityLogger $logger,
    ): StreamedResponse {
        abort_unless($backup->isCompleted(), 404);

        $absolutePath = $backupService->resolveAbsolutePath($backup);

        abort_unless(File::exists($absolutePath), 404);

        $logger->logAudit(
            'backup_downloaded',
            $backup,
            'Backup database diunduh.',
            ['filename' => $backup->filename],
            $request,
            logName: 'security',
        );

        return response()->streamDownload(function () use ($absolutePath): void {
            readfile($absolutePath);
        }, $backup->filename, [
            'Content-Type' => 'application/octet-stream',
        ]);
    }
}
