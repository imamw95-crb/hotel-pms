<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>@yield('title', 'Welcome') - {{ \App\Models\HotelSetting::first()->hotel_name }}</title>
    <link href="{{ asset('assets/fontawesome/css/all.min.css') }}" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body {
            width: 100%;
            height: 100%;
            min-height: 100vh;
            min-height: -webkit-fill-available;
            overflow: hidden;
            background: #000;
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
        }
        /* Android TV fix: pastikan konten penuh */
        .tv-fullscreen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            height: -webkit-fill-available;
        }
    </style>
</head>
<body>
    @yield('content')
    @stack('scripts')
</body>
</html>
