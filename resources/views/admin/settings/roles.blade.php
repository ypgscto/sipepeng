<x-app-layout>
    <x-slot name="header">Mapping Role</x-slot>

    <div class="sipeng-page">
        <x-sipeng.page-header
            title="Mapping Role SIAKAD → SiPepeng"
            description="Tentukan kode jenis user atau level ID SIAKAD yang memetakan ke setiap role aplikasi."
        />

        @include('admin.settings.partials.nav', ['canBackup' => auth()->user()->hasRole('super_admin')])

        @if (session('success'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="space-y-4">
            @foreach ($roles as $role)
                <form method="POST" action="{{ route('admin.settings.roles.update', $role) }}" class="sipeng-card">
                    @csrf
                    @method('PUT')
                    <div class="sipeng-card-body">
                        <div class="flex flex-col lg:flex-row lg:items-end gap-4">
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-slate-900">{{ $role->name }}</p>
                                <p class="text-xs text-slate-500">{{ $role->code }} — {{ $role->description }}</p>
                            </div>
                            <div class="grid sm:grid-cols-2 gap-3 flex-1">
                                <div>
                                    <label class="sipeng-label" for="map_type_{{ $role->id }}">Tipe Map</label>
                                    <select id="map_type_{{ $role->id }}" name="siakad_map_type" class="sipeng-input">
                                        <option value="">— Tidak dipetakan —</option>
                                        @foreach ($mapTypes as $value => $label)
                                            <option value="{{ $value }}" @selected(old('siakad_map_type', $role->siakad_map_type) === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="sipeng-label" for="map_key_{{ $role->id }}">Kunci Map</label>
                                    <input type="text" id="map_key_{{ $role->id }}" name="siakad_map_key" value="{{ old('siakad_map_key', $role->siakad_map_key) }}" class="sipeng-input" placeholder="contoh: 7">
                                </div>
                            </div>
                            <button type="submit" class="sipeng-btn-secondary shrink-0">Simpan</button>
                        </div>
                    </div>
                </form>
            @endforeach
        </div>
    </div>
</x-app-layout>
