@php
    $tabs = [
        'prodi' => 'Program Studi',
        'dosen' => 'Dosen',
        'mahasiswa' => 'Mahasiswa',
        'tahun_akademik' => 'Tahun Akademik',
        'semester' => 'Semester',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">Data Referensi SIAKAD</x-slot>

    <div class="sipeng-page">
        <x-sipeng.page-header
            title="Data Referensi SIAKAD"
            description="Data akademik read-only dari SIAKAD-API. Bukan master lokal — disimpan sementara di cache."
        />

        @include('admin.siakad-reference.partials.error-banner')

        @include('admin.siakad-reference.partials.tabs', ['tabs' => $tabs, 'activeTab' => $tab])

        @include('admin.siakad-reference.partials.meta-bar')

        @include('admin.siakad-reference.partials.filters')

        <div class="sipeng-card overflow-hidden">
            <div class="sipeng-card-body p-0">
                @if (count($records) === 0)
                    @include('admin.siakad-reference.partials.empty-state')
                @else
                    @include('admin.siakad-reference.partials.table-'.$tab)
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
