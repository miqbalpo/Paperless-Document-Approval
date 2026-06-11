<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-gray-50">

<div class="flex min-h-screen">

    <!-- Overlay -->
    <div id="overlay"
        class="fixed inset-0 bg-black/50 z-40 lg:hidden hidden">
    </div>

    @include('layouts.sidebar')

    <main class="flex-1 lg:ml-64 min-w-0 p-6 bg-gray-50">
        <div class="lg:hidden flex items-center gap-3 mb-4 -mx-6 -mt-6 px-4 py-3 bg-white border-b border-gray-200 sticky top-0 z-30">

            <!-- Open Sidebar Button -->
            <button id="openSidebar"
                class="p-2 rounded-md text-gray-600 hover:bg-gray-100 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <span class="font-semibold text-gray-700 text-sm tracking-wide">
                Approval Document System
            </span>
        </div>

        {{ $slot }}
    </main>
</div>

</body>
</html>
