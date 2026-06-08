<x-app-layout>
    <x-slot name="header">Laporan LPPM</x-slot>
    <div class="sipeng-page">
        <x-sipeng.page-header title="Scope Laporan Tidak Tersedia" />
        <x-sipeng.alert type="warning" :message="$message" />
        <a href="{{ route('admin.reports.index') }}" class="sipeng-btn-secondary mt-4 inline-flex">Kembali ke Daftar Laporan</a>
    </div>
</x-app-layout>
