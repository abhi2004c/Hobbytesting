<x-layouts.guest :title="'Verify Email — HobbyHub'">
    <h2 class="text-2xl font-bold text-gray-900 text-center mb-2">Verify your email</h2>
    <p class="text-sm text-gray-500 text-center mb-8">
        Thanks for signing up! Please check your inbox for a verification link before continuing.
    </p>

    @if(session('success'))
        <p class="mb-6 text-sm text-green-600 text-center bg-green-50 rounded-xl py-3 px-4">{{ session('success') }}</p>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="w-full py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg shadow-indigo-200 hover:shadow-xl hover:shadow-indigo-300 hover:from-indigo-700 hover:to-purple-700 transition-all text-sm">
            Resend verification email
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-4">
        @csrf
        <button type="submit" class="w-full py-3 border border-gray-200 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
            Log out
        </button>
    </form>
</x-layouts.guest>
