<x-layouts.app :title="'Notifications — HobbyHub'">
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
                <p class="text-sm text-gray-500 mt-1">Stay up to date with your communities</p>
            </div>
            @if($notifications->where('read_at', null)->count() > 0)
                <form method="POST" action="{{ route('notifications.readAll') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-xl transition-colors">
                        Mark all as read
                    </button>
                </form>
            @endif
        </div>

        @forelse($notifications as $notification)
            <div class="bg-white rounded-2xl border shadow-sm p-5 flex items-start gap-4 transition-all
                {{ $notification->read_at ? 'border-gray-200/60' : 'border-indigo-200 bg-indigo-50/30' }}">
                {{-- Icon --}}
                <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0
                    {{ $notification->read_at ? 'bg-gray-100' : 'bg-indigo-100' }}">
                    @php
                        $icon = match(class_basename($notification->type)) {
                            'NewCommentNotification' => '💬',
                            'PostReactionNotification' => '❤️',
                            'MembershipRequestNotification' => '🙋',
                            'MemberApprovedNotification' => '✅',
                            'MemberJoinedNotification' => '👋',
                            'EventCreatedNotification' => '📅',
                            'EventReminderNotification' => '⏰',
                            'EventCancelledNotification' => '❌',
                            'WaitlistPromotedNotification' => '🎉',
                            'NewMessageNotification' => '✉️',
                            default => '🔔',
                        };
                    @endphp
                    <span class="text-lg">{{ $icon }}</span>
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-900 leading-relaxed">
                        {{ $notification->data['message'] ?? $notification->data['action'] ?? 'You have a new notification.' }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                </div>

                {{-- Unread dot --}}
                @unless($notification->read_at)
                    <span class="w-2.5 h-2.5 bg-indigo-500 rounded-full shrink-0 mt-2"></span>
                @endunless
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-gray-200/60 shadow-sm p-16 text-center">
                <div class="w-16 h-16 mx-auto bg-indigo-50 rounded-2xl flex items-center justify-center mb-4">
                    <span class="text-3xl">🔔</span>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No notifications yet</h3>
                <p class="text-sm text-gray-500">When someone interacts with your content, you'll see it here.</p>
            </div>
        @endforelse

        <div class="pt-2">{{ $notifications->links() }}</div>
    </div>
</x-layouts.app>
