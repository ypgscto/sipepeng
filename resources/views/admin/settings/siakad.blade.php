<x-app-layout>
    <x-slot name="header">SIAKAD-API</x-slot>

    <div class="sipeng-page">
        <x-sipeng.page-header
            title="Konfigurasi SIAKAD-API"
            description="Token API disimpan terenkripsi dan tidak pernah ditampilkan di layar."
        />

        @include('admin.settings.partials.nav', ['canBackup' => auth()->user()->hasRole('super_admin')])

        @if (session('success'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if (! empty($tokenDecryptFailed))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                Token tersimpan di database tidak dapat didekripsi. Kemungkinan <code class="text-xs">APP_KEY</code> berubah.
                Masukkan token SIAKAD-API baru di form di bawah.
            </div>
        @endif

        <div class="mb-4 rounded-lg border px-4 py-3 text-sm @if($isConfigured) border-emerald-200 bg-emerald-50 text-emerald-800 @else border-amber-200 bg-amber-50 text-amber-900 @endif">
            @if ($isConfigured)
                Koneksi SIAKAD-API siap digunakan.
            @else
                Base URL atau token belum lengkap. Lengkapi konfigurasi di bawah atau via file <code class="text-xs">.env</code>.
            @endif
        </div>

        <form method="POST" action="{{ route('admin.settings.siakad.update') }}" class="sipeng-card">
            @csrf
            @method('PUT')
            <div class="sipeng-card-body space-y-4">
                <div>
                    <label for="base_url" class="sipeng-label">Base URL API</label>
                    <input type="url" id="base_url" name="base_url" value="{{ old('base_url', $baseUrl) }}" class="sipeng-input" placeholder="https://api.example.ac.id">
                    @if ($envBaseUrl !== '' && $envBaseUrl !== $baseUrl)
                        <p class="mt-1 text-xs text-slate-500">Nilai .env: {{ $envBaseUrl }}</p>
                    @endif
                </div>

                <div>
                    <label class="sipeng-label">Status Token</label>
                    <p class="text-sm text-slate-700">
                        @if ($tokenConfigured)
                            <span class="inline-flex items-center gap-1 text-emerald-700 font-medium">● Terkonfigurasi</span>
                            <span class="text-slate-500">— token tidak ditampilkan demi keamanan.</span>
                        @else
                            <span class="text-amber-700 font-medium">Belum dikonfigurasi</span>
                        @endif
                    </p>
                </div>

                <div>
                    <label for="api_token_new" class="sipeng-label">Token Baru (opsional)</label>
                    <input type="password" id="api_token_new" name="api_token_new" autocomplete="new-password" class="sipeng-input" placeholder="Isi hanya jika ingin mengganti token">
                    <p class="mt-1 text-xs text-slate-500">Token disimpan terenkripsi di database. Kosongkan jika tidak ingin mengubah.</p>
                    @error('api_token_new')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="timeout" class="sipeng-label">Timeout (detik)</label>
                        <input type="number" id="timeout" name="timeout" min="5" max="600" value="{{ old('timeout', $timeout) }}" class="sipeng-input" required>
                    </div>
                    <div>
                        <label for="cache_ttl_minutes" class="sipeng-label">TTL Cache (menit)</label>
                        <input type="number" id="cache_ttl_minutes" name="cache_ttl_minutes" min="1" max="10080" value="{{ old('cache_ttl_minutes', $cacheTtlMinutes) }}" class="sipeng-input" required>
                    </div>
                </div>

                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="hidden" name="cache_enabled" value="0">
                    <input type="checkbox" name="cache_enabled" value="1" @checked(old('cache_enabled', $cacheEnabled)) class="rounded border-slate-300 text-emerald-600">
                    Aktifkan cache referensi SIAKAD
                </label>

                <div class="flex justify-end">
                    <button type="submit" class="sipeng-btn-primary">Simpan Konfigurasi</button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
