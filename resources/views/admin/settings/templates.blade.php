<x-app-layout>
    <x-slot name="header">Template Dokumen & Surat</x-slot>

    <div class="sipeng-page">
        <x-sipeng.page-header
            title="Template Dokumen & Surat"
            description="Kelola template melalui modul master LPPM yang sudah ada."
        />

        @include('admin.settings.partials.nav', ['canBackup' => auth()->user()->hasRole('super_admin')])

        <div class="grid sm:grid-cols-2 gap-4">
            <a href="{{ route('admin.master.document-templates.index') }}" class="sipeng-card group hover:border-emerald-300 transition">
                <div class="sipeng-card-body flex items-center gap-3">
                    <x-sipeng.icon name="document" class="h-8 w-8 text-emerald-700" />
                    <div>
                        <p class="font-semibold text-slate-900 group-hover:text-emerald-800">Template Dokumen</p>
                        <p class="text-xs text-slate-500">Formulir, panduan, lampiran proposal.</p>
                    </div>
                </div>
            </a>
            <a href="{{ route('admin.master.letter-types.index') }}" class="sipeng-card group hover:border-emerald-300 transition">
                <div class="sipeng-card-body flex items-center gap-3">
                    <x-sipeng.icon name="document" class="h-8 w-8 text-emerald-700" />
                    <div>
                        <p class="font-semibold text-slate-900 group-hover:text-emerald-800">Jenis Surat</p>
                        <p class="text-xs text-slate-500">Template surat tugas, izin, undangan, dll.</p>
                    </div>
                </div>
            </a>
        </div>

        @unless ($canManage)
            <p class="mt-4 text-sm text-slate-500">Anda memiliki akses lihat. Perubahan template memerlukan hak kelola master LPPM.</p>
        @endunless
    </div>
</x-app-layout>
