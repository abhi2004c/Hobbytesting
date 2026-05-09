<x-layouts.app :title="$group->name . ' — HobbyHub'">
    <div class="space-y-6">
        {{-- Group Header --}}
        <div class="bg-white rounded-2xl border border-gray-200/60 shadow-sm overflow-hidden">
            <div class="h-40 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 relative">
                @if($group->is_verified)
                    <div class="absolute top-4 right-4 px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-white text-xs font-medium flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Verified
                    </div>
                @endif
            </div>
            <div class="px-6 py-5">
                <div class="flex flex-col sm:flex-row items-start justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-bold text-gray-900">{{ $group->name }}</h1>
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $group->privacy->value === 'public' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                {{ ucfirst($group->privacy->value) }}
                            </span>
                        </div>
                        @if($group->location)
                            <p class="text-sm text-gray-500 mt-1 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                {{ $group->location }}
                            </p>
                        @endif
                        <p class="text-sm text-gray-600 mt-3 leading-relaxed max-w-2xl">{{ $group->description }}</p>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex items-center gap-2 shrink-0">
                        @php $isMember = $group->isMember(auth()->user()); @endphp
                        @if($isMember)
                            <form method="POST" action="{{ route('groups.leave', $group) }}">
                                @csrf @method('DELETE')
                                <button class="px-4 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-xl hover:bg-red-100 transition-colors">Leave Group</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('groups.join', $group) }}">
                                @csrf
                                <button class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-md hover:shadow-lg transition-all">
                                    {{ $group->privacy->value === 'private' ? 'Request to Join' : 'Join Group' }}
                                </button>
                            </form>
                        @endif
                        @if($group->isAdmin(auth()->user()))
                            <a href="{{ route('groups.edit', $group) }}" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Stats --}}
                <div class="mt-4 flex items-center gap-6">
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span class="font-medium">{{ $group->member_count_cache ?? $group->memberships()->count() }}</span> members
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span class="font-medium">{{ $group->events()->count() }}</span> events
                    </div>
                    @if($group->category)
                        <span class="px-2.5 py-1 text-xs bg-gray-100 text-gray-600 rounded-full">{{ $group->category->name }}</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <div x-data="{ tab: 'feed' }" class="space-y-4">
            <div class="flex gap-1 bg-white rounded-xl border border-gray-200/60 shadow-sm p-1">
                <button @click="tab = 'feed'" :class="tab === 'feed' ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:text-gray-900'" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors">Feed</button>
                <button @click="tab = 'events'" :class="tab === 'events' ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:text-gray-900'" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors">Events</button>
                <button @click="tab = 'members'" :class="tab === 'members' ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:text-gray-900'" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors">Members</button>
            </div>

            {{-- Feed Tab --}}
            <div x-show="tab === 'feed'" x-transition class="space-y-4">
                @if($isMember)
                    <div class="bg-white rounded-2xl border border-gray-200/60 shadow-sm p-4">
                        <form method="POST" action="{{ route('posts.store', $group) }}">
                            @csrf
                            <textarea name="content" rows="3" placeholder="Share something with the group..." required
                                      class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-all resize-none placeholder-gray-400"></textarea>
                            <div class="mt-3 flex justify-end">
                                <button type="submit" class="px-5 py-2 bg-indigo-600 text-white text-sm font-medium rounded-xl hover:bg-indigo-700 transition-colors">Post</button>
                            </div>
                        </form>
                    </div>
                @endif

                @forelse($group->posts()->with(['user', 'comments.user'])->latest()->take(20)->get() as $post)
                    <div class="bg-white rounded-2xl border border-gray-200/60 shadow-sm p-5">
                        <div class="flex items-start gap-3">
                            <img src="{{ $post->user->avatar_url }}" alt="" class="w-10 h-10 rounded-full object-cover">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-gray-900">{{ $post->user->name }}</span>
                                    <span class="text-xs text-gray-400">{{ $post->created_at->diffForHumans() }}</span>
                                    @if($post->is_announcement)
                                        <span class="px-2 py-0.5 text-xs bg-amber-50 text-amber-700 rounded-full font-medium">📢 Announcement</span>
                                    @endif
                                </div>
                                <p class="mt-2 text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $post->content }}</p>

                                {{-- Actions --}}
                                <div class="mt-3 flex items-center gap-4">
                                    <form method="POST" action="{{ route('posts.react', $post) }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="type" value="like">
                                        <button class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-indigo-600 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                            {{ $post->likes_count }}
                                        </button>
                                    </form>
                                    <span class="flex items-center gap-1.5 text-sm text-gray-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                        {{ $post->comments_count }} comments
                                    </span>
                                </div>

                                {{-- Comments --}}
                                @foreach($post->comments->take(3) as $comment)
                                    <div class="mt-3 pl-4 border-l-2 border-gray-100">
                                        <div class="flex items-center gap-2">
                                            <img src="{{ $comment->user->avatar_url }}" alt="" class="w-6 h-6 rounded-full">
                                            <span class="text-xs font-semibold text-gray-900">{{ $comment->user->name }}</span>
                                            <span class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="mt-1 text-sm text-gray-600">{{ $comment->content }}</p>
                                    </div>
                                @endforeach

                                {{-- Comment form --}}
                                @if($isMember)
                                    <form method="POST" action="{{ route('comments.store', $post) }}" class="mt-3 flex gap-2">
                                        @csrf
                                        <input type="text" name="content" placeholder="Write a comment..." required
                                               class="flex-1 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 placeholder-gray-400">
                                        <button type="submit" class="px-3 py-2 bg-indigo-50 text-indigo-600 text-sm font-medium rounded-lg hover:bg-indigo-100 transition-colors">Send</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-2xl border border-gray-200/60 shadow-sm p-12 text-center">
                        <p class="text-gray-400 text-sm">No posts yet. Be the first to share!</p>
                    </div>
                @endforelse
            </div>

            {{-- Events Tab --}}
            <div x-show="tab === 'events'" x-transition class="space-y-4">
                @if($group->isAdmin(auth()->user()))
                    <a href="{{ route('events.create', $group) }}"
                       class="block bg-white rounded-2xl border-2 border-dashed border-gray-200 hover:border-indigo-300 p-6 text-center transition-colors">
                        <svg class="w-8 h-8 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <p class="mt-2 text-sm font-medium text-gray-600">Create Event</p>
                    </a>
                @endif
                @livewire('events.event-list', ['groupId' => $group->id])
            </div>

            {{-- Members Tab --}}
            <div x-show="tab === 'members'" x-transition>
                @livewire('groups.member-list', ['group' => $group])
            </div>
        </div>
    </div>
</x-layouts.app>
