<x-app-layout>
    <x-slot name="header">Edit Surat</x-slot>
    <div class="sipeng-page">
        <x-sipeng.page-header title="Edit Surat" :description="$record->internal_number">
            <x-slot name="actions"><a href="{{ route('admin.letters.show', $record) }}" class="sipeng-btn-secondary text-sm">Detail</a></x-slot>
        </x-sipeng.page-header>
        <form method="POST" action="{{ route('admin.letters.update', $record) }}" class="sipeng-card">
            @csrf @method('PUT')
            <div class="sipeng-card-body">@include('admin.letters.partials.form', ['record'=>$record])</div>
            <div class="sipeng-card-body border-t flex gap-2"><button class="sipeng-btn-primary text-sm">Simpan</button></div>
        </form>
    </div>
</x-app-layout>
