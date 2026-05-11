<x-layouts.app :title="$event->title . ' — HobbyHub'">
    <div class="max-w-3xl mx-auto space-y-6">
        {{-- Event Card --}}
        <div class="bg-white rounded-2xl border border-gray-200/60 shadow-sm overflow-hidden">
            <div class="h-48 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 relative flex items-end">
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                <div class="relative p-6 text-white w-full">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-white/20 backdrop-blur-sm">{{ ucfirst($event->type->value ?? $event->type) }}</span>
                        <span class="px-2.5 py-1 text-xs font-medium rounded-full {{ ($event->status->value ?? $event->status) === 'cancelled' ? 'bg-red-500/80' : 'bg-emerald-500/80' }}">{{ ucfirst($event->status->value ?? $event->status) }}</span>
                    </div>
                    <h1 class="text-2xl font-bold">{{ $event->title }}</h1>
                </div>
            </div>

            <div class="p-6 space-y-5">
                {{-- Date & Time --}}
                <div class="flex flex-wrap gap-6">
                    <div class="flex items-start gap-3">
                        <div class="p-2 bg-indigo-50 rounded-xl">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $event->starts_at->format('l, M j, Y') }}</p>
                            <p class="text-xs text-gray-500">{{ $event->starts_at->format('g:i A') }} — {{ $event->ends_at?->format('g:i A') ?? 'TBD' }}</p>
                        </div>
                    </div>
                    @if($event->location)
                        <div class="flex items-start gap-3">
                            <div class="p-2 bg-emerald-50 rounded-xl">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $event->location }}</p>
                                <p class="text-xs text-gray-500">{{ ($event->type->value ?? $event->type) === 'online' ? 'Virtual Event' : 'In Person' }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Capacity --}}
                @if($event->capacity)
                    <div>
                        @php $pct = min(100, ($event->rsvp_count_cache / max(1, $event->capacity)) * 100); @endphp
                        <div class="flex items-center justify-between text-sm mb-1">
                            <span class="text-gray-600">{{ $event->rsvp_count_cache ?? 0 }} / {{ $event->capacity }} attending</span>
                            <span class="font-medium {{ $pct >= 90 ? 'text-red-600' : 'text-indigo-600' }}">{{ round($pct) }}%</span>
                        </div>
                        <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @endif

                {{-- Description --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">About this event</h3>
                    <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $event->description }}</p>
                </div>

                {{-- Group link --}}
                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white font-bold shadow-sm">
                        {{ strtoupper(substr($event->group->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $event->group->name }}</p>
                        <p class="text-xs text-gray-500">Hosted by {{ $event->creator->name }}</p>
                    </div>
                    <a href="{{ route('groups.show', $event->group) }}" class="ml-auto text-sm text-indigo-600 font-medium hover:text-indigo-700">View Group →</a>
                </div>

                {{-- RSVP --}}
                @livewire('events.rsvp-button', ['event' => $event])
            </div>
        </div>

        {{-- Attendees --}}
        @livewire('events.attendee-list', ['event' => $event])
    </div>
</x-layouts.app>
