@extends('layouts.app')

@section('title', 'Live Sessions - BhaktiDeep')
@section('description', 'Choose free live aarti, booked pooja, or personalized hawan live sessions with BhaktiDeep.')

@push('styles')
<link href="{{ asset('css/light-diya.css') }}" rel="stylesheet">
<style>
    .live-sessions-hero {
        padding-top: clamp(36px, 7vw, 86px);
    }

    .live-sessions-hero h1 {
        max-width: 780px;
        font-size: clamp(32px, 6vw, 64px);
    }

    .live-sessions-hero p {
        max-width: 820px;
    }

    .live-note {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 18px;
        padding: 10px 14px;
        border: 1px solid rgba(199, 141, 34, .26);
        border-radius: 999px;
        background: rgba(251, 244, 223, .08);
        color: var(--cream);
        font-size: 13px;
    }

    .live-session-choice {
        min-height: 100%;
        padding: 24px;
        display: flex;
        flex-direction: column;
        border: 1px solid rgba(199, 141, 34, .22);
        transition: transform .2s ease, border-color .2s ease, background .2s ease;
    }

    .live-session-choice:hover {
        transform: translateY(-2px);
        border-color: var(--gold);
        background: rgba(199, 141, 34, .11);
    }

    .live-session-choice .choice-icon {
        width: 46px;
        height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: rgba(199, 141, 34, .18);
        color: var(--gold);
        font-size: 22px;
    }

    .choice-status {
        width: fit-content;
        margin-top: 18px;
        padding: 5px 11px;
        border-radius: 999px;
        background: rgba(255, 122, 0, .14);
        color: var(--gold);
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .live-session-choice h3 {
        margin: 16px 0 6px;
    }

    .live-session-choice p {
        flex: 1;
        margin: 14px 0 20px;
        color: var(--muted);
    }

    .live-info-list {
        display: grid;
        gap: 12px;
    }

    .live-info-row {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(110px, .7fr) minmax(110px, .8fr);
        gap: 12px;
        align-items: center;
        padding: 14px 0;
        border-bottom: 1px solid rgba(199, 141, 34, .18);
    }

    .live-info-row:last-child {
        border-bottom: 0;
    }

    .live-info-row strong,
    .live-info-row span,
    .live-info-row em {
        min-width: 0;
    }

    .live-info-row em {
        color: var(--gold);
        font-style: normal;
        font-size: 13px;
    }

    .live-steps {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .live-step {
        padding: 16px;
        text-align: center;
    }

    .live-step span {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: var(--gold);
        color: #1f1206;
        font-weight: 800;
    }

    .live-step strong {
        display: block;
        margin-top: 10px;
        font-size: 14px;
    }

    @media (max-width: 991.98px) {
        .live-steps {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 575.98px) {
        .live-note {
            align-items: flex-start;
            border-radius: 14px;
        }

        .live-info-row {
            grid-template-columns: 1fr;
            gap: 4px;
        }

        .live-steps {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('body')
{{-- @include('partials.header') --}}

@php
    $poojaBookings = $poojaBookings ?? collect();
    $activePaidPoojaBooking = $poojaBookings->first();
    $activePoojaMeta = $activePaidPoojaBooking?->admin_note ? (json_decode($activePaidPoojaBooking->admin_note, true) ?: []) : [];

    $hawanBookings = $hawanBookings ?? collect();
    $activePaidHawanBooking = $hawanBookings->first();
    $activeHawanMeta = $activePaidHawanBooking?->admin_note ? (json_decode($activePaidHawanBooking->admin_note, true) ?: []) : [];

    $poojaBookingUrl = \Illuminate\Support\Facades\Route::has('pooja.booking') ? route('pooja.booking') : '#';
    $hawanBookingUrl = \Illuminate\Support\Facades\Route::has('hawan.booking') ? route('hawan.booking') : '#';
    $poojaSessionsUrl = \Illuminate\Support\Facades\Route::has('live.sessions.pooja') ? route('live.sessions.pooja') : $poojaBookingUrl;
    $hawanSessionsUrl = \Illuminate\Support\Facades\Route::has('live.sessions.hawan') ? route('live.sessions.hawan') : $hawanBookingUrl;

    $liveCards = [
        [
            'icon' => 'bi-broadcast',
            'title' => 'Free Live Aarti',
            'service' => 'Evening Lakshmi Aarti',
            'status' => 'Free to Join',
            'description' => 'Join live aarti with devotional audio, diya visuals, family participation and optional donation.',
            'button' => 'Join Live Aarti',
            'url' => route('aarti.index'),
            'buttonClass' => 'btn-gold',
        ],
        [
            'icon' => 'bi-stars',
            'title' => 'Live Pooja',
            'service' => $activePaidPoojaBooking ? ($activePoojaMeta['pooja_name'] ?? 'Personalized Pooja Session') : 'Personalized Pooja Session',
            'status' => $poojaBookings->count() ? $poojaBookings->count().' Booked' : 'Booking Required',
            'description' => 'Join your booked pooja with sankalp, mantra chanting, aarti, family access and receipt.',
            'button' => $activePaidPoojaBooking ? 'View Pooja Sessions' : 'Book Pooja',
            'url' => $activePaidPoojaBooking ? $poojaSessionsUrl : $poojaBookingUrl,
            'buttonClass' => $activePaidPoojaBooking ? 'btn-gold' : 'btn-saffron',
        ],
        [
            'icon' => 'bi-fire',
            'title' => 'Live Hawan',
            'service' => $activePaidHawanBooking ? ($activeHawanMeta['hawan_name'] ?? 'Personalized Hawan Session') : 'Personalized Hawan Session',
            'status' => $hawanBookings->count() ? $hawanBookings->count().' Booked' : 'Booking Required',
            'description' => 'Join your booked hawan with personalized sankalp, sacred fire visuals, mantra japa, ahuti counter and certificate.',
            'button' => $activePaidHawanBooking ? 'View Hawan Sessions' : 'Book Hawan',
            'url' => $activePaidHawanBooking ? $hawanSessionsUrl : $hawanBookingUrl,
            'buttonClass' => $activePaidHawanBooking ? 'btn-gold' : 'btn-saffron',
        ],
    ];

    $schedule = [
        ['Morning Aarti', '6:00 AM', 'Free'],
        ['Evening Aarti', '7:00 PM', 'Free'],
        ['Booked Pooja Sessions', 'As per selected slot', 'Booking Required'],
        ['Booked Hawan Sessions', 'As per selected slot', 'Booking Required'],
    ];

    $steps = [
        'Choose Live Session',
        'Join Free Aarti or Book Pooja/Hawan',
        'Add Sankalp',
        'Invite Family',
        'Attend Live Session',
        'Download Receipt / Replay / Certificate',
    ];
@endphp

<main class="page-shell live-sessions-page">
    <section class="container ld-hero">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="eyebrow"><i class="bi bi-broadcast"></i> LIVE DARSHAN</span>
                <h1 class="mt-4">
                    <span>Choose Your <span class="gold-text">Live Spiritual Session</span></span>
                </h1>
                <p class="mt-4">
                   Attend free live aarti, join your booked pooja, or enter your personalized hawan session with family.
                </p>
                {{-- <div class="hero-buttons mt-4">
                    <a href="#choose" class="btn btn-saffron btn-lg rounded-pill">
                        <i class="bi bi-fire"></i> Light My Diya
                    </a>
                    <a href="#wall" class="btn btn-ghost-gold btn-lg rounded-pill">
                        See Live Diya Wall <i class="bi bi-chevron-right"></i>
                    </a>
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
                </div> --}}
                 <div class="live-note"><i class="bi bi-info-circle"></i> Live Aarti is open for everyone. Pooja and Hawan require booking and payment confirmation.</div>
            </div>
            <div class="col-lg-6">
                <div class="ld-hero-img-wrap">
                    <div class="ld-img-glow"></div>
                    <div class="ld-img-card glass">
                        <img src="{{ asset('assets/temple-hero.jpg') }}" alt="Live spiritual session">
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

    <section class="container page-section">
        <div class="row g-4">
            @foreach ($liveCards as $card)
                <div class="col-md-6 col-xl-4">
                    <div class="glass live-session-choice">
                        <span class="choice-icon"><i class="bi {{ $card['icon'] }}"></i></span>
                        <span class="choice-status">{{ $card['status'] }}</span>
                        <h3>{{ $card['title'] }}</h3>
                        <strong class="gold-text">{{ $card['service'] }}</strong>
                        <p>{{ $card['description'] }}</p>
                        <a href="{{ $card['url'] }}" class="btn {{ $card['buttonClass'] }} w-100">{{ $card['button'] }} <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="container page-section">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="glass side-panel h-100">
                    <div class="section-kicker"><i class="bi bi-calendar2-heart"></i> Today</div>
                    <h2>Today's <span class="gold-text">Live Schedule</span></h2>
                    <div class="live-info-list mt-3">
                        @foreach ($schedule as $item)
                            <div class="live-info-row">
                                <strong>{{ $item[0] }}</strong>
                                <span>{{ $item[1] }}</span>
                                <em>{{ $item[2] }}</em>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="glass side-panel h-100">
                    <div class="section-kicker"><i class="bi bi-list-check"></i> Simple Flow</div>
                    <h2>How It <span class="gold-text">Works</span></h2>
                    <div class="live-steps mt-3">
                        @foreach ($steps as $index => $step)
                            <div class="glass live-step">
                                <span>{{ $index + 1 }}</span>
                                <strong>{{ $step }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

{{-- @include('partials.footer') --}}
@endsection
