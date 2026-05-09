<div class="bg-white rounded-2xl border border-gray-200/60 shadow-sm p-4" x-data="{ showPoll: @entangle('type').live === 'poll' }">
    <div class="flex items-start gap-3">
        <img src="{{ auth()->user()->avatar_url }}" alt="" class="w-10 h-10 rounded-full object-cover ring-2 ring-white shadow-sm">
        <div class="flex-1 space-y-3">
            <textarea wire:model="content" rows="3" placeholder="Share something with the group..."
                      class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-all resize-none placeholder-gray-400"></textarea>
            @error('content') <p class="text-xs text-red-500">{{ $message }}</p> @enderror

            {{-- Type Selector --}}
            <div class="flex items-center gap-2">
                <select wire:model.live="type" class="px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500">
                    <option value="text">📝 Text</option>
                    <option value="image">📷 Image</option>
                    <option value="link">🔗 Link</option>
                    <option value="poll">📊 Poll</option>
                </select>
            </div>

            {{-- Poll Fields --}}
            @if($type === 'poll')
                <div class="space-y-2 p-3 bg-indigo-50/50 rounded-xl border border-indigo-100">
                    <input wire:model="pollQuestion" type="text" placeholder="Ask a question..."
                           class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    @error('pollQuestion') <p class="text-xs text-red-500">{{ $message }}</p> @enderror

                    @foreach($pollOptions as $i => $opt)
                        <div class="flex gap-2">
                            <input wire:model="pollOptions.{{ $i }}" type="text" placeholder="Option {{ $i + 1 }}"
                                   class="flex-1 px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm">
                            @if(count($pollOptions) > 2)
                                <button wire:click="removePollOption({{ $i }})" class="p-2 text-red-400 hover:text-red-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            @endif
                        </div>
                    @endforeach

                    <div class="flex items-center justify-between">
                        <button wire:click="addPollOption" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">+ Add Option</button>
                        <label class="flex items-center gap-1.5 text-xs text-gray-600">
                            <input wire:model="allowMultiple" type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            Allow multiple votes
                        </label>
                    </div>
                </div>
            @endif

            <div class="flex justify-end">
                <button wire:click="submit" wire:loading.attr="disabled"
                        class="px-5 py-2 bg-indigo-600 text-white text-sm font-medium rounded-xl hover:bg-indigo-700 transition-colors disabled:opacity-50">
                    <span wire:loading.remove>Post</span>
                    <span wire:loading class="spinner inline-block"></span>
                </button>
            </div>
        </div>
    </div>
</div>
