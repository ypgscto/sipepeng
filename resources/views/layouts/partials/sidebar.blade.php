@php
    $forceExpanded = $forceExpanded ?? false;
    $sidebarGroups = $sidebarGroups ?? [];
@endphp
<aside
    class="flex flex-col h-full min-h-screen bg-gradient-to-b from-sipeng-navy-950 via-sipeng-navy-900 to-sipeng-navy-800 text-white shadow-xl border-r border-white/5"
    :class="(!$forceExpanded && $store.sidebar.collapsed) ? 'lg:items-center' : ''"
>
    <div class="px-3 py-4 border-b border-white/10 shrink-0 w-full bg-sipeng-navy-950/50">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group justify-center lg:justify-start" title="{{ $sipengBranding['app_name'] }}">
            <x-sipeng.institution-logo
                :force-expanded="$forceExpanded"
                class="ring-pink-400/30"
            />
            @if ($forceExpanded)
                <div class="min-w-0 flex-1">
                    <p class="text-base font-bold leading-tight tracking-tight text-white">{{ $sipengBranding['app_name'] }}</p>
                    <p class="text-[11px] font-medium text-pink-100/90 leading-snug mt-0.5 line-clamp-2">
                        {{ $sipengBranding['institution_name'] }}
                    </p>
                    <p class="text-[10px] uppercase tracking-wider text-pink-300/80 mt-1">{{ $sipengBranding['module'] }}</p>
                </div>
            @else
                <div class="min-w-0 flex-1" x-show="!$store.sidebar.collapsed" x-cloak>
                    <p class="text-base font-bold leading-tight tracking-tight text-white">{{ $sipengBranding['app_name'] }}</p>
                    <p class="text-[11px] font-medium text-pink-100/90 leading-snug mt-0.5 line-clamp-2">
                        {{ $sipengBranding['institution_name'] }}
                    </p>
                    <p class="text-[10px] uppercase tracking-wider text-pink-300/80 mt-1">{{ $sipengBranding['module'] }}</p>
                </div>
            @endif
        </a>
    </div>

    @include('layouts.partials.sidebar-nav')

    @include('layouts.partials.footer-brand', ['variant' => 'sidebar', 'forceExpanded' => $forceExpanded])
</aside>
