<x-app-layout>
    <x-slot name="header">Buat Proposal Penelitian</x-slot>

    <div class="sipeng-page">
        <x-sipeng.page-header title="Buat Proposal Penelitian" />

        <form method="POST" action="{{ route('admin.research.store') }}" enctype="multipart/form-data" class="sipeng-card">
            @csrf
            <div class="sipeng-card-body space-y-4">
                <x-sipeng.form-error-summary />
                @include('admin.research.partials.form')
                <div class="flex gap-2 pt-4 border-t">
                    <button type="submit" class="sipeng-btn-primary text-sm">Simpan Draft</button>
                    <a href="{{ route('admin.research.index') }}" class="sipeng-btn-secondary text-sm">Batal</a>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
