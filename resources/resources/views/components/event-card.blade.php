@props(['event'])

@php
    $isToday = $event->starts_at?->isToday();
    $isThisWeek = $event->starts_at?->isCurrentWeek();
@endphp

<a href="{{ route('events.show', $event) }}" class="block bg-white rounded-2xl border border-gray-200/60 shadow-sm overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
    {{-- Cover --}}
    <div class="relative h-32">
        @if($event->getFirstMediaUrl('cover'))
            <img src="{{ $event->getFirstMediaUrl('cover') }}" alt="" class="w-full h-full object-cover">
        @else
            <div class="w-full h-full bg-gradient-to-br from-purple-500 to-pink-600"></div>
        @endif
        {{-- Date Pill --}}
        <div class="absolute top-3 right-3 px-2.5 py-1.5 rounded-lg text-center shadow-sm
            {{ $isToday ? 'bg-green-500 text-white' : ($isThisWeek ? 'bg-blue-500 text-white' : 'bg-white/90 backdrop-blur-sm text-gray-700') }}">
            <p class="text-[10px] font-bold uppercase leading-none">{{ $event->starts_at?->format('M') }}</p>
            <p class="text-lg font-black leading-none">{{ $event->starts_at?->format('d') }}</p>
        </div>
    </div>

    {{-- Body --}}
    <div class="p-4">
        <h3 class="text-sm font-bold text-gray-900 truncate">{{ $event->title }}</h3>
        <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            {{ $event->location ?? 'Online' }}
        </p>

        <div class="flex items-center justify-between mt-3">
            <span class="text-xs text-gray-400">
                {{ $event->rsvp_count_cache ?? 0 }} going
            </span>
            <span class="px-2 py-0.5 text-xs rounded-full font-medium
                {{ $event->type->value === 'online' ? 'bg-blue-50 text-blue-700' : ($event->type->value === 'hybrid' ? 'bg-amber-50 text-amber-700' : 'bg-green-50 text-green-700') }}">
                {{ ucfirst(str_replace('_', ' ', $event->type->value)) }}
            </span>
        </div>
    </div>
</a>
