@php
    $items = [
        ['route' => 'admin.settings.index', 'label' => 'Ringkasan', 'icon' => 'home'],
        ['route' => 'admin.settings.general.edit', 'label' => 'Profil & Footer', 'icon' => 'document'],
        ['route' => 'admin.settings.logo.edit', 'label' => 'Logo Institusi', 'icon' => 'certificate'],
        ['route' => 'admin.settings.siakad.edit', 'label' => 'SIAKAD-API', 'icon' => 'database'],
        ['route' => 'admin.settings.roles.index', 'label' => 'Mapping Role', 'icon' => 'community'],
        ['route' => 'admin.settings.user-sync.index', 'label' => 'Sinkron User', 'icon' => 'sync'],
        ['route' => 'admin.settings.users.index', 'label' => 'Pengguna', 'icon' => 'community'],
        ['route' => 'admin.settings.templates.index', 'label' => 'Template', 'icon' => 'document'],
    ];

    if ($canBackup ?? false) {
        $items[] = ['route' => 'admin.settings.backup.index', 'label' => 'Backup Database', 'icon' => 'archive'];
    }
@endphp

<nav class="flex flex-wrap gap-2 mb-6">
    @foreach ($items as $item)
        @php
            $active = request()->routeIs(str_replace('.index', '.*', $item['route'])) || request()->routeIs($item['route']);
            if ($item['route'] === 'admin.settings.index') {
                $active = request()->routeIs('admin.settings.index');
            }
        @endphp
        <a
            href="{{ route($item['route']) }}"
            @class([
                'inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition',
                'bg-emerald-700 text-white shadow-sm' => $active,
                'bg-white text-slate-700 ring-1 ring-slate-200 hover:bg-emerald-50 hover:text-emerald-800' => ! $active,
            ])
        >
            <x-sipeng.icon :name="$item['icon']" class="h-4 w-4" />
            {{ $item['label'] }}
        </a>
    @endforeach
</nav>
