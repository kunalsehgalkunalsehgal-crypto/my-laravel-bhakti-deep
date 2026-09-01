<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'BhaktiDeep - AI Powered Virtual Temple')</title>
    <meta name="description" content="@yield('description', 'Light a virtual diya, book a personalized pooja, or join a live hawan from home.')">
<link rel="icon" type="image/x-icon" href="https://i.pinimg.com/736x/c3/30/ae/c330aeba4ebb8971936067cd0b077c70.jpg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('css/header.css') }}" rel="stylesheet">
    <link href="{{ asset('css/home.css') }}" rel="stylesheet">
    <link href="{{ asset('css/pages.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
@include('partials.header')

    @yield('body')


    @include('partials.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @vite(['resources/js/app.js'])
    <script src="{{ asset('js/bhaktideep.js') }}"></script>
    @stack('scripts')
</body>
</html>
