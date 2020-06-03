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
    <script src="{{ asset('js/home_page.js') }}"></script>

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
            <div><a href="#" name="shuffle_songs">Shuffle Songs</a></div>
            <div><a href="{{ url('/genres') }}">Genres</a></div>
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

<script type="text/javascript">
    var APP_URL = {!! json_encode(url('/')) !!}
</script>