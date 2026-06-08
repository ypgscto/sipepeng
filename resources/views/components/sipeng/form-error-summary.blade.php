@if ($errors->any())
    <x-sipeng.alert type="error" title="Periksa kembali isian formulir:" class="mb-4">
        <ul class="list-disc list-inside space-y-1 mt-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-sipeng.alert>
@endif
