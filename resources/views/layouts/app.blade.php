<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name') }}</title>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="min-h-screen  bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
        <header class="sticky top-0 px-4 sm:px-6 lg:px-8 bg-white dark:bg-gray-700 border-gray-100 dark:border-gray-600">
            <!-- Navigation menu -->
            <nav class="mx-auto max-w-5xl">
                <div class="flex justify-between h-16">
                    <!-- Left -->
                    <div class="flex">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <a href="{{ route('homepage') }}">
                                <x-application-logo class="block h-10 w-auto fill-current text-gray-800 dark:text-gray-200" />
                            </a>
                        </div>

                        <!-- Navigation -->
                        <div class="space-x-8 sm:-my-px sm:ms-10 sm:flex">
                            <x-nav-link :href="route('homepage')" :active="request()->routeIs('homepage')">
                                Homepage
                            </x-nav-link>
                            <x-nav-link :href="route('test')" :active="request()->routeIs('test')">
                                Blog
                            </x-nav-link>
                            <x-nav-link :href="route('test')" :active="request()->routeIs('test')">
                                Notes
                            </x-nav-link>
                        </div>
                    </div>
    
                    <!-- Right -->
                    <div class="flex">
                        <x-button-link :href="route('test')">
                            Login
                        </x-button-link>
                    </div>
                </div>
            </nav>
        </header>

        <main class="max-w-7xl mx-auto bg-gray-200 dark:bg-gray-800"
            style="min-height: calc(100vh - 4rem)">
            {{ $slot }}
        </main>

        <footer>

        </footer>
    </body>

</html>