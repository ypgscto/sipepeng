<x-app-layout>
    <x-slot name="header">Edit Proposal PkM</x-slot>

    <div class="sipeng-page">
        <x-sipeng.page-header title="Edit Proposal PkM" :description="$proposal->proposal_number" />

        <form method="POST" action="{{ route('admin.community-service.update', $proposal) }}" enctype="multipart/form-data" class="sipeng-card">
            @csrf
            @method('PUT')
            <div class="sipeng-card-body space-y-4">
                @include('admin.community-service.partials.form')
                <div class="flex gap-2 pt-4 border-t">
                    <button type="submit" class="sipeng-btn-primary text-sm">Simpan Perubahan</button>
                    <a href="{{ route('admin.community-service.show', $proposal) }}" class="sipeng-btn-secondary text-sm">Batal</a>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
