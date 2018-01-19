<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Maximized Living Auth Portal') }}</title>

    <!-- Styles -->
    <!--
    Currently fonts are loaded in with app.css - we need to look into how to load this separately.
    <link href="{{ asset('css/fonts.css') }}" rel="stylesheet">
    -->
    <link href="{{ mix('/css/app.css') }}" rel="stylesheet">

    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/favicons/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicons/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicons/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('images/favicons/manifest.json') }}">
    <link rel="mask-icon" href="{{ asset('images/favicons/safari-pinned-tab.svg') }}" color="#7890a2">
    <meta name="theme-color" content="#ffffff">
</head>
<body>
    <main id="app">
        @yield('content', 'No content section added to page.')
    </main>

    <!-- Scripts -->
    <script src="{{ mix('js/app.js') }}"></script>
    @yield('scripts')
</body>
</html>
