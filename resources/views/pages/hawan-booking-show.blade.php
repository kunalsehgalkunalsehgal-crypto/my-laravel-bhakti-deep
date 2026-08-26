@extends('layouts.app')

@section('title', $hawan['name'] . ' - BhaktiDeep')

@push('styles')
    <link href="{{ asset('css/hawan-detail.css') }}" rel="stylesheet">
    <link href="{{ asset('css/light-diya.css') }}" rel="stylesheet">
@endpush

@php
    $enabledHawanTypes = $hawan['types'] ?? [];
    $defaultHawanType = $enabledHawanTypes[0] ?? null;
    $displayHawanPrice = $defaultHawanType['price'] ?? ($hawan['display_price'] ?? $hawan['base_price']);
@endphp

@section('body')
    <!-- Hero Section -->
    {{-- <section class="hawan-hero-section">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <div class="hawan-hero-badge">
                        <i class="bi bi-fire"></i>
                        Sacred Fire Ritual
                    </div>
                    <h1 class="hawan-hero-title">
                        Book a <span class="hawan-highlight">Live {{ $hawan['name'] }}</span><br>
                        From Home
                    </h1>
                    <p class="hawan-hero-desc">{{ $hawan['short_description'] }}</p>
                    <div class="hawan-hero-buttons">
                        <button class="hawan-btn hawan-btn-saffron" onclick="scrollToBooking()">
                            <i class="bi bi-fire me-2"></i>Book This Hawan
                        </button>
                        <button class="hawan-btn hawan-btn-outline-saffron">
                            <i class="bi bi-play-circle me-2"></i>Watch Sample
                        </button>
                    </div>

                    <div class="row g-3 mt-4">
                        <div class="col-6 col-sm-3">
                            <div class="hawan-info-card">
                                <div class="hawan-icon"><i class="bi bi-currency-rupee"></i></div>
                                <h6>Rs.{{ number_format($hawan['base_price']) }}</h6>
                                <p>Base Price</p>
                            </div>
                        </div>
                        <div class="col-6 col-sm-3">
                            <div class="hawan-info-card">
                                <div class="hawan-icon"><i class="bi bi-clock"></i></div>
                                <h6>{{ $hawan['duration'] }}</h6>
                                <p>Duration</p>
                            </div>
                        </div>
                        <div class="col-6 col-sm-3">
                            <div class="hawan-info-card">
                                <div class="hawan-icon"><i class="bi bi-broadcast"></i></div>
                                <h6>{{ $hawan['mode'] }}</h6>
                                <p>Mode</p>
                            </div>
                        </div>
                        <div class="col-6 col-sm-3">
                            <div class="hawan-info-card">
                                <div class="hawan-icon"><i class="bi bi-people"></i></div>
                                <h6>Family</h6>
                                <p>Join Access</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hawan-hero-image-card">
                        <img src="{{ asset($hawan['featured_image']) }}" alt="{{ $hawan['name'] }}">
                        <div class="hawan-live-badge"><i class="bi bi-circle-fill"></i> Booking Open</div>
                        <div class="hawan-hero-image-overlay">
                            <h5>Next Hawan Today</h5>
                            <h4>6:30 PM IST</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
       <section class="container ld-hero">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="eyebrow"><i class="bi bi-fire"></i> LIGHT A VIRTUAL DIYA</span>
                <h1 class="mt-4">
                    <span>Har Deep Mein </span><span class="gold-text">Bhakti</span>
                </h1>
                <p class="mt-4">
                    Light a sacred diya from home — choose Akhand, Festival, Family or Health Diya, add your sankalp and let it glow with mantra and temple ambience for the duration you choose.
                </p>
                <div class="hero-buttons mt-4">
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
                </div>
            </div>
            <div class="col-lg-6">
                <div class="ld-hero-img-wrap">
                    <div class="ld-img-glow"></div>
                    <div class="ld-img-card glass">
                        <img src="{{ asset('assets/diya.jpg') }}" alt="Glowing diya">
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
    </section> --}}
    <section class="ld-hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <!-- Eyebrow -->
                <span class="eyebrow">
                    <i class="bi bi-fire"></i> SACRED FIRE RITUAL
                </span>

                <!-- Title with dynamic Hawan name -->
                <h1 class="mt-4">
                    <span>Book a </span><span class="gold-text">Live {{ $hawan['name'] }}</span><br>
                    <span>From Home</span>
                </h1>

                <!-- Dynamic Description -->
                <p class="mt-4">
                    {{ $hawan['short_description'] }}
                </p>

                <!-- Buttons -->
                <div class="hero-buttons mt-4">
                    <button class="btn btn-saffron btn-lg rounded-pill" onclick="scrollToBooking()">
                        <i class="bi bi-fire"></i> Book This Hawan
                    </button>
                    <button class="btn btn-ghost-gold btn-lg rounded-pill">
                        <i class="bi bi-play-circle"></i> Watch Sample
                    </button>
                </div>

                <!-- Stats Cards (Dynamic Hawan Data) -->
                <div class="row g-3 mt-3 ld-stats">
                    <div class="col-4">
                        <div class="glass rounded-3 px-3 py-2 text-center">
                            <div class="gold-text fw-bold" style="font-family:'Cinzel',serif;font-size:18px;">
                                Rs.{{ number_format((int) $displayHawanPrice) }}
                            </div>
                            <div class="ld-stat-label">Starting Price</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="glass rounded-3 px-3 py-2 text-center">
                            <div class="gold-text fw-bold" style="font-family:'Cinzel',serif;font-size:18px;">
                                {{ $hawan['duration'] }}
                            </div>
                            <div class="ld-stat-label">Duration</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="glass rounded-3 px-3 py-2 text-center">
                            <div class="gold-text fw-bold" style="font-family:'Cinzel',serif;font-size:18px;">
                                {{ $hawan['mode'] }}
                            </div>
                            <div class="ld-stat-label">Mode</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Image with Glow Effect (Light Diya Style) -->
            <div class="col-lg-6">
                <div class="ld-hero-img-wrap">
                    <div class="ld-img-glow"></div>
                    <div class="ld-img-card glass">
                        <img src="{{ asset($hawan['featured_image']) }}" alt="{{ $hawan['name'] }}">
                        <div class="ld-img-overlay"></div>
                        <div class="ld-img-badge">
                            <div class="d-flex align-items-center gap-2">
                                <span class="ld-pulse-dot"></span>
                                <span>Live hawan • sacred fire ritual</span>
                            </div>
                            <span class="ld-akhand-tag">Booking Open</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

    <!-- About & Benefits -->
    <section class="py-5">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-8">
                    <!-- About -->
                    <div class="mb-5">
                        <h2 class="hawan-section-title">About This <span class="hawan-highlight">Hawan</span></h2>
                        <p class="hawan-section-subtitle">{{ $hawan['name'] }} is a powerful Vedic ritual.</p>
                        <div class="hawan-about-box">
                            <p class="mb-0">{{ $hawan['full_description'] }}</p>
                        </div>
                    </div>

                    <!-- Benefits -->
                    <div class="mb-5">
                        <h2 class="hawan-section-title">Benefits of <span class="hawan-highlight">This Hawan</span></h2>
                        <p class="hawan-section-subtitle">Sacred blessings that transform your life</p>
                        <div class="row g-3">
                            @foreach($hawan['benefits'] as $benefit)
                            <div class="col-6 col-md-4">
                                <div class="hawan-benefit-card">
                                    <i class="bi bi-stars"></i>
                                    <h6>{{ $benefit }}</h6>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- <!-- What's Included -->
                    <div class="mb-5">
                        <h2 class="hawan-section-title">What is <span class="hawan-highlight">Included</span></h2>
                        <p class="hawan-section-subtitle">Everything you need for a complete hawan experience</p>
                        <div class="row g-3">
                            @foreach($hawan['included_items'] as $item)
                            <div class="col-md-6">
                                <div class="hawan-included-item">
                                    <i class="bi bi-check-circle-fill"></i>
                                    <span>{{ $item }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div> --}}

                    <!-- Live Preview -->
                    <div class="mb-5">
                        <h2 class="hawan-section-title">Live Hawan <span class="hawan-highlight">Preview</span></h2>
                        <div class="hawan-preview-section">
                            <div class="row g-4 align-items-center">
                                <div class="col-md-7">
                                    <div class="hawan-preview-image">
                                        <img src="{{ asset($hawan['featured_image']) }}" alt="Live Hawan Preview">
                                        <div class="hawan-play-btn"><i class="bi bi-play-fill"></i></div>
                                    </div>
                                    <div class="mt-3">
                                        <span class="hawan-preview-label">Preview Live Hawan</span>
                                        <h4 class="mt-1 hawan-preview-title">{{ $hawan['name'] }} - 47 of 108</h4>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="hawan-ahuti-counter mb-4">
                                        <h6>Ahuti Counter</h6>
                                        <div class="hawan-count">47 <span>of 108</span></div>
                                        <div class="hawan-progress-bar-custom"><div class="hawan-fill"></div></div>
                                        <div class="d-flex justify-content-between hawan-preview-meta">
                                            <span><i class="bi bi-people me-1"></i> Family preview</span>
                                            <span><i class="bi bi-share me-1"></i> Invite</span>
                                        </div>
                                    </div>
                                    <h6 class="hawan-timeline-title">Session Timeline</h6>
                                    @foreach($hawan['session_timeline'] as $index => $timeline)
                                    <div class="hawan-timeline-item">
                                        <div class="hawan-num">{{ $index + 1 }}</div>
                                        <div class="hawan-text">{{ $timeline['title'] }}</div>
                                        <div class="hawan-time">{{ $timeline['duration'] }}</div>
                                    </div>
                                    @endforeach
                                    <button class="hawan-btn hawan-btn-gold w-100 mt-3" onclick="scrollToBooking()">
                                        Book to Join Live Hawan <i class="bi bi-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>









                     <div class="hawan-stepper-wrapper" id="bookingSection">
                        <!-- Stepper Header -->
                        <div class="hawan-stepper-header">
                            <div class="hawan-step-item hawan-active" data-step="1">
                                <div class="hawan-step-circle">1</div>
                                <span class="hawan-step-label">Hawan Details</span>
                            </div>
                            <div class="hawan-step-item" data-step="2">
                                <div class="hawan-step-circle">2</div>
                                <span class="hawan-step-label">Sankalp</span>
                            </div>
                            <div class="hawan-step-item" data-step="3">
                                <div class="hawan-step-circle">3</div>
                                <span class="hawan-step-label">Hawan Type</span>
                            </div>
                            <div class="hawan-step-item" data-step="4">
                                <div class="hawan-step-circle">4</div>
                                <span class="hawan-step-label">Slot</span>
                            </div>
                            <div class="hawan-step-item" data-step="5">
                                <div class="hawan-step-circle">5</div>
                                <span class="hawan-step-label">Review</span>
                            </div>
                        </div>

                        <!-- Step 1: Hawan Details -->
                        <div class="hawan-step-content hawan-active" id="step1">
                            <h3 class="hawan-step-title">Selected Hawan Details</h3>
                            <p class="hawan-step-desc">Confirm your selected hawan and proceed to sankalp</p>

                            <div class="hawan-hawan-confirm-box mb-4">
                                <div class="d-flex align-items-center gap-4 flex-wrap">
                                    <img src="{{ asset($hawan['featured_image']) }}" class="hawan-hawan-confirm-img" alt="{{ $hawan['name'] }}">
                                    <div>
                                        <h4 class="hawan-hawan-confirm-title">{{ $hawan['name'] }}</h4>
                                        <p class="hawan-hawan-confirm-desc">{{ $hawan['short_description'] }}</p>
                                        <div class="d-flex gap-3 flex-wrap">
                                            <span class="hawan-badge-saffron"><i class="bi bi-currency-rupee me-1"></i>Rs.{{ number_format((int) $displayHawanPrice) }}</span>
                                            <span class="hawan-badge-gold"><i class="bi bi-clock me-1"></i>{{ $hawan['duration'] }}</span>
                                            <span class="hawan-badge-saffron"><i class="bi bi-broadcast me-1"></i>{{ $hawan['mode'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button class="hawan-btn hawan-btn-saffron w-100" onclick="nextStep(2)">
                                Continue to Sankalp <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                        </div>

                        <!-- Step 2: Sankalp Details -->
                        <div class="hawan-step-content" id="step2">
                            <h3 class="hawan-step-title">Sankalp Details</h3>
                            <p class="hawan-step-desc">Enter your details for personalized sankalp</p>

                            <form id="sankalpForm" class="row g-3">
                                <div class="col-md-6">
                                    <label class="hawan-form-label">Full Name *</label>
                                    <input type="text" class="hawan-form-control" name="full_name" placeholder="Enter your full name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="hawan-form-label">Gotra</label>
                                    <input type="text" class="hawan-form-control" name="gotra" placeholder="Enter your gotra">
                                </div>
                                <div class="col-md-6">
                                    <label class="hawan-form-label">Date of Birth</label>
                                    <input type="date" class="hawan-form-control" name="dob">
                                </div>
                                <div class="col-md-6">
                                    <label class="hawan-form-label">Birth Time</label>
                                    <input type="time" class="hawan-form-control" name="birth_time">
                                </div>
                                <div class="col-md-6">
                                    <label class="hawan-form-label">Mobile Number *</label>
                                    <input type="tel" class="hawan-form-control" name="mobile" id="sankalpMobile" placeholder="Enter mobile number" maxlength="10">
                                </div>
                                <div class="col-md-6">
                                    <label class="hawan-form-label">Birth Place</label>
                                    <input type="text" class="hawan-form-control" name="birth_place" placeholder="City, State">
                                </div>
                                <div class="col-md-6">
                                    <label class="hawan-form-label">Father's Name</label>
                                    <input type="text" class="hawan-form-control" name="father_name" placeholder="Enter father's name">
                                </div>
                                <div class="col-md-6">
                                    <label class="hawan-form-label">Mother's Name</label>
                                    <input type="text" class="hawan-form-control" name="mother_name" placeholder="Enter mother's name">
                                </div>
                                <div class="col-md-6">
                                    <label class="hawan-form-label">Spouse Name (if married)</label>
                                    <input type="text" class="hawan-form-control" name="spouse_name" placeholder="Enter spouse name">
                                </div>
                                <div class="col-12">
                                    <label class="hawan-form-label">Family Member Names</label>
                                    <textarea class="hawan-form-control" name="family_members" rows="2" placeholder="Enter family member names separated by comma"></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="hawan-form-label">Purpose *</label>
                                    <div class="d-flex flex-wrap">
                                        <span class="hawan-purpose-tag" onclick="selectPurpose(this)"><i class="bi bi-heart-pulse"></i> Health</span>
                                        <span class="hawan-purpose-tag" onclick="selectPurpose(this)"><i class="bi bi-shield-check"></i> Protection</span>
                                        <span class="hawan-purpose-tag" onclick="selectPurpose(this)"><i class="bi bi-emoji-smile"></i> Peace</span>
                                        <span class="hawan-purpose-tag" onclick="selectPurpose(this)"><i class="bi bi-people-fill"></i> Family Well-being</span>
                                        <span class="hawan-purpose-tag" onclick="selectPurpose(this)"><i class="bi bi-briefcase"></i> Business</span>
                                        <span class="hawan-purpose-tag" onclick="selectPurpose(this)"><i class="bi bi-graph-up"></i> Career</span>
                                        <span class="hawan-purpose-tag" onclick="selectPurpose(this)"><i class="bi bi-heart"></i> Marriage</span>
                                        <span class="hawan-purpose-tag" onclick="selectPurpose(this)"><i class="bi bi-stars"></i> Spiritual Growth</span>
                                        <span class="hawan-purpose-tag" onclick="selectPurpose(this)"><i class="bi bi-three-dots"></i> Other</span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="hawan-form-label">Mannokamna / Special Message</label>
                                    <textarea class="hawan-form-control" name="mannokamna" rows="3" placeholder="Enter your special wish or message for the hawan"></textarea>
                                </div>
                            </form>

                            <div class="d-flex gap-3 mt-4">
                                <button class="hawan-btn hawan-btn-outline-saffron" onclick="prevStep(1)">
                                    <i class="bi bi-arrow-left me-2"></i>Back
                                </button>
                                <button class="hawan-btn hawan-btn-saffron flex-fill" onclick="nextStep(3)">
                                    Continue to Hawan Type <i class="bi bi-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 3: Hawan Type & Offering -->
                        <div class="hawan-step-content" id="step3">
                            <h3 class="hawan-step-title">Choose Hawan Type & Offering</h3>
                            <p class="hawan-step-desc">Select Samuhik or Special Hawan and add optional dakshina if you wish</p>

                            <div class="row g-3 mb-4">
                                @foreach($enabledHawanTypes as $index => $type)
                                    <div class="col-md-6">
                                        <div class="hawan-mode-card hawan-package-card {{ $index === 0 ? 'hawan-selected' : '' }}"
                                             data-hawan-type="{{ $type['key'] }}"
                                             onclick="selectPackage(this, @json($type['key']), @json($type['title']), {{ (int) $type['price'] }})">
                                            <i class="bi {{ $type['icon'] }}"></i>
                                            <h6>{{ $type['title'] }}</h6>
                                            <p>{{ $type['description'] }}</p>
                                            <strong>Rs.{{ $type['formatted_price'] }}</strong>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <h5 class="hawan-review-box-title mb-3">Add Custom Offering (Optional)</h5>
                            <div class="row g-3 mb-4">
                                @foreach($hawan['donation_options'] as $index => $option)
                                <div class="col-6 col-md-3">
                                    <div class="hawan-donation-option" onclick="selectDonation(this, {{ $option }})">
                                        <div class="hawan-amount">Rs.{{ number_format($option) }}</div>
                                        <div class="hawan-label">{{ $index == 0 ? 'Blessing' : ($index == 1 ? 'Support' : ($index == 2 ? 'Popular' : 'Premium')) }}</div>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            <div class="mb-4">
                                <label class="hawan-form-label">Custom Amount (Optional)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rs.</span>
                                    <input type="number" class="hawan-form-control" id="customDonation" placeholder="Enter custom amount" onchange="updateCustomDonation(this)">
                                </div>
                            </div>

                            <div class="d-flex gap-3">
                                <button class="hawan-btn hawan-btn-outline-saffron" onclick="prevStep(2)">
                                    <i class="bi bi-arrow-left me-2"></i>Back
                                </button>
                                <button class="hawan-btn hawan-btn-saffron flex-fill" onclick="nextStep(4)">
                                    Continue to Slot <i class="bi bi-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 4: Date & Slot -->
                        <div class="hawan-step-content" id="step4">
                            <h3 class="hawan-step-title">Date & Slot</h3>
                            <p class="hawan-step-desc">Select your preferred date and time for the hawan</p>

                            <div class="mb-4">
                                <label class="hawan-form-label">Select Date *</label>
                                <input type="date" class="hawan-form-control" id="hawanDate" onchange="updateSummary()" min="{{ date('Y-m-d') }}">
                            </div>

                            <div class="mb-4">
                                <label class="hawan-form-label">Select Time Slot *</label>
                                <div class="row g-3">
                                    @foreach($hawan['available_slots'] as $index => $slot)
                                    <div class="col-md-4">
                                        <div class="hawan-slot-card" onclick="selectSlot(this, '{{ $slot }}')">
                                            <div class="hawan-time">
                                                <i class="bi bi-{{ $index == 0 ? 'sunrise' : ($index == 1 ? 'sun' : 'sunset') }} me-2"></i>
                                                {{ $slot }}
                                            </div>
                                            <div class="hawan-period">{{ $index == 0 ? 'Morning' : ($index == 1 ? 'Afternoon' : 'Evening') }} Session</div>
                                            <div class="hawan-availability"><i class="bi bi-check-circle me-1"></i>Available</div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="d-flex gap-3">
                                <button class="hawan-btn hawan-btn-outline-saffron" onclick="prevStep(3)">
                                    <i class="bi bi-arrow-left me-2"></i>Back
                                </button>
                                <button class="hawan-btn hawan-btn-saffron flex-fill" onclick="choosePandit()">
                                    Choose Pandit <i class="bi bi-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 5: Review & Pay -->
                        <div class="hawan-step-content" id="step5">
                            <h3 class="hawan-step-title">Review & Pay</h3>
                            <p class="hawan-step-desc">Review your booking details and complete payment</p>

                            <div class="hawan-review-box mb-4">
                                <h5 class="hawan-review-box-title">Booking Summary</h5>
                                <div class="hawan-summary-row"><span class="hawan-label">Hawan: </span><span class="hawan-value"> {{ $hawan['name'] }}</span></div>
                                <div class="hawan-summary-row"><span class="hawan-label">Pandit: </span><span class="hawan-value">{{ $selectedPandit ? ($selectedPandit->pandit_name ?: $selectedPandit->full_name) : 'Please select pandit' }}</span></div>
                                <div class="hawan-summary-row"><span class="hawan-label">Hawan Type: </span><span class="hawan-value" id="reviewPackage">{{ $defaultHawanType['title'] ?? '-' }}</span></div>
                                <div class="hawan-summary-row"><span class="hawan-label">Name:</span><span class="hawan-value" id="reviewName">-</span></div>
                                <div class="hawan-summary-row"><span class="hawan-label">Purpose:</span><span class="hawan-value" id="reviewPurpose">-</span></div>
                                <div class="hawan-summary-row"><span class="hawan-label">Date:</span><span class="hawan-value" id="reviewDate">-</span></div>
                                <div class="hawan-summary-row"><span class="hawan-label">Time:</span><span class="hawan-value" id="reviewSlot">-</span></div>
                                <div class="hawan-summary-row"><span class="hawan-label">Hawan Type Price:</span><span class="hawan-value" id="reviewPackageAmount">Rs.{{ number_format((int) $displayHawanPrice) }}</span></div>
                                <div class="hawan-summary-row"><span class="hawan-label">Dakshina:</span><span class="hawan-value" id="reviewDakshina">Rs.0</span></div>
                                <div class="hawan-summary-row hawan-total"><span class="hawan-label">Total Amount:</span><span class="hawan-value" id="reviewTotal">Rs.{{ number_format((int) $displayHawanPrice) }}</span></div>
                            </div>

                            <div class="hawan-login-box mb-4">
                                <h5 class="hawan-login-box-title"><i class="bi bi-shield-lock me-2"></i>Secure Login Required</h5>
                                <p class="hawan-login-box-desc">Please verify your mobile number to proceed with payment</p>

                                <div id="mobileSection">
                                    <label class="hawan-form-label">Mobile Number</label>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text">+91</span>
                                        <input type="tel" class="hawan-form-control" id="mobileNumber" placeholder="Enter your mobile number" maxlength="10">
                                    </div>
                                    <button class="hawan-btn hawan-btn-saffron w-100" onclick="sendOTP()">
                                        <i class="bi bi-send me-2"></i>Send OTP
                                    </button>
                                </div>

                                <div class="hawan-otp-section" id="otpSection">
                                    <div class="text-center mb-3">
                                        <i class="bi bi-check-circle-fill hawan-otp-success-icon"></i>
                                        <p class="mt-2 mb-0 hawan-otp-sent-text">OTP sent to <strong id="otpMobile">+91 XXXXX XXXXX</strong></p>
                                    </div>
                                    <label class="hawan-form-label text-center d-block">Enter 6 digit OTP</label>
                                    <div class="hawan-otp-boxes">
                                        <input type="text" class="hawan-otp-box" maxlength="1" oninput="moveOTP(this, 0)" onkeydown="moveOTPBack(event, 0)">
                                        <input type="text" class="hawan-otp-box" maxlength="1" oninput="moveOTP(this, 1)" onkeydown="moveOTPBack(event, 1)">
                                        <input type="text" class="hawan-otp-box" maxlength="1" oninput="moveOTP(this, 2)" onkeydown="moveOTPBack(event, 2)">
                                        <input type="text" class="hawan-otp-box" maxlength="1" oninput="moveOTP(this, 3)" onkeydown="moveOTPBack(event, 3)">
                                        <input type="text" class="hawan-otp-box" maxlength="1" oninput="moveOTP(this, 4)" onkeydown="moveOTPBack(event, 4)">
                                        <input type="text" class="hawan-otp-box" maxlength="1" oninput="moveOTP(this, 5)" onkeydown="moveOTPBack(event, 5)">
                                    </div>
                                    <button class="hawan-btn-verify mb-3" onclick="verifyOTP()">Verify & Proceed to Pay</button>
                                    <div class="text-center">
                                        <a href="#" class="hawan-otp-link" onclick="resendOTP()">Resend OTP</a>
                                        <span class="mx-2" style="color: var(--glass-border);">|</span>
                                        <a href="#" class="hawan-otp-link-muted" onclick="changeMobile()">Change Mobile</a>
                                    </div>
                                </div>
                            </div>

                            <label class="consent-checkbox mb-3">
                                <input type="checkbox" id="hawanConsentCheckbox" onchange="checkPaymentReadiness()">
                                I agree to BhaktiDeep's terms and consent to my sankalp being used for this hawan booking.
                            </label>

                            <button class="hawan-btn hawan-btn-saffron w-100" id="payButton" disabled onclick="proceedToPay()">
                                <i class="bi bi-credit-card me-2"></i>Confirm & Pay
                            </button>

                            <div class="d-flex gap-3 mt-3">
                                <button class="hawan-btn hawan-btn-outline-saffron" onclick="prevStep(4)">
                                    <i class="bi bi-arrow-left me-2"></i>Back
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side - Order Summary (Sticky) -->
                <div class="col-lg-4">
                    <div class="hawan-order-summary" id="orderSummary">
                        <h5><i class="bi bi-receipt me-2"></i>Order Summary</h5>
                        <div class="hawan-summary-row">
                            <span class="hawan-label">Hawan:</span>
                            <span class="hawan-value" id="summaryHawan">{{ $hawan['name'] }}</span>
                        </div>
                        <div class="hawan-summary-row">
                            <span class="hawan-label">Purpose:</span>
                            <span class="hawan-value" id="summaryPurpose">-</span>
                        </div>
                        <div class="hawan-summary-row">
                            <span class="hawan-label">Date:</span>
                            <span class="hawan-value" id="summaryDate">-</span>
                        </div>
                        <div class="hawan-summary-row">
                            <span class="hawan-label">Slot:</span>
                            <span class="hawan-value" id="summarySlot">-</span>
                        </div>
                        <div class="hawan-summary-row">
                            <span class="hawan-label">Hawan Type:</span>
                            <span class="hawan-value" id="summaryPackage">{{ $defaultHawanType['title'] ?? '-' }}</span>
                        </div>
                        <div class="hawan-summary-row">
                            <span class="hawan-label">Hawan Type Price:</span>
                            <span class="hawan-value" id="summaryBase">Rs.{{ number_format((int) $displayHawanPrice) }}</span>
                        </div>
                        <div class="hawan-summary-row">
                            <span class="hawan-label">Dakshina:</span>
                            <span class="hawan-value" id="summaryDakshina">Rs.0</span>
                        </div>
                        <div class="hawan-summary-row hawan-total">
                            <span class="hawan-label">Total:</span>
                            <span class="hawan-value" id="summaryTotal">Rs.{{ number_format((int) $displayHawanPrice) }}</span>
                        </div>
                        <div class="hawan-trust-strip">
                            <div class="hawan-trust-item"><i class="bi bi-check-circle-fill"></i> Trusted Pandit Ji</div>
                            <div class="hawan-trust-item"><i class="bi bi-check-circle-fill"></i> Secure Payment</div>
                            <div class="hawan-trust-item"><i class="bi bi-check-circle-fill"></i> WhatsApp Updates</div>
                            <div class="hawan-trust-item"><i class="bi bi-check-circle-fill"></i> Family Join</div>
                            <div class="hawan-trust-item"><i class="bi bi-check-circle-fill"></i> Replay Available</div>
                            {{-- <div class="hawan-trust-item"><i class="bi bi-check-circle-fill"></i> Digital Certificate</div> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Booking Stepper Section -->
    {{-- <section class="py-5" id="bookingSection">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="hawan-section-title">Complete Your <span class="hawan-highlight">Booking</span></h2>
                <p class="hawan-section-subtitle">Follow the steps to book your sacred hawan</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="hawan-stepper-wrapper">
                        <!-- Stepper Header -->
                        <div class="hawan-stepper-header">
                            <div class="hawan-step-item hawan-active" data-step="1">
                                <div class="hawan-step-circle">1</div>
                                <span class="hawan-step-label">Hawan Details</span>
                            </div>
                            <div class="hawan-step-item" data-step="2">
                                <div class="hawan-step-circle">2</div>
                                <span class="hawan-step-label">Sankalp</span>
                            </div>
                            <div class="hawan-step-item" data-step="3">
                                <div class="hawan-step-circle">3</div>
                                <span class="hawan-step-label">Donation</span>
                            </div>
                            <div class="hawan-step-item" data-step="4">
                                <div class="hawan-step-circle">4</div>
                                <span class="hawan-step-label">Date & Slot</span>
                            </div>
                            <div class="hawan-step-item" data-step="5">
                                <div class="hawan-step-circle">5</div>
                                <span class="hawan-step-label">Review & Pay</span>
                            </div>
                        </div>

                        <!-- Step 1: Hawan Details -->
                        <div class="hawan-step-content hawan-active" id="step1">
                            <h3 class="hawan-step-title">Selected Hawan Details</h3>
                            <p class="hawan-step-desc">Confirm your selected hawan and proceed to sankalp</p>

                            <div class="hawan-hawan-confirm-box mb-4">
                                <div class="d-flex align-items-center gap-4 flex-wrap">
                                    <img src="{{ asset($hawan['featured_image']) }}" class="hawan-hawan-confirm-img" alt="{{ $hawan['name'] }}">
                                    <div>
                                        <h4 class="hawan-hawan-confirm-title">{{ $hawan['name'] }}</h4>
                                        <p class="hawan-hawan-confirm-desc">{{ $hawan['short_description'] }}</p>
                                        <div class="d-flex gap-3 flex-wrap">
                                            <span class="hawan-badge-saffron"><i class="bi bi-currency-rupee me-1"></i>Rs.{{ number_format($hawan['base_price']) }}</span>
                                            <span class="hawan-badge-gold"><i class="bi bi-clock me-1"></i>{{ $hawan['duration'] }}</span>
                                            <span class="hawan-badge-saffron"><i class="bi bi-broadcast me-1"></i>{{ $hawan['mode'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button class="hawan-btn hawan-btn-saffron w-100" onclick="nextStep(2)">
                                Continue to Sankalp <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                        </div>

                        <!-- Step 2: Sankalp Details -->
                        <div class="hawan-step-content" id="step2">
                            <h3 class="hawan-step-title">Sankalp Details</h3>
                            <p class="hawan-step-desc">Enter your details for personalized sankalp</p>

                            <form id="sankalpForm" class="row g-3">
                                <div class="col-md-6">
                                    <label class="hawan-form-label">Full Name *</label>
                                    <input type="text" class="hawan-form-control" name="full_name" placeholder="Enter your full name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="hawan-form-label">Gotra</label>
                                    <input type="text" class="hawan-form-control" name="gotra" placeholder="Enter your gotra">
                                </div>
                                <div class="col-md-6">
                                    <label class="hawan-form-label">Date of Birth</label>
                                    <input type="date" class="hawan-form-control" name="dob">
                                </div>
                                <div class="col-md-6">
                                    <label class="hawan-form-label">Birth Time</label>
                                    <input type="time" class="hawan-form-control" name="birth_time">
                                </div>
                                <div class="col-md-6">
                                    <label class="hawan-form-label">Birth Place</label>
                                    <input type="text" class="hawan-form-control" name="birth_place" placeholder="City, State">
                                </div>
                                <div class="col-md-6">
                                    <label class="hawan-form-label">Father's Name</label>
                                    <input type="text" class="hawan-form-control" name="father_name" placeholder="Enter father's name">
                                </div>
                                <div class="col-md-6">
                                    <label class="hawan-form-label">Mother's Name</label>
                                    <input type="text" class="hawan-form-control" name="mother_name" placeholder="Enter mother's name">
                                </div>
                                <div class="col-md-6">
                                    <label class="hawan-form-label">Spouse Name (if married)</label>
                                    <input type="text" class="hawan-form-control" name="spouse_name" placeholder="Enter spouse name">
                                </div>
                                <div class="col-12">
                                    <label class="hawan-form-label">Family Member Names</label>
                                    <textarea class="hawan-form-control" name="family_members" rows="2" placeholder="Enter family member names separated by comma"></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="hawan-form-label">Purpose *</label>
                                    <div class="d-flex flex-wrap">
                                        <span class="hawan-purpose-tag" onclick="selectPurpose(this)"><i class="bi bi-heart-pulse"></i> Health</span>
                                        <span class="hawan-purpose-tag" onclick="selectPurpose(this)"><i class="bi bi-shield-check"></i> Protection</span>
                                        <span class="hawan-purpose-tag" onclick="selectPurpose(this)"><i class="bi bi-emoji-smile"></i> Peace</span>
                                        <span class="hawan-purpose-tag" onclick="selectPurpose(this)"><i class="bi bi-people-fill"></i> Family Well-being</span>
                                        <span class="hawan-purpose-tag" onclick="selectPurpose(this)"><i class="bi bi-briefcase"></i> Business</span>
                                        <span class="hawan-purpose-tag" onclick="selectPurpose(this)"><i class="bi bi-graph-up"></i> Career</span>
                                        <span class="hawan-purpose-tag" onclick="selectPurpose(this)"><i class="bi bi-heart"></i> Marriage</span>
                                        <span class="hawan-purpose-tag" onclick="selectPurpose(this)"><i class="bi bi-stars"></i> Spiritual Growth</span>
                                        <span class="hawan-purpose-tag" onclick="selectPurpose(this)"><i class="bi bi-three-dots"></i> Other</span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="hawan-form-label">Mannokamna / Special Message</label>
                                    <textarea class="hawan-form-control" name="mannokamna" rows="3" placeholder="Enter your special wish or message for the hawan"></textarea>
                                </div>
                            </form>

                            <div class="d-flex gap-3 mt-4">
                                <button class="hawan-btn hawan-btn-outline-saffron" onclick="prevStep(1)">
                                    <i class="bi bi-arrow-left me-2"></i>Back
                                </button>
                                <button class="hawan-btn hawan-btn-saffron flex-fill" onclick="nextStep(3)">
                                    Continue to Donation <i class="bi bi-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 3: Donation -->
                        <div class="hawan-step-content" id="step3">
                            <h3 class="hawan-step-title">Donation / Dakshina</h3>
                            <p class="hawan-step-desc">Your dakshina supports hawan samagri, pandit ji seva, temple seva and spiritual service</p>

                            <div class="hawan-info-alert mb-4">
                                <p class="mb-0">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Donation is optional. Base hawan amount will always be charged.
                                </p>
                            </div>

                            <div class="row g-3 mb-4">
                                @foreach($hawan['donation_options'] as $index => $option)
                                <div class="col-6 col-md-3">
                                    <div class="hawan-donation-option {{ $index == 2 ? 'hawan-selected' : '' }}" onclick="selectDonation(this, {{ $option }})">
                                        <div class="hawan-amount">Rs.{{ number_format($option) }}</div>
                                        <div class="hawan-label">{{ $index == 0 ? 'Blessing' : ($index == 1 ? 'Support' : ($index == 2 ? 'Popular' : 'Premium')) }}</div>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            <div class="mb-4">
                                <label class="hawan-form-label">Custom Amount (Optional)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rs.</span>
                                    <input type="number" class="hawan-form-control" id="customDonation" placeholder="Enter custom amount" onchange="updateCustomDonation(this)">
                                </div>
                            </div>

                            <div class="d-flex gap-3">
                                <button class="hawan-btn hawan-btn-outline-saffron" onclick="prevStep(2)">
                                    <i class="bi bi-arrow-left me-2"></i>Back
                                </button>
                                <button class="hawan-btn hawan-btn-saffron flex-fill" onclick="nextStep(4)">
                                    Continue to Date & Slot <i class="bi bi-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 4: Date & Slot -->
                        <div class="hawan-step-content" id="step4">
                            <h3 class="hawan-step-title">Date & Slot</h3>
                            <p class="hawan-step-desc">Select your preferred date and time for the hawan</p>

                            <div class="mb-4">
                                <label class="hawan-form-label">Select Date *</label>
                                <input type="date" class="hawan-form-control" id="hawanDate" onchange="updateSummary()" min="{{ date('Y-m-d') }}">
                            </div>

                            <div class="mb-4">
                                <label class="hawan-form-label">Select Time Slot *</label>
                                <div class="row g-3">
                                    @foreach($hawan['available_slots'] as $index => $slot)
                                    <div class="col-md-4">
                                        <div class="hawan-slot-card" onclick="selectSlot(this, '{{ $slot }}')">
                                            <div class="hawan-time">
                                                <i class="bi bi-{{ $index == 0 ? 'sunrise' : ($index == 1 ? 'sun' : 'sunset') }} me-2"></i>
                                                {{ $slot }}
                                            </div>
                                            <div class="hawan-period">{{ $index == 0 ? 'Morning' : ($index == 1 ? 'Afternoon' : 'Evening') }} Session</div>
                                            <div class="hawan-availability"><i class="bi bi-check-circle me-1"></i>Available</div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="hawan-form-label">Select Mode *</label>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="hawan-mode-card" onclick="selectMode(this, 'Live Hawan')">
                                            <i class="bi bi-broadcast"></i>
                                            <h6>Live Hawan</h6>
                                            <p>Watch live session only</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="hawan-mode-card hawan-selected" onclick="selectMode(this, 'Live + Replay')">
                                            <i class="bi bi-camera-video"></i>
                                            <h6>Live + Replay</h6>
                                            <p>Live + 7 days replay access</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="hawan-mode-card" onclick="selectMode(this, 'Family Join')">
                                            <i class="bi bi-people"></i>
                                            <h6>Family Join</h6>
                                            <p>Live + Replay + Family access</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-3">
                                <button class="hawan-btn hawan-btn-outline-saffron" onclick="prevStep(3)">
                                    <i class="bi bi-arrow-left me-2"></i>Back
                                </button>
                                <button class="hawan-btn hawan-btn-saffron flex-fill" onclick="choosePandit()">
                                    Choose Pandit <i class="bi bi-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 5: Review & Pay -->
                        <div class="hawan-step-content" id="step5">
                            <h3 class="hawan-step-title">Review & Pay</h3>
                            <p class="hawan-step-desc">Review your booking details and complete payment</p>

                            <div class="hawan-review-box mb-4">
                                <h5 class="hawan-review-box-title">Booking Summary</h5>
                                <div class="hawan-summary-row"><span class="hawan-label">Hawan</span><span class="hawan-value">{{ $hawan['name'] }}</span></div>
                                <div class="hawan-summary-row"><span class="hawan-label">Pandit</span><span class="hawan-value">{{ $selectedPandit ? ($selectedPandit->pandit_name ?: $selectedPandit->full_name) : 'Please select pandit' }}</span></div>
                                <div class="hawan-summary-row"><span class="hawan-label">Date</span><span class="hawan-value" id="reviewDate">-</span></div>
                                <div class="hawan-summary-row"><span class="hawan-label">Time</span><span class="hawan-value" id="reviewSlot">-</span></div>
                                <div class="hawan-summary-row"><span class="hawan-label">Mode</span><span class="hawan-value" id="reviewMode">-</span></div>
                                <div class="hawan-summary-row"><span class="hawan-label">Base Amount</span><span class="hawan-value">Rs.{{ number_format($hawan['base_price']) }}</span></div>
                                <div class="hawan-summary-row"><span class="hawan-label">Dakshina</span><span class="hawan-value" id="reviewDakshina">Rs.2,100</span></div>
                                <div class="hawan-summary-row hawan-total"><span class="hawan-label">Total Amount</span><span class="hawan-value" id="reviewTotal">Rs.4,201</span></div>
                            </div>

                            <div class="hawan-login-box mb-4">
                                <h5 class="hawan-login-box-title"><i class="bi bi-shield-lock me-2"></i>Secure Login Required</h5>
                                <p class="hawan-login-box-desc">Please verify your mobile number to proceed with payment</p>

                                <div id="mobileSection">
                                    <label class="hawan-form-label">Mobile Number</label>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text">+91</span>
                                        <input type="tel" class="hawan-form-control" id="mobileNumber" placeholder="Enter your mobile number" maxlength="10">
                                    </div>
                                    <button class="hawan-btn hawan-btn-saffron w-100" onclick="sendOTP()">
                                        <i class="bi bi-send me-2"></i>Send OTP
                                    </button>
                                </div>

                                <div class="hawan-otp-section" id="otpSection">
                                    <div class="text-center mb-3">
                                        <i class="bi bi-check-circle-fill hawan-otp-success-icon"></i>
                                        <p class="mt-2 mb-0 hawan-otp-sent-text">OTP sent to <strong id="otpMobile">+91 XXXXX XXXXX</strong></p>
                                    </div>
                                    <label class="hawan-form-label text-center d-block">Enter 6 digit OTP</label>
                                    <div class="hawan-otp-boxes">
                                        <input type="text" class="hawan-otp-box" maxlength="1" oninput="moveOTP(this, 0)" onkeydown="moveOTPBack(event, 0)">
                                        <input type="text" class="hawan-otp-box" maxlength="1" oninput="moveOTP(this, 1)" onkeydown="moveOTPBack(event, 1)">
                                        <input type="text" class="hawan-otp-box" maxlength="1" oninput="moveOTP(this, 2)" onkeydown="moveOTPBack(event, 2)">
                                        <input type="text" class="hawan-otp-box" maxlength="1" oninput="moveOTP(this, 3)" onkeydown="moveOTPBack(event, 3)">
                                        <input type="text" class="hawan-otp-box" maxlength="1" oninput="moveOTP(this, 4)" onkeydown="moveOTPBack(event, 4)">
                                        <input type="text" class="hawan-otp-box" maxlength="1" oninput="moveOTP(this, 5)" onkeydown="moveOTPBack(event, 5)">
                                    </div>
                                    <button class="hawan-btn-verify mb-3" onclick="verifyOTP()">Verify & Proceed to Pay</button>
                                    <div class="text-center">
                                        <a href="#" class="hawan-otp-link" onclick="resendOTP()">Resend OTP</a>
                                        <span class="mx-2" style="color: var(--glass-border);">|</span>
                                        <a href="#" class="hawan-otp-link-muted" onclick="changeMobile()">Change Mobile</a>
                                    </div>
                                </div>
                            </div>

                            <button class="hawan-btn hawan-btn-saffron w-100" id="payButton" style="display: none;" onclick="proceedToPay()">
                                <i class="bi bi-credit-card me-2"></i>Proceed to Pay
                            </button>

                            <div class="d-flex gap-3 mt-3">
                                <button class="hawan-btn hawan-btn-outline-saffron" onclick="prevStep(4)">
                                    <i class="bi bi-arrow-left me-2"></i>Back
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side - Order Summary (Sticky) -->
                <div class="col-lg-4">
                    <div class="hawan-order-summary" style="position: sticky; top: 100px;">
                        <h5><i class="bi bi-receipt me-2"></i>Order Summary</h5>
                        <div class="hawan-summary-row">
                            <span class="hawan-label">Hawan</span>
                            <span class="hawan-value">{{ $hawan['name'] }}</span>
                        </div>
                        <div class="hawan-summary-row">
                            <span class="hawan-label">Purpose</span>
                            <span class="hawan-value" id="summaryPurpose2">-</span>
                        </div>
                        <div class="hawan-summary-row">
                            <span class="hawan-label">Date</span>
                            <span class="hawan-value" id="summaryDate2">-</span>
                        </div>
                        <div class="hawan-summary-row">
                            <span class="hawan-label">Slot</span>
                            <span class="hawan-value" id="summarySlot2">-</span>
                        </div>
                        <div class="hawan-summary-row">
                            <span class="hawan-label">Mode</span>
                            <span class="hawan-value" id="summaryMode2">-</span>
                        </div>
                        <div class="hawan-summary-row">
                            <span class="hawan-label">Base Amount</span>
                            <span class="hawan-value">Rs.{{ number_format($hawan['base_price']) }}</span>
                        </div>
                        <div class="hawan-summary-row">
                            <span class="hawan-label">Dakshina</span>
                            <span class="hawan-value" id="summaryDakshina2">Rs.0</span>
                        </div>
                        <div class="hawan-summary-row hawan-total">
                            <span class="hawan-label">Total</span>
                            <span class="hawan-value" id="summaryTotal2">Rs.{{ number_format($hawan['base_price']) }}</span>
                        </div>
                        <div class="hawan-trust-strip">
                            <div class="hawan-trust-item"><i class="bi bi-check-circle-fill"></i> Trusted Pandit Ji</div>
                            <div class="hawan-trust-item"><i class="bi bi-check-circle-fill"></i> Secure Payment</div>
                            <div class="hawan-trust-item"><i class="bi bi-check-circle-fill"></i> WhatsApp Updates</div>
                            <div class="hawan-trust-item"><i class="bi bi-check-circle-fill"></i> Family Join</div>
                            <div class="hawan-trust-item"><i class="bi bi-check-circle-fill"></i> Replay Available</div>
                            <div class="hawan-trust-item"><i class="bi bi-check-circle-fill"></i> Digital Certificate</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section> --}}
@endsection

@push('scripts')
<script>
    let currentStep = 1;
    const enabledHawanTypes = @json($enabledHawanTypes);
    let selectedHawanType = @json($defaultHawanType['key'] ?? null);
    let selectedPackage = @json($defaultHawanType['title'] ?? '');
    let selectedPackagePrice = Number(@json($displayHawanPrice));
    let selectedDonation = 0;
    let selectedSlot = '';
    let selectedPurpose = '';
    const selectedPanditId = @json($selectedPandit?->id);
    const openReviewStep = @json($openReviewStep ?? false);
    const savedBookingDate = @json(session('hawan_booking.date'));
    const savedBookingSlot = @json(session('hawan_booking.slot'));
    const savedBookingMode = @json(session('hawan_booking.mode'));
    const savedBookingHawanType = @json(session('hawan_booking.hawan_type'));
    const draftKey = 'hawan_booking_draft_' + @json($hawan['slug']);

    function setText(id, value) {
        const el = document.getElementById(id);
        if (el) el.textContent = value;
    }

    function formatAmount(amount) {
        return 'Rs.' + Number(amount || 0).toLocaleString('en-IN');
    }

    function getSankalpValue(name) {
        return document.querySelector('[name="' + name + '"]')?.value?.trim() || '';
    }

    function nextStep(step) {
        document.querySelectorAll('.hawan-step-content').forEach(el => el.classList.remove('hawan-active'));
        document.querySelectorAll('.hawan-step-item').forEach(el => {
            el.classList.remove('hawan-active');
            if (parseInt(el.dataset.step) < step) el.classList.add('hawan-completed');
            else el.classList.remove('hawan-completed');
        });
        document.getElementById('step' + step).classList.add('hawan-active');
        document.querySelector('.hawan-step-item[data-step="' + step + '"]').classList.add('hawan-active');
        currentStep = step;
        updateSummary();
        if (step === 5) updateReviewSummary();
    }

    function prevStep(step) {
        nextStep(step);
    }

    function selectPurpose(el) {
        document.querySelectorAll('.hawan-purpose-tag').forEach(tag => tag.classList.remove('hawan-selected'));
        el.classList.add('hawan-selected');
        selectedPurpose = el.textContent.trim();
        setText('summaryPurpose', selectedPurpose);
        setText('summaryPurpose2', selectedPurpose);
    }

    function findEnabledHawanType(type) {
        return enabledHawanTypes.find(item => item.key === type) || null;
    }

    function selectPackage(el, hawanType, packageName, amount) {
        document.querySelectorAll('.hawan-package-card').forEach(card => card.classList.remove('hawan-selected'));
        el.classList.add('hawan-selected');
        selectedHawanType = hawanType;
        selectedPackage = packageName;
        selectedPackagePrice = Number(amount) || 0;
        updateSummary();
    }

    function selectDonation(el, amount) {
        document.querySelectorAll('.hawan-donation-option').forEach(opt => opt.classList.remove('hawan-selected'));
        el.classList.add('hawan-selected');
        selectedDonation = amount;
        document.getElementById('customDonation').value = '';
        updateSummary();
    }

    function updateCustomDonation(el) {
        if (el.value) {
            document.querySelectorAll('.hawan-donation-option').forEach(opt => opt.classList.remove('hawan-selected'));
            selectedDonation = parseInt(el.value) || 0;
            updateSummary();
        }
    }

    function selectSlot(el, slot) {
        document.querySelectorAll('.hawan-slot-card').forEach(card => card.classList.remove('hawan-selected'));
        el.classList.add('hawan-selected');
        selectedSlot = slot;
        updateSummary();
    }

    function selectMode(el, mode) {
        document.querySelectorAll('.hawan-mode-card').forEach(card => card.classList.remove('hawan-selected'));
        el.classList.add('hawan-selected');
        selectedPackage = mode;
        updateSummary();
    }

    function saveBookingDraft() {
        const draft = {
            selectedPackage,
            selectedHawanType,
            selectedPackagePrice,
            selectedDonation,
            selectedSlot,
            selectedPurpose,
            bookingDate: document.getElementById('hawanDate')?.value || '',
            full_name: getSankalpValue('full_name'),
            gotra: getSankalpValue('gotra'),
            dob: getSankalpValue('dob'),
            birth_time: getSankalpValue('birth_time'),
            birth_place: getSankalpValue('birth_place'),
            father_name: getSankalpValue('father_name'),
            mother_name: getSankalpValue('mother_name'),
            spouse_name: getSankalpValue('spouse_name'),
            family_members: getSankalpValue('family_members'),
            mobile: getSankalpValue('mobile'),
            mannokamna: getSankalpValue('mannokamna'),
        };

        sessionStorage.setItem(draftKey, JSON.stringify(draft));
    }

    function restoreBookingDraft() {
        const draft = JSON.parse(sessionStorage.getItem(draftKey) || '{}');

        Object.keys(draft).forEach(function (key) {
            const input = document.querySelector('[name="' + key + '"]');
            if (input) input.value = draft[key] || '';
        });

        const restoredType = savedBookingHawanType || draft.selectedHawanType || selectedHawanType;
        const enabledType = findEnabledHawanType(restoredType);
        if (enabledType) {
            selectedHawanType = enabledType.key;
            selectedPackage = enabledType.title;
            selectedPackagePrice = Number(enabledType.price) || selectedPackagePrice;
            document.querySelectorAll('.hawan-package-card').forEach(card => {
                card.classList.toggle('hawan-selected', card.dataset.hawanType === enabledType.key);
            });
        } else {
            selectedPackage = savedBookingMode || draft.selectedPackage || selectedPackage;
            selectedPackagePrice = Number(draft.selectedPackagePrice) || selectedPackagePrice;
        }
        selectedDonation = parseInt(draft.selectedDonation) || selectedDonation;
        selectedSlot = savedBookingSlot || draft.selectedSlot || selectedSlot;
        selectedPurpose = draft.selectedPurpose || selectedPurpose;

        const dateInput = document.getElementById('hawanDate');
        if (dateInput) dateInput.value = savedBookingDate || draft.bookingDate || dateInput.value;

        updateSummary();
    }

    function choosePandit() {
        const bookingDate = document.getElementById('hawanDate')?.value;

        if (!bookingDate) {
            alert('Please select hawan date');
            return;
        }

        if (!selectedSlot) {
            alert('Please select a time slot');
            return;
        }

        if (!selectedHawanType) {
            alert('Please select Samuhik or Special Hawan');
            return;
        }

        saveBookingDraft();

        const url = new URL(@json(route('hawan.pandits', ['slug' => $hawan['slug']])), window.location.origin);
        url.searchParams.set('date', bookingDate);
        url.searchParams.set('slot', selectedSlot);
        url.searchParams.set('mode', selectedPackage);
        url.searchParams.set('hawan_type', selectedHawanType);
        url.searchParams.set('booking_mode', 'online');
        window.location.href = url.toString();
    }

    function updateSummary() {
        const date = document.getElementById('hawanDate')?.value;
        const total = selectedPackagePrice + selectedDonation;

        setText('summaryDate', date || '-');
        setText('summaryDate2', date || '-');
        setText('summarySlot', selectedSlot || '-');
        setText('summarySlot2', selectedSlot || '-');
        setText('summaryPackage', selectedPackage);
        setText('summaryMode2', selectedPackage);
        setText('summaryBase', formatAmount(selectedPackagePrice));
        setText('summaryDakshina', selectedDonation ? formatAmount(selectedDonation) : 'None');
        setText('summaryDakshina2', selectedDonation ? formatAmount(selectedDonation) : 'None');
        setText('summaryTotal', formatAmount(total));
        setText('summaryTotal2', formatAmount(total));
    }

    function updateReviewSummary() {
        const date = document.getElementById('hawanDate')?.value || '-';
        const total = selectedPackagePrice + selectedDonation;
        const mobile = getSankalpValue('mobile');

        setText('reviewPackage', selectedPackage);
        setText('reviewPackageAmount', formatAmount(selectedPackagePrice));
        setText('reviewName', getSankalpValue('full_name') || '-');
        setText('reviewPurpose', selectedPurpose || '-');
        setText('reviewDate', date);
        setText('reviewSlot', selectedSlot || '-');
        setText('reviewMode', selectedPackage);
        setText('reviewDakshina', selectedDonation ? formatAmount(selectedDonation) : 'None');
        setText('reviewTotal', formatAmount(total));

        const payButton = document.getElementById('payButton');
        if (payButton) {
            payButton.innerHTML = '<i class="bi bi-credit-card me-2"></i>Confirm & Pay ' + formatAmount(total);
        }
        const mobileInput = document.getElementById('mobileNumber');
        if (mobileInput && mobile && !mobileInput.value) mobileInput.value = mobile;
        checkPaymentReadiness();
    }

    function scrollToBooking() {
        document.getElementById('bookingSection')?.scrollIntoView({ behavior: 'smooth' });
    }

    function sendOTP() {
        const mobile = document.getElementById('mobileNumber').value;
        if (mobile.length !== 10) {
            alert('Please enter a valid 10-digit mobile number');
            return;
        }
        document.getElementById('mobileSection').style.display = 'none';
        document.getElementById('otpSection').style.display = 'block';
        document.getElementById('otpSection').classList.add('hawan-show');
        document.getElementById('otpMobile').textContent = '+91 ' + mobile;
        setTimeout(() => document.querySelectorAll('.hawan-otp-box')[0].focus(), 300);
    }

    function moveOTP(el, index) {
        if (el.value) {
            el.classList.add('filled');
            const boxes = document.querySelectorAll('.hawan-otp-box');
            if (index < boxes.length - 1) boxes[index + 1].focus();
        }
    }

    function moveOTPBack(e, index) {
        if (e.key === 'Backspace' && !e.target.value) {
            const boxes = document.querySelectorAll('.hawan-otp-box');
            if (index > 0) boxes[index - 1].focus();
        }
    }

    function verifyOTP() {
        let otp = '';
        document.querySelectorAll('.hawan-otp-box').forEach(box => otp += box.value);
        if (otp.length !== 6) {
            alert('Please enter complete 6-digit OTP');
            return;
        }
        document.getElementById('otpSection').style.display = 'none';
        checkPaymentReadiness();
        alert('OTP Verified! You can now proceed to payment.');
    }

    function changeMobile() {
        document.getElementById('otpSection').classList.remove('hawan-show');
        document.getElementById('mobileSection').style.display = 'block';
        document.getElementById('mobileNumber').value = '';
    }

    function resendOTP() {
        alert('OTP resent successfully!');
    }

    async function proceedToPay() {
        const mobile = document.getElementById('mobileNumber')?.value || getSankalpValue('mobile');
        const otp = Array.from(document.querySelectorAll('.hawan-otp-box')).map(box => box.value).join('');
        const bookingDate = document.getElementById('hawanDate')?.value || new Date().toISOString().slice(0, 10);

        if (!getSankalpValue('full_name')) {
            alert('Please enter full name in sankalp details');
            return;
        }

        if (!selectedPurpose || selectedPurpose === '-') {
            alert('Please select purpose in sankalp details');
            return;
        }

        if (!mobile || mobile.length < 10) {
            alert('Please enter mobile number');
            return;
        }

        if (!selectedSlot) {
            alert('Please select a time slot');
            return;
        }

        if (!selectedHawanType) {
            alert('Please select Samuhik or Special Hawan');
            return;
        }

        if (!selectedPanditId) {
            alert('Please select a pandit first');
            choosePandit();
            return;
        }

        const bookingData = {
            hawan_slug: @json($hawan['slug']),
            hawan_name: @json($hawan['name']),
            hawan_type: selectedHawanType,
            package_name: selectedPackage,
            package_amount: selectedPackagePrice,
            full_name: getSankalpValue('full_name'),
            gotra: getSankalpValue('gotra'),
            dob: getSankalpValue('dob'),
            birth_time: getSankalpValue('birth_time'),
            birth_place: getSankalpValue('birth_place'),
            father_name: getSankalpValue('father_name'),
            mother_name: getSankalpValue('mother_name'),
            spouse_name: getSankalpValue('spouse_name'),
            family_names: getSankalpValue('family_members'),
            mobile: mobile,
            purpose: selectedPurpose,
            mannokamna: getSankalpValue('mannokamna'),
            donation_amount: selectedDonation,
            total_amount: selectedPackagePrice + selectedDonation,
            booking_date: bookingDate,
            slot: selectedSlot || '-',
            otp: otp || 'demo',
        };

        console.log('Hawan Booking Data:', bookingData);

        const payButton = document.getElementById('payButton');
        const originalText = payButton.innerHTML;
        payButton.disabled = true;
        payButton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Saving Booking...';

        try {
            const response = await fetch(@json(route('hawan.store')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': @json(csrf_token()),
                },
                body: JSON.stringify(bookingData),
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Booking save failed');
            }

            window.location.href = result.redirect_url;
        } catch (error) {
            alert(error.message || 'Booking save failed. Please try again.');
            payButton.innerHTML = originalText;
            checkPaymentReadiness();
        }
    }

    function checkPaymentReadiness() {
        const consentChecked = document.getElementById('hawanConsentCheckbox')?.checked || false;
        const payButton = document.getElementById('payButton');
        if (payButton) payButton.disabled = !consentChecked;
    }

    // Initialize summary
    restoreBookingDraft();
    if (openReviewStep) {
        nextStep(5);
        updateReviewSummary();
    }
    updateSummary();
</script>
@endpush
