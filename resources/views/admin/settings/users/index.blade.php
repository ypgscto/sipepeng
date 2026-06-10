<x-app-layout>
    <x-slot name="header">Pengaturan Pengguna</x-slot>

    <div class="sipeng-page space-y-6">
        <x-sipeng.page-header
            title="Pengaturan Pengguna"
            description="Aktifkan login SiPepeng dan tetapkan peran untuk akun dari Siakad atau lokal."
        />

        @include('admin.settings.partials.nav', ['canBackup' => auth()->user()?->hasRole('super_admin') ?? false])

        @if (session('success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('success') }}</div>
        @endif
        @if (session('sync_success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('sync_success') }}</div>
        @endif
        @if (session('sync_warning'))
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">{{ session('sync_warning') }}</div>
        @endif
        @if (session('error'))
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">{{ session('error') }}</div>
        @endif

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.settings.user-sync.index') }}" class="sipeng-btn-primary">Sinkronisasi User Login</a>
        </div>

        <form method="GET" class="sipeng-card sipeng-card-body flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[12rem]">
                <label class="block text-xs font-medium text-slate-600 mb-1" for="q">Cari</label>
                <input id="q" name="q" type="search" value="{{ request('q') }}" class="sipeng-input w-full" placeholder="Nama, email, login Siakad" />
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1" for="source">Sumber</label>
                <select id="source" name="source" class="sipeng-input">
                    <option value="">Semua</option>
                    <option value="siakad" @selected(request('source') === 'siakad')>Siakad</option>
                    <option value="local" @selected(request('source') === 'local')>Lokal</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1" for="login_access">Login</label>
                <select id="login_access" name="login_access" class="sipeng-input">
                    <option value="">Semua</option>
                    <option value="allowed" @selected(request('login_access') === 'allowed')>Diizinkan</option>
                    <option value="blocked" @selected(request('login_access') === 'blocked')>Belum diaktifkan</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1" for="role">Peran</label>
                <select id="role" name="role" class="sipeng-input">
                    <option value="">Semua</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->code }}" @selected(request('role') === $role->code)>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="sipeng-btn-secondary">Filter</button>
            <a href="{{ route('admin.settings.users.index') }}" class="sipeng-btn-secondary">Reset</a>
        </form>

        <div class="sipeng-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-slate-600">
                        <tr>
                            <th class="px-4 py-3 font-medium">Nama</th>
                            <th class="px-4 py-3 font-medium">Email / Login</th>
                            <th class="px-4 py-3 font-medium">Peran</th>
                            <th class="px-4 py-3 font-medium">Sumber</th>
                            <th class="px-4 py-3 font-medium">Login</th>
                            <th class="px-4 py-3 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($items as $row)
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $row->name }}</td>
                                <td class="px-4 py-3">
                                    <div>{{ $row->email }}</div>
                                    @if ($row->siakad_login)
                                        <div class="text-xs text-slate-500">Siakad: {{ $row->siakad_login }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $row->activeRoles->pluck('name')->join(', ') ?: '—' }}</td>
                                <td class="px-4 py-3">{{ $row->isSiakadSourced() ? 'Siakad' : 'Lokal' }}</td>
                                <td class="px-4 py-3">
                                    @if ($row->is_allowed_login && $row->is_active)
                                        <span class="text-emerald-700 text-xs font-medium">Diizinkan</span>
                                    @else
                                        <span class="text-amber-700 text-xs font-medium">Diblokir</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.settings.users.edit', $row) }}" class="sipeng-btn-secondary sipeng-btn-sm">Ubah</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada pengguna. Jalankan sinkronisasi user dari Siakad-API.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($items->hasPages())
                <div class="px-4 py-3 border-t border-slate-100">{{ $items->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
