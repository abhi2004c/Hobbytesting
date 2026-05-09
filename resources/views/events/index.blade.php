<x-layouts.app :title="'Events — HobbyHub'">
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Upcoming Events</h1>
            <p class="text-sm text-gray-500 mt-1">Events from your groups and public communities</p>
        </div>
        @livewire('events.event-list')
    </div>
</x-layouts.app>
