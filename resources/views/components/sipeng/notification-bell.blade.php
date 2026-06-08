@auth
<div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
    <button
        type="button"
        class="relative p-2 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700"
        @click="open = !open"
        aria-label="Notifikasi"
    >
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        @if(($unreadNotificationCount ?? 0) > 0)
            <span class="absolute top-1 right-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold text-white">
                {{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}
            </span>
        @endif
    </button>

    <div
        x-show="open"
        x-cloak
        @click.outside="open = false"
        class="absolute right-0 mt-2 w-80 sm:w-96 rounded-xl border border-slate-200 bg-white shadow-lg z-50"
        style="display: none;"
    >
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <p class="text-sm font-semibold text-slate-900">Notifikasi</p>
            @if(($unreadNotificationCount ?? 0) > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="text-xs text-emerald-700 hover:underline">Tandai semua dibaca</button>
                </form>
            @endif
        </div>

        <div class="max-h-80 overflow-y-auto divide-y divide-slate-100">
            @forelse($recentNotifications ?? [] as $notification)
                <div class="px-4 py-3 {{ $notification->isRead() ? 'bg-white' : 'bg-emerald-50/40' }}">
                    <div class="flex items-start gap-2">
                        @unless($notification->isRead())
                            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-emerald-600"></span>
                        @endunless
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-slate-900 truncate">{{ $notification->title }}</p>
                            <p class="text-xs text-slate-600 mt-0.5 line-clamp-2">{{ $notification->body }}</p>
                            <p class="text-[11px] text-slate-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                            @if($notification->action_url)
                                <form method="POST" action="{{ route('notifications.read', $notification) }}" class="mt-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="redirect" value="1">
                                    <button type="submit" class="text-xs font-medium text-emerald-700 hover:underline">
                                        {{ $notification->action_label ?? 'Buka' }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <p class="px-4 py-8 text-center text-sm text-slate-500">Belum ada notifikasi.</p>
            @endforelse
        </div>

        <div class="border-t border-slate-100 px-4 py-2 text-center">
            <a href="{{ route('notifications.index') }}" class="text-xs font-medium text-emerald-700 hover:underline">Lihat semua notifikasi</a>
        </div>
    </div>
</div>
@endauth
