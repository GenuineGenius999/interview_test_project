<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', 'Tickets') · {{ config('app.name') }}</title>
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    </head>
    <body>
        @auth
            <header class="site-header">
                <div class="container site-header__content">
                    <a class="brand" href="{{ route('tickets.index') }}">Ticket Desk</a>
                    <div class="account">
                        <div class="account__details">
                            <strong>{{ auth()->user()->name }}</strong>
                            <span>{{ auth()->user()->organization->name }} · {{ ucfirst(auth()->user()->role) }}</span>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="button button--quiet" type="submit">Log out</button>
                        </form>
                    </div>
                </div>
            </header>
        @endauth

        <main class="container page">
            @if (session('success'))
                <div class="alert alert--success" role="status">{{ session('success') }}</div>
            @endif

            @yield('content')
        </main>

        <script src="{{ asset('js/app.js') }}"></script>
    </body>
</html>
