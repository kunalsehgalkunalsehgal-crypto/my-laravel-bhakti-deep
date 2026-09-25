{{-- <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BhaktiDeep - AI Powered Virtual Temple</title>
    <meta name="description" content="Light a virtual diya, book a personalized pooja, or join a live hawan from home.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('css/header.css') }}" rel="stylesheet">
    <link href="{{ asset('css/home.css') }}" rel="stylesheet">
</head> --}}
@extends('layouts.app')

@section('title', 'BhaktiDeep - AI Powered Virtual Temple')
@section('description', 'Light a virtual diya, book a personalized pooja, or join a live hawan from home.')

@push('styles')
    <link href="{{ asset('css/header.css') }}" rel="stylesheet">
    <link href="{{ asset('css/home.css') }}" rel="stylesheet">
@endpush

@section('body')

{{-- <body> --}}
    {{-- <header class="site-header sticky-top">
        <nav class="navbar navbar-expand-xl">
            <div class="container site-nav">
                <a class="navbar-brand brand-wrap" href="/">
                    <span class="brand-icon"><i class="bi bi-fire"></i></span>
                    <span>
                        <span class="brand-title gold-text">BhaktiDeep</span>
                        <span class="brand-tagline">Har Deep Mein Bhakti</span>
                    </span>
                </a>

                <button class="navbar-toggler menu-btn" type="button" data-bs-toggle="collapse"
                    data-bs-target="#mainMenu" aria-controls="mainMenu" aria-expanded="false"
                    aria-label="Toggle navigation">
                    <i class="bi bi-list"></i>
                </button>

                <div class="collapse navbar-collapse" id="mainMenu">
                    <ul class="navbar-nav mx-auto main-links">
                        <li class="nav-item"><a class="nav-link active" href="{{ route('home') }}">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('light-diya') }}">Light Diya</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('personalized-pooja') }}">Book Pooja</a>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('hawan') }}">Book Hawan</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('live.sessions') }}">Live Sessions</a>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('how.works') }}">How It Works</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('blogs') }}">Blog</a></li>
                        <li class="nav-item"><a class="nav-link" href="#footer">Contact</a></li>
                    </ul>

                    <div class="header-actions">
                        <button class="btn btn-outline-saffron"><i class="bi bi-box-arrow-in-right"></i> Login with
                            OTP</button>
                        <button class="btn btn-saffron"><i class="bi bi-stars"></i> Start Bhakti Journey</button>
                    </div>
                </div>
            </div>
        </nav>
    </header> --}}
    

    <main>
        <section class="hero-section">
            <div class="particles">
                @for ($i = 0; $i < 26; $i++)
                    <span style="left: {{ ($i * 37) % 100 }}%; animation-delay: {{ $i * 0.35 }}s;"></span>
                @endfor
            </div>

            <div class="container hero-container">
                <div class="row align-items-center g-5">
                    <div class="col-lg-6">
                        <span class="eyebrow"><i class="bi bi-stars"></i> AI POWERED VIRTUAL TEMPLE</span>
                        <h1 class="hero-title">
                            <span>Personalized Pooja,</span>
                            <span class="gold-text">Diya &amp; Hawan</span>
                            <small>from the comfort of your home</small>
                        </h1>
                        <p class="hero-text">
                            Light a virtual diya, book a personalized pooja, or join a live hawan. Experience divine
                            blessings with sankalp, mantra, aarti and family participation - all in one beautiful
                            digital temple.
                        </p>

                        <div class="hero-buttons">
                            <button class="btn btn-saffron btn-lg"><i class="bi bi-fire"></i> Light Diya</button>
                            <button class="btn btn-gold btn-lg">Book Pooja</button>
                            <button class="btn btn-ghost-gold btn-lg"><i class="bi bi-fire"></i> Book Hawan</button>
                        </div>

                        <div class="row g-3 hero-features">
                            @foreach ([['bi-phone', 'Mobile OTP Login', 'Easy & Secure'], ['bi-shield-check', 'Secure Donation', '100% Protected'], ['bi-people', 'Family Join', 'Connect Together'], ['bi-stars', 'AI Mantra Engine', 'Personalized for you']] as $feature)
                                <div class="col-sm-6 col-xl-3">
                                    <div class="mini-feature">
                                        <span><i class="bi {{ $feature[0] }}"></i></span>
                                        <div>
                                            <strong>{{ $feature[1] }}</strong>
                                            <small>{{ $feature[2] }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="hero-visual-wrap">
                            <div class="hero-glow"></div>
                            <div class="hero-image">
                                <img src="{{ asset('assets/temple-hero.jpg') }}"
                                    alt="Glowing virtual temple arch with diyas">
                            </div>

                            <div class="live-card glass">
                                <div class="live-label"><span></span> Live Diya Wall</div>
                                <div class="live-number gold-text">{{ number_format($liveDiyaCount ?? 0) }}</div>
                                <p>Diyas glowing right now</p>
                                <div class="live-bars">
                                    @for ($i = 0; $i < 18; $i++)
                                        <span style="height: {{ 20 + (($i * 17) % 24) }}px;"></span>
                                    @endfor
                                </div>
                                <button class="btn btn-saffron w-100">Light Your Diya</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="diya-wall-section" id="diya-wall">
            <div class="container">
                <div class="glass diya-panel">
                    <div class="row g-5 align-items-center">
                        <div class="col-lg-6">
                            <div class="section-kicker"><span class="pulse-dot"></span> Live Diya Wall</div>
                            <h2><span class="gold-text">{{ number_format($liveDiyaCount ?? 0) }}</span> diyas glowing right now</h2>
                            <p>Every flame carries a devotee's intention. Light yours and join a global circle of bhakti
                                - your diya stays lit for the duration you choose.</p>

                            @if(($liveDiyas ?? collect())->isNotEmpty())
                                <div class="diya-grid">
                                    @foreach ($liveDiyas as $index => $liveDiya)
                                        <span
                                            title="{{ $liveDiya->diya?->name ?? 'Diya' }}{{ $liveDiya->deity ? ' for '.$liveDiya->deity->name : '' }}"
                                            style="
                                                animation-delay: {{ fmod($index * 0.15, 3) }}s;
                                                --move-y: {{ $index % 2 === 0 ? '-3px' : '3px' }};
                                                --rotate: {{ $index % 3 === 0 ? '-2deg' : '2deg' }};
                                            "
                                        >
                                            <img src="{{ asset('assets/small-deep.png') }}" alt="Glowing diya icon">
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <p class="small-note">No paid diyas are currently glowing.</p>
                            @endif


                            <div class="row g-3 mt-2">
                                @foreach ([['bi-fire', number_format($diyaLitToday ?? 0), 'lit today'], ['bi-people', number_format($liveDiyaCount ?? 0), 'glowing now'], ['bi-heart', number_format(($liveDiyas ?? collect())->count()), 'shown here']] as $stat)
                                    <div class="col-4">
                                        <div class="stat-box">
                                            <i class="bi {{ $stat[0] }}"></i>
                                            <strong>{{ $stat[1] }}</strong>
                                            <span>{{ $stat[2] }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="diya-form">
                                <div class="d-flex align-items-center justify-content-between gap-3">
                                    <h3>Light Your Diya</h3>
                                    <span class="free-badge">Free · 1 min</span>
                                </div>

                                <label>Glow duration</label>
                                <div class="row g-2">
                                    @foreach ([['1 hr', 'Free'], ['24 hrs', '₹11'], ['7 days', '₹51'], ['Akhand', '₹101']] as $key => $duration)
                                        <div class="col-3">
                                            <button class="diya-option {{ $key === 1 ? 'active' : '' }}">
                                                <i class="bi bi-fire"></i>
                                                <strong>{{ $duration[0] }}</strong>
                                                <small>{{ $duration[1] }}</small>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>

                                <label>Intention</label>
                                <div class="intentions">
                                    @foreach (['Prosperity', 'Health', 'Family', 'Peace', 'Business', 'Studies'] as $key => $intent)
                                        <button class="{{ $key === 0 ? 'active' : '' }}">{{ $intent }}</button>
                                    @endforeach
                                </div>

                                <div class="note-box"><i class="bi bi-stars"></i> Your diya will be visible to family
                                    via a share link with live burn timer.</div>
                                <button class="btn btn-saffron w-100 mt-3"><i class="bi bi-fire"></i> Light My
                                    Diya</button>
                                <p class="small-note">Your diya will appear on the wall instantly with a soft glow.</p>
                            </div>

                            <div class="recent-diyas">
                                <div class="recent-head"><span>Recent diyas</span><span><i class="bi bi-clock"></i>
                                        burns left</span></div>
                                <div class="recent-grid">
                                    @forelse (($liveDiyas ?? collect()) as $diyaSession)
                                        @php
                                            $timeLeft = $diyaSession->end_at
                                                ? $diyaSession->end_at->diffForHumans(null, true).' left'
                                                : 'Live';
                                        @endphp
                                        <div class="recent-row">
                                            <span class="small-flame"><i class="bi bi-fire"></i></span>
                                            <div>
                                                <strong>{{ $diyaSession->diya?->name ?? 'Diya' }}</strong><small>{{ $diyaSession->deity?->name ?? 'Temple offering' }}</small>
                                            </div>
                                            <em>{{ $timeLeft }}</em>
                                        </div>
                                    @empty
                                        <div class="recent-row">
                                            <span class="small-flame"><i class="bi bi-fire"></i></span>
                                            <div>
                                                <strong>No live diyas</strong><small>Paid active diyas will appear here.</small>
                                            </div>
                                            <em>-</em>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="category-section" id="categories">
            <div class="container">
                <div class="row g-4">
                    @foreach ([['assets/diya.jpg', 'Diya', ['Standard Diya', 'Akhand Diya', 'Festival Diya', 'Family Diya', 'Business Prosperity Diya', 'Health Protection Diya'], 'Light Your Diya', false], ['assets/lakshmi-hero.jpg', 'Pooja', ['Lakshmi Pooja', 'Shiv Pooja', 'Hanuman Pooja', 'Durga Pooja', 'Ganesh Pooja', '& More Poojas'], 'Book Personalized Pooja', true], ['assets/havan-live.jpg', 'Hawan', ['Mahamrityunjaya Hawan', 'Lakshmi Hawan', 'Navgrah Hawan', 'Vastu Hawan', 'Protection Hawan', '& More Hawans'], 'Book Hawan Session', false]] as $card)
                        <div class="col-lg-4">
                            <div class="category-card glass {{ $card[4] ? 'featured' : '' }}">
                                <div class="row g-4">
                                    <div class="col-md-5 col-lg-12 col-xl-5 align-content-center">
                                        <div class="category-img"><img src="{{ asset($card[0]) }}"
                                                alt="{{ $card[1] }}"></div>
                                    </div>
                                    <div class="col-md-7 col-lg-12 col-xl-7">
                                        <span>Category</span>
                                        <h3 class="gold-text">{{ $card[1] }}</h3>
                                        <ul>
                                            @foreach ($card[2] as $item)
                                                <li><i class="bi bi-check"></i>{{ $item }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                <button class="btn btn-saffron w-100 mt-3">{{ $card[3] }} <i
                                        class="bi bi-chevron-right"></i></button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="experience-section">
            <div class="container">
                <div class="section-title text-center">
                    <h2>Every Service Opens a <span class="gold-text">Different Temple Experience</span></h2>
                    <p>Dynamic themes, mantras, ambiences &amp; visuals for every pooja and hawan.</p>
                </div>

                <div class="row g-4 horizontal-scroll">
                    @foreach ([['assets/lakshmi-hero.jpg', 'Lakshmi Pooja', 'Gold · Lotus · Prosperity'], ['assets/shiv.jpg', 'Shiv Pooja', 'Kailash · Om Namah Shivaya'], ['assets/hanuman.jpg', 'Hanuman Pooja', 'Saffron · Power · Bhakti'], ['assets/hanuman.jpg', 'Hanuman Pooja', 'Saffron · Power · Bhakti'], ['assets/hanuman.jpg', 'Hanuman Pooja', 'Saffron · Power · Bhakti'], ['assets/hanuman.jpg', 'Hanuman Pooja', 'Saffron · Power · Bhakti'], ['assets/hanuman.jpg', 'Hanuman Pooja', 'Saffron · Power · Bhakti'], ['assets/havan-live.jpg', 'Hawan Experience', 'Fire · Mantra · Shanti']] as $item)
                        <div class="col-sm-6 col-lg-3">
                            <div class="temple-card">
                                <img src="{{ asset($item[0]) }}" alt="{{ $item[1] }}">
                                <button><i class="bi bi-play-fill"></i></button>
                                <div>
                                    <h3>{{ $item[1] }}</h3>
                                    <p>{{ $item[2] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- <section class="steps-section" id="how-it-works">
            <div class="container">
                <div class="glass steps-panel">
                    <h2>How <span class="gold-text">BhaktiDeep</span> Works</h2>
                    <div class="row g-4">
                        @foreach ([['bi-globe', 'Visit Website', 'Explore services, live diya wall & festival offers'], ['bi-phone', 'Login with OTP', 'Enter mobile number and verify OTP'], ['bi-grid', 'Choose Service', 'Select Diya, Pooja or Hawan'], ['bi-file-text', 'Fill Sankalp', 'Fill personal details and purpose'], ['bi-heart', 'Donate', 'Choose amount and complete payment'], ['bi-fire', 'Join Live Session', 'Experience pooja, mantra, aarti & blessings']] as $step)
                            <div class="col-sm-6 col-lg-2">
                                <div class="step-item">
                                    <span><i class="bi {{ $step[0] }}"></i></span>
                                    <strong>{{ $step[1] }}</strong>
                                    <p>{{ $step[2] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section> --}}

        <section class="live-section" id="live-session">
            <div class="container">
                <div class="row g-4 justify-content-center">
                    <div class="col-lg-8 ">
                        <div class="live-session-card">
                            {{-- <img src="{{ asset('assets/havan-live.jpg') }}" alt="Live hawan session"> --}}
                            <video autoplay muted loop playsinline>
                                <source src="{{ asset('assets/havan-live.mp4') }}" type="video/mp4">
                            </video>
                            <div class="live-session-content">
                                <h3 class="gold-text">Join a Live Spiritual Session</h3>
                                <span class="live-badge"><i></i> Live</span>
                                <div class="aarti-time"><span><i class="bi bi-bell"></i> Aarti starts in</span><strong
                                        class="gold-text">08:24</strong></div>
                                <div class="row g-2 session-options mt-3">
                                    @foreach ([['bi-music-note', 'Live Mantra', 'Audio'], ['bi-clipboard-check', 'Session', 'Progress'], ['bi-heart', 'Family', 'Join'], ['bi-fire', 'Donate', 'Now']] as $option)
                                        <div class="col-3">
                                            <div><i
                                                    class="bi {{ $option[0] }}"></i><strong>{{ $option[1] }}</strong><span>{{ $option[2] }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <button class="btn btn-saffron w-100 mt-3">Explore Live Sessions <i
                                        class="bi bi-arrow-right"></i></button>
                            </div>
                        </div>
                    </div>

                    {{-- <div class="col-lg-4">
                        <div class="glass invite-card">
                            <h3 class="gold-text">Invite Your Family</h3>
                            <p>Share the session link and bring your family together in divine moments.</p>
                            <div class="share-grid">
                                @foreach ([['bi-whatsapp', 'WhatsApp', 'green'], ['bi-link-45deg', 'Copy Link', 'orange'], ['bi-facebook', 'Facebook', 'blue'], ['bi-envelope', 'Email', 'gold']] as $share)
                                    <span><i class="bi {{ $share[0] }} {{ $share[2] }}"></i>{{ $share[1] }}</span>
                                @endforeach
                            </div>
                            <button class="btn btn-saffron w-100">Invite Family Now</button>
                        </div>
                    </div> --}}
                </div>
            </div>
        </section>

        <section class="festival-section">
            <div class="container">
                <div class="glass festival-panel">
                    <div class="row g-5 align-items-center">
                        <div class="col-lg-6">
                            <span class="festival-badge"><i class="bi bi-stars"></i> Festival Special</span>
                            <h2 class="festival-title">
                                <span class="gold-text">Guru Purnima</span>
                                <span>Mahotsav</span>
                            </h2>
                            <p class="festival-description">
                                Celebrate with personalized pooja, live aarti and sacred diya lighting from home.
                            </p>

                            <div class="festival-buttons">
                                <button class="btn btn-saffron btn-lg"><i class="bi bi-fire"></i> Book Pooja</button>
                                <button class="btn btn-gold btn-lg"><i class="bi bi-fire"></i> Light Diya</button>
                            </div>

                            <div class="row g-3 festival-features ">
                                @foreach ([['bi-music-note', 'Live Aarti'], ['bi-people', 'Family Join'], ['bi-sparkles', 'Personalized Sankalp']] as $feature)
                                    <div class="col-sm-6 col-lg-4 W-100">
                                        <div class="festival-feature">
                                            <span><i class="bi {{ $feature[0] }}"></i></span>
                                            <strong>{{ $feature[1] }}</strong>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="festival-image">
                                <img src="{{ asset('assets/pornima.jpeg') }}" alt="Guru Purnima Celebration">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="scroll-section">
            <div class="container">
                <div class="section-heading">
                    <div><span>Testimonials</span>
                        <h2>Loved by <span class="gold-text">Devotees</span></h2>
                    </div>
                    <p>← swipe →</p>
                </div>
                <div class="horizontal-scroll">
                    @foreach ([['Priya Sharma', 'Jaipur, Rajasthan', 'BhaktiDeep ne hume ghar baithe ek real temple jaisa experience diya.'], ['Rahul Verma', 'Mumbai, Maharashtra', 'The live hawan session felt so divine and personal.'], ['Ananya Gupta', 'Delhi NCR', 'Lighting an akhand diya for my parents health gave me so much peace.'], ['Vikram Joshi', 'Pune', 'Booked a Lakshmi Pooja for our business. The pandit was knowledgeable.'], ['Meera Patel', 'Ahmedabad', 'Finally a digital temple that feels premium and calming.']] as $review)
                        <div class="glass review-card">
                            <i class="bi bi-quote"></i>
                            <p>"{{ $review[2] }}"</p>
                            <div>
                                <span>{{ collect(explode(' ', $review[0]))->map(fn($n) => $n[0])->join('') }}</span><strong>{{ $review[0] }}<small>{{ $review[1] }}</small></strong><em>★★★★★</em>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="scroll-section" id="blogs">
            <div class="container">
                <div class="section-heading">
                    <div><span>Read</span>
                        <h2>Spiritual Guides &amp; <span class="gold-text">Blogs</span></h2>
                        <p>Inspiring stories, festival guides, and timeless spiritual knowledge.</p>
                    </div>
                    <button class="btn btn-ghost-gold d-none d-md-inline-flex"><i class="bi bi-book"></i> Read
                        All</button>
                </div>
                <div class="horizontal-scroll">
                    @foreach ([['assets/diya.jpg', 'DIYA', 'Why You Should Light a Diya Daily?', 'Discover the spiritual significance of the daily diya.'], ['assets/lakshmi-hero.jpg', 'POOJA', 'Importance of Lakshmi Pooja', 'Invite prosperity into every corner of your home.'], ['assets/havan-live.jpg', 'HAWAN', 'Mahamrityunjaya Hawan Benefits', 'Sacred fire for health, protection, and longevity.'], ['assets/shiv.jpg', 'POOJA', 'Shiv Pooja Step-by-Step', 'The complete guide to Mondays with Mahadev.']] as $blog)
                        <a class="blog-card">
                            <div class="home-blog"><img src="{{ asset($blog[0]) }}"
                                    alt="{{ $blog[2] }}"><span>{{ $blog[1] }}</span></div>
                            <h3>{{ $blog[2] }}</h3>
                            <p>{{ $blog[3] }}</p>
                            <strong>Read more <i class="bi bi-arrow-right"></i></strong>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="journey-section">
            <div class="container">
                <div class="journey-panel">
                    <div class="row g-4 align-items-center">
                        <div class="col-lg-7">
                            <span>Begin Today</span>
                            <h2>Start Your Spiritual <span class="gold-text">Journey Today</span></h2>
                            <p>Light a diya, book a personalized pooja, or join a live hawan - and feel divine blessings
                                flow into your home.</p>
                            <div class="hero-buttons">
                                <button class="btn btn-saffron btn-lg"><i class="bi bi-fire"></i> Light Diya</button>
                                <button class="btn btn-gold btn-lg">Book Pooja</button>
                                <button class="btn btn-ghost-gold btn-lg"><i class="bi bi-fire"></i> Book
                                    Hawan</button>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="big-flame"><i class="bi bi-fire"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    {{-- <footer class="footer-section" id="footer">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-3">
                    <div class="brand-wrap">
                        <span class="brand-icon"><i class="bi bi-fire"></i></span>
                        <span><span class="brand-title gold-text">BhaktiDeep</span><span class="brand-tagline">Har
                                Deep Mein Bhakti</span></span>
                    </div>
                    <p>AI Powered Virtual Temple for Personalized Pooja, Diya &amp; Hawan.</p>
                    <div class="socials"><a><i class="bi bi-facebook"></i></a><a><i
                                class="bi bi-instagram"></i></a><a><i class="bi bi-youtube"></i></a><a><i
                                class="bi bi-whatsapp"></i></a></div>
                </div>
                @foreach ([['Quick Links', ['Home', 'Light Diya', 'Book Pooja', 'Book Hawan', 'Live Sessions', 'Blogs', 'Contact Us']], ['Our Services', ['All Diyas', 'All Poojas', 'All Hawans', 'Sankalp', 'Live Sessions', 'Spiritual Dashboard']], ['Important Links', ['Privacy Policy', 'Terms & Conditions', 'Refund Policy', 'Donation Policy', 'Spiritual Disclaimer', 'Cookie Policy']]] as $col)
                    <div class="col-sm-6 col-lg-2 footer-links">
                        <h4 class="gold-text">{{ $col[0] }}</h4>
                        @foreach ($col[1] as $link)
                            <a>{{ $link }}</a>
                        @endforeach
                    </div>
                @endforeach
                <div class="col-lg-3">
                    <h4 class="gold-text">We Accept</h4>
                    <div class="payment-box">
                        <strong>Razorpay</strong>
                        <div><span>VISA</span><span>MC</span><span>RuPay</span><span>UPI</span></div>
                        <small>100% Secure Payments</small>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© {{ date('Y') }} BhaktiDeep. All rights reserved.</p>
                <p>Made with <i class="bi bi-heart-fill"></i> for Devotees</p>
            </div>
        </div>
    </footer> --}}


{{-- <style>
    .horizontal-scroll::-webkit-scrollbar {
        height: 7px;
        /* scrollbar ki motai */
    }

    .horizontal-scroll::-webkit-scrollbar-track {
        background: #f1e8d2;
        /* track color */
    }

    .horizontal-scroll::-webkit-scrollbar-thumb {
        background: #c78d22;
        /* scrollbar color */
        border-radius: 10px;
    }
</style> --}}

@endsection

{{-- @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@endpush --}}
