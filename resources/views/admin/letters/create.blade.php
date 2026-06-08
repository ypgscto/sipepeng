<x-app-layout>
    <x-slot name="header">Buat Surat</x-slot>
    <div class="sipeng-page">
        <x-sipeng.page-header title="Buat Surat LPPM" description="Draft surat baru.">
            <x-slot name="actions"><a href="{{ route('admin.letters.index') }}" class="sipeng-btn-secondary text-sm">Daftar</a></x-slot>
        </x-sipeng.page-header>
        <form method="POST" action="{{ route('admin.letters.store') }}" class="sipeng-card">
            @csrf
            <div class="sipeng-card-body">@include('admin.letters.partials.form')</div>
            <div class="sipeng-card-body border-t flex gap-2"><button class="sipeng-btn-primary text-sm">Simpan Draft</button></div>
        </form>
    </div>
</x-app-layout>
