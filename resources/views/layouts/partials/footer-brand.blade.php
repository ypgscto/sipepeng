@php
    $variant = $variant ?? 'sidebar';
    $forceExpanded = $forceExpanded ?? false;
    $institutionUrl = $sipengBranding['institution_url'];
    $institutionLabel = $sipengBranding['institution_url_label'];
@endphp

@if ($variant === 'sidebar')
    @if ($forceExpanded)
        <a
            href="{{ $institutionUrl }}"
            target="_blank"
            rel="noopener noreferrer"
            class="mt-auto block px-3 py-4 border-t border-emerald-500/40 bg-emerald-950/40 text-center hover:bg-emerald-900/50 transition group"
            title="Kunjungi website {{ $sipengBranding['institution_name'] }}"
        >
            <p class="text-[10px] font-semibold uppercase tracking-wider text-emerald-200/90 group-hover:text-white">Website</p>
            <p class="text-sm font-bold text-white break-all leading-snug mt-1 group-hover:underline">{{ $institutionLabel }}</p>
        </a>
    @else
        <a
            href="{{ $institutionUrl }}"
            target="_blank"
            rel="noopener noreferrer"
            class="mt-auto block px-2 py-3 border-t border-emerald-500/40 bg-emerald-950/40 text-center w-full hover:bg-emerald-900/50 transition group"
            x-show="!$store.sidebar.collapsed"
            x-cloak
            title="Kunjungi website {{ $sipengBranding['institution_name'] }}"
        >
            <p class="text-[10px] font-semibold uppercase tracking-wider text-emerald-200/90">Website</p>
            <p class="text-xs sm:text-sm font-bold text-white break-all leading-snug mt-0.5 group-hover:underline">{{ $institutionLabel }}</p>
        </a>
        <a
            href="{{ $institutionUrl }}"
            target="_blank"
            rel="noopener noreferrer"
            class="mt-auto flex flex-col items-center justify-center px-1 py-3 border-t border-emerald-500/40 bg-emerald-950/40 w-full hover:bg-emerald-900/50 transition"
            x-show="$store.sidebar.collapsed"
            x-cloak
            title="{{ $institutionLabel }}"
        >
            <svg class="h-5 w-5 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
            </svg>
        </a>
    @endif
@endif
