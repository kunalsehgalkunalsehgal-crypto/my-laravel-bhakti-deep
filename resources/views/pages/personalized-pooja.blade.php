@extends('layouts.app')

@section('title', 'Personalized Pooja Booking - BhaktiDeep')
@section('description', 'A premium step-by-step pooja booking flow with sankalp, AI mantra playlist, slot and family join.')

@section('body')
@include('partials.header')

<main class="page-shell pooja-booking-page" data-booking-flow>
    <div class="particles subtle">
        @for ($i = 0; $i < 14; $i++)
            <span style="left: {{ ($i * 53) % 100 }}%; animation-delay: {{ ($i * .45) }}s;"></span>
        @endfor
    </div>

    <section class="container page-hero compact">
        <div class="breadcrumb-line">
            <a href="{{ route('home') }}">Home</a><i class="bi bi-chevron-right"></i><span>Pooja</span><i class="bi bi-chevron-right"></i><strong>Personalized Booking</strong>
        </div>
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-4 mt-4">
            <div>
                <h1><span>Book Your </span><span class="gold-text">Personalized Pooja</span></h1>
                <p>A guided, temple-grade ritual - performed in your name and broadcast live.</p>
            </div>
            <div class="booking-stepper" data-stepper>
                @foreach (['Deity', 'Sankalp', 'Mantra', 'Slot', 'Review'] as $i => $label)
                    <span class="{{ $i === 0 ? 'active' : '' }}" data-step-dot="{{ $i + 1 }}"><b>{{ $i + 1 }}</b><em>{{ $label }}</em></span>
                @endforeach
            </div>
        </div>
    </section>

    <section class="container pb-5">
        <div class="row g-4 g-xl-5">
            <div class="col-lg-8">
                <div class="glass flow-card">
                    <div class="flow-step active" data-step="1">
                        <h2>Choose your deity</h2>
                        <p>Each deity opens a unique temple theme, mantras and visuals.</p>
                        <div class="row g-3 mt-2 deity-grid">
                            @foreach ([
                                ['Lakshmi', 'Prosperity'], ['Shiv', 'Healing'], ['Hanuman', 'Strength'],
                                ['Durga', 'Power'], ['Ganesh', 'Beginnings'], ['Saraswati', 'Wisdom'],
                            ] as $i => $deity)
                                <div class="col-sm-6 col-xl-4">
                                    <button class="select-card {{ $i === 0 ? 'selected' : '' }}" data-select-group="deity" data-summary-target="summary-deity" data-summary-value="{{ $deity[0] }}">
                                        <i class="bi bi-fire"></i>
                                        <strong>{{ $deity[0] }}</strong>
                                        <small>{{ $deity[1] }}</small>
                                        <b><i class="bi bi-check"></i></b>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flow-step" data-step="2">
                        <div class="section-kicker"><i class="bi bi-person"></i> Step 2</div>
                        <h2>Your Sankalp</h2>
                        <p>These details will be chanted live in your name during the pooja.</p>
                        <div class="row g-3 mt-2">
                            @foreach ([['Full Name', 'Ananya Sharma', 'text'], ['Gotra', 'Kashyap', 'text'], ['Date of Birth', '', 'date'], ['Birth Time', '', 'time'], ['Birth Place', 'Varanasi, UP', 'text'], ['Spouse Name (optional)', '-', 'text']] as $field)
                                <div class="col-sm-6">
                                    <label class="form-label small-label">{{ $field[0] }}</label>
                                    <input class="form-control sacred-input" type="{{ $field[2] }}" placeholder="{{ $field[1] }}">
                                </div>
                            @endforeach
                            <div class="col-12">
                                <label class="form-label small-label">Mannokamna (Your Wish)</label>
                                <textarea class="form-control sacred-input" rows="3" placeholder="Share your intention..."></textarea>
                            </div>
                        </div>
                        <div class="note-box mt-3"><i class="bi bi-shield-check"></i> Your sankalp is encrypted and used only for this pooja.</div>
                    </div>

                    <div class="flow-step" data-step="3">
                        <div class="section-kicker"><i class="bi bi-stars"></i> AI Mantra Engine</div>
                        <h2>Your mantra playlist</h2>
                        <p>Tap any track to preview. AI has tuned the order to your sankalp.</p>
                        <div class="track-list mt-4">
                            @foreach ([['bi-music-note', 'Welcome Intro', '02:15'], ['bi-play-fill', 'Sankalp Narration', '03:20'], ['bi-fire', 'Mahalakshmi Ashtakam', '11:08'], ['bi-bell', 'Lakshmi Aarti', '05:30'], ['bi-star', 'Completion Blessing', '02:45']] as $i => $track)
                                <button class="track-row {{ $i === 2 ? 'playing' : '' }}" data-toggle-class="playing">
                                    <span><i class="bi {{ $track[0] }}"></i></span>
                                    <strong>{{ $track[1] }}<small>AI-selected for your pooja</small></strong>
                                    <em>{{ $track[2] }}</em>
                                    <div class="sound-bars">@for ($b = 0; $b < 18; $b++)<i style="height: {{ 6 + (($b * 11) % 18) }}px"></i>@endfor</div>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="flow-step" data-step="4">
                        <div class="section-kicker"><i class="bi bi-calendar"></i> Pick a slot</div>
                        <h2>When should we begin?</h2>
                        <p>All sessions are performed at our temple in Varanasi, streamed live to you.</p>
                        <div class="slot-days mt-4">
                            @foreach (['Today', 'Tomorrow', 'Sat 12', 'Sun 13', 'Mon 14'] as $i => $day)
                                <button class="{{ $i === 0 ? 'active' : '' }}" data-select-group="slot-day">{{ $day }}</button>
                            @endforeach
                        </div>
                        <div class="row g-3 mt-2">
                            @foreach (['6:00 AM', '9:30 AM', '12:00 PM', '5:00 PM', '7:00 PM', '8:30 PM'] as $i => $slot)
                                <div class="col-6 col-md-4">
                                    <button class="slot-card {{ $i === 4 ? 'selected' : '' }}" data-select-group="slot" data-summary-target="summary-slot" data-summary-value="Today - {{ $slot }} IST">
                                        <i class="bi bi-clock"></i>
                                        <strong>{{ $slot }}</strong>
                                        <small>{{ $i === 4 ? '3 slots left' : 'Available' }}</small>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flow-step" data-step="5">
                        <div class="section-kicker"><i class="bi bi-file-earmark-text"></i> Review &amp; confirm</div>
                        <h2>Almost ready</h2>
                        <p>Verify your sankalp. You can edit anything before paying.</p>
                        <div class="row g-3 mt-2">
                            <div class="col-md-6"><div class="review-box"><span>Pooja</span><strong><b data-summary-read="summary-deity">Lakshmi</b> Pooja - Premium</strong><p>Acharya R. Sharma<br>Today - 7:00 PM IST<br>Live broadcast + replay</p></div></div>
                            <div class="col-md-6"><div class="review-box"><span>Sankalp</span><strong>Ananya Sharma</strong><p>Kashyap gotra - Varanasi - For prosperity &amp; family wellbeing</p></div></div>
                        </div>
                        <label class="consent-row mt-3"><input type="checkbox" checked> I agree to BhaktiDeep's terms and consent to my sankalp being chanted live.</label>
                    </div>

                    <div class="flow-actions">
                        <button class="btn btn-ghost-gold" data-prev-step disabled><i class="bi bi-arrow-left"></i> Back</button>
                        <button class="btn btn-gold" data-next-step>Continue <i class="bi bi-arrow-right"></i></button>
                        <a class="btn btn-saffron d-none" href="{{ route('live') }}" data-pay-step>Confirm &amp; Pay <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <aside class="col-lg-4">
                <div class="glass summary-card">
                    <h3>Order Summary</h3>
                    <dl>
                        <dt>Deity</dt><dd data-summary-read="summary-deity">Lakshmi</dd>
                        <dt>Package</dt><dd>Premium</dd>
                        <dt>Slot</dt><dd data-summary-read="summary-slot">Today - 7:00 PM IST</dd>
                        <dt>Pandit</dt><dd>Acharya R. Sharma</dd>
                    </dl>
                    <hr>
                    <dl>
                        <dt>Package</dt><dd>&#8377;1,501</dd>
                        <dt>Family join</dt><dd>Free</dd>
                        <dt>Donation (optional)</dt><dd>&#8377;101</dd>
                    </dl>
                    <div class="total-row"><span>Total</span><strong class="gold-text">&#8377;1,602</strong></div>
                    <div class="trust-grid">
                        <span><i class="bi bi-shield-check"></i> Razorpay</span>
                        <span><i class="bi bi-heart"></i> 80G Receipt</span>
                        <span><i class="bi bi-people"></i> Family Join</span>
                    </div>
                </div>
                <div class="glass summary-card mt-4">
                    <div class="section-kicker"><i class="bi bi-stars"></i> What's included</div>
                    <ul class="check-list mt-3">
                        @foreach (['Personalized Sankalp', 'AI Mantra playlist', 'Live aarti + bell', 'Family join link', 'Session replay', 'Donation receipt'] as $item)
                            <li><i class="bi bi-check"></i>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
            </aside>
        </div>
    </section>
</main>

@include('partials.footer')
@endsection
