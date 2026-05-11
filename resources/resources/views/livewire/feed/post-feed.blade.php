<div class="space-y-4" wire:poll.30s>
    {{-- Filter Bar --}}
    <div class="flex gap-1 bg-white rounded-xl border border-gray-200/60 shadow-sm p-1">
        <button wire:click="$set('filter', 'latest')" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ $filter === 'latest' ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:text-gray-900' }}">Latest</button>
        <button wire:click="$set('filter', 'popular')" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ $filter === 'popular' ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:text-gray-900' }}">Popular</button>
        <button wire:click="$set('filter', 'announcements')" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ $filter === 'announcements' ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:text-gray-900' }}">📢 Announcements</button>
    </div>

    {{-- Posts --}}
    <div wire:loading.class="opacity-50" class="space-y-4">
        @forelse($posts as $post)
            @livewire('feed.post-card', ['post' => $post], key('post-'.$post->id))
        @empty
            <div class="bg-white rounded-2xl border border-gray-200/60 shadow-sm p-12 text-center">
                <div class="w-14 h-14 mx-auto bg-indigo-50 rounded-2xl flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                </div>
                <p class="text-gray-500 text-sm">No posts to show yet.</p>
            </div>
        @endforelse
    </div>

    {{ $posts->links() }}
</div>
