<div class="mt-3 p-3 bg-gray-50 rounded-xl border border-gray-100">
    <p class="text-sm font-medium text-gray-900 mb-3">{{ $poll->question }}</p>

    @if($poll->isExpired())
        <p class="text-xs text-amber-600 mb-2">⏰ This poll has ended</p>
    @endif

    <div class="space-y-2">
        @foreach($results as $option)
            <div class="relative">
                @if($hasVoted || $poll->isExpired())
                    {{-- Results view --}}
                    <div class="relative bg-white rounded-lg border border-gray-200 overflow-hidden">
                        <div class="absolute inset-y-0 left-0 bg-indigo-50 transition-all duration-500 rounded-lg"
                             style="width: {{ $option['percentage'] }}%"></div>
                        <div class="relative flex items-center justify-between px-3 py-2">
                            <span class="text-sm text-gray-700 flex items-center gap-2">
                                @if(in_array($option['id'], $selectedOptions))
                                    <svg class="w-4 h-4 text-indigo-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                @endif
                                {{ $option['text'] }}
                            </span>
                            <span class="text-xs font-semibold text-gray-500">{{ $option['votes_count'] }} ({{ round($option['percentage']) }}%)</span>
                        </div>
                    </div>
                @else
                    {{-- Voting view --}}
                    <label class="flex items-center gap-3 px-3 py-2 bg-white rounded-lg border border-gray-200 cursor-pointer hover:border-indigo-300 hover:bg-indigo-50/30 transition-colors">
                        <input wire:model="selectedOptions" type="{{ $poll->allow_multiple ? 'checkbox' : 'radio' }}"
                               value="{{ $option['id'] }}" name="poll_option"
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-gray-700">{{ $option['text'] }}</span>
                    </label>
                @endif
            </div>
        @endforeach
    </div>

    @if(!$hasVoted && !$poll->isExpired())
        <button wire:click="vote" class="mt-3 w-full py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
            Vote
        </button>
    @endif

    @if($poll->ends_at)
        <p class="text-xs text-gray-400 mt-2">
            {{ $poll->isExpired() ? 'Ended' : 'Ends' }} {{ $poll->ends_at->diffForHumans() }}
        </p>
    @endif
</div>
