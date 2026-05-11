<x-layouts.app :title="'Activity Feed — HobbyHub'">
    <div class="space-y-6 max-w-2xl mx-auto">
        <div class="mb-2">
            <h1 class="text-2xl font-bold text-gray-900">Activity Feed</h1>
            <p class="text-sm text-gray-500 mt-1">Latest updates from your groups</p>
        </div>

@php
    $defaultGroupId = auth()->user()->memberships()->where('status', 'active')->value('group_id');
@endphp

        @livewire('feed.create-post', ['groupId' => $defaultGroupId])

        @livewire('feed.post-feed')
    </div>
</x-layouts.app>
