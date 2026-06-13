<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $sipengBranding['app_name'].' — Portal Publik')</title>
    <x-sipeng.favicon />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gradient-to-b from-pink-50/50 to-slate-50 text-slate-800 min-h-dvh flex flex-col">
    <x-sipeng.mascot-assistant layout="public" />

    <header class="bg-white border-b border-pink-100 shadow-sm sticky top-0 z-40 shrink-0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 py-4">
                <a href="{{ route('public.landing') }}" class="flex items-center gap-3 group shrink-0">
                    <x-sipeng.institution-logo size="md" class="ring-pink-100 shadow-sm group-hover:ring-pink-200 transition" />
                    <div class="min-w-0">
                        <p class="text-base sm:text-lg font-bold text-sipeng-navy-900 leading-tight">{{ $sipengBranding['app_name'] }}</p>
                        <p class="text-xs sm:text-sm text-slate-600 truncate">{{ $sipengBranding['app_subtitle'] }}</p>
                        <p class="text-[11px] font-semibold text-pink-800 mt-0.5">{{ $sipengBranding['institution_name'] }}</p>
                    </div>
                </a>

                <nav class="flex flex-wrap items-center gap-2 sm:gap-4">
                    <a href="{{ route('public.landing') }}"
                       class="text-sm font-medium px-3 py-2 rounded-lg transition {{ request()->routeIs('public.landing') ? 'bg-pink-50 text-pink-800' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        Beranda
                    </a>
                    <a href="{{ route('public.dashboard') }}"
                       class="text-sm font-medium px-3 py-2 rounded-lg transition {{ request()->routeIs('public.dashboard') ? 'bg-pink-50 text-pink-800' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        Dashboard Umum
                    </a>
                    <a href="{{ route('login') }}" class="sipeng-btn-primary text-sm py-2 px-4 whitespace-nowrap">
                        Login SiPepeng
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <main class="flex-1 w-full sipeng-content-pad-footer">
        @yield('content')
    </main>

    <x-sipeng.site-footer />

    @stack('scripts')
</body>
</html>
