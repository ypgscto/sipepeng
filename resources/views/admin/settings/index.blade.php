<x-app-layout>
    <x-slot name="header">Pengaturan Aplikasi</x-slot>

    <div class="sipeng-page">
        <x-sipeng.page-header
            title="Pengaturan SiPepeng"
            description="Kelola profil institusi, integrasi SIAKAD, sinkron user login, mapping role, template, dan backup database."
        />

        @include('admin.settings.partials.nav', ['canBackup' => $canBackup])

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <a href="{{ route('admin.settings.general.edit') }}" class="sipeng-card group hover:border-emerald-300 transition">
                <div class="sipeng-card-body">
                    <p class="font-semibold text-slate-900 group-hover:text-emerald-800">Profil & Footer</p>
                    <p class="text-xs text-slate-500 mt-1">Nama aplikasi, institusi, footer default.</p>
                </div>
            </a>
            <a href="{{ route('admin.settings.logo.edit') }}" class="sipeng-card group hover:border-emerald-300 transition">
                <div class="sipeng-card-body">
                    <p class="font-semibold text-slate-900 group-hover:text-emerald-800">Logo Institusi</p>
                    <p class="text-xs text-slate-500 mt-1">Unggah logo untuk sidebar dan login.</p>
                </div>
            </a>
            <a href="{{ route('admin.settings.siakad.edit') }}" class="sipeng-card group hover:border-emerald-300 transition">
                <div class="sipeng-card-body">
                    <p class="font-semibold text-slate-900 group-hover:text-emerald-800">SIAKAD-API</p>
                    <p class="text-xs text-slate-500 mt-1">Base URL, cache, token aman.</p>
                </div>
            </a>
            <a href="{{ route('admin.settings.roles.index') }}" class="sipeng-card group hover:border-emerald-300 transition">
                <div class="sipeng-card-body">
                    <p class="font-semibold text-slate-900 group-hover:text-emerald-800">Mapping Role</p>
                    <p class="text-xs text-slate-500 mt-1">Petakan jenis user SIAKAD ke role SiPepeng.</p>
                </div>
            </a>
            <a href="{{ route('admin.settings.user-sync.index') }}" class="sipeng-card group hover:border-emerald-300 transition">
                <div class="sipeng-card-body">
                    <p class="font-semibold text-slate-900 group-hover:text-emerald-800">Sinkronisasi User Login</p>
                    <p class="text-xs text-slate-500 mt-1">Tarik akun Siakad (termasuk karyawan-only) ke database lokal.</p>
                </div>
            </a>
            <a href="{{ route('admin.settings.users.index') }}" class="sipeng-card group hover:border-emerald-300 transition">
                <div class="sipeng-card-body">
                    <p class="font-semibold text-slate-900 group-hover:text-emerald-800">Pengaturan Pengguna</p>
                    <p class="text-xs text-slate-500 mt-1">Aktifkan login dan tetapkan peran SiPepeng.</p>
                </div>
            </a>
            <a href="{{ route('admin.settings.templates.index') }}" class="sipeng-card group hover:border-emerald-300 transition">
                <div class="sipeng-card-body">
                    <p class="font-semibold text-slate-900 group-hover:text-emerald-800">Template Dokumen & Surat</p>
                    <p class="text-xs text-slate-500 mt-1">Kelola template via master LPPM.</p>
                </div>
            </a>
            @if ($canBackup)
                <a href="{{ route('admin.settings.backup.index') }}" class="sipeng-card group hover:border-emerald-300 transition">
                    <div class="sipeng-card-body">
                        <p class="font-semibold text-slate-900 group-hover:text-emerald-800">Backup Database</p>
                        <p class="text-xs text-slate-500 mt-1">Buat dan unduh backup aman (super admin).</p>
                    </div>
                </a>
            @endif
        </div>
    </div>
</x-app-layout>
