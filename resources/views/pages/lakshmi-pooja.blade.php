@extends('layouts.app')

@section('title', 'Lakshmi Pooja - Prosperity, Peace & Blessings - BhaktiDeep')
@section('description', 'Book a personalized Lakshmi Pooja with sankalp, mantra playlist, aarti, and family participation.')

@section('body')
@include('partials.header')

<main class="page-shell lakshmi-page">
    <section class="container page-hero">
        <div class="particles subtle">@for ($i = 0; $i < 18; $i++)<span style="left: {{ ($i * 47) % 100 }}%; animation-delay: {{ $i * .25 }}s;"></span>@endfor</div>
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="breadcrumb-line"><a href="{{ route('home') }}">Home</a><span>/</span><span>Pooja</span><span>/</span><strong>Lakshmi</strong></div>
                <span class="eyebrow mt-4"><i class="bi bi-stars"></i> GOLDEN RED LAKSHMI EXPERIENCE</span>
                <h1><span>Lakshmi Pooja for </span><span class="gold-text">Prosperity, Peace &amp; Blessings</span></h1>
                <p>Book a personalized Lakshmi Pooja with sankalp, mantra playlist, aarti, and family participation - performed with temple-grade rituals and broadcast live to you.</p>
                <div class="hero-buttons">
                    <a href="#packages" class="btn btn-gold btn-lg">Book Lakshmi Pooja</a>
                    <button class="btn btn-ghost-gold btn-lg"><i class="bi bi-share"></i> Share with Family</button>
                </div>
                <div class="trust-line"><span><i class="bi bi-check"></i> 4.9 / 5 from 12,400+ devotees</span><span><i class="bi bi-check"></i> Session replay included</span></div>
            </div>
            <div class="col-lg-6">
                <div class="ritual-visual lakshmi">
                    <img src="{{ asset('assets/lakshmi-hero.jpg') }}" alt="Lakshmi temple altar with lotus and diyas">
                    <div class="floating-session wide"><strong>Next slot<small>Today - 7:00 PM IST</small></strong><em>3 slots left</em></div>
                </div>
            </div>
        </div>
    </section>

    <section class="container page-section">
        <h2>What's <span class="gold-text">included</span></h2>
        <p>Everything you need for a complete, temple-like Lakshmi Pooja experience.</p>
        <div class="row g-4 mt-3">
            @foreach ([['bi-person', 'Personalized Sankalp', 'Your name, gotra and mannokamna chanted live by the pandit.'], ['bi-music-note', 'AI-Selected Mantra Playlist', 'Curated audio flow tailored to Lakshmi worship.'], ['bi-bell', 'Live Aarti Timer', 'Synchronised aarti with bell, lamp and chant.'], ['bi-people', 'Family Join Link', 'One WhatsApp link, the whole family attends together.'], ['bi-file-earmark-text', 'Donation Receipt', '80G-ready receipt sent to your email instantly.'], ['bi-play-circle', 'Session Replay', 'Re-live and re-share your pooja anytime.']] as $item)
                <div class="col-sm-6 col-lg-4"><div class="glass feature-card"><span><i class="bi {{ $item[0] }}"></i></span><strong>{{ $item[1] }}</strong><p>{{ $item[2] }}</p></div></div>
            @endforeach
        </div>
    </section>

    <section class="container page-section">
        <div class="row g-5">
            <div class="col-lg-4">
                <h2>Your <span class="gold-text">Sankalp</span></h2>
                <p>A sankalp is a sacred resolve. We weave these details into your pooja so every mantra is offered in your name.</p>
                <ul class="check-list mt-4">
                    <li><i class="bi bi-check"></i>Encrypted &amp; private</li>
                    <li><i class="bi bi-check"></i>Used only for your pooja</li>
                    <li><i class="bi bi-check"></i>Editable until session starts</li>
                </ul>
            </div>
            <div class="col-lg-8">
                <form class="glass sankalp-form" onsubmit="return false">
                    <div class="row g-3">
                        @foreach ([['Full Name', 'Ananya Sharma', 'text'], ['Gotra', 'Kashyap', 'text'], ['Date of Birth', '', 'date'], ['Birth Time', '', 'time'], ['Birth Place', 'Varanasi, UP', 'text'], ["Father's Name", 'Shri Ramesh', 'text'], ["Mother's Name", 'Smt Sunita', 'text'], ['Spouse Name (optional)', '-', 'text']] as $field)
                            <div class="col-sm-6"><label class="form-label small-label">{{ $field[0] }}</label><input class="form-control sacred-input" type="{{ $field[2] }}" placeholder="{{ $field[1] }}"></div>
                        @endforeach
                        <div class="col-12"><label class="form-label small-label">Purpose</label><select class="form-select sacred-input"><option>Prosperity &amp; Wealth</option><option>Peace &amp; Harmony</option><option>Business Growth</option><option>Health &amp; Healing</option></select></div>
                        <div class="col-12"><label class="form-label small-label">Mannokamna Message</label><textarea class="form-control sacred-input" rows="3" placeholder="Share your intention..."></textarea></div>
                    </div>
                    <button class="btn btn-gold btn-lg mt-4">Continue to Sankalp <i class="bi bi-arrow-right"></i></button>
                </form>
            </div>
        </div>
    </section>

    <section class="container page-section">
        <h2>Session <span class="gold-text">ambience</span></h2>
        <p>A theme designed for Lakshmi - gold, lotus, bells.</p>
        <div class="row g-4 mt-3">
            @foreach ([['bi-palette', 'Gold + Red Theme', 'Royal Lakshmi visual palette'], ['bi-music-note', 'Lakshmi Mantra Audio', 'Mahalakshmi Ashtakam & more'], ['bi-flower1', 'Lotus Petal Animation', 'Falling petals throughout'], ['bi-bell', 'Temple Bell Ambience', 'Soft 3D bell soundscape']] as $item)
                <div class="col-sm-6 col-lg-3"><div class="glass mini-card"><i class="bi {{ $item[0] }}"></i><strong>{{ $item[1] }}</strong><p>{{ $item[2] }}</p></div></div>
            @endforeach
        </div>
    </section>

    <section class="container page-section" id="packages">
        <div class="section-heading">
            <div><h2>Choose your <span class="gold-text">package</span></h2><p>All packages include live broadcast &amp; session replay.</p></div>
            <p><i class="bi bi-calendar text-warning"></i> Slots available daily 5 AM to 9 PM IST</p>
        </div>
        <div class="row g-4">
            @foreach ([['Basic Pooja', '501', ['Personal sankalp', 'Standard mantra playlist', 'Live broadcast', 'Digital prasad photo'], false], ['Premium Pooja', '1,501', ['Everything in Basic', 'Senior pandit', 'Extended aarti', 'Couriered prasad', 'Family join link'], true], ['Family Pooja', '3,101', ['Everything in Premium', 'Up to 25 family joiners', 'Personalised mantra narration', 'Festival decor theme', 'Priority slot'], false]] as $pack)
                <div class="col-md-4"><div class="package-card {{ $pack[3] ? 'featured' : 'glass' }}">@if($pack[3])<span class="package-badge">Most Loved</span>@endif<h3>{{ $pack[0] }}</h3><strong class="gold-text">&#8377;{{ $pack[1] }}</strong><small>/ session</small><ul class="check-list">@foreach($pack[2] as $f)<li><i class="bi bi-check"></i>{{ $f }}</li>@endforeach</ul><a href="{{ route('personalized-pooja') }}" class="btn {{ $pack[3] ? 'btn-gold' : 'btn-ghost-gold' }} w-100">Book {{ explode(' ', $pack[0])[0] }}</a></div></div>
            @endforeach
        </div>
    </section>

    <section class="container pb-5">
        <div class="glass final-cta">
            <i class="bi bi-flower1"></i>
            <h2>Start your <span class="gold-text">personalized Lakshmi Pooja</span> today</h2>
            <a href="{{ route('personalized-pooja') }}" class="btn btn-gold btn-lg">Continue to Sankalp <i class="bi bi-arrow-right"></i></a>
        </div>
    </section>
</main>

@include('partials.footer')
@endsection
