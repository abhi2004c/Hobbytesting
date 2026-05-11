@props(['group'])

<a href="{{ route('groups.show', $group) }}" class="block bg-white rounded-2xl border border-gray-200/60 shadow-sm overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
    {{-- Cover --}}
    <div class="relative h-40">
        @if($group->cover_url)
            <img src="{{ $group->cover_url }}" alt="" class="w-full h-full object-cover">
        @else
            <div class="w-full h-full bg-gradient-to-br from-indigo-500 to-purple-600"></div>
        @endif
        <span class="absolute top-3 left-3 px-2.5 py-1 text-xs font-medium bg-white/90 backdrop-blur-sm rounded-full text-gray-700 shadow-sm">
            {{ $group->category?->name ?? 'General' }}
        </span>
    </div>

    {{-- Body --}}
    <div class="p-4">
        <h3 class="text-sm font-bold text-gray-900 truncate">{{ $group->name }}</h3>
        <p class="text-xs text-gray-500 mt-1 line-clamp-2 leading-relaxed">{{ $group->description }}</p>

        <div class="flex items-center justify-between mt-3">
            <div class="flex items-center gap-1.5 text-xs text-gray-400">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                {{ number_format($group->member_count_cache) }} members
            </div>
            <span class="px-2 py-0.5 text-xs rounded-full font-medium
                {{ $group->privacy->value === 'public' ? 'bg-green-50 text-green-700' : ($group->privacy->value === 'private' ? 'bg-amber-50 text-amber-700' : 'bg-gray-100 text-gray-600') }}">
                {{ ucfirst($group->privacy->value) }}
            </span>
        </div>
    </div>
</a>
