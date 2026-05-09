<div class="flex flex-col h-full">
    {{-- Header --}}
    <div class="p-4 border-b border-gray-200 flex justify-between items-center shrink-0">
        <h2 class="text-lg font-bold text-gray-900">Messages</h2>
        <button wire:click="$set('showNewModal', true)"
                class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-colors"
                title="New Message">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
        </button>
    </div>

    {{-- Conversation List --}}
    <div class="flex-1 overflow-y-auto p-2 space-y-1">
        @forelse($conversations as $conv)
            @php $other = $conv->type === 'direct' ? $conv->users->where('id', '!=', auth()->id())->first() : null; @endphp
            <div wire:click="$dispatch('open-conversation', { id: {{ $conv->id }} })"
                 class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 cursor-pointer transition-colors">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white font-bold shrink-0">
                    {{ strtoupper(substr($other?->name ?? $conv->name ?? 'G', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">
                        {{ $conv->type === 'direct' ? ($other?->name ?? 'Unknown') : $conv->name }}
                    </p>
                    @if($conv->lastMessage)
                        <p class="text-xs text-gray-500 truncate">{{ $conv->lastMessage->content }}</p>
                    @endif
                </div>
                @php $unread = $conv->getUnreadCountFor(auth()->user()); @endphp
                @if($unread > 0)
                    <span class="w-5 h-5 bg-indigo-600 text-white text-xs font-bold rounded-full flex items-center justify-center">{{ $unread > 9 ? '9+' : $unread }}</span>
                @endif
            </div>
        @empty
            <div class="p-8 text-center">
                <p class="text-sm text-gray-400">No conversations yet</p>
                <p class="text-xs text-gray-400 mt-1">Click + to start one</p>
            </div>
        @endforelse
    </div>

    {{-- New Message Modal --}}
    @if($showNewModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" wire:click.self="$set('showNewModal', false)">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm mx-4 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-gray-900">New Message</h3>
                    <button wire:click="$set('showNewModal', false)" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>

                <input wire:model.live="search"
                       type="text"
                       placeholder="Search people..."
                       autofocus
                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 placeholder-gray-400 mb-3">

                <div class="space-y-1 max-h-60 overflow-y-auto">
                    @forelse($users as $user)
                        <button wire:click="startConversation({{ $user->id }})"
                                class="flex items-center gap-3 w-full p-2.5 rounded-xl hover:bg-indigo-50 transition-colors text-left">
                            <img src="{{ $user->avatar_url }}" alt="" class="w-9 h-9 rounded-full object-cover">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                            </div>
                        </button>
                    @empty
                        @if(strlen($search) >= 1)
                            <p class="text-sm text-gray-400 text-center py-4">No users found</p>
                        @else
                            <p class="text-sm text-gray-400 text-center py-4">Type a name to search</p>
                        @endif
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>
