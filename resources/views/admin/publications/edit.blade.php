<x-app-layout>
    <x-slot name="header">Edit Publikasi</x-slot>
    <div class="sipeng-page">
        <form method="POST" action="{{ route('admin.publications.update', $record) }}" enctype="multipart/form-data" class="sipeng-card">
            @csrf @method('PUT')
            <div class="sipeng-card-body space-y-4">
                @include('admin.publications.partials.form')
                <div class="flex gap-2 pt-4 border-t">
                    <button type="submit" class="sipeng-btn-primary text-sm">Simpan</button>
                    <a href="{{ route('admin.publications.show', $record) }}" class="sipeng-btn-secondary text-sm">Batal</a>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
