<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'HobbyHub') }}</title>
    <meta name="description" content="{{ $metaDescription ?? 'Community platform for hobby groups & clubs. Find your people, plan events, and share your passions.' }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-gray-50 font-sans antialiased" x-data="{ sidebarOpen: false, notifOpen: false }">

    {{-- Top Navbar --}}
    <nav class="fixed top-0 inset-x-0 z-50 h-16 bg-white/80 backdrop-blur-xl border-b border-gray-200/60 shadow-sm">
        <div class="mx-auto max-w-7xl flex items-center justify-between h-full px-4 lg:px-8">
            {{-- Left: Logo + Nav --}}
            <div class="flex items-center gap-6">
                <a href="{{ route('feed.index') }}" class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-sm">H</span>
                    </div>
                    <span class="text-xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent hidden sm:inline">HobbyHub</span>
                </a>

                <div class="hidden md:flex items-center gap-1">
                    <a href="{{ route('feed.index') }}"
                       class="px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('feed.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Feed
                    </a>
                    <a href="{{ route('groups.index') }}"
                       class="px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('groups.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Groups
                    </a>
                    <a href="{{ route('events.index') }}"
                       class="px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('events.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Events
                    </a>
                    <a href="{{ route('messages.index') }}"
                       class="px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('messages.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        Messages
                    </a>
                </div>
            </div>

            {{-- Right: Notifications + User --}}
            <div class="flex items-center gap-3">
                @livewire('notifications.notification-bell')

                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center gap-2 p-1.5 rounded-full hover:bg-gray-100 transition-colors">
                        <img src="{{ auth()->user()->avatar_url }}" alt="" class="w-8 h-8 rounded-full object-cover ring-2 ring-white shadow-sm">
                        <svg class="w-4 h-4 text-gray-400 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div x-show="open" @click.away="open = false" x-transition
                         class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                        </div>
                        <a href="{{ route('profile.show', auth()->user()) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            My Profile
                        </a>
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Settings
                        </a>
                        <div class="border-t border-gray-100 my-1"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Sign Out
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Mobile menu toggle --}}
                <button @click="sidebarOpen = !sidebarOpen" class="md:hidden p-2 rounded-lg hover:bg-gray-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>
    </nav>

    {{-- Mobile Sidebar Overlay --}}
    <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" class="fixed inset-0 bg-black/20 z-40 md:hidden"></div>

    {{-- Main Layout --}}
    <div class="pt-16 min-h-screen">
        <div class="mx-auto max-w-7xl px-4 lg:px-8 py-6 flex gap-6">

            {{-- Left Sidebar (desktop) --}}
            <aside class="hidden lg:block w-64 shrink-0">
                <div class="sticky top-22 space-y-4">
                    {{-- My Groups --}}
                    <div class="bg-white rounded-2xl border border-gray-200/60 shadow-sm p-4">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">My Groups</h3>
                        @forelse(auth()->user()->groups()->take(5)->get() as $group)
                            <a href="{{ route('groups.show', $group) }}" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition-colors group/item">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white text-xs font-bold shadow-sm">
                                    {{ strtoupper(substr($group->name, 0, 1)) }}
                                </div>
                                <span class="text-sm text-gray-700 group-hover/item:text-gray-900 truncate">{{ $group->name }}</span>
                            </a>
                        @empty
                            <p class="text-sm text-gray-400 italic">No groups yet</p>
                        @endforelse
                        <a href="{{ route('groups.index') }}" class="mt-2 flex items-center gap-2 p-2 text-sm text-indigo-600 hover:text-indigo-700 font-medium rounded-lg hover:bg-indigo-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            Discover Groups
                        </a>
                    </div>

                    {{-- Quick Links --}}
                    <div class="bg-white rounded-2xl border border-gray-200/60 shadow-sm p-4">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Quick Links</h3>
                        <a href="{{ route('groups.create') }}" class="flex items-center gap-2 p-2 text-sm text-gray-600 hover:text-gray-900 rounded-lg hover:bg-gray-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Create a Group
                        </a>
                        <a href="{{ route('events.index') }}" class="flex items-center gap-2 p-2 text-sm text-gray-600 hover:text-gray-900 rounded-lg hover:bg-gray-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            My Events
                        </a>
                    </div>
                </div>
            </aside>

            {{-- Main Content --}}
            <main class="flex-1 min-w-0">
                @if(session('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                         x-transition class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm flex items-center justify-between">
                        <span>{{ session('success') }}</span>
                        <button @click="show = false" class="text-emerald-500 hover:text-emerald-700">&times;</button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot }}
            </main>

            {{-- Right Sidebar (desktop) --}}
            <aside class="hidden xl:block w-72 shrink-0">
                <div class="sticky top-22 space-y-4">
                    {{-- Upcoming Events Widget --}}
                    <div class="bg-white rounded-2xl border border-gray-200/60 shadow-sm p-4">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Upcoming Events</h3>
                        @php
                            $upcomingEvents = \App\Models\Event::query()
                                ->where('status', 'published')
                                ->where('starts_at', '>', now())
                                ->whereIn('group_id', auth()->user()->groups()->pluck('groups.id'))
                                ->orderBy('starts_at')
                                ->take(3)
                                ->get();
                        @endphp
                        @forelse($upcomingEvents as $event)
                            <a href="{{ route('events.show', $event) }}" class="block p-3 rounded-lg hover:bg-gray-50 transition-colors border-l-3 border-indigo-400 mb-2">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $event->title }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $event->starts_at->format('M j, g:i A') }}
                                </p>
                            </a>
                        @empty
                            <p class="text-sm text-gray-400 italic">No upcoming events</p>
                        @endforelse
                    </div>

                    {{-- Suggested Groups Widget --}}
                    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl border border-indigo-100/60 p-4">
                        <h3 class="text-xs font-semibold text-indigo-600 uppercase tracking-wider mb-3">Suggested for You</h3>
                        @php
                            $suggested = \App\Models\Group::query()
                                ->public()
                                ->whereNotIn('id', auth()->user()->groups()->pluck('groups.id'))
                                ->inRandomOrder()
                                ->take(3)
                                ->get();
                        @endphp
                        @forelse($suggested as $sg)
                            <a href="{{ route('groups.show', $sg) }}" class="flex items-center gap-3 p-2 rounded-lg hover:bg-white/60 transition-colors mb-1">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white font-bold shadow-sm">
                                    {{ strtoupper(substr($sg->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $sg->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $sg->member_count_cache }} members</p>
                                </div>
                            </a>
                        @empty
                            <p class="text-sm text-gray-400 italic">None right now</p>
                        @endforelse
                    </div>
                </div>
            </aside>
        </div>
    </div>

    {{-- Toast notifications --}}
    <div x-data="{ toasts: [] }"
         @toast.window="toasts.push({ message: $event.detail.message, id: Date.now() }); setTimeout(() => toasts.shift(), 3000)"
         class="fixed bottom-6 right-6 z-50 space-y-2">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-transition class="bg-gray-900 text-white px-4 py-3 rounded-xl shadow-lg text-sm" x-text="toast.message"></div>
        </template>
    </div>

    @livewireScripts
</body>
</html>
