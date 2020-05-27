<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/utilities.js') }}"></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Raleway:300,400,600" rel="stylesheet" type="text/css">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>

<body class="mysounds-home-page">

    <div class="mysounds-home-page-div">
        @auth
            <div><a href="{{ url('/songs') }}">Songs</a></div>
            <div><a href="{{ url('/artists') }}">Artists</a></div>
            <div><a href="{{ url('/playlists') }}">Playlists</a></div>
            <div><a href="#">Shuffle Songs</a></div>
            @if ( Auth::user()->id == 1)
                <div><a href="{{ url('/utilities') }}">Utilities</a></div>
            @endif
            <div>
                <a href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                    Logout
                </a>
            </div>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        @endauth
    </div>

</body>

</html>