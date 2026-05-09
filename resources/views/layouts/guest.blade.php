<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'HobbyHub') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gradient-to-br from-indigo-50 via-white to-purple-50 font-sans antialiased">
    <div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
        {{-- Logo --}}
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
            <a href="/" class="inline-flex items-center gap-3 mb-6">
                <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-200">
                    <span class="text-white font-extrabold text-lg">H</span>
                </div>
                <span class="text-2xl font-extrabold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">HobbyHub</span>
            </a>
        </div>

        {{-- Card --}}
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white/80 backdrop-blur-xl shadow-xl shadow-gray-200/50 rounded-3xl px-8 py-10 sm:px-10 border border-gray-100">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>
