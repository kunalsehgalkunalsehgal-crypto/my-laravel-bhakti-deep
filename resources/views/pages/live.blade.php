@extends('layouts.app')

@section('title', 'Live Session - BhaktiDeep')
@section('description', 'Immersive live pooja and hawan session with mantras, aarti, family join and donations in one screen.')

@section('body')
<header class="site-header sticky-top live-topbar">
    <div class="container d-flex align-items-center justify-content-between gap-3 py-3">
        <a class="brand-wrap" href="{{ route('home') }}">
            <span class="brand-icon"><i class="bi bi-fire"></i></span>
            <span class="brand-title gold-text">BhaktiDeep</span>
        </a>
        <div class="d-flex align-items-center gap-2 gap-md-3">
            <span class="live-pill"><i></i> LIVE</span>
            <span class="family-pill d-none d-sm-inline-flex"><i class="bi bi-people"></i> 4 family joined</span>
            <button class="btn btn-gold btn-sm rounded-pill"><i class="bi bi-heart"></i> Donate</button>
        </div>
    </div>
</header>

<main class="page-shell live-page">
    <section class="container py-4 py-md-5">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="live-player">
                    <img src="{{ asset('assets/havan-live.jpg') }}" alt="Live havan kund with sacred fire">
                    <div class="particles">@for($i = 0; $i < 28; $i++)<span style="left: {{ ($i * 53) % 100 }}%; animation-delay: {{ $i * .18 }}s"></span>@endfor</div>
                    <span class="now-playing"><i class="bi bi-music-note"></i> Now Playing</span>
                    <div class="player-copy">
                        <small>Mantra</small>
                        <h1>Mahamrityunjaya Mantra</h1>
                        <div class="audio-row">
                            <button class="play-round"><i class="bi bi-pause-fill"></i></button>
                            <div class="audio-wave">
                                <div>@for($i = 0; $i < 60; $i++)<span class="{{ $i < 36 ? 'active' : '' }}" style="height: {{ 6 + abs(sin($i * .6) * 22 + ($i % 5) * 2) }}px"></span>@endfor</div>
                                <p><span>06:42</span><span>11:08</span></p>
                            </div>
                            <button class="volume-round"><i class="bi bi-volume-up"></i></button>
                        </div>
                    </div>
                </div>

                <div class="glass countdown-card mt-4">
                    <span><i class="bi bi-bell"></i></span>
                    <strong>Up next<small>Aarti starts in</small></strong>
                    <em class="gold-text">08:24</em>
                </div>

                <div class="glass timeline-card large mt-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3>Session Progress</h3><small>2 of 4 complete</small>
                    </div>
                    @foreach ([['Sankalp', 'done', 'Completed'], ['Mantra Chanting', 'active', 'In progress - Mahamrityunjaya'], ['Aarti', 'upcoming', 'Upcoming'], ['Completion Blessing', 'upcoming', 'Upcoming']] as $i => $row)
                        <div class="timeline-row {{ $row[1] }}"><span>{{ $row[1] === 'done' ? '✓' : $i + 1 }}</span><strong>{{ $row[0] }}<small>{{ $row[2] }}</small></strong></div>
                    @endforeach
                </div>

                <div class="glass completion-card mt-4">
                    <span><i class="bi bi-stars"></i></span>
                    <div>
                        <h3>Your pooja is completed</h3>
                        <p>Blessings have been registered in your name. May Lakshmi ji bring prosperity to your home.</p>
                        <div class="d-flex flex-wrap gap-2"><button class="btn btn-gold btn-sm"><i class="bi bi-download"></i> Download Receipt</button><button class="btn btn-ghost-gold btn-sm"><i class="bi bi-play-circle"></i> View Replay</button><button class="btn btn-ghost-gold btn-sm"><i class="bi bi-share"></i> Share Blessings</button></div>
                    </div>
                </div>
            </div>

            <aside class="col-lg-4">
                <div class="glass side-panel">
                    <div class="d-flex justify-content-between align-items-center"><h3>Family Present</h3><span>4 joined</span></div>
                    @foreach ([['Mother', 'Smt. Sunita'], ['Father', 'Shri Ramesh'], ['Spouse', 'Aarav'], ['Guest', 'Priya']] as $i => $member)
                        <div class="family-row"><b class="{{ $i % 2 ? 'saffron' : '' }}">{{ substr($member[1], 0, 1) }}</b><strong>{{ $member[0] }}<small>{{ $member[1] }}</small></strong><em><i class="bi bi-check-circle"></i> Live</em></div>
                    @endforeach
                    <button class="btn btn-saffron w-100 mt-3"><i class="bi bi-share"></i> Invite Family on WhatsApp</button>
                </div>

                <div class="donation-panel mt-4">
                    <h3><i class="bi bi-heart"></i> Quick Donation</h3>
                    <p>Offer a small contribution as the aarti unfolds.</p>
                    <div class="row g-2">@foreach(['&#8377;11', '&#8377;51', '&#8377;101', 'Custom'] as $i => $amount)<div class="col-3"><button class="btn {{ $i === 1 ? 'btn-gold' : 'btn-ghost-gold' }} w-100">{!! $amount !!}</button></div>@endforeach</div>
                    <button class="btn btn-light w-100 mt-3">Donate Securely</button>
                </div>

                <div class="glass side-panel mt-4">
                    <h3>Coming Up</h3>
                    @foreach ([['Aarti', '5:30'], ['Completion Blessing', '2:15']] as $item)
                        <div class="coming-row"><i class="bi bi-play-circle"></i><strong>{{ $item[0] }}<small>{{ $item[1] }}</small></strong><em class="bi bi-arrow-right"></em></div>
                    @endforeach
                </div>
            </aside>
        </div>
    </section>
</main>
@endsection
