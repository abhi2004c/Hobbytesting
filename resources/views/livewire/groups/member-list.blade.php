<div class="bg-white rounded-2xl border border-gray-200/60 shadow-sm p-5">
    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Members ({{ $members->total() }})</h3>

    <div class="space-y-3">
        @foreach($members as $member)
            <div class="flex items-center gap-3 p-2 rounded-xl hover:bg-gray-50 transition-colors">
                <img src="{{ $member->avatar_url }}" alt="" class="w-10 h-10 rounded-full object-cover">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ $member->name }}</p>
                    <p class="text-xs text-gray-500">{{ ucfirst($member->pivot->role ?? 'member') }} · Joined {{ $member->pivot->joined_at ? \Carbon\Carbon::parse($member->pivot->joined_at)->diffForHumans() : 'recently' }}</p>
                </div>
                @if(($member->pivot->role ?? '') === 'owner')
                    <span class="px-2 py-0.5 text-xs bg-amber-50 text-amber-700 rounded-full font-medium">👑 Owner</span>
                @elseif(($member->pivot->role ?? '') === 'admin')
                    <span class="px-2 py-0.5 text-xs bg-indigo-50 text-indigo-700 rounded-full font-medium">Admin</span>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-4">{{ $members->links() }}</div>
</div>
