@php
    $forceExpanded = $forceExpanded ?? false;
    $sidebarGroups = $sidebarGroups ?? [];
@endphp
<nav class="flex-1 overflow-y-auto px-2 py-3 space-y-1" x-data>
    @forelse ($sidebarGroups as $group)
        @if (! ($forceExpanded ?? false))
            <p class="sipeng-sidebar-group-label truncate" x-show="!$store.sidebar.collapsed" x-cloak>{{ $group['label'] }}</p>
        @else
            <p class="sipeng-sidebar-group-label truncate">{{ $group['label'] }}</p>
        @endif

        @foreach ($group['items'] as $item)
            <a
                href="{{ $item['url'] }}"
                @class([
                    'sipeng-sidebar-link',
                    'sipeng-sidebar-link-active' => $item['active'],
                    'sipeng-sidebar-link-inactive' => ! $item['active'],
                    'justify-center lg:justify-start' => ! ($forceExpanded ?? false),
                ])
                title="{{ $item['label'] }}"
            >
                <x-sipeng.icon :name="$item['icon'] ?? 'link'" class="h-5 w-5 shrink-0" />
                <span class="truncate" @if (! ($forceExpanded ?? false)) x-show="!$store.sidebar.collapsed" x-cloak @endif>{{ $item['label'] }}</span>
            </a>
        @endforeach
    @empty
        <p class="px-3 py-2 text-xs text-teal-200/60">Tidak ada menu tersedia.</p>
    @endforelse
</nav>
