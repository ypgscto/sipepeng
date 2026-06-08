<x-app-layout>
    <x-slot name="header">Profil & Footer</x-slot>

    <div class="sipeng-page">
        <x-sipeng.page-header title="Profil Aplikasi & Footer" description="Informasi branding yang ditampilkan di sidebar, topbar, dan halaman login." />

        @include('admin.settings.partials.nav', ['canBackup' => auth()->user()->hasRole('super_admin')])

        @if (session('success'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.settings.general.update') }}" class="sipeng-card">
            @csrf
            @method('PUT')
            <div class="sipeng-card-body space-y-4">
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="app_name" class="sipeng-label">Nama Aplikasi</label>
                        <input type="text" id="app_name" name="app_name" value="{{ old('app_name', $values['app_name']) }}" class="sipeng-input" required>
                        @error('app_name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="module" class="sipeng-label">Modul</label>
                        <input type="text" id="module" name="module" value="{{ old('module', $values['module']) }}" class="sipeng-input" required>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="app_subtitle" class="sipeng-label">Subjudul</label>
                        <input type="text" id="app_subtitle" name="app_subtitle" value="{{ old('app_subtitle', $values['app_subtitle']) }}" class="sipeng-input">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="institution_name" class="sipeng-label">Nama Institusi</label>
                        <input type="text" id="institution_name" name="institution_name" value="{{ old('institution_name', $values['institution_name']) }}" class="sipeng-input" required>
                    </div>
                    <div>
                        <label for="institution_url" class="sipeng-label">URL Institusi</label>
                        <input type="url" id="institution_url" name="institution_url" value="{{ old('institution_url', $values['institution_url']) }}" class="sipeng-input">
                    </div>
                    <div>
                        <label for="institution_url_label" class="sipeng-label">Label URL</label>
                        <input type="text" id="institution_url_label" name="institution_url_label" value="{{ old('institution_url_label', $values['institution_url_label']) }}" class="sipeng-input">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="footer_credit" class="sipeng-label">Footer</label>
                        <input type="text" id="footer_credit" name="footer_credit" value="{{ old('footer_credit', $values['footer_credit']) }}" class="sipeng-input" required>
                        <p class="mt-1 text-xs text-slate-500">Default: YPGS IT Division, 2026</p>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="sipeng-btn-primary">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
