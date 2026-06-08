<x-app-layout>
    <x-slot name="header">Panduan &amp; SOP</x-slot>

    <div class="sipeng-page space-y-6">
        <x-sipeng.page-header
            :title="$manualMeta['title']"
            description="Pilih modul untuk membaca panduan penggunaan (HTML). Setiap modul dapat diunduh sebagai PDF."
        >
            <x-slot name="actions">
                <span class="text-xs text-slate-500 font-medium">
                    Versi {{ $manualMeta['version'] }} · {{ $manualMeta['updated_at'] }}
                </span>
            </x-slot>
        </x-sipeng.page-header>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach ($modules as $slug => $item)
                <article class="sipeng-card hover:ring-pink-200/80 transition flex flex-col">
                    <div class="sipeng-card-body flex flex-col flex-1">
                        <div class="flex items-start gap-3">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-pink-50 text-pink-700 shrink-0">
                                <x-sipeng.icon :name="$item['icon'] ?? 'book'" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <h3 class="font-semibold text-sipeng-navy-900">{{ $item['title'] }}</h3>
                                <p class="text-sm text-slate-600 mt-1">{{ $item['summary'] }}</p>
                            </div>
                        </div>
                        <div class="mt-4 flex flex-wrap gap-2 pt-3 border-t border-slate-100 mt-auto">
                            <a href="{{ route('manual.show', $slug) }}" class="sipeng-btn-primary text-sm !py-1.5 !px-3">
                                Baca HTML
                            </a>
                            <a href="{{ route('manual.pdf', $slug) }}" class="sipeng-btn-secondary text-sm !py-1.5 !px-3">
                                Unduh PDF
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</x-app-layout>
