<x-app-layout>
    <x-slot name="header">Buat Proposal PkM</x-slot>

    <div class="sipeng-page">
        <x-sipeng.page-header title="Buat Proposal Pengabdian Masyarakat" />

        <form method="POST" action="{{ route('admin.community-service.store') }}" enctype="multipart/form-data" class="sipeng-card">
            @csrf
            <div class="sipeng-card-body space-y-4">
                <x-sipeng.form-error-summary />
                @include('admin.community-service.partials.form', ['record' => null])
                <div class="flex gap-2 pt-4 border-t">
                    <button type="submit" class="sipeng-btn-primary text-sm">Simpan Draft</button>
                    <a href="{{ route('admin.community-service.index') }}" class="sipeng-btn-secondary text-sm">Batal</a>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
