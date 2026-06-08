<x-app-layout>
    <x-slot name="header">{{ $label }}</x-slot>

    <div class="sipeng-page">
        <x-sipeng.page-header
            :title="$label"
            description="Modul ini sedang dalam pengembangan dan akan segera tersedia."
        />

        <div class="sipeng-card max-w-2xl mx-auto mt-8">
            <div class="sipeng-card-body text-center py-12 px-6">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 mb-6">
                    <x-sipeng.icon name="document" class="h-8 w-8" />
                </div>
                <h3 class="text-lg font-semibold text-slate-900">Segera Hadir</h3>
                <p class="mt-2 text-sm text-slate-600 max-w-md mx-auto">
                    Halaman <strong>{{ $label }}</strong> merupakan bagian dari {{ config('sipeng_branding.app_name') }}
                    untuk {{ config('sipeng_branding.institution_name') }}. Fungsionalitas modul akan diimplementasikan pada tahap berikutnya.
                </p>
                <div class="mt-8">
                    <a href="{{ route('dashboard') }}" class="sipeng-btn-primary">
                        <x-sipeng.icon name="home" class="h-4 w-4" />
                        Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
