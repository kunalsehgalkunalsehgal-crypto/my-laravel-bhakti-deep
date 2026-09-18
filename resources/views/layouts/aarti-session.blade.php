<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        @yield('title', 'Aarti Darshan - BhaktiDeep')
    </title>

    <meta
        name="description"
        content="@yield('description', 'Experience divine Aarti Darshan with BhaktiDeep.')"
    >


    {{-- GOOGLE FONT --}}

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >


    {{-- BOOTSTRAP ICONS ONLY --}}

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >


    {{-- ONLY AARTI SESSION CSS --}}

    <link
        rel="stylesheet"
        href="{{ asset('css/aarti-session.css') }}"
    >


    @stack('styles')

</head>


<body class="aarti-session-body">

    @yield('body')


    @stack('scripts')

</body>

</html>