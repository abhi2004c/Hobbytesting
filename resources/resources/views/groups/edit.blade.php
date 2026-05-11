<x-layouts.app :title="'Edit ' . $group->name . ' — HobbyHub'">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Edit Group</h1>
            <p class="text-sm text-gray-500 mt-1">Update your group's settings</p>
        </div>

        <form method="POST" action="{{ route('groups.update', $group) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf @method('PATCH')

            <div class="bg-white rounded-2xl border border-gray-200/60 shadow-sm p-6 space-y-5">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Group Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $group->name) }}" required
                           class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-all">
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                    <textarea id="description" name="description" rows="4" required
                              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-all resize-none">{{ old('description', $group->description) }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1.5">Category</label>
                        <select id="category_id" name="category_id" required
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $group->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="privacy" class="block text-sm font-medium text-gray-700 mb-1.5">Privacy</label>
                        <select id="privacy" name="privacy" required
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="public" {{ old('privacy', $group->privacy->value) === 'public' ? 'selected' : '' }}>🌐 Public</option>
                            <option value="private" {{ old('privacy', $group->privacy->value) === 'private' ? 'selected' : '' }}>🔒 Private</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700 mb-1.5">Location</label>
                    <input type="text" id="location" name="location" value="{{ old('location', $group->location) }}"
                           class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-all">
                </div>

                <div>
                    <label for="cover" class="block text-sm font-medium text-gray-700 mb-1.5">Cover Image</label>
                    <input type="file" id="cover" name="cover" accept="image/jpeg,image/png,image/webp"
                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                </div>
            </div>

            {{-- Danger Zone --}}
            <div class="bg-red-50/50 border border-red-200 rounded-2xl p-6">
                <h3 class="text-sm font-semibold text-red-700 mb-2">Danger Zone</h3>
                <p class="text-sm text-red-600 mb-4">Deleting a group is permanent and cannot be undone.</p>
                <button type="button" onclick="document.getElementById('delete-form').submit()"
                        class="px-4 py-2 text-sm font-medium text-red-600 bg-white border border-red-200 rounded-xl hover:bg-red-50 transition-colors">
                    Delete Group
                </button>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('groups.show', $group) }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 hover:text-gray-900">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg shadow-indigo-200 hover:shadow-xl transition-all text-sm">
                    Save Changes
                </button>
            </div>
        </form>

        <form id="delete-form" method="POST" action="{{ route('groups.destroy', $group) }}" class="hidden">
            @csrf @method('DELETE')
        </form>
    </div>
</x-layouts.app>
