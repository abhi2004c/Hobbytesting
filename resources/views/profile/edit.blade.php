<x-layouts.app :title="'Edit Profile — HobbyHub'">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Edit Profile</h1>
            <p class="text-sm text-gray-500 mt-1">Update your personal information</p>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
            @csrf @method('PATCH')

            <div class="bg-white rounded-2xl border border-gray-200/60 shadow-sm p-6 space-y-5">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Full Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-all">
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="bio" class="block text-sm font-medium text-gray-700 mb-1.5">Bio</label>
                    <textarea id="bio" name="bio" rows="3"
                              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-all resize-none"
                              placeholder="Tell us about yourself...">{{ old('bio', $user->bio) }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-1.5">City</label>
                        <input type="text" id="city" name="city" value="{{ old('city', $user->city) }}"
                               class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-all">
                    </div>
                    <div>
                        <label for="country" class="block text-sm font-medium text-gray-700 mb-1.5">Country (2-letter code)</label>
                        <input type="text" id="country" name="country" value="{{ old('country', $user->country) }}" maxlength="2"
                               class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-all"
                               placeholder="US">
                    </div>
                </div>

                <div>
                    <label for="website" class="block text-sm font-medium text-gray-700 mb-1.5">Website</label>
                    <input type="url" id="website" name="website" value="{{ old('website', $user->website) }}"
                           class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-all"
                           placeholder="https://yoursite.com">
                </div>

                {{-- Interests --}}
                @php $allInterests = \App\Models\Interest::orderBy('category')->get(); @endphp
                @if($allInterests->isNotEmpty())
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Interests</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($allInterests as $interest)
                                <label class="inline-flex items-center gap-1.5 px-3 py-1.5 border rounded-full text-sm cursor-pointer transition-colors
                                    {{ in_array($interest->id, old('interest_ids', $user->interests->pluck('id')->toArray())) ? 'bg-indigo-50 border-indigo-300 text-indigo-700' : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100' }}">
                                    <input type="checkbox" name="interest_ids[]" value="{{ $interest->id }}" class="hidden"
                                        {{ in_array($interest->id, old('interest_ids', $user->interests->pluck('id')->toArray())) ? 'checked' : '' }}>
                                    {{ $interest->icon ?? '🎯' }} {{ $interest->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('profile.show', auth()->user()) }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 hover:text-gray-900">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg shadow-indigo-200 hover:shadow-xl transition-all text-sm">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
