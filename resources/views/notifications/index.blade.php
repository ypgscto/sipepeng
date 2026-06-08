<x-app-layout>
    <x-slot name="header">Notifikasi</x-slot>

    <div class="sipeng-page space-y-4">
        <x-sipeng.page-header title="Notifikasi" description="Pemberitahuan dari modul LPPM berdasarkan aktivitas nyata.">
            <x-slot name="actions">
                @if($unreadCount > 0)
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="sipeng-btn-secondary text-sm">Tandai semua dibaca</button>
                    </form>
                @endif
            </x-slot>
        </x-sipeng.page-header>

        <div class="sipeng-card">
            <div class="sipeng-card-body border-b border-slate-100 pb-4 mb-4">
                <form method="GET" class="flex flex-wrap gap-2">
                    @foreach(['all' => 'Semua', 'unread' => 'Belum dibaca', 'read' => 'Sudah dibaca'] as $key => $label)
                        <a href="{{ route('notifications.index', ['filter' => $key]) }}"
                           class="px-3 py-1.5 rounded-lg text-sm font-medium transition {{ ($filter ?? 'all') === $key ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </form>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($notifications as $notification)
                    <div class="px-4 py-4 flex flex-col sm:flex-row sm:items-start gap-3 {{ $notification->isRead() ? '' : 'bg-emerald-50/30' }}">
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                @unless($notification->isRead())
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-emerald-800">Baru</span>
                                @endunless
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600">
                                    {{ $categories[$notification->category] ?? $notification->category }}
                                </span>
                                @if($notification->severity === 'warning')
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-medium text-amber-800">Penting</span>
                                @elseif($notification->severity === 'urgent')
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-medium text-red-800">Segera</span>
                                @endif
                            </div>
                            <p class="font-semibold text-slate-900">{{ $notification->title }}</p>
                            <p class="text-sm text-slate-600 mt-1">{{ $notification->body }}</p>
                            <p class="text-xs text-slate-400 mt-2">{{ $notification->created_at->format('d/m/Y H:i') }} · {{ $notification->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2 shrink-0">
                            @if($notification->action_url)
                                <form method="POST" action="{{ route('notifications.read', $notification) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="redirect" value="1">
                                    <button type="submit" class="sipeng-btn-primary text-xs">{{ $notification->action_label ?? 'Buka' }}</button>
                                </form>
                            @endif
                            @unless($notification->isRead())
                                <form method="POST" action="{{ route('notifications.read', $notification) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="sipeng-btn-secondary text-xs">Tandai dibaca</button>
                                </form>
                            @endunless
                            <form method="POST" action="{{ route('notifications.dismiss', $notification) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="sipeng-btn-secondary text-xs">Abaikan</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="px-4 py-12 text-center text-slate-500">Tidak ada notifikasi untuk filter ini.</p>
                @endforelse
            </div>

            @if($notifications->hasPages())
                <div class="px-4 py-3 border-t border-slate-100">{{ $notifications->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
