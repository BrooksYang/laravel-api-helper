<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="{{ asset('vendor/api_doc/css/bulma.css') }}" rel="stylesheet">

    {{-- Css --}}
    @yield('css')

</head>
<body>
<div class="container is-fluid">
    <div style="position: fixed; top: 10px; height: 30px;">
        {{-- Nav Bar --}}
        @include('api_doc::layouts.includes.nav_bar')
    </div>

    <div class="columns" style="position: relative; top: 80px;">
        {{-- Menu --}}
        <div class="column is-2" style="position: fixed; height: 85%; overflow: scroll;">
            @include('api_doc::layouts.includes.menu')
        </div>

        <div class="column is-offset-2 is-10">
            @yield('content')
        </div>
    </div>
</div>

{{-- Js --}}
@yield('Js')

</body>
</html>
