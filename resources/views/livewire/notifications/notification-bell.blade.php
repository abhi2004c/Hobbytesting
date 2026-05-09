<div class="relative" x-data="{ open: false }">
    <button @click="open = !open" class="relative p-2 rounded-lg hover:bg-gray-100 transition-colors">
        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
        @if($unreadCount > 0)
            <span class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
        @endif
    </button>

    <div x-show="open" @click.away="open = false" x-transition
         class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-gray-100 z-50 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
            @if($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">Mark all read</button>
                </form>
            @endif
        </div>
        <div class="max-h-64 overflow-y-auto">
            @forelse($notifications as $notif)
                <div class="px-4 py-3 hover:bg-gray-50 transition-colors {{ $notif->read_at ? '' : 'bg-indigo-50/50' }}">
                    <p class="text-sm text-gray-700">{{ $notif->data['message'] ?? 'New notification' }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                </div>
            @empty
                <div class="p-8 text-center">
                    <p class="text-sm text-gray-400">All caught up! 🎉</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
