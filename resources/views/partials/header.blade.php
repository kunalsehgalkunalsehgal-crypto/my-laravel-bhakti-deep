@php
    $links = [
        ['Home', route('home'), request()->routeIs('home')],
        ['Light Diya', route('home') . '#diya-wall', false],
        ['Book Pooja', route('personalized-pooja'), request()->routeIs('personalized-pooja') || request()->routeIs('lakshmi-pooja')],
        ['Book Hawan', route('hawan'), request()->routeIs('hawan')],
        ['Live Aarti', route('live'), request()->routeIs('live')],
        ['How It Works', route('home') . '#how-it-works', false],
        ['Blog', route('home') . '#blogs', false],
        ['Contact', route('home') . '#footer', false],
    ];
@endphp

<header class="site-header sticky-top">
    <nav class="navbar navbar-expand-xl">
        <div class="container site-nav">
            <a class="navbar-brand brand-wrap" href="{{ route('home') }}">
                <span class="brand-icon"><i class="bi bi-fire"></i></span>
                <span>
                    <span class="brand-title gold-text">BhaktiDeep</span>
                    <span class="brand-tagline">Har Deep Mein Bhakti</span>
                </span>
            </a>

            <button class="navbar-toggler menu-btn" type="button" data-bs-toggle="collapse" data-bs-target="#mainMenu" aria-controls="mainMenu" aria-expanded="false" aria-label="Toggle navigation">
                <i class="bi bi-list"></i>
            </button>

            <div class="collapse navbar-collapse" id="mainMenu">
                <ul class="navbar-nav mx-auto main-links">
                    @foreach ($links as [$label, $url, $active])
                        <li class="nav-item"><a class="nav-link {{ $active ? 'active' : '' }}" href="{{ $url }}">{{ $label }}</a></li>
                    @endforeach
                </ul>

                <div class="header-actions">
                    <button class="btn btn-outline-saffron"><i class="bi bi-box-arrow-in-right"></i> Login with OTP</button>
                    <a class="btn btn-saffron" href="{{ route('personalized-pooja') }}"><i class="bi bi-stars"></i> Start Bhakti Journey</a>
                </div>
            </div>
        </div>
    </nav>
</header>
