<x-layouts.app :title="'Create Group — HobbyHub'">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Create a New Group</h1>
            <p class="text-sm text-gray-500 mt-1">Bring people together around your favorite hobby</p>
        </div>

        <form method="POST" action="{{ route('groups.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="bg-white rounded-2xl border border-gray-200/60 shadow-sm p-6 space-y-5">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Group Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-all"
                           placeholder="e.g. Bay Area Hikers">
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                    <textarea id="description" name="description" rows="4" required
                              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-all resize-none"
                              placeholder="Tell people what your group is about...">{{ old('description') }}</textarea>
                    @error('description') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1.5">Category</label>
                        <select id="category_id" name="category_id" required
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="privacy" class="block text-sm font-medium text-gray-700 mb-1.5">Privacy</label>
                        <select id="privacy" name="privacy" required
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="public" {{ old('privacy') === 'public' ? 'selected' : '' }}>🌐 Public</option>
                            <option value="private" {{ old('privacy') === 'private' ? 'selected' : '' }}>🔒 Private</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700 mb-1.5">Location (optional)</label>
                    <input type="text" id="location" name="location" value="{{ old('location') }}"
                           class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-all"
                           placeholder="e.g. New York, NY">
                </div>

                <div>
                    <label for="cover" class="block text-sm font-medium text-gray-700 mb-1.5">Cover Image (optional)</label>
                    <input type="file" id="cover" name="cover" accept="image/jpeg,image/png,image/webp"
                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('groups.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 hover:text-gray-900 transition-colors">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg shadow-indigo-200 hover:shadow-xl transition-all text-sm">
                    Create Group
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
