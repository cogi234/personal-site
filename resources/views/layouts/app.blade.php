<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name') }}</title>

        <!-- Links -->
        <link rel="indieauth-metadata" href="{{ route('indieauth.metadata') }}">
        <link rel="authorization_endpoint" href="{{ route('indieauth') }}">
        <link rel="token_endpoint" href="{{ route('indieauth') }}">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body>
        <header>
            @php
                $navLinks = [
                    'Homepage' => 'homepage',
                    'Blog' => 'blog',
                    'Notes' => 'notes',
                    'Tags' => 'tags',
                    'About' => 'about'
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
                <div>
                    @auth
                    <form method="POST" action="{{ route('logout') }}">
                        <button type="submit" style="position: relative; top: 50%; transform:translateY(-50%); -ms-transform:translateY(-50%)">Logout</button>
                    </form>
                    @endauth
                </div>
            </nav>
        </header>

        <main>
            {{ $slot }}
        </main>

        <footer>
            <div class="h-card my-h-card">
                <h2>About me</h2>
                <img class="u-photo" src="/images/avatar.jpg">
                <a class="p-name u-url" href="{{ route('homepage') }}">
                    <span class="p-given-name">Colin</span>
                    <span class="p-family-name">Bougie</span>
                </a>
                <p class="p-note">
                    I'm a computer science student from <span class="p-region">Quebec</span>, making a personal website for fun and to share my interests.
                </p>
                <p>
                    I'm <span class="p-nickname">cogi234</span> in most games and on most platforms where I'm active.
                </p>
                <p>
                    <strong>Pronouns</strong>: <span class="p-pronouns">he/him</span>
                </p>
                <p class="social-links">
                    <a href="https://www.tumblr.com/blog/cogi234" rel="me" target="_blank"><img src="images/icons/tumblr-blue.png"></a>
                    <a href="https://github.com/cogi234" rel="me" target="_blank"><img class="dark" src="images/icons/github-white.png"><img class="light" src="images/icons/github-gray.png"></a>
                    <a href="mailto:colinbougie@gmail.com" rel="me">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                            <path d="M1.5 8.67v8.58a3 3 0 0 0 3 3h15a3 3 0 0 0 3-3V8.67l-8.928 5.493a3 3 0 0 1-3.144 0L1.5 8.67Z" />
                            <path d="M22.5 6.908V6.75a3 3 0 0 0-3-3h-15a3 3 0 0 0-3 3v.158l9.714 5.978a1.5 1.5 0 0 0 1.572 0L22.5 6.908Z" />
                        </svg>
                    </a>
                      
                </p>
            </div>
            <!-- Badges -->
            <div class="badges">
                <a href="https://indieweb.org/"><img src="/images/buttons/indiewebcamp-button.png" class="button"></a>
                <a href="https://microformats.org/"><img src="/images/buttons/microformats-button.png" class="button"></a>
            </div>
        </footer>
    </body>

</html>