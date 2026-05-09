<x-layouts.app :title="($user->id === auth()->id() ? 'My Profile' : $user->name) . ' — HobbyHub'">
    <div class="max-w-3xl mx-auto space-y-6">
        {{-- Profile Header --}}
        <div class="bg-white rounded-2xl border border-gray-200/60 shadow-sm overflow-hidden">
            <div class="h-32 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500"></div>
            <div class="px-6 pb-6 -mt-12">
                <div class="flex flex-col sm:flex-row items-start gap-4">
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                         class="w-24 h-24 rounded-2xl border-4 border-white shadow-lg object-cover">
                    <div class="flex-1 pt-2">
                        <div class="flex items-start justify-between flex-wrap gap-3">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h1>
                                @if($user->full_location)
                                    <p class="text-sm text-gray-500 flex items-center gap-1 mt-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        {{ $user->full_location }}
                                    </p>
                                @endif
                            </div>
                            @if($user->id === auth()->id())
                                <a href="{{ route('profile.edit') }}"
                                   class="px-4 py-2 text-sm font-medium text-indigo-600 bg-indigo-50 rounded-xl hover:bg-indigo-100 transition-colors">
                                    Edit Profile
                                </a>
                            @endif
                        </div>
                        @if($user->bio)
                            <p class="mt-3 text-sm text-gray-600 leading-relaxed">{{ $user->bio }}</p>
                        @endif
                    </div>
                </div>

                {{-- Stats --}}
                <div class="mt-6 flex items-center gap-6 text-center">
                    <div class="px-4 py-2 bg-gray-50 rounded-xl">
                        <p class="text-lg font-bold text-gray-900">{{ $user->groups->count() }}</p>
                        <p class="text-xs text-gray-500">Groups</p>
                    </div>
                    <div class="px-4 py-2 bg-gray-50 rounded-xl">
                        <p class="text-lg font-bold text-gray-900">{{ $user->posts()->count() }}</p>
                        <p class="text-xs text-gray-500">Posts</p>
                    </div>
                    @if(method_exists($user, 'attendingEvents'))
                    <div class="px-4 py-2 bg-gray-50 rounded-xl">
                        <p class="text-lg font-bold text-gray-900">{{ $user->attendingEvents->count() }}</p>
                        <p class="text-xs text-gray-500">Events</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Interests --}}
        @if($user->interests->isNotEmpty())
            <div class="bg-white rounded-2xl border border-gray-200/60 shadow-sm p-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Interests</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($user->interests as $interest)
                        <span class="px-3 py-1.5 text-sm bg-indigo-50 text-indigo-700 rounded-full font-medium">
                            {{ $interest->icon ?? '🎯' }} {{ $interest->name }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Groups --}}
        @if($user->groups->isNotEmpty())
            <div class="bg-white rounded-2xl border border-gray-200/60 shadow-sm p-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Groups</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($user->groups->take(6) as $group)
                        <a href="{{ route('groups.show', $group) }}" class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-indigo-200 hover:bg-indigo-50/30 transition-colors card-hover">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white font-bold shadow-sm">
                                {{ strtoupper(substr($group->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $group->name }}</p>
                                <p class="text-xs text-gray-500">{{ $group->pivot->role }} · Joined {{ \Carbon\Carbon::parse($group->pivot->joined_at)->diffForHumans() }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
