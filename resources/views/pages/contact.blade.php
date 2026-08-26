@extends('layouts.app')

@section('title', 'Contact BhaktiDeep | Pooja Booking & Live Aarti Support')
@section('description', 'Contact BhaktiDeep for pooja booking, diya offering, hawan, live aarti, sankalp and spiritual service support.')

@push('styles')
    <link href="{{ asset('css/contact.css') }}" rel="stylesheet">
@endpush

@section('body')
{{-- @include('partials.header') --}}

@php
    $contactInfoCards = [
        [
            'icon' => 'bi-telephone-fill',
            'title' => 'Call Support',
            'text' => 'Speak with our team for booking and ritual related help.',
            'value' => '+91 12345 67890',
            'type' => 'phone',
        ],
        [
            'icon' => 'bi-envelope-fill',
            'title' => 'Email Support',
            'text' => 'Send your query and our team will reply as soon as possible.',
            'value' => 'support@bhaktideep.com',
            'type' => 'email',
        ],
        [
            'icon' => 'bi-clock-fill',
            'title' => 'Support Hours',
            'text' => 'We are available for help with live sessions and bookings.',
            'value' => '9:00 AM - 8:00 PM',
            'type' => 'text',
        ],
        [
            'icon' => 'bi-camera-video-fill',
            'title' => 'Live Session Help',
            'text' => 'Need help joining a live aarti or pooja session? Contact us before your session starts.',
            'value' => '',
            'type' => 'none',
        ],
    ];

    $queryTypes = [
        'Pooja Booking',
        'Live Aarti',
        'Diya Offering',
        'Hawan Booking',
        'Personalized Pooja',
        'Payment Help',
        'Technical Support',
        'General Query',
    ];

    $preferredServices = [
        'Diya',
        'Pooja',
        'Hawan',
        'Live Aarti',
        'Personalized Pooja',
        'Not Sure',
    ];

    $supportPoints = [
        ['bi-flower1', 'Help with pooja and hawan booking'],
        ['bi-scroll', 'Guidance for sankalp details'],
        ['bi-broadcast', 'Support for live aarti and session access'],
    ];

    $quickHelpCards = [
        [
            'icon' => 'bi-flower1',
            'title' => 'Want to Book a Pooja?',
            'text' => 'Explore personalized pooja options and choose the ritual that matches your purpose.',
            'button' => 'Book Pooja',
            'route' => 'personalized-pooja',
            'btnClass' => 'btn-saffron',
        ],
        [
            'icon' => 'bi-bell-fill',
            'title' => 'Want to Join Live Aarti?',
            'text' => 'Join live prayers and feel connected from your home.',
            'button' => 'Join Live Aarti',
            'route' => 'live.sessions',
            'btnClass' => 'btn-saffron',
        ],
        [
            'icon' => 'bi-question-circle-fill',
            'title' => 'Not Sure What to Choose?',
            'text' => 'Send your concern and our team will guide you with the right ritual.',
            'button' => 'Ask for Help',
            'route' => null,
            'btnClass' => 'btn-ghost-gold',
            'scrollTo' => 'contact-form',
        ],
    ];
@endphp

