<x-layouts.app :title="'Groups — HobbyHub'">
    <div class="space-y-6">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Discover Groups</h1>
                <p class="text-sm text-gray-500 mt-1">Find your community and start connecting</p>
            </div>
            <a href="{{ route('groups.create') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg shadow-indigo-200 hover:shadow-xl transition-all text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create Group
            </a>
        </div>

        @livewire('groups.group-list')
    </div>
</x-layouts.app>
