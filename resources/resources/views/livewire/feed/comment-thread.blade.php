<div class="space-y-3" x-data="{
    emojiPickerFor: null,
    emojis: ['😂','❤️','🔥','👏','😍','🙌','💯','😭','🤣','✨','😎','🥳'],
    insertEmoji(emoji, target) {
        const el = document.getElementById(target);
        if (!el) return;
        const pos = el.selectionStart ?? el.value.length;
        el.value = el.value.slice(0, pos) + emoji + el.value.slice(pos);
        el.dispatchEvent(new Event('input'));
        el.focus();
        this.emojiPickerFor = null;
    }
}">

    {{-- Comment Input --}}
    <div class="flex gap-2 items-end">
        <img src="{{ auth()->user()->avatar_url }}" alt="" class="w-8 h-8 rounded-full object-cover shrink-0">
        <div class="flex-1 relative">
            <div class="flex items-center bg-gray-50 border border-gray-200 rounded-2xl px-3 py-1.5 focus-within:border-indigo-400 focus-within:bg-white transition-all">
                <input
                    id="comment-input-{{ $postId }}"
                    wire:model="newComment"
                    wire:keydown.enter="addComment"
                    type="text"
                    placeholder="Write a comment…"
                    class="flex-1 bg-transparent text-sm outline-none placeholder-gray-400 py-1"
                >
                {{-- Emoji toggle --}}
                <button type="button"
                    @click="emojiPickerFor = emojiPickerFor === 'new' ? null : 'new'"
                    class="text-gray-400 hover:text-yellow-500 transition-colors text-lg leading-none ml-1 shrink-0"
                >😊</button>
                {{-- Send --}}
                <button wire:click="addComment"
                    class="ml-2 text-indigo-500 hover:text-indigo-700 transition-colors shrink-0"
                    title="Send">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                </button>
            </div>

            {{-- Emoji picker for new comment --}}
            <div x-show="emojiPickerFor === 'new'"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-90 translate-y-1"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click.outside="emojiPickerFor = null"
                 class="absolute bottom-full mb-2 left-0 bg-white border border-gray-100 rounded-2xl shadow-xl p-2 flex flex-wrap gap-1 w-56 z-50">
                <template x-for="e in emojis" :key="e">
                    <button type="button"
                        @click="insertEmoji(e, 'comment-input-{{ $postId }}')"
                        class="text-xl p-1 rounded-lg hover:bg-gray-100 hover:scale-125 transition-transform duration-100"
                        x-text="e"></button>
                </template>
            </div>
        </div>
    </div>
    @error('newComment') <p class="text-xs text-red-500 ml-10">{{ $message }}</p> @enderror

    {{-- Comments --}}
    @foreach($comments as $comment)
        <div class="flex gap-2 ml-2 comment-slide-in">
            <img src="{{ $comment->user->avatar_url }}" alt="" class="w-7 h-7 rounded-full object-cover mt-1 shrink-0">
            <div class="flex-1">
                <div class="bg-gray-50 rounded-2xl px-3 py-2 group relative">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold text-gray-900">{{ $comment->user->name }}</span>
                        <span class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-sm text-gray-700 mt-0.5 leading-relaxed">{{ $comment->content }}</p>

                    {{-- Double-tap heart (Instagram style) --}}
                    <div x-data="{ hearted: false, show: false }"
                         @dblclick="hearted = !hearted; show = true; setTimeout(() => show = false, 800)"
                         class="absolute inset-0 rounded-2xl cursor-pointer select-none">
                        <div x-show="show"
                             x-transition:enter="transition duration-200"
                             x-transition:enter-start="opacity-0 scale-50"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition duration-300"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-150"
                             class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <span class="text-3xl drop-shadow-lg" :class="hearted ? 'text-red-500' : 'text-gray-300'">❤️</span>

                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 mt-1 ml-2">
                    <button wire:click="startReply({{ $comment->id }})" class="text-xs text-gray-500 hover:text-indigo-600 font-semibold transition-colors">Reply</button>
                    @if($comment->user_id === auth()->id())
                        <button wire:click="deleteComment({{ $comment->id }})" class="text-xs text-gray-400 hover:text-red-500 transition-colors">Delete</button>
                    @endif
                </div>

                {{-- Reply form --}}
                @if($replyingTo === $comment->id)
                    <div class="flex gap-2 mt-2 ml-4 items-center">
                        <div class="flex-1 flex items-center bg-white border border-gray-200 rounded-2xl px-3 py-1.5 focus-within:border-indigo-400 transition-all relative">
                            <input
                                id="reply-input-{{ $comment->id }}"
                                wire:model="replyContent"
                                wire:keydown.enter="reply"
                                type="text"
                                placeholder="Reply to {{ $comment->user->name }}…"
                                class="flex-1 bg-transparent text-sm outline-none placeholder-gray-400 py-0.5"
                                autofocus
                            >
                            <button type="button"
                                @click="emojiPickerFor = emojiPickerFor === 'reply-{{ $comment->id }}' ? null : 'reply-{{ $comment->id }}'"
                                class="text-gray-400 hover:text-yellow-500 text-base ml-1 shrink-0">😊</button>

                            {{-- Emoji picker for reply --}}
                            <div x-show="emojiPickerFor === 'reply-{{ $comment->id }}'"
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 scale-90"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 @click.outside="emojiPickerFor = null"
                                 class="absolute bottom-full mb-2 left-0 bg-white border border-gray-100 rounded-2xl shadow-xl p-2 flex flex-wrap gap-1 w-56 z-50">
                                <template x-for="e in emojis" :key="e">
                                    <button type="button"
                                        @click="insertEmoji(e, 'reply-input-{{ $comment->id }}')"
                                        class="text-xl p-1 rounded-lg hover:bg-gray-100 hover:scale-125 transition-transform duration-100"
                                        x-text="e"></button>
                                </template>
                            </div>
                        </div>
                        <button wire:click="reply" class="text-indigo-500 hover:text-indigo-700 transition-colors" title="Send">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                        </button>
                        <button wire:click="cancelReply" class="text-xs text-gray-400 hover:text-gray-600 transition-colors">✕</button>
                    </div>
                @endif

                {{-- Nested Replies --}}
                @foreach($comment->replies as $reply)
                    <div class="flex gap-2 mt-2 ml-4 pl-3 border-l-2 border-indigo-100 comment-slide-in">
                        <img src="{{ $reply->user->avatar_url }}" alt="" class="w-6 h-6 rounded-full object-cover mt-1 shrink-0">
                        <div>
                            <div class="bg-gray-50 rounded-2xl px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-semibold text-gray-900">{{ $reply->user->name }}</span>
                                    <span class="text-xs text-gray-400">{{ $reply->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-sm text-gray-700 mt-0.5">{{ $reply->content }}</p>
                            </div>
                            @if($reply->user_id === auth()->id())
                                <button wire:click="deleteComment({{ $reply->id }})" class="text-xs text-gray-400 hover:text-red-500 ml-2 mt-1 transition-colors">Delete</button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
