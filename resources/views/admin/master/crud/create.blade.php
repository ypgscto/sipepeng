<x-app-layout>
    <x-slot name="header">Tambah {{ $label }}</x-slot>

    <div class="sipeng-page">
        <x-sipeng.page-header :title="'Tambah '.$label" />

        <form method="POST" action="{{ route('admin.master.'.$entityKey.'.store') }}"
            @if ($entityKey === 'document-templates') enctype="multipart/form-data" @endif
            class="sipeng-card">
            @csrf
            <div class="sipeng-card-body space-y-4">
                @include('admin.master.forms.'.$formPartial, ['record' => null])
                @include('admin.master.partials.form-actions', [
                    'cancelUrl' => route('admin.master.'.$entityKey.'.index'),
                ])
            </div>
        </form>
    </div>
</x-app-layout>
