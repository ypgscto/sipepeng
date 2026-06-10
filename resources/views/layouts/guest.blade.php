<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Masuk — '.$sipengBranding['app_name'])</title>
    <x-sipeng.favicon />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gradient-to-br from-pink-50 via-rose-50/60 to-pink-100/40 text-slate-800 min-h-dvh flex flex-col overflow-x-hidden">
    <div class="flex-1 min-h-0 flex items-center justify-center px-4 py-3 sm:py-4 sipeng-content-pad-footer">
        <div class="w-full max-w-md relative z-10">
            <div class="text-center mb-4 sm:mb-5">
                <div class="flex justify-center mb-3">
                    <x-sipeng.institution-logo size="lg" class="!h-14 !w-14 ring-pink-200 shadow-md" />
                </div>
                <h1 class="text-xl font-bold text-sipeng-navy-900">{{ $sipengBranding['app_name'] }}</h1>
                <p class="text-sm text-slate-600 mt-0.5">{{ $sipengBranding['app_subtitle'] }}</p>
                <p class="text-xs text-pink-800 font-semibold mt-1">{{ $sipengBranding['institution_name'] }} — {{ $sipengBranding['module'] }}</p>
            </div>

            <div class="relative">
                <x-sipeng.mascot variant="login" class="sipeng-mascot-login" />

                <div class="sipeng-card shadow-xl border-t-4 border-t-pink-500 ring-1 ring-pink-100/80 relative z-10">
                    <div class="sipeng-card-body py-4 sm:py-5">
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-sipeng.site-footer />
</body>
</html>
