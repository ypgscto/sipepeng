@php
    $formatBytes = function (?int $bytes): string {
        if ($bytes === null || $bytes <= 0) {
            return '—';
        }

        if ($bytes < 1024) {
            return $bytes.' B';
        }

        if ($bytes < 1048576) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return number_format($bytes / 1048576, 2).' MB';
    };
@endphp

<x-app-layout>
    <x-slot name="header">Backup Database</x-slot>

    <div class="sipeng-page">
        <x-sipeng.page-header
            title="Backup Database"
            description="Backup disimpan di storage privat. Hanya super admin yang dapat membuat dan mengunduh."
        />

        @include('admin.settings.partials.nav', ['canBackup' => true])

        @if (session('success'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.settings.backup.store') }}" class="mb-6">
            @csrf
            <button type="submit" class="sipeng-btn-primary" onclick="return confirm('Buat backup database sekarang?')">
                Buat Backup Sekarang
            </button>
        </form>

        <div class="sipeng-card overflow-hidden">
            <div class="sipeng-card-body p-0 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">Waktu</th>
                            <th class="px-4 py-3 text-left font-medium">File</th>
                            <th class="px-4 py-3 text-left font-medium">Ukuran</th>
                            <th class="px-4 py-3 text-left font-medium">Status</th>
                            <th class="px-4 py-3 text-left font-medium">Oleh</th>
                            <th class="px-4 py-3 text-right font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($logs as $log)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $log->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3 font-mono text-xs">{{ $log->filename }}</td>
                                <td class="px-4 py-3">{{ $formatBytes($log->size_bytes) }}</td>
                                <td class="px-4 py-3">
                                    @if ($log->isCompleted())
                                        <span class="text-emerald-700">Selesai</span>
                                    @else
                                        <span class="text-red-700">{{ $log->status }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $log->creator?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    @if ($log->isCompleted())
                                        <a href="{{ route('admin.settings.backup.download', $log) }}" class="text-emerald-700 hover:underline">Unduh</a>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada backup.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">{{ $logs->links() }}</div>
    </div>
</x-app-layout>
