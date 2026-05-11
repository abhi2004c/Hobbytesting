<div class="flex flex-col h-full" x-data="{ typingUser: null, typingTimer: null }">
    @if($conversation)
        {{-- Header --}}
        <div class="flex items-center gap-3 p-4 border-b border-gray-200 bg-white">
            <div class="flex -space-x-2">
                @foreach($conversation->participants->take(3) as $p)
                    <img src="{{ $p->user->avatar_url }}" alt="" class="w-8 h-8 rounded-full ring-2 ring-white object-cover">
                @endforeach
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900 truncate">
                    {{ $conversation->name ?? $conversation->participants->where('user_id', '!=', auth()->id())->pluck('user.name')->join(', ') }}
                </p>
                <p class="text-xs text-gray-400" x-show="typingUser" x-text="typingUser + ' is typing...'" x-cloak></p>
            </div>
        </div>

        {{-- Messages --}}
        <div class="flex-1 overflow-y-auto p-4 space-y-3 flex flex-col-reverse" id="chat-messages">
            @foreach($messages as $msg)
                <div class="flex gap-2 {{ $msg->user_id === auth()->id() ? 'flex-row-reverse' : '' }}">
                    @if($msg->user_id !== auth()->id())
                        <img src="{{ $msg->user->avatar_url }}" alt="" class="w-7 h-7 rounded-full object-cover mt-1">
                    @endif
                    <div class="max-w-[70%]">
                        <div class="px-3.5 py-2 rounded-2xl text-sm {{ $msg->user_id === auth()->id() ? 'bg-indigo-600 text-white rounded-br-md' : 'bg-gray-100 text-gray-800 rounded-bl-md' }}">
                            <p>{{ $msg->content }}</p>
                        </div>
                        <div class="flex items-center gap-1 mt-0.5 {{ $msg->user_id === auth()->id() ? 'justify-end' : '' }}">
                            <span class="text-[10px] text-gray-400">{{ $msg->created_at->format('g:i A') }}</span>
                            @if($msg->is_edited)
                                <span class="text-[10px] text-gray-400">(edited)</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

            @if($hasMore)
                <button wire:click="loadOlder" class="mx-auto px-4 py-1.5 text-xs text-indigo-600 bg-indigo-50 rounded-full hover:bg-indigo-100 transition-colors">
                    Load older messages
                </button>
            @endif
        </div>

        {{-- Input --}}
        <div class="p-3 border-t border-gray-200 bg-white">
            <div class="flex gap-2">
                <input wire:model="newMessage"
                       wire:keydown.enter.prevent="sendMessage"
                       wire:keydown.debounce.1500ms="startTyping"
                       type="text"
                       placeholder="Type a message..."
                       class="flex-1 px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 placeholder-gray-400">
                <button wire:click="sendMessage" class="px-4 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </button>
            </div>
        </div>
    @else
        {{-- Empty State --}}
        <div class="flex-1 flex items-center justify-center text-center p-8">
            <div>
                <div class="w-16 h-16 mx-auto bg-indigo-50 rounded-2xl flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <p class="text-gray-500 text-sm">Select a conversation to start messaging</p>
            </div>
        </div>
    @endif
</div>
