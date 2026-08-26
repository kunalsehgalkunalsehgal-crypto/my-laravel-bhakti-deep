@extends('layouts.app')

@section('title', 'Hawan Booking & Live Session - BhaktiDeep')
@section('description', 'Book a sacred hawan with real-time fire visuals, mantra timeline, ahuti counter and family join.')

@push('styles')
<link href="{{ asset('css/light-diya.css') }}" rel="stylesheet">
<link href="{{ asset('css/hawan.css') }}" rel="stylesheet">
@endpush

@section('body')
{{-- @include('partials.header') --}}

@php
    $hawanServices = $hawanServices ?? [];

    $hawanIncludes = [
        'Live + replay',
        'Personalized Sankalp',
        'Family Join Link',
        'Session Replay',
        'Digital Receipt',
        'Completion Certificate',
    ];

    $liveHawanPoints = [
        ['bi-person-heart', 'Personalized Sankalp', 'Your name, gotra and intention are included in the live ritual.'],
        ['bi-fire', 'Dynamic Fire Visuals', 'Premium sacred fire visuals keep the experience immersive.'],
        ['bi-cloud-haze2', 'Smoke Ambience', 'Soft smoke ambience supports the hawan atmosphere.'],
        ['bi-volume-up', 'Fire Crackling Audio', 'Fire sound and mantra audio create a guided ritual feel.'],
        ['bi-music-note-beamed', 'Hawan Mantra Chants', 'Mantras are followed through the session timeline.'],
        ['bi-123', 'Real-time Ahuti Counter', 'Track offerings as the ritual progresses in your name.'],
        
        ['bi-play-circle', 'Session Replay', 'Watch the completed session later after booking.'],
        ['bi-award', 'Digital Certificate', 'Receive a digital completion certificate after the hawan.'],
    ];

    $bookingSteps = [
        ['bi-fire', 'Choose Hawan'],
        ['bi-person-lines-fill', 'Add Sankalp'],
        ['bi-calendar2-check', 'Choose Slot'],
        ['bi-gift', 'Add Dakshina / Offering'],
        ['bi-credit-card', 'Review & Pay'],
        ['bi-broadcast', 'Join Live Hawan'],
    ];
@endphp

