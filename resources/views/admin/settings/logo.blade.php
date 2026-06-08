<x-app-layout>
    <x-slot name="header">Logo Institusi</x-slot>

    <div class="sipeng-page">
        <x-sipeng.page-header title="Logo Institusi" description="Logo ditampilkan di sidebar, halaman login, dan footer." />

        @include('admin.settings.partials.nav', ['canBackup' => auth()->user()->hasRole('super_admin')])

        @if (session('success'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid lg:grid-cols-2 gap-6">
            <div class="sipeng-card">
                <div class="sipeng-card-body">
                    <p class="text-sm font-medium text-slate-700 mb-4">Pratinjau</p>
                    @if ($hasLogo)
                        <img src="{{ $logoUrl }}" alt="Logo institusi" class="h-24 w-24 object-contain rounded-full bg-white ring-2 ring-slate-200 shadow">
                    @else
                        <p class="text-sm text-slate-500">Belum ada logo. Unggah file PNG, JPG, atau WebP (maks. 2 MB).</p>
                    @endif
                </div>
            </div>

            <form method="POST" action="{{ route('admin.settings.logo.update') }}" enctype="multipart/form-data" class="sipeng-card">
                @csrf
                <div class="sipeng-card-body space-y-4">
                    <div>
                        <label for="logo" class="sipeng-label">File Logo</label>
                        <input type="file" id="logo" name="logo" accept=".png,.jpg,.jpeg,.webp" class="sipeng-input" required>
                        @error('logo')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="sipeng-btn-primary">Unggah Logo</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
