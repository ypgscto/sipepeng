@props(['user', 'roleOptions', 'selectedRoles', 'isEdit' => false])

<div class="space-y-4">
    @if ($user->isSiakadSourced())
        <div class="rounded-lg border border-sky-100 bg-sky-50 px-4 py-3 text-sm text-sky-900">
            Akun dari <strong>Siakad-API</strong>. Identitas diperbarui lewat sinkronisasi.
            Centang <strong>Diizinkan login SiPepeng</strong> dan pilih minimal satu peran.
        </div>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
            <div>
                <dt class="text-slate-500">Nama</dt>
                <dd class="font-medium">{{ $user->name }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Email</dt>
                <dd class="font-medium">{{ $user->email }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Login Siakad</dt>
                <dd class="font-medium">{{ $user->siakad_login ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Sinkron terakhir</dt>
                <dd class="font-medium">{{ $user->synced_at?->format('d/m/Y H:i') ?? '—' }}</dd>
            </div>
        </dl>
    @else
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1" for="name">Nama</label>
            <input id="name" name="name" type="text" class="sipeng-input w-full" value="{{ old('name', $user->name) }}" required />
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1" for="email">Email</label>
            <input id="email" name="email" type="email" class="sipeng-input w-full" value="{{ old('email', $user->email) }}" required />
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1" for="password">Kata sandi {{ $isEdit ? '(kosongkan jika tidak diubah)' : '' }}</label>
            <input id="password" name="password" type="password" class="sipeng-input w-full" autocomplete="new-password" />
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1" for="password_confirmation">Konfirmasi kata sandi</label>
            <input id="password_confirmation" name="password_confirmation" type="password" class="sipeng-input w-full" autocomplete="new-password" />
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="hidden" name="is_active" value="0" />
            <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-emerald-600" @checked(old('is_active', $user->is_active)) />
            Akun aktif
        </label>
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="hidden" name="is_allowed_login" value="0" />
            <input type="checkbox" name="is_allowed_login" value="1" class="rounded border-slate-300 text-emerald-600" @checked(old('is_allowed_login', $user->is_allowed_login)) />
            Diizinkan login SiPepeng
        </label>
        <p class="text-xs text-slate-500 sm:col-span-2">Tanpa centang ini, pengguna tidak bisa masuk meskipun password Siakad valid.</p>
    </div>

    <div>
        <p class="block text-sm font-medium text-slate-700 mb-2">Peran SiPepeng <span class="text-red-600">*</span></p>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
            @php $selected = old('roles', $selectedRoles); @endphp
            @foreach ($roleOptions as $role)
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="roles[]" value="{{ $role->code }}" class="rounded border-slate-300 text-emerald-600"
                        @checked(is_array($selected) && in_array($role->code, $selected, true)) />
                    {{ $role->name }}
                </label>
            @endforeach
        </div>
    </div>
</div>
