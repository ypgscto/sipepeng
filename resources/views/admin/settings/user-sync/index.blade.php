<x-app-layout>
    <x-slot name="header">Sinkronisasi User Login</x-slot>

    <div class="sipeng-page space-y-6">
        <x-sipeng.page-header
            title="Sinkronisasi User Login"
            description="Impor akun Siakad (termasuk karyawan-only) ke database lokal SiPepeng. Aktivasi login dilakukan di Pengaturan Pengguna."
        />

        @include('admin.settings.partials.nav', ['canBackup' => auth()->user()?->hasRole('super_admin') ?? false])

        @if (session('sync_success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('sync_success') }}</div>
        @endif
        @if (session('sync_warning'))
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">{{ session('sync_warning') }}</div>
        @endif
        @if (session('sync_error'))
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">{{ session('sync_error') }}</div>
        @endif

        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
            @foreach ([
                ['Total user', $stats['total']],
                ['Dari Siakad', $stats['siakad']],
                ['Lokal cadangan', $stats['local']],
                ['Boleh login', $stats['allowed']],
                ['Menunggu aktivasi', $stats['pending']],
            ] as [$label, $value])
                <div class="sipeng-card sipeng-card-body text-center">
                    <p class="text-2xl font-bold text-slate-900">{{ $value }}</p>
                    <p class="text-xs text-slate-500 mt-1">{{ $label }}</p>
                </div>
            @endforeach
        </div>

        <div class="sipeng-card sipeng-card-body space-y-4">
            <p class="text-sm text-slate-600">
                Endpoint: <code class="text-xs bg-slate-100 px-1 rounded">GET /api/sipepeng/login-users</code>.
                Koneksi: <strong>{{ $connectionLabel }}</strong>.
            </p>
            <ul class="text-sm text-slate-600 list-disc pl-5 space-y-1">
                <li>Sinkronisasi mencakup tabel <strong>users</strong> dan akun <strong>karyawan-only</strong> (mis. <code>KodeLogin</code> tanpa baris users).</li>
                <li>User baru <strong>belum bisa login</strong> sampai admin mengaktifkan di
                    <a href="{{ route('admin.settings.users.index', ['login_access' => 'blocked', 'source' => 'siakad']) }}" class="text-emerald-700 hover:underline">Pengaturan Pengguna</a>.
                </li>
                <li>Verifikasi password tetap di Siakad-API saat login (password SIAKAD-GS).</li>
                <li>Status login yang sudah diatur admin tidak ditimpa otomatis menjadi aktif.</li>
            </ul>
            <div class="flex flex-wrap gap-2">
                <form method="POST" action="{{ route('admin.settings.user-sync.run') }}" data-confirm="Jalankan sinkronisasi semua user login dari Siakad-API?">
                    @csrf
                    <button type="submit" class="sipeng-btn-primary">Sinkronkan User Login</button>
                </form>
                <a href="{{ route('admin.settings.users.index', ['login_access' => 'blocked', 'source' => 'siakad']) }}" class="sipeng-btn-secondary">Kelola aktivasi pengguna</a>
            </div>
        </div>

        @if ($recent->isNotEmpty())
            <div class="sipeng-card overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-base font-semibold text-slate-900">Terakhir disinkronkan</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-slate-600">
                            <tr>
                                <th class="px-4 py-3 font-medium">Nama</th>
                                <th class="px-4 py-3 font-medium">Login Siakad</th>
                                <th class="px-4 py-3 font-medium">Peran</th>
                                <th class="px-4 py-3 font-medium">Login SiPepeng</th>
                                <th class="px-4 py-3 font-medium">Sinkron</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($recent as $user)
                                <tr>
                                    <td class="px-4 py-3">{{ $user->name }}</td>
                                    <td class="px-4 py-3">{{ $user->siakad_login }}</td>
                                    <td class="px-4 py-3">{{ $user->activeRoles->pluck('name')->join(', ') ?: '—' }}</td>
                                    <td class="px-4 py-3">
                                        @if ($user->is_allowed_login)
                                            <span class="text-emerald-700 text-xs font-medium">Diizinkan</span>
                                        @else
                                            <span class="text-amber-700 text-xs font-medium">Belum diaktifkan</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">{{ $user->synced_at?->diffForHumans() ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