<main class="page-shell hawan-page">
    <section class="container ld-hero">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="eyebrow"><i class="bi bi-fire"></i> SACRED FIRE RITUAL</span>
                <h1 class="mt-4"><span>Book a </span><span class="gold-text">Live Hawan</span><span> from Home</span></h1>
                <p class="mt-4">Sacred fire, Vedic mantras, real-time ahuti counter and family join - a complete temple-grade hawan, streamed live in HD.</p>
                <div class="hero-buttons mt-4">
                    <a href="#packages" class="btn btn-saffron btn-lg"><i class="bi bi-fire"></i> Book Hawan</a>
                    <a href="#preview" class="btn btn-ghost-gold btn-lg"><i class="bi bi-play-fill"></i> Watch Sample</a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="ld-hero-img-wrap">
                    <div class="ld-img-glow"></div>
                    <div class="ld-img-card glass">
                        <video autoplay muted loop playsinline poster="{{ asset('assets/havan-live.jpg') }}">
                            <source src="{{ asset('assets/havan-live.mp4') }}" type="video/mp4">
                        </video>
                        <div class="ld-img-overlay"></div>
                        <div class="ld-img-badge">
                            <div class="d-flex align-items-center gap-2">
                                <span class="ld-pulse-dot"></span>
                                <span>Next Hawan Today - 6:30 PM</span>
                            </div>
                            <span class="ld-akhand-tag">Booking Open</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="container page-section">
        <h2>What happens in a <span class="gold-text">live hawan</span></h2>
        <p>A guided sacred fire ritual with synchronised mantra, ahuti, aarti, family access and digital completion record.</p>
        <div class="row g-4 mt-3">
            @foreach ($liveHawanPoints as $item)
                <div class=" col-sm-6 col-md-4 col-lg-3">
                    <div class="glass feature-card  ld-festival-feature hawan-feature-card">
                        <span><i class="bi {{ $item[0] }}"></i></span>
                        <strong>{{ $item[1] }}</strong>
                        <p>{{ $item[2] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="container page-section" id="preview">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class=" live-session-card">
                    {{-- <img src="{{ asset('assets/havan-live.jpg') }}" alt="Sample live hawan fire preview"> --}}
                    <video autoplay muted loop playsinline>
                                <source src="{{ asset('assets/havan-live.mp4') }}" type="video/mp4">
                            </video>
                    <span class="live-badge"><i></i> Sample Preview</span>
                    <div class="preview-copy">
                        <small class="text-white">Preview Live Hawan</small>
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
                    <p><i class="bi bi-people"></i> Family preview <button type="button"><i class="bi bi-share"></i> Invite</button></p>
                </div>
                <div class="glass timeline-card mt-4">
                    <h3>Session timeline</h3>
                    @foreach ([['Kund Sthapana', 'done', '8 min'], ['Sankalp', 'done', '5 min'], ['Mantra Japa', 'active', '32 min'], ['Purna Ahuti', 'upcoming', '10 min'], ['Aarti & Blessing', 'upcoming', '8 min']] as $i => $row)
                        <div class="timeline-row {{ $row[1] }}"><span>{!! $row[1] === 'done' ? '&#10003;' : $i + 1 !!}</span><strong>{{ $row[0] }}</strong><em>{{ $row[2] }}</em></div>
                    @endforeach
                </div>
                <a href="#packages" class="btn btn-gold w-100 mt-4 py-3">Book to Join Live Hawan <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </section>

    {{-- <section class="container page-section hawan-booking-flow">
        <div class="section-heading">
            <div>
                <h2>How Hawan <span>Booking Works</span></h2>
                <p>A simple flow from selecting the hawan to joining the paid live session.</p>
            </div>
        </div>
        <div class="hawan-step-grid mt-4">
            @foreach ($bookingSteps as $i => $step)
                <div class="glass hawan-step-card">
                    <span>{{ $i + 1 }}</span>
                    <i class="bi {{ $step[0] }}"></i>
                    <strong>{{ $step[1] }}</strong>
                </div>
            @endforeach
        </div>
    </section> --}}

    {{-- <section class="container page-section hawan-family-section">
        <div class="glass hawan-family-card">
            <div>
                <span class="eyebrow"><i class="bi bi-whatsapp"></i> FAMILY SHARING</span>
                <h2>Invite family to attend the <span class="gold-text">live hawan</span></h2>
                <p>Invite family via WhatsApp, copy the Hawan link, and let family members join the live session for aarti and blessings after booking.</p>
            </div>
            <div class="hawan-family-actions">
                <span><i class="bi bi-whatsapp"></i> Invite family via WhatsApp</span>
                <span><i class="bi bi-link-45deg"></i> Copy Hawan link</span>
                <span><i class="bi bi-broadcast"></i> Family can join live session</span>
                <span><i class="bi bi-stars"></i> Family can attend aarti and blessings</span>
            </div>
        </div>
    </section> --}}

    <section class="container page-section" id="packages">
        <div class="section-heading">
            <div><h2>Choose your <span class="gold-text">hawan</span></h2><p>All hawans include sankalp, live access, family join, session replay and digital receipt.</p></div>
            <p><i class="bi bi-calendar text-warning"></i> Slots daily - 5 AM to 9 PM IST</p>
        </div>
        <div class="row g-4" data-hawan-grid>
            @foreach ($hawanServices as $index => $hawan)
                @php
                    $hawanSlug = $hawan['slug'] ?? \Illuminate\Support\Str::slug($hawan['name']);
                    $bookingUrl = route('hawan.show', ['slug' => $hawanSlug]);
                @endphp
                <div class="col-sm-6 col-lg-4 col-xl-3 {{ $index >= 4 ? 'hawan-hidden' : '' }}" data-hawan-card>
                    <div class="package-card {{ $hawan['featured'] ? 'featured' : 'glass' }}">
                        @if ($hawan['featured'])<span class="package-badge">Most Booked</span>@endif
                        <i class="bi bi-fire"></i>
                        <h3>{{ $hawan['name'] }}</h3>
                        <p>{{ $hawan['purpose'] }}</p>
                        <strong class="gold-text">&#8377;{{ $hawan['price'] }}</strong>
                        <small>{{ $hawan['duration'] }} - Live + replay</small>
                        <ul class="hawan-benefits">
                            @foreach ($hawan['benefits'] as $benefit)
                                <li><i class="bi bi-check"></i>{{ $benefit }}</li>
                            @endforeach
                        </ul>
                        {{-- <ul class="hawan-includes">
                            @foreach ($hawanIncludes as $include)
                                <li><i class="bi bi-check-circle"></i>{{ $include }}</li>
                            @endforeach
                        </ul> --}}

                        <a href="{{ $bookingUrl }}" data-hawan-slug="{{ $hawanSlug }}" class="btn {{ $hawan['featured'] ? 'btn-gold' : 'btn-saffron' }} w-100">Book Now</a>
                    </div>
                </div>
            @endforeach
        </div>
        @if (count($hawanServices) > 4)
            <div class="text-center mt-4">
                <button type="button" class="btn btn-ghost-gold" data-view-more-hawans>View More Hawans <i class="bi bi-chevron-down"></i></button>
            </div>
        @endif
    </section>

    <section class="container pb-5">
        <div class="glass final-cta">
            <i class="bi bi-fire"></i>
            <h2>Invite the <span class="gold-text">sacred fire</span> into your home</h2>
            <a href="#packages" class="btn btn-saffron btn-lg">Book Hawan <i class="bi bi-arrow-right"></i></a>
            <a href="#preview" class="btn btn-ghost-gold btn-lg">Watch Sample</a>
        </div>
    </section>
</main>

{{-- @include('partials.footer') --}}


@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const viewMoreButton = document.querySelector('[data-view-more-hawans]');
    const hiddenCards = document.querySelectorAll('[data-hawan-card].hawan-hidden');

    if (!viewMoreButton || !hiddenCards.length) {
        return;
    }

    viewMoreButton.addEventListener('click', function () {
        const isExpanded = viewMoreButton.getAttribute('aria-expanded') === 'true';

        hiddenCards.forEach(function (card) {
            card.classList.toggle('hawan-hidden', isExpanded);
        });

        viewMoreButton.setAttribute('aria-expanded', String(!isExpanded));
        viewMoreButton.innerHTML = isExpanded
            ? 'View More Hawans <i class="bi bi-chevron-down"></i>'
            : 'Show Less <i class="bi bi-chevron-up"></i>';
    });
});
</script>
@endpush
