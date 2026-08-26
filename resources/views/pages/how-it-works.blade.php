@extends('layouts.app')

@section('title', 'How BhaktiDeep Works - BhaktiDeep')
@section('description', 'Learn how to light a diya, join live aarti, book pooja and attend hawan on BhaktiDeep.')

@push('styles')
    <link href="{{ asset('css/how-it-works.css') }}" rel="stylesheet">
    <link href="{{ asset('css/light-diya.css') }}" rel="stylesheet">
@endpush

@section('body')
{{-- @include('partials.header') --}}

@php
    $heroBadges = [
        ['assets/small-deep.png', 'Authentic Rituals', 'Performed by verified pandits'],
        ['bi-shield-check', 'Trusted & Secure', '100% safe and transparent'],
        ['bi-flower1', 'Pure & Sacred', 'Following vedic traditions'],
        ['bi-people-fill', 'Devotion United', 'Thousands of devotees everyday'],
    ];

    $videos = [
        ['assets/diya.jpg', 'How to Light Diya'],
        ['assets/temple-hero.jpg', 'How to Join Live Aarti'],
        ['assets/lakshmi-hero.jpg', 'How to Book Pooja'],
        ['assets/havan-live.jpg', 'How to Book Hawan'],
    ];

    $serviceSteps = [
        [
            'class' => 'diya',
            'icon' => 'assets/small-deep.png',
            'title' => 'Diya Steps',
            'steps' => [
                ['bi-fire', 'Choose Diya'],
                ['bi-scroll', 'Add Sankalp'],
                ['bi-heart', 'Select Donation'],
                ['bi-credit-card', 'Pay Securely'],
                ['bi-fire', 'Diya Activated'],
            ],
        ],
        [
            'class' => 'hawan',
            'icon' => 'bi-fire',
            'title' => 'Hawan Steps',
            'steps' => [
                ['bi-fire', 'Choose Hawan'],
                ['bi-scroll', 'Add Sankalp'],
                ['bi-coin', 'Choose Dakshina'],
                ['bi-calendar-event', 'Select Slot'],
                ['bi-credit-card', 'Review & Pay'],
                ['bi-person-video3', 'Join Hawan'],
            ],
        ],
        [
            'class' => 'pooja',
            'icon' => 'bi-flower1',
            'title' => 'Pooja Steps',
            'steps' => [
                ['bi-flower1', 'Choose Pooja'],
                ['bi-scroll', 'Add Sankalp'],
                ['bi-gift', 'Choose Package'],
                ['bi-calendar-event', 'Select Slot'],
                ['bi-receipt', 'Review & Pay'],
                ['bi-person-video3', 'Join Live Pooja'],
            ],
        ],
        [
            'class' => 'aarti',
            'icon' => 'bi-bell-fill',
            'title' => 'Aarti Steps',
            'steps' => [
                ['bi-play-btn', 'Open Live Sessions'],
                ['bi-people-fill', 'Choose Free Live Aarti'],
                ['bi-clock', 'Countdown to Start'],
                ['bi-person-video3', 'Join Aarti Room'],
                ['bi-heart-fill', 'Optional Donation'],
            ],
        ],
    ];

    $sessionCards = [
        ['bi-calendar-event', 'Before Live', ['Countdown to session start', 'Confirm date & slot', 'Invite family & loved ones']],
        ['bi-cast', 'During Live', ['Audio / video live stream', 'Mantra chanting & aarti', 'Live progress & participation', 'Make donation optional']],
        ['bi-check-circle', 'After Live', ['Completion & thank you message', 'Watch replay available', 'Download receipt', 'Get blessings certificate']],
    ];

    $benefits = [
        ['bi-calendar-check', 'Easy Online Booking', 'Book diya, pooja, hawan or live aarti in just a few simple steps.'],
        ['bi-camera-video', 'Live Ritual Experience', 'Join rituals live with audio, video, mantras and peaceful temple feel.'],
        ['bi-person-heart', 'Personal Sankalp', 'Add your name, gotra and purpose before your sacred ritual begins.'],
        ['bi-receipt', 'Receipt & Blessings', 'Get receipt, replay access and blessing confirmation after the session.'],
    ];
@endphp

