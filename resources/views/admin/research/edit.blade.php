<x-app-layout>
    <x-slot name="header">Edit Proposal</x-slot>

    <div class="sipeng-page">
        <x-sipeng.page-header :title="'Edit: '.$proposal->proposal_number" />

        <form method="POST" action="{{ route('admin.research.update', $proposal) }}" enctype="multipart/form-data" class="sipeng-card">
            @csrf
            @method('PUT')
            <div class="sipeng-card-body space-y-4">
                <x-sipeng.form-error-summary />
                @include('admin.research.partials.form', ['proposal' => $proposal])
                <div class="flex gap-2 pt-4 border-t">
                    <button type="submit" class="sipeng-btn-primary text-sm">Simpan</button>
                    <a href="{{ route('admin.research.show', $proposal) }}" class="sipeng-btn-secondary text-sm">Batal</a>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
