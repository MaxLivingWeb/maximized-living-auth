<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-client="error">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Maximized Living Store') }}</title>

    <!-- Styles -->
    <!--
    Currently fonts are loaded in with app.css - we need to look into how to load this separately.
    <link href="{{ asset('css/fonts.css') }}" rel="stylesheet">
    -->
    <link href="{{ mix('/css/app.css') }}" rel="stylesheet">
</head>
<body>
    @include('client_store/components/header')

    <main id="app">
        @yield('content')
    </main>

    @include('client_store/components/footer')

    <!-- Scripts -->
    <script src="{{ mix('js/app.js') }}"></script>
    @yield('scripts')
</body>
</html>
