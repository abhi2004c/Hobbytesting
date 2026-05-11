<div class="space-y-3">
    @forelse($events as $event)
        <a href="{{ route('events.show', $event) }}" class="block bg-white rounded-2xl border border-gray-200/60 shadow-sm p-4 card-hover">
            <div class="flex items-start gap-4">
                <div class="text-center shrink-0">
                    <div class="w-14 h-14 bg-indigo-50 rounded-xl flex flex-col items-center justify-center">
                        <span class="text-xs font-bold text-indigo-600 uppercase">{{ $event->starts_at->format('M') }}</span>
                        <span class="text-lg font-bold text-gray-900">{{ $event->starts_at->format('j') }}</span>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-bold text-gray-900 truncate">{{ $event->title }}</h3>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ $event->starts_at->format('g:i A') }}
                        @if($event->location) · {{ $event->location }} @endif
                    </p>
                    <div class="mt-2 flex items-center gap-3">
                        <span class="flex items-center gap-1 text-xs text-gray-500">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            {{ $event->rsvp_count_cache ?? 0 }}{{ $event->capacity ? '/' . $event->capacity : '' }}
                        </span>
                        <span class="px-2 py-0.5 text-xs rounded-full font-medium {{ ($event->status->value ?? $event->status) === 'published' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($event->status->value ?? $event->status) }}</span>
                    </div>
                </div>
            </div>
        </a>
    @empty
        <div class="text-center p-8 text-sm text-gray-400">No events found.</div>
    @endforelse

    @if(method_exists($events, 'links'))
        {{ $events->links() }}
    @endif
</div>
