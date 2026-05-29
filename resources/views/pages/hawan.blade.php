@extends('layouts.app')

@section('title', 'Hawan Booking & Live Session - BhaktiDeep')
@section('description', 'Book a sacred hawan with real-time fire visuals, mantra timeline, ahuti counter and family join.')

@section('body')
@include('partials.header')

<main class="page-shell">
    <section class="container page-hero">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="breadcrumb-line"><a href="{{ route('home') }}">Home</a><i class="bi bi-chevron-right"></i><strong>Hawan</strong></div>
                <span class="eyebrow mt-4"><i class="bi bi-fire"></i> SACRED FIRE RITUAL</span>
                <h1><span>Book a </span><span class="gold-text">Live Hawan</span><span> from Home</span></h1>
                <p>Sacred fire, Vedic mantras, real-time ahuti counter and family join - a complete temple-grade hawan, streamed live in HD.</p>
                <div class="hero-buttons">
                    <a href="#packages" class="btn btn-saffron btn-lg"><i class="bi bi-fire"></i> Book Hawan</a>
                    <a href="#preview" class="btn btn-ghost-gold btn-lg"><i class="bi bi-play-fill"></i> Watch Sample</a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="ritual-visual">
                    <img src="{{ asset('assets/havan-live.jpg') }}" alt="Sacred hawan fire">
                    <div class="particles">@for ($i = 0; $i < 32; $i++)<span style="left: {{ ($i * 53) % 100 }}%; animation-delay: {{ $i * .18 }}s"></span>@endfor</div>
                    <div class="floating-session">
                        <span><i class="bi bi-fire"></i></span>
                        <strong>Next Hawan<small>Today - 6:30 PM</small></strong>
                        <em>3 slots left</em>
                    </div>
                    <div class="spark-bars">@for ($i = 0; $i < 24; $i++)<i style="height: {{ 8 + (($i * 13) % 30) }}px"></i>@endfor</div>
                </div>
            </div>
        </div>
    </section>

    <section class="container page-section">
        <h2>What happens in a <span class="gold-text">live hawan</span></h2>
        <p>A guided sacred fire ritual with synchronised mantra, ahuti and aarti.</p>
        <div class="row g-4 mt-3">
            @foreach ([['bi-fire', 'Sacred Fire Setup', 'Pandit prepares hawan kund with samidha and ghee.'], ['bi-music-note', 'Vedic Mantras', '108 mantras chanted live with timed precision.'], ['bi-stars', 'Ahuti Counter', 'Real-time count of offerings made in your name.'], ['bi-people', 'Family Join', 'Up to 25 family members can join over WhatsApp link.']] as $item)
                <div class="col-sm-6 col-lg-3"><div class="glass feature-card"><span><i class="bi {{ $item[0] }}"></i></span><strong>{{ $item[1] }}</strong><p>{{ $item[2] }}</p></div></div>
            @endforeach
        </div>
    </section>

    <section class="container page-section" id="preview">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="preview-card">
                    <img src="{{ asset('assets/havan-live.jpg') }}" alt="Live hawan fire">
                    <span class="live-badge"><i></i> Live Preview</span>
                    <div class="preview-copy">
                        <small>Currently Chanting</small>
                        <h3>Mahamrityunjaya Mantra - 47 of 108</h3>
                        <div class="progress sacred-progress"><div style="width:43%"></div></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="ahuti-card" data-counter data-start="47" data-end="108">
                    <div class="d-flex align-items-center gap-3">
                        <span><i class="bi bi-fire"></i></span>
                        <div><small>Ahuti Counter</small><strong><b data-count>47</b> <em>of 108</em></strong></div>
                    </div>
                    <div class="progress sacred-progress mt-3"><div data-count-bar style="width:43%"></div></div>
                    <p><i class="bi bi-people"></i> 4 family present <button><i class="bi bi-share"></i> Invite</button></p>
                </div>
                <div class="glass timeline-card mt-4">
                    <h3>Session timeline</h3>
                    @foreach ([['Kund Sthapana', 'done', '8 min'], ['Sankalp', 'done', '5 min'], ['Mantra Japa', 'active', '32 min'], ['Purna Ahuti', 'upcoming', '10 min'], ['Aarti & Blessing', 'upcoming', '8 min']] as $i => $row)
                        <div class="timeline-row {{ $row[1] }}"><span>{{ $row[1] === 'done' ? '✓' : $i + 1 }}</span><strong>{{ $row[0] }}</strong><em>{{ $row[2] }}</em></div>
                    @endforeach
                </div>
                <a href="{{ route('live') }}" class="btn btn-gold w-100 mt-4 py-3">Enter Live Session <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </section>

    <section class="container page-section" id="packages">
        <div class="section-heading">
            <div><h2>Choose your <span class="gold-text">hawan</span></h2><p>All hawans include sankalp, family join and digital prasad photo.</p></div>
            <p><i class="bi bi-calendar text-warning"></i> Slots daily - 5 AM to 9 PM IST</p>
        </div>
        <div class="row g-4">
            @foreach ([['Mahamrityunjaya Hawan', 'Health, longevity, protection', '2,101', '75 min', false], ['Lakshmi Hawan', 'Prosperity & wealth', '2,501', '60 min', false], ['Navgrah Hawan', 'Planetary peace & balance', '3,501', '90 min', true], ['Vastu Hawan', 'Home & business shanti', '4,101', '120 min', false]] as $hawan)
                <div class="col-sm-6 col-lg-3">
                    <div class="package-card {{ $hawan[4] ? 'featured' : 'glass' }}">
                        @if ($hawan[4])<span class="package-badge">Most Booked</span>@endif
                        <i class="bi bi-fire"></i>
                        <h3>{{ $hawan[0] }}</h3>
                        <p>{{ $hawan[1] }}</p>
                        <strong class="gold-text">&#8377;{{ $hawan[2] }}</strong>
                        <small>{{ $hawan[3] }} - Live + replay</small>
                        <button class="btn {{ $hawan[4] ? 'btn-gold' : 'btn-saffron' }} w-100">Book Now</button>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="container pb-5">
        <div class="glass final-cta">
            <i class="bi bi-fire"></i>
            <h2>Invite the <span class="gold-text">sacred fire</span> into your home</h2>
            <a href="#packages" class="btn btn-saffron btn-lg">Book Hawan <i class="bi bi-arrow-right"></i></a>
            <a href="{{ route('live') }}" class="btn btn-ghost-gold btn-lg">Join Live Now</a>
        </div>
    </section>
</main>

@include('partials.footer')
@endsection
