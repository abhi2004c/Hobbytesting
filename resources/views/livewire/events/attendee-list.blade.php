<div class="bg-white rounded-2xl border border-gray-200/60 shadow-sm p-5">
    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Attendees ({{ $attendees->count() }})</h3>
    <div class="flex flex-wrap gap-3">
        @foreach($attendees as $attendee)
            @php $user = $attendee->user ?? $attendee; @endphp
            <div class="flex items-center gap-2 px-3 py-2 bg-gray-50 rounded-xl">
                <img src="{{ $user->avatar_url }}" alt="" class="w-7 h-7 rounded-full object-cover">
                <span class="text-sm text-gray-700">{{ $user->name }}</span>
                <span class="px-1.5 py-0.5 text-xs rounded-full {{ $tab === 'going' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">{{ ucfirst($tab) }}</span>
            </div>
        @endforeach
    </div>
    @if($attendees->isEmpty())
        <p class="text-sm text-gray-400 italic">No RSVPs yet. Be the first!</p>
    @endif
</div>