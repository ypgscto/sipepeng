<x-app-layout>
    <x-slot name="header">Akses Ditolak</x-slot>

    <div class="sipeng-page">
        <div class="sipeng-card max-w-2xl mx-auto">
            <div class="sipeng-card-body text-center space-y-4 py-10">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-red-700">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-slate-900">Anda tidak memiliki akses</h2>
                <p class="text-sm text-slate-600">
                    {{ session('access_denied_message', 'Halaman ini memerlukan peran tertentu di SiPepeng.') }}
                </p>
                @auth
                    <p class="text-xs text-slate-500">
                        Peran Anda saat ini:
                        <span class="font-medium text-slate-700">{{ implode(', ', auth()->user()->roleCodes()) ?: '—' }}</span>
                    </p>
                @endauth
                <div class="pt-2">
                    <a href="{{ route('dashboard') }}" class="sipeng-btn-primary">Kembali ke Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
