<?php

namespace App\Services;

use App\Models\BackupLog;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DatabaseBackupService
{
    public function __construct(
        protected AppSettingsService $settings,
    ) {}

    /**
     * @return array{log: BackupLog, absolute_path: string}
     */
    public function create(User $user): array
    {
        $driver = (string) config('database.default');
        $directory = trim((string) config('sipepeng_settings.backup.directory', 'backups'), '/');
        $disk = (string) config('sipepeng_settings.backup.disk', 'local');
        $timestamp = now()->format('Y-m-d_His');
        $filename = "sipepeng_backup_{$timestamp}.sql";

        if ($driver === 'sqlite') {
            $filename = "sipepeng_backup_{$timestamp}.sqlite";
        }

        Storage::disk($disk)->makeDirectory($directory);
        $relativePath = "{$directory}/{$filename}";
        $absolutePath = Storage::disk($disk)->path($relativePath);

        $log = BackupLog::query()->create([
            'filename' => $filename,
            'disk' => $disk,
            'path' => $relativePath,
            'driver' => $driver,
            'status' => 'running',
            'created_by' => $user->id,
        ]);

        try {
            if ($driver === 'sqlite') {
                $this->backupSqlite($absolutePath);
            } elseif ($driver === 'mysql') {
                $this->backupMysql($absolutePath);
            } else {
                throw new RuntimeException("Driver database [{$driver}] belum didukung untuk backup.");
            }

            $size = File::exists($absolutePath) ? (int) File::size($absolutePath) : 0;

            $log->update([
                'size_bytes' => $size,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            $this->enforceRetention($disk, $directory);

            return ['log' => $log->fresh(), 'absolute_path' => $absolutePath];
        } catch (\Throwable $exception) {
            $log->update([
                'status' => 'failed',
                'notes' => $exception->getMessage(),
                'completed_at' => now(),
            ]);

            if (File::exists($absolutePath)) {
                File::delete($absolutePath);
            }

            throw $exception;
        }
    }

    public function resolveAbsolutePath(BackupLog $log): string
    {
        return Storage::disk($log->disk)->path($log->path);
    }

    protected function backupSqlite(string $destination): void
    {
        $source = (string) config('database.connections.sqlite.database');

        if ($source === '' || ! File::exists($source)) {
            throw new RuntimeException('File database SQLite tidak ditemukan.');
        }

        File::copy($source, $destination);
    }

    protected function backupMysql(string $destination): void
    {
        $connection = config('database.connections.mysql');

        $host = (string) ($connection['host'] ?? '127.0.0.1');
        $port = (string) ($connection['port'] ?? '3306');
        $database = (string) ($connection['database'] ?? '');
        $username = (string) ($connection['username'] ?? '');
        $password = (string) ($connection['password'] ?? '');

        if ($database === '') {
            throw new RuntimeException('Nama database MySQL belum dikonfigurasi.');
        }

        $mysqldump = $this->resolveMysqldumpBinary();

        $command = [
            $mysqldump,
            '--host='.$host,
            '--port='.$port,
            '--user='.$username,
            '--single-transaction',
            '--quick',
            '--lock-tables=false',
            $database,
        ];

        $environment = $password !== '' ? ['MYSQL_PWD' => $password] : [];

        $result = Process::timeout(300)->env($environment)->run($command);

        if (! $result->successful()) {
            throw new RuntimeException(trim($result->errorOutput() ?: $result->output() ?: 'mysqldump gagal.'));
        }

        File::put($absolutePath, $result->output());

        if (! File::exists($absolutePath) || File::size($absolutePath) === 0) {
            throw new RuntimeException('File backup kosong atau tidak dibuat.');
        }
    }

    protected function resolveMysqldumpBinary(): string
    {
        $candidates = array_filter([
            env('MYSQLDUMP_PATH'),
            'C:\\laragon\\bin\\mysql\\mysql-8.4.3-winx64\\bin\\mysqldump.exe',
            'C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysqldump.exe',
            'mysqldump',
        ]);

        foreach ($candidates as $candidate) {
            if ($candidate === 'mysqldump') {
                return $candidate;
            }

            if (is_string($candidate) && file_exists($candidate)) {
                return $candidate;
            }
        }

        return 'mysqldump';
    }

    protected function enforceRetention(string $disk, string $directory): void
    {
        $retention = max(1, (int) config('sipepeng_settings.backup.retention_count', 10));

        $logs = BackupLog::query()
            ->where('status', 'completed')
            ->orderByDesc('id')
            ->get();

        if ($logs->count() <= $retention) {
            return;
        }

        foreach ($logs->slice($retention) as $oldLog) {
            if (Storage::disk($oldLog->disk)->exists($oldLog->path)) {
                Storage::disk($oldLog->disk)->delete($oldLog->path);
            }

            $oldLog->delete();
        }
    }
}
