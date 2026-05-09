<div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
    {{-- Header --}}
    <div class="mb-4 flex items-center justify-between">
        <button wire:click="previous" class="rounded-lg p-2 hover:bg-gray-100">←</button>
        <h2 class="text-lg font-semibold text-gray-900">{{ $cursor->format('F Y') }}</h2>
        <button wire:click="next" class="rounded-lg p-2 hover:bg-gray-100">→</button>
    </div>

    {{-- Weekday header --}}
    <div class="grid grid-cols-7 gap-1 mb-2 text-center text-xs font-medium text-gray-500">
        @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $weekday)
            <div>{{ $weekday }}</div>
        @endforeach
    </div>

    {{-- Days --}}
    <div class="grid grid-cols-7 gap-1">
        @foreach ($days as $day)
            <div class="min-h-[80px] rounded-lg border p-2 text-xs
                {{ $day['inMonth'] ? 'bg-white' : 'bg-gray-50 text-gray-400' }}
                {{ $day['isToday'] ? 'border-indigo-500' : 'border-gray-100' }}">
                <div class="font-semibold mb-1">{{ $day['date']->day }}</div>
                @foreach ($day['events']->take(2) as $event)
                    <a href="{{ route('events.show', $event->slug) }}"
                       class="block truncate rounded bg-indigo-50 px-1 py-0.5 text-[10px] text-indigo-700 hover:bg-indigo-100">
                        {{ $event->title }}
                    </a>
                @endforeach
                @if ($day['events']->count() > 2)
                    <p class="text-[10px] text-gray-500 mt-1">
                        +{{ $day['events']->count() - 2 }} more
                    </p>
                @endif
            </div>
        @endforeach
    </div>
</div>