<x-app-layout>
    <x-slot name="header">Edit {{ $label }}</x-slot>

    <div class="sipeng-page">
        <x-sipeng.page-header :title="'Edit '.$label" />

        <form method="POST" action="{{ route('admin.master.'.$entityKey.'.update', $record) }}"
            @if ($entityKey === 'document-templates') enctype="multipart/form-data" @endif
            class="sipeng-card">
            @csrf
            @method('PUT')
            <div class="sipeng-card-body space-y-4">
                @include('admin.master.forms.'.$formPartial, ['record' => $record])
                @include('admin.master.partials.form-actions', [
                    'cancelUrl' => route('admin.master.'.$entityKey.'.show', $record),
                ])
            </div>
        </form>
    </div>
</x-app-layout>