<main class="page-shell hiw-page">
    <div class="particles subtle">
        @for ($i = 0; $i < 22; $i++)
            <span style="left: {{ ($i * 53) % 100 }}%; animation-delay: {{ $i * 0.28 }}s;"></span>
        @endfor
    </div>
    <div class="hiw-hero-glow"></div>

    {{-- HERO --}}
    <section class="container page-section  ld-hero">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="eyebrow"><i class="bi bi-star"></i> GUIDED BHAKTI JOURNEY</span>
                <h1 class="mt-4"><span>How </span><span class="gold-text">BhaktiDeep</span><span> Works</span></h1>
                <p class="mt-4">Light a diya, join live aarti, book personalized pooja or attend sacred hawan with your family - all in one devotional experience.</p>
                 <div class="hero-buttons mt-4">
                    <a class="btn btn-saffron btn-lg" href="{{ route('light-diya') }}"><i class="bi bi-fire"></i> Light Diya</a>
                    <a class="btn btn-ghost-gold btn-lg" href="{{ route('live.sessions') }}"><i class="bi bi-bell-fill"></i> Join Live Aarti</a>
                </div>
                <div class="row g-3 mt-3 ld-stats">
                    @foreach ([['23,589', 'diyas glowing'], ['12,840', 'lit today'], ['184', 'lighting now']] as $s)
                        <div class="col-4">
                            <div class="glass rounded-3 px-3 py-2 text-center">
                                <div class="gold-text fw-bold" style="font-family:'Cinzel',serif;font-size:18px;">{{ $s[0] }}</div>
                                <div class="ld-stat-label">{{ $s[1] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col-lg-6">
                <div class="ld-hero-img-wrap">
                    <div class="ld-img-glow"></div>
                    <div class="ld-img-card glass">
                        <img src="{{ asset('assets/temple-background-image.jpeg') }}" alt="How BhaktiDeep works">
                        <div class="ld-img-overlay"></div>
                        <div class="ld-img-badge">
                            <div class="d-flex align-items-center gap-2">
                                <span class="ld-pulse-dot"></span>
                                <span>Live diya • animated flame</span>
                            </div>
                            <span class="ld-akhand-tag">Akhand</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- GUIDE VIDEOS --}}
    <section class=" hiw-video-section container page-section">
        <div class="container">
            <div class="hiw-section-title">
                <h2>Guide Videos</h2>
                <span class="hiw-divider"></span>
            </div>
            <div class="hiw-video-grid">
                @foreach ($videos as $video)
                    <button class="hiw-video-card" type="button" aria-label="{{ $video[1] }}">
                        <img src="{{ asset($video[0]) }}" alt="{{ $video[1] }}">
                        <span class="hiw-play"><i class="bi bi-play-fill"></i></span>
                        <strong>{{ $video[1] }}</strong>
                    </button>
                @endforeach
            </div>
        </div>
    </section>

    {{-- HOW IT WORKS - SIMPLE STEPS --}}
    <section class="container page-section">
        <div class="container">
            <div class="hiw-section-title hiw-lined">
                <h2>How It Works - Simple Steps</h2>
                <span class="hiw-divider"></span>
            </div>
            <div class="hiw-service-grid">
                @foreach ($serviceSteps as $service)
                    <article class="hiw-card hiw-flow-card {{ $service['class'] }}">
                        <h3>
                            @if (str_starts_with($service['icon'], 'assets/'))
                                <img src="{{ asset($service['icon']) }}" alt="">
                            @else
                                <i class="bi {{ $service['icon'] }}"></i>
                            @endif
                            {{ $service['title'] }}
                        </h3>
                        <div class="hiw-step-flow">
                            @foreach ($service['steps'] as $step)
                                <div class="hiw-step-item">
                                    <span><i class="bi {{ $step[0] }}"></i></span>
                                    <small>{{ $step[1] }}</small>
                                </div>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- WHAT HAPPENS INSIDE THE LIVE SESSION --}}
    <section class="container page-section">
        <div class="container">
            <div class="hiw-section-title hiw-lined">
                <h2>What Happens Inside the Live Session</h2>
                <span class="hiw-divider"></span>
            </div>
            <div class="hiw-session-grid">
                @foreach ($sessionCards as $card)
                    <article class="hiw-card hiw-session-card">
                        <div class="hiw-session-icon"><i class="bi {{ $card[0] }}"></i></div>
                        <div>
                            <h3>{{ $card[1] }}</h3>
                            <ul>
                                @foreach ($card[2] as $point)
                                    <li><i class="bi bi-check-circle"></i>{{ $point }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- WHY DEVOTEES CHOOSE BHAKTIDEEP --}}
    <section class="container page-section hiw-benefits-section">
        <div class="container">
            <div class="hiw-section-title hiw-lined">
                <h2>Why Devotees Choose BhaktiDeep</h2>
                <span class="hiw-divider"></span>
            </div>
            <div class="hiw-benefits-grid">
                @foreach ($benefits as $benefit)
                    <article class="hiw-card hiw-benefit-card">
                        <div class="benefit-icon-wrapper">
                            <div class="benefit-icon">
                                <i class="bi {{ $benefit[0] }}"></i>
                            </div>
                        </div>
                        <h3>{{ $benefit[1] }}</h3>
                        <p>{{ $benefit[2] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
    {{-- FINAL CTA --}}
    <section class="container page-section">
        <div class="hiw-final-cta">
            <div>
                <h2>Begin Your Spiritual Journey Today</h2>
                <p>Light a Diya. Join the prayers. Receive divine blessings.</p>
            </div>
            <div class="hiw-actions justify-content-center">
                <a class="btn btn-saffron btn-lg" href="{{ route('light-diya') }}">Start Bhakti Journey</a>
                <a class="btn btn-ghost-gold btn-lg" href="{{ route('live.sessions') }}">Join Live Aarti</a>
            </div>
        </div>
    </section>
</main>

{{-- @include('partials.footer') --}}
@endsection
