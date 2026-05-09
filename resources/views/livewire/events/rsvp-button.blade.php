<div class="mt-4">
    @php $isCancelled = ($event->status->value ?? $event->status) === 'cancelled'; @endphp

    @if($isCancelled)
        <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 text-center">This event has been cancelled.</div>
    @elseif(!auth()->check())
        <a href="{{ route('login') }}" class="block w-full py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl text-center shadow-lg text-sm">Sign in to RSVP</a>
    @elseif($isWaitlisted)
        <div class="flex items-center gap-3">
            <div class="flex-1 p-3 bg-amber-50 border border-amber-200 rounded-xl text-center">
                <p class="text-sm font-medium text-amber-700">⏳ Waitlisted (#{{ $waitlistPosition }})</p>
            </div>
            <button wire:click="cancel" class="px-4 py-3 text-sm font-medium text-red-600 bg-red-50 rounded-xl hover:bg-red-100 transition-colors">Cancel</button>
        </div>
    @elseif($currentStatus === 'going')
        <div class="flex items-center gap-3">
            <div class="flex-1 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-center">
                <p class="text-sm font-medium text-emerald-700">✅ You're going!</p>
            </div>
            <button wire:click="cancel" class="px-4 py-3 text-sm font-medium text-red-600 bg-red-50 rounded-xl hover:bg-red-100 transition-colors">Cancel</button>
        </div>
    @else
        <div class="flex gap-2">
            <button wire:click="setStatus('going')" class="flex-1 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg shadow-indigo-200 hover:shadow-xl transition-all text-sm">
                Going ✓
            </button>
            <button wire:click="setStatus('maybe')" class="px-5 py-3 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">
                Maybe
            </button>
            <button wire:click="setStatus('not_going')" class="px-5 py-3 text-sm font-medium text-gray-500 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                Can't Go
            </button>
        </div>
    @endif
</div>