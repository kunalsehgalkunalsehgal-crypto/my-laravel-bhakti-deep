@extends('layouts.app')

@section('title', 'Personalized Pooja Booking - BhaktiDeep')
@section('description', 'Choose a personalized pooja, add sankalp, select slot and join the live pooja session.')

@push('styles')
<link href="{{ asset('css/light-diya.css') }}" rel="stylesheet">
<link href="{{ asset('css/hawan.css') }}" rel="stylesheet">
@endpush

@section('body')
@php
    $poojaServices = $poojaServices ?? [];

    $poojaPoints = [
        ['bi-flower1', 'Choose Pooja', 'Select the pooja according to your spiritual need.'],
        ['bi-person-lines-fill', 'Add Sankalp', 'Your name, gotra and mannokamna are used in the ritual.'],
        ['bi-calendar2-check', 'Choose Slot', 'Pick a date and time for your live pooja.'],
        ['bi-broadcast', 'Join Live', 'After booking, join your pooja from the live session link.'],
    ];
@endphp

<main class="page-shell hawan-page">
    <section class="container ld-hero">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="eyebrow"><i class="bi bi-flower1"></i> PERSONALIZED POOJA</span>
                <h1 class="mt-4"><span>Book a </span><span class="gold-text">Live Pooja</span><span> from Home</span></h1>
                <p class="mt-4">Choose your pooja, add sankalp, select package and slot, then join the live pooja with family access and digital receipt.</p>
                <div class="hero-buttons mt-4">
                    <a href="#poojas" class="btn btn-saffron btn-lg"><i class="bi bi-flower1"></i> Choose Pooja</a>
                    <a href="{{ route('live.sessions') }}" class="btn btn-ghost-gold btn-lg"><i class="bi bi-broadcast"></i> Live Sessions</a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="ld-hero-img-wrap">
                    <div class="ld-img-glow"></div>
                    <div class="ld-img-card glass">
                        <img src="{{ asset('assets/lakshmi-hero.jpg') }}" alt="Personalized pooja altar">
                        <div class="ld-img-overlay"></div>
                        <div class="ld-img-badge">
                            <div class="d-flex align-items-center gap-2">
                                <span class="ld-pulse-dot"></span>
                                <span>Live pooja with sankalp</span>
                            </div>
                            <span class="ld-akhand-tag">Booking Open</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="container page-section">
        <h2>How pooja <span class="gold-text">booking works</span></h2>
        <p>Same simple flow: choose pooja, fill details, pay and join live.</p>
        <div class="row g-4 mt-3">
            @foreach ($poojaPoints as $point)
                <div class="col-sm-6 col-lg-3">
                    <div class="glass feature-card ld-festival-feature hawan-feature-card">
                        <span><i class="bi {{ $point[0] }}"></i></span>
                        <strong>{{ $point[1] }}</strong>
                        <p>{{ $point[2] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="container page-section" id="poojas">
        <div class="section-heading">
            <div>
                <h2>Choose your <span class="gold-text">pooja</span></h2>
                <p>All poojas include sankalp, live access, family join and digital receipt.</p>
            </div>
            <p><i class="bi bi-calendar text-warning"></i> Slots daily - 5 AM to 9 PM IST</p>
        </div>

        <div class="row g-4" data-pooja-grid>
            @foreach ($poojaServices as $index => $pooja)
                <div class="col-sm-6 col-lg-4 col-xl-3 {{ $index >= 4 ? 'hawan-hidden' : '' }}" data-pooja-card>
                    <div class="package-card {{ $pooja['featured'] ? 'featured' : 'glass' }}">
                        @if ($pooja['featured'])
                            <span class="package-badge">Most Booked</span>
                        @endif
                        <i class="bi bi-flower1"></i>
                        <h3>{{ $pooja['name'] }}</h3>
                        <p>{{ $pooja['purpose'] }}</p>
                        <strong class="gold-text">Rs.{{ $pooja['price'] }}</strong>
                        <small>{{ $pooja['duration'] }} - Live + replay</small>
                        <ul class="hawan-benefits">
                            @foreach ($pooja['benefits'] as $benefit)
                                <li><i class="bi bi-check"></i>{{ $benefit }}</li>
                            @endforeach
                        </ul>
                        <a href="{{ route('pooja.show', ['slug' => $pooja['slug']]) }}" class="btn {{ $pooja['featured'] ? 'btn-gold' : 'btn-saffron' }} w-100">Book Now</a>
                    </div>
                </div>
            @endforeach
        </div>

        @if (count($poojaServices) > 4)
            <div class="text-center mt-4">
                <button type="button" class="btn btn-ghost-gold" data-view-more-poojas>View More Poojas <i class="bi bi-chevron-down"></i></button>
            </div>
        @endif
    </section>

    <section class="container pb-5">
        <div class="glass final-cta">
            <i class="bi bi-flower1"></i>
            <h2>Start your <span class="gold-text">personalized pooja</span> booking</h2>
            <a href="#poojas" class="btn btn-saffron btn-lg">Choose Pooja <i class="bi bi-arrow-right"></i></a>
        </div>
    </section>
</main>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const viewMoreButton = document.querySelector('[data-view-more-poojas]');
    const hiddenCards = document.querySelectorAll('[data-pooja-card].hawan-hidden');

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
            ? 'View More Poojas <i class="bi bi-chevron-down"></i>'
            : 'Show Less <i class="bi bi-chevron-up"></i>';
    });
});
</script>
@endpush
