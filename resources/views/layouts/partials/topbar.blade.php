<header class="sticky top-0 z-30 flex h-14 sm:h-16 shrink-0 items-center gap-2 sm:gap-3 border-b border-slate-200 bg-white shadow-sm px-3 sm:px-6">
    <button
        type="button"
        class="lg:hidden p-2.5 rounded-lg text-slate-600 hover:bg-slate-100 min-h-[44px] min-w-[44px] flex items-center justify-center"
        @click="$store.sidebar.openMobile()"
        aria-label="Buka menu"
    >
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <button
        type="button"
        class="hidden lg:inline-flex p-2 rounded-lg text-slate-500 hover:bg-slate-100 min-h-[44px] min-w-[44px] items-center justify-center"
        @click="$store.sidebar.toggleCollapse()"
        aria-label="Ciutkan sidebar"
    >
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
        </svg>
    </button>

    <div class="flex flex-1 min-w-0 items-center gap-2 sm:gap-3">
        <x-sipeng.institution-logo size="sm" class="lg:hidden !h-9 !w-9 ring-slate-200 shrink-0" />
        <div class="min-w-0 flex-1">
            @isset($header)
                <h1 class="text-sm sm:text-base font-bold text-sipeng-navy-900 truncate leading-tight">{{ $header }}</h1>
            @else
                <h1 class="text-sm sm:text-base font-bold text-sipeng-navy-900 truncate">{{ $sipengBranding['app_name'] }}</h1>
            @endisset
            <p class="hidden sm:block text-xs text-slate-500 truncate">{{ $sipengBranding['institution_name'] }}</p>
        </div>
    </div>

    @auth
        <x-sipeng.notification-bell />

        <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
            <button
                type="button"
                @click="open = !open"
                class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white pl-1.5 pr-2 py-1.5 min-h-[44px] hover:bg-slate-50 transition"
                aria-label="Menu pengguna"
            >
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-pink-600 text-xs font-bold text-white shrink-0">
                    {{ auth()->user()->initials() }}
                </span>
                <span class="hidden md:block max-w-[8rem] truncate text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</span>
                <svg class="hidden md:block h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div
                x-show="open"
                x-cloak
                @click.outside="open = false"
                class="absolute right-0 mt-2 w-56 rounded-xl border border-slate-200 bg-white shadow-lg py-1 z-50"
                style="display: none;"
            >
                <div class="px-4 py-3 border-b border-slate-100">
                    <p class="text-sm font-semibold text-slate-900 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-slate-500 truncate">{{ auth()->user()->primaryRoleCode() ?? 'pengguna' }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="p-2">
                    @csrf
                    <button type="submit" class="w-full text-left px-3 py-2.5 text-sm font-medium text-rose-700 hover:bg-rose-50 rounded-lg min-h-[44px]">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    @endauth
</header>
