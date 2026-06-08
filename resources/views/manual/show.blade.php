<x-app-layout>
    <x-slot name="header">Panduan — {{ $meta['title'] }}</x-slot>

    <div class="sipeng-page space-y-6">
        <div class="sipeng-card overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-4 sm:px-6 py-4 bg-slate-50/80">
                <div class="min-w-0">
                    <p class="text-xs text-slate-500 mb-1">
                        <a href="{{ route('manual.index') }}" class="hover:text-pink-700">Panduan / SOP</a>
                        <span class="mx-1">/</span>
                        <span class="text-slate-700">{{ $meta['title'] }}</span>
                    </p>
                    <h2 class="text-lg font-semibold text-sipeng-navy-900">{{ $meta['title'] }}</h2>
                    <p class="text-sm text-slate-600">{{ $meta['summary'] }}</p>
                </div>
                <div class="flex flex-wrap gap-2 print:hidden">
                    <a href="{{ route('manual.index') }}" class="sipeng-btn-secondary text-sm !py-1.5 !px-3">
                        ← Daftar modul
                    </a>
                    <a href="{{ route('manual.pdf', $module) }}" class="sipeng-btn-secondary text-sm !py-1.5 !px-3">
                        Unduh PDF
                    </a>
                    <button type="button" onclick="window.print()" class="sipeng-btn-primary text-sm !py-1.5 !px-3">
                        Cetak
                    </button>
                </div>
            </div>

            <div class="p-6 sm:p-8 manual-prose max-w-4xl">
                @include("manual.modules.{$module}")
                @include('manual.partials.footer-note')
            </div>
        </div>

        @if (count($modules) > 1)
            <div class="sipeng-card print:hidden">
                <div class="sipeng-card-body">
                    <h3 class="text-sm font-semibold text-slate-800 mb-3">Modul panduan lainnya</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($modules as $slug => $item)
                            @continue($slug === $module)
                            <a href="{{ route('manual.show', $slug) }}"
                                class="px-3 py-1 text-xs rounded-full border border-slate-200 hover:border-pink-300 hover:bg-pink-50 text-slate-700">
                                {{ $item['title'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    <style>@include('manual.partials.styles')</style>
</x-app-layout>
