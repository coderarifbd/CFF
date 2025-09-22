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

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="relative isolate overflow-hidden bg-gray-900 min-h-screen py-16 sm:py-24 lg:py-32">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl flex min-h-screen items-center justify-center">
                    <div class="w-full max-w-xl text-center">
                        <a href="/" class="inline-flex items-center gap-2 text-indigo-400 mb-6">
                            <x-application-logo class="h-7 w-7 fill-current text-indigo-400" />
                            <span class="text-sm font-semibold tracking-wide">CFF</span>
                        </a>
                        <h1 class="text-4xl font-semibold tracking-tight text-white">Sign in to CFF</h1>
                        <p class="mt-4 text-lg text-gray-300">Manage members, deposits, investments and expenses in one place.</p>
                        <div class="mt-6 w-full max-w-md mx-auto text-left">
                            <div class="rounded-2xl bg-white/5 p-6 ring-1 ring-white/10">
                                {{ $slot }}
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>
            <div aria-hidden="true" class="absolute top-0 left-1/2 -z-10 -translate-x-1/2 blur-3xl xl:-top-6">
                <div class="aspect-[1155/678] w-[72rem] bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-30" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"></div>
            </div>
        </div>
    </body>
</html>
