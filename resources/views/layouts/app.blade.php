<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title')@yield('title') | {{ config('community.name') }}@else{{ config('community.name') }}@endif</title>
    <meta name="description" content="@yield('description', config('community.description'))">
    <meta property="og:title" content="@yield('title', config('community.name'))">
    <meta property="og:description" content="@yield('description', config('community.description'))">
    <meta property="og:type" content="website">
    <meta name="theme-color" content="#04060f">
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,400;0,500;0,600;1,500&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/rp.css') }}">
    <link rel="stylesheet" href="{{ asset('css/rp-ext.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <a class="skip-link" href="#konten">Langsung ke Konten</a>

    <div id="top" @class(['is-home', 'is-sub' => ! request()->routeIs('home')])>
        <x-navbar />

        <main id="konten">
            @yield('content')
        </main>

        <x-footer />
        <x-contact-modal />
    </div>

    <x-chat-widget />

    <script src="{{ asset('js/site.js') }}" defer></script>
    <script src="{{ asset('js/chat-position.js') }}" defer></script>
    <script src="{{ asset('js/chat.js') }}" defer></script>
</body>
</html>
