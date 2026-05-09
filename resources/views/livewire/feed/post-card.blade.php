<div class="bg-white rounded-2xl border border-gray-200/60 shadow-sm p-5"
     x-data="{
        showComments: false,
        activeReaction: '{{ $post->reactions()->where('user_id', auth()->id())->value('type') ?? '' }}',
        counts: {
            like: {{ $post->reactions()->where('type','like')->count() }},
            love: {{ $post->reactions()->where('type','love')->count() }},
            wow:  {{ $post->reactions()->where('type','wow')->count() }},
            haha: {{ $post->reactions()->where('type','haha')->count() }},
        },
        showPicker: false,
        particles: [],
        spawnParticle(emoji, el) {
            const rect = el.getBoundingClientRect();
            const id = Date.now() + Math.random();
            this.particles.push({ id, emoji, x: rect.left + rect.width/2, y: rect.top + window.scrollY });
            setTimeout(() => this.particles = this.particles.filter(p => p.id !== id), 900);
        },
        totalReactions() { return Object.values(this.counts).reduce((a,b)=>a+b,0); }
     }">
    {{-- Header --}}
    <div class="flex items-start gap-3">
        <img src="{{ $post->user->avatar_url }}" alt="" class="w-10 h-10 rounded-full object-cover">
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-sm font-semibold text-gray-900">{{ $post->user->name }}</span>
                @if($post->group)
                    <span class="text-xs text-gray-400">in</span>
                    <a href="{{ route('groups.show', $post->group) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">{{ $post->group->name }}</a>
                @endif
                <span class="text-xs text-gray-400">· {{ $post->created_at->diffForHumans() }}</span>
                @if($post->is_announcement)
                    <span class="px-2 py-0.5 text-xs bg-amber-50 text-amber-700 rounded-full font-medium">📢 Announcement</span>
                @endif
                @if($post->is_pinned)
                    <span class="px-2 py-0.5 text-xs bg-indigo-50 text-indigo-600 rounded-full font-medium">📌 Pinned</span>
                @endif
            </div>

            {{-- Content --}}
            <div class="mt-2">
                @if(strlen($post->content) > 300 && !$expanded)
                    <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ Str::limit($post->content, 300) }}</p>
                    <button wire:click="toggleExpanded" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium mt-1">Read more</button>
                @else
                    <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $post->content }}</p>
                    @if(strlen($post->content) > 300)
                        <button wire:click="toggleExpanded" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium mt-1">Show less</button>
                    @endif
                @endif
            </div>

            {{-- Media Grid --}}
            @if($post->getMedia('attachments')->isNotEmpty())
                <div class="mt-3 grid {{ $post->getMedia('attachments')->count() === 1 ? 'grid-cols-1' : 'grid-cols-2' }} gap-2 rounded-xl overflow-hidden">
                    @foreach($post->getMedia('attachments')->take(4) as $i => $media)
                        <div class="relative {{ $i === 3 && $post->getMedia('attachments')->count() > 4 ? '' : '' }}">
                            <img src="{{ $media->getUrl() }}" alt="" class="w-full h-48 object-cover rounded-lg">
                            @if($i === 3 && $post->getMedia('attachments')->count() > 4)
                                <div class="absolute inset-0 bg-black/50 rounded-lg flex items-center justify-center">
                                    <span class="text-white text-lg font-bold">+{{ $post->getMedia('attachments')->count() - 4 }}</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Poll --}}
            @if($post->poll)
                @livewire('feed.poll-widget', ['pollId' => $post->poll->id], key('poll-'.$post->poll->id))
            @endif

            {{-- Reaction Bar --}}
            <div class="mt-3 flex items-center gap-2 border-t border-gray-100 pt-3">

                {{-- Floating emoji particles (fixed position) --}}
                <template x-for="p in particles" :key="p.id">
                    <div class="emoji-particle fixed pointer-events-none z-[9999] text-xl"
                         :style="`left:${p.x}px; top:${p.y}px;`"
                         x-text="p.emoji"></div>
                </template>

                {{-- Reaction picker trigger --}}
                <div class="relative" @mouseleave="showPicker = false">
                    {{-- Main react button --}}
                    <button
                        @mouseenter="showPicker = true"
                        @click="
                            const type = activeReaction ? '' : 'like';
                            if (!type) { activeReaction = ''; counts['like'] = Math.max(0, counts['like']-1); }
                            else { if (!activeReaction) counts['like']++; activeReaction = 'like'; spawnParticle('👍', $el); }
                            $wire.toggleReaction('like');
                        "
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium transition-all duration-150 select-none"
                        :class="activeReaction ? 'text-indigo-600 bg-indigo-50 scale-105' : 'text-gray-500 hover:bg-gray-100'"
                    >
                        <span class="text-base" :class="activeReaction ? 'animate-reaction-pop' : ''">
                            <template x-if="activeReaction === 'like'">👍</template>
                            <template x-if="activeReaction === 'love'">❤️</template>
                            <template x-if="activeReaction === 'wow'">😮</template>
                            <template x-if="activeReaction === 'haha'">😂</template>
                            <template x-if="!activeReaction">👍</template>
                        </span>
                        <span x-text="activeReaction ? (activeReaction.charAt(0).toUpperCase() + activeReaction.slice(1)) : 'Like'"></span>
                    </button>

                    {{-- Hover emoji picker (Facebook-style) --}}
                    <div
                        x-show="showPicker"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 scale-75 -translate-y-2"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-75"
                        class="absolute bottom-full left-0 mb-2 flex items-center gap-1 bg-white rounded-full shadow-xl border border-gray-100 px-3 py-2 z-50"
                        @mouseenter="showPicker = true"
                    >
                        @foreach(['like' => '👍', 'love' => '❤️', 'wow' => '😮', 'haha' => '😂'] as $rtype => $remoji)
                        <button
                            @click.stop="
                                const prev = activeReaction;
                                if (prev === '{{ $rtype }}') {
                                    activeReaction = '';
                                    counts['{{ $rtype }}'] = Math.max(0, counts['{{ $rtype }}']-1);
                                } else {
                                    if (prev) counts[prev] = Math.max(0, counts[prev]-1);
                                    else counts['{{ $rtype }}']++;
                                    activeReaction = '{{ $rtype }}';
                                    spawnParticle('{{ $remoji }}', $el);
                                }
                                showPicker = false;
                                $wire.toggleReaction('{{ $rtype }}');
                            "
                            class="emoji-btn text-2xl leading-none transition-transform duration-150 hover:scale-125 active:scale-90 p-1 rounded-full hover:bg-gray-100"
                            :class="activeReaction === '{{ $rtype }}' ? 'scale-125 -translate-y-1' : ''"
                            title="{{ ucfirst($rtype) }}"
                        >{{ $remoji }}</button>
                        @endforeach
                    </div>
                </div>

                {{-- Reaction summary (stacked emojis + total) --}}
                <template x-if="totalReactions() > 0">
                    <div class="flex items-center gap-1">
                        <div class="flex -space-x-1">
                            @foreach(['like' => '👍', 'love' => '❤️', 'wow' => '😮', 'haha' => '😂'] as $rtype => $remoji)
                            <template x-if="counts['{{ $rtype }}'] > 0">
                                <span class="text-sm leading-none">{{ $remoji }}</span>
                            </template>
                            @endforeach
                        </div>
                        <span class="text-xs text-gray-400" x-text="totalReactions()"></span>
                    </div>
                </template>

                {{-- Comment toggle --}}
                <button @click="showComments = !showComments"
                        class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 ml-auto px-3 py-1.5 rounded-full hover:bg-gray-100 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    <span>{{ $post->comments_count }} comments</span>
                </button>
            </div>

            {{-- Comments Thread --}}
            <div x-show="showComments" x-transition x-cloak class="mt-3">
                @livewire('feed.comment-thread', ['postId' => $post->id], key('comments-'.$post->id))
            </div>
        </div>
    </div>
</div>
