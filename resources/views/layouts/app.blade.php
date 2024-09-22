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

    <body>
        <header>
            @php
                $navLinks = [
                    'Homepage' => 'homepage',
                    'Blog' => 'blog',
                    'Notes' => 'notes'
                ];
            @endphp 
            <!-- Navigation menu -->
            <nav>
                <!-- Left -->
                <div>
                    <!-- Logo -->
                    <div class="main-logo-container">
                        <a href="{{ route('homepage') }}">
                            <x-application-logo class="main-logo" />
                        </a>
                    </div>

                    <!-- Navigation -->
                    <div class="navigation">
                        @foreach ($navLinks as $name => $route)
                            <a class="navlink {{ request()->routeIs($route) ? 'active' : '' }}" href="{{ route($route) }}">
                                {{ $name }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Right -->
                <div></div>
            </nav>
        </header>

        <main>
            {{ $slot }}
        </main>

        <footer>

        </footer>
    </body>

</html>