<div class="space-y-4">
    {{-- Filters --}}
    <div class="flex flex-wrap gap-3 items-center">
        <div class="flex-1 min-w-[200px]">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search groups..."
                   class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-all placeholder-gray-400">
        </div>
        <select wire:model.live="categoryId" class="px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
        </select>
        @if($search || $categoryId)
            <button wire:click="clearFilters" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">Clear</button>
        @endif
    </div>

    {{-- Grid --}}
    <div wire:loading.class="opacity-50" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($groups as $group)
            <a href="{{ route('groups.show', $group) }}" class="block bg-white rounded-2xl border border-gray-200/60 shadow-sm overflow-hidden card-hover">
                <div class="h-28 bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400 relative">
                    @if($group->is_verified)
                        <span class="absolute top-3 right-3 px-2 py-0.5 bg-white/20 backdrop-blur text-white text-xs rounded-full font-medium">✓ Verified</span>
                    @endif
                </div>
                <div class="p-4">
                    <div class="flex items-start gap-3">
                        <div class="w-12 h-12 -mt-8 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg shadow-lg border-2 border-white">
                            {{ strtoupper(substr($group->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-sm font-bold text-gray-900 truncate">{{ $group->name }}</h3>
                            <p class="text-xs text-gray-500">{{ $group->member_count_cache ?? 0 }} members · {{ ucfirst($group->privacy->value) }}</p>
                        </div>
                    </div>
                    <p class="mt-3 text-sm text-gray-600 line-clamp-2">{{ $group->description }}</p>
                    @if($group->category)
                        <span class="mt-3 inline-block px-2.5 py-1 text-xs bg-gray-100 text-gray-600 rounded-full">{{ $group->category->name }}</span>
                    @endif
                </div>
            </a>
        @empty
            <div class="col-span-full bg-white rounded-2xl border border-gray-200/60 p-12 text-center">
                <p class="text-gray-400">No groups found. Try adjusting your filters!</p>
            </div>
        @endforelse
    </div>

    {{ $groups->links() }}
</div>