<main class="page-shell contact-page">
    <!-- Mandala Background Pattern -->
    <div class="mandala-pattern"></div>

    <!-- Hanging Bells (decorative) -->
    <div class="hanging-bell"><i class="bi bi-bell"></i></div>
    <div class="hanging-bell"><i class="bi bi-bell"></i></div>
    <div class="hanging-bell"><i class="bi bi-bell"></i></div>
    <div class="hanging-bell"><i class="bi bi-bell"></i></div>

    {{-- CONTACT HERO SECTION --}}
    <section class="container contact-hero">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="eyebrow"><i class="bi bi-headset"></i> BhaktiDeep Support</span>
                <h1 class="mt-4">
                    <span>We're Here to Help</span><br>
                    <span class="gold-text">Your Bhakti Journey</span>
                </h1>
                <p class="mt-4">
                    Have a question about pooja booking, live aarti, diya offering, hawan or sankalp? Send us your query and our team will help you with the next step.
                </p>
                <div class="hero-buttons mt-4">
                    <a href="#contact-form" class="btn btn-saffron btn-lg">
                        <i class="bi bi-chat-dots-fill"></i> Send Query
                    </a>
                    <a href="tel:+911234567890" class="btn btn-ghost-gold btn-lg">
                        <i class="bi bi-telephone-fill"></i> Call Now
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="contact-hero-img-wrap">
                    <div class="contact-img-glow"></div>
                    <div class="contact-img-card glass">
                        <img src="{{ asset('assets/temple-hero.jpg') }}" alt="Temple with glowing diya lamp">
                        <div class="contact-img-overlay"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CONTACT INFO CARDS --}}
    <section class="container contact-info-section">
        <div class="contact-info-grid">
            @foreach ($contactInfoCards as $card)
                <div class="contact-info-card">
                    <div class="contact-info-icon">
                        <i class="bi {{ $card['icon'] }}"></i>
                    </div>
                    <h3>{{ $card['title'] }}</h3>
                    <p>{{ $card['text'] }}</p>
                    @if ($card['type'] === 'phone')
                        <div class="contact-info-value">
                            <a href="tel:{{ str_replace(' ', '', $card['value']) }}">{{ $card['value'] }}</a>
                        </div>
                    @elseif ($card['type'] === 'email')
                        <div class="contact-info-value">
                            <a href="mailto:{{ $card['value'] }}">{{ $card['value'] }}</a>
                        </div>
                    @elseif ($card['type'] === 'text')
                        <div class="contact-info-value">{{ $card['value'] }}</div>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    {{-- CONTACT FORM SECTION --}}
    <section class="container contact-form-section" id="contact-form">
        <div class="contact-form-card glass">
            <div class="row g-4 g-xl-5">
                {{-- Left Column --}}
                <div class="col-lg-5">
                    <div class="contact-form-left">
                        <img src="{{ asset('assets/diya.jpg') }}" alt="Sacred diya lamp glowing">
                        <h2>Send Us Your Query</h2>
                        <p>Fill the form and our team will guide you with the right pooja, booking process or live session support.</p>
                        <ul class="support-points">
                            @foreach ($supportPoints as $point)
                                <li>
                                    <i class="bi {{ $point[0] }}"></i>
                                    <span>{{ $point[1] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                {{-- Right Column - Form --}}
                <div class="col-lg-7">
                    <form class="contact-form-right" action="{{ route('contact.submit') }}" method="POST" onsubmit="return handleContactSubmit(event)">
                        @csrf
                        <div class="row g-3">
                            {{-- Full Name --}}
                            <div class="col-sm-6">
                                <label class="form-label">
                                    Full Name <span class="required">*</span>
                                </label>
                                <input type="text" name="full_name" class="form-control" placeholder="Enter your full name" required>
                            </div>

                            {{-- Mobile Number --}}
                            <div class="col-sm-6">
                                <label class="form-label">
                                    Mobile Number <span class="required">*</span>
                                </label>
                                <input type="tel" name="mobile" class="form-control" placeholder="Enter your mobile number" required>
                            </div>

                            {{-- Email Address --}}
                            <div class="col-12">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" placeholder="Enter your email address">
                            </div>

                            {{-- Query Type --}}
                            <div class="col-sm-6">
                                <label class="form-label">
                                    Query Type <span class="required">*</span>
                                </label>
                                <select name="query_type" class="form-select" required>
                                    <option value="" selected disabled>Select query type</option>
                                    @foreach ($queryTypes as $type)
                                        <option value="{{ $type }}">{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Preferred Service --}}
                            <div class="col-sm-6">
                                <label class="form-label">Preferred Service</label>
                                <select name="preferred_service" class="form-select">
                                    <option value="" selected disabled>Select preferred service</option>
                                    @foreach ($preferredServices as $service)
                                        <option value="{{ $service }}">{{ $service }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Message --}}
                            <div class="col-12">
                                <label class="form-label">
                                    Message <span class="required">*</span>
                                </label>
                                <textarea name="message" class="form-control" rows="4" placeholder="Write your message here..." required></textarea>
                            </div>

                            {{-- Submit Button --}}
                            <div class="col-12 mt-2">
                                <button type="submit" class="contact-submit-btn">
                                    <i class="bi bi-send-fill"></i> Send Message
                                </button>
                                <p class="form-helper-text">
                                    <i class="bi bi-shield-check"></i>
                                    Our team will get back to you soon.
                                </p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    {{-- QUICK HELP SECTION --}}
    <section class="container quick-help-section">
        <h2>Need Quick Help?</h2>
        <p>Choose the right path and get started faster.</p>
        <div class="quick-help-grid">
            @foreach ($quickHelpCards as $card)
                <div class="quick-help-card">
                    <div class="quick-help-icon">
                        <i class="bi {{ $card['icon'] }}"></i>
                    </div>
                    <h3>{{ $card['title'] }}</h3>
                    <p>{{ $card['text'] }}</p>
                    @if ($card['route'])
                        <a href="{{ route($card['route']) }}" class="btn {{ $card['btnClass'] }}">
                            {{ $card['button'] }} <i class="bi bi-arrow-right"></i>
                        </a>
                    @else
                        <a href="#contact-form" class="btn {{ $card['btnClass'] }}" onclick="scrollToForm(event)">
                            {{ $card['button'] }} <i class="bi bi-arrow-right"></i>
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    {{-- FINAL CTA SECTION --}}
    <section class="container pb-5">
        <div class="contact-final-cta">
            <div class="cta-badge">
                <i class="bi bi-stars"></i> Begin Your Journey
            </div>
            <h2>
                Start Your Spiritual Journey<br>
                With <span class="gold-text">BhaktiDeep</span>
            </h2>
            <div class="cta-divider">
                <span></span>
                <i class="bi bi-fire"></i>
                <span></span>
            </div>
            <p>Book pooja, offer diya, join live aarti and receive blessings with ease.</p>
            <div class="cta-buttons">
                <a href="{{ route('personalized-pooja') }}" class="btn btn-light btn-lg">
                    <i class="bi bi-heart-fill"></i> Start Bhakti Journey
                </a>
                <a href="{{ route('live.sessions') }}" class="btn btn-outline-light btn-lg">
                    <i class="bi bi-bell-fill"></i> Join Live Aarti
                </a>
            </div>
        </div>
    </section>
</main>

{{-- @include('partials.footer') --}}

@push('scripts')
<script>
function handleContactSubmit(event) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);

    // Show loading state
    const submitBtn = form.querySelector('.contact-submit-btn');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Sending...';
    submitBtn.disabled = true;

    // Simulate form submission (replace with actual AJAX call)
    setTimeout(() => {
        submitBtn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Message Sent!';
        submitBtn.style.background = 'linear-gradient(135deg, #2fb66d, #1a8f4c)';

        // Reset form after 2 seconds
        setTimeout(() => {
            form.reset();
            submitBtn.innerHTML = originalText;
            submitBtn.style.background = '';
            submitBtn.disabled = false;

            // Show success message
            alert('Thank you! Your message has been sent. Our team will contact you soon.');
        }, 2000);
    }, 1500);

    return false;
}

function scrollToForm(event) {
    event.preventDefault();
    const formSection = document.getElementById('contact-form');
    if (formSection) {
        formSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        if (href !== '#') {
            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    });
});
</script>
@endpush

@endsection