<x-layouts.app :title="'Create Event — HobbyHub'">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Create Event</h1>
            <p class="text-sm text-gray-500 mt-1">for <span class="font-medium text-gray-700">{{ $group->name }}</span></p>
        </div>

        <form method="POST" action="{{ route('events.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <input type="hidden" name="group_id" value="{{ $group->id }}">
            <input type="hidden" name="creator_id" value="{{ auth()->id() }}">

            <div class="bg-white rounded-2xl border border-gray-200/60 shadow-sm p-6 space-y-5">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1.5">Event Title</label>
                    <input type="text" id="title" name="title" value="{{ old('title') }}" required
                           class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-all"
                           placeholder="e.g. Monthly Hiking Meetup">
                    @error('title') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                    <textarea id="description" name="description" rows="4" required
                              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-all resize-none"
                              placeholder="Describe your event...">{{ old('description') }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1.5">Type</label>
                        <select id="type" name="type" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="in_person">📍 In Person</option>
                            <option value="online">💻 Online</option>
                            <option value="hybrid">🔀 Hybrid</option>
                        </select>
                    </div>
                    <div>
                        <label for="capacity" class="block text-sm font-medium text-gray-700 mb-1.5">Capacity</label>
                        <input type="number" id="capacity" name="capacity" value="{{ old('capacity') }}" min="1"
                               class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500"
                               placeholder="Leave empty for unlimited">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="starts_at" class="block text-sm font-medium text-gray-700 mb-1.5">Start Date & Time</label>
                        <input type="datetime-local" id="starts_at" name="starts_at" value="{{ old('starts_at') }}" required
                               class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="ends_at" class="block text-sm font-medium text-gray-700 mb-1.5">End Date & Time</label>
                        <input type="datetime-local" id="ends_at" name="ends_at" value="{{ old('ends_at') }}"
                               class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700 mb-1.5">Location</label>
                    <input type="text" id="location" name="location" value="{{ old('location') }}"
                           class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-all"
                           placeholder="e.g. Central Park, NYC or Zoom link">
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                    <select id="status" name="status" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="published">Published (visible immediately)</option>
                        <option value="draft">Draft (hidden)</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('groups.show', $group) }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 hover:text-gray-900">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg shadow-indigo-200 hover:shadow-xl transition-all text-sm">
                    Create Event
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
