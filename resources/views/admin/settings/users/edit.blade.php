<x-app-layout>
    <x-slot name="header">Ubah Pengguna</x-slot>

    <div class="sipeng-page">
        @include('admin.settings.partials.nav', ['canBackup' => auth()->user()?->hasRole('super_admin') ?? false])

        <form method="POST" action="{{ route('admin.settings.users.update', $user) }}" class="sipeng-card sipeng-card-body space-y-4 max-w-3xl">
            @csrf
            @method('PUT')

            @include('admin.settings.users._form', [
                'user' => $user,
                'roleOptions' => $roleOptions,
                'selectedRoles' => $selectedRoles,
                'isEdit' => true,
            ])

            <div class="flex gap-2 pt-2">
                <button type="submit" class="sipeng-btn-primary">Simpan</button>
                <a href="{{ route('admin.settings.users.index') }}" class="sipeng-btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>
