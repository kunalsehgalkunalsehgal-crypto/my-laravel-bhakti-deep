@extends('layouts.app')

@section('title', $pooja['name'].' - BhaktiDeep')
@section('description', $pooja['short_description'])

@push('styles')
<link href="{{ asset('css/hawan-detail.css') }}" rel="stylesheet">
<link href="{{ asset('css/light-diya.css') }}" rel="stylesheet">
@endpush

@section('body')
@include('partials.razorpay-checkout')

<section class="ld-hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="eyebrow"><i class="bi bi-flower1"></i> PERSONALIZED POOJA</span>
                <h1 class="mt-4">
                    <span>Book a </span><span class="gold-text">Live {{ $pooja['name'] }}</span><br>
                    <span>From Home</span>
                </h1>
                <p class="mt-4">{{ $pooja['short_description'] }}</p>
                <div class="hero-buttons mt-4">
                    <button class="btn btn-saffron btn-lg rounded-pill" onclick="scrollToBooking()">
                        <i class="bi bi-flower1"></i> Book This Pooja
                    </button>
                    <a href="{{ route('personalized-pooja') }}" class="btn btn-ghost-gold btn-lg rounded-pill">
                        <i class="bi bi-arrow-left"></i> All Poojas
                    </a>
                </div>
                <div class="row g-3 mt-3 ld-stats">
                    <div class="col-4">
                        <div class="glass rounded-3 px-3 py-2 text-center">
                            <div class="gold-text fw-bold" style="font-family:'Cinzel',serif;font-size:18px;">Rs.{{ number_format($pooja['base_price']) }}</div>
                            <div class="ld-stat-label">Base Price</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="glass rounded-3 px-3 py-2 text-center">
                            <div class="gold-text fw-bold" style="font-family:'Cinzel',serif;font-size:18px;">{{ $pooja['duration'] }}</div>
                            <div class="ld-stat-label">Duration</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="glass rounded-3 px-3 py-2 text-center">
                            <div class="gold-text fw-bold" style="font-family:'Cinzel',serif;font-size:18px;">{{ $pooja['mode'] }}</div>
                            <div class="ld-stat-label">Mode</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="ld-hero-img-wrap">
                    <div class="ld-img-glow"></div>
                    <div class="ld-img-card glass">
                        <img src="{{ asset($pooja['featured_image']) }}" alt="{{ $pooja['name'] }}">
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
    </div>
</section>

<main class="hawan-page">
    {{-- <section class="py-5">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-8">
                    <div class="mb-5">
                        <h2 class="hawan-section-title">About This <span class="hawan-highlight">Pooja</span></h2>
                        <p class="hawan-section-subtitle">{{ $pooja['name'] }} is performed with your personal sankalp.</p>
                        <div class="hawan-about-box">
                            <p class="mb-0">{{ $pooja['full_description'] }}</p>
                        </div>
                    </div>

                    <div class="mb-5">
                        <h2 class="hawan-section-title">Benefits</h2>
                        <div class="row g-3">
                            @foreach ($pooja['benefits'] as $benefit)
                                <div class="col-md-4">
                                    <div class="hawan-benefit-card">
                                        <i class="bi bi-check-circle"></i>
                                        <h6>{{ $benefit }}</h6>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="hawan-order-summary">
                        <h5><i class="bi bi-receipt"></i> Booking Summary</h5>
                        <div class="hawan-summary-row"><span class="hawan-label">Pooja</span><span class="hawan-value">{{ $pooja['name'] }}</span></div>
                        <div class="hawan-summary-row"><span class="hawan-label">Package</span><span class="hawan-value">Standard Pooja</span></div>
                        <div class="hawan-summary-row"><span class="hawan-label">Slot</span><span class="hawan-value">Select slot</span></div>
                        <div class="hawan-summary-row"><span class="hawan-label">Dakshina</span><span class="hawan-value">Rs.0</span></div>
                        <div class="hawan-summary-row hawan-total"><span class="hawan-label">Total</span><span class="hawan-value">Rs.{{ number_format($pooja['base_price']) }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    </section> --}}

    <section class="py-5" id="bookingSection">
        <div class="container">
            {{-- <div class="text-center mb-5">
                <h2 class="hawan-section-title">Complete Your <span class="hawan-highlight">Booking</span></h2>
                <p class="hawan-section-subtitle">Follow the steps to book your personalized pooja</p>
            </div> --}}

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="mb-5">
                        <h2 class="hawan-section-title">About This <span class="hawan-highlight">Pooja</span></h2>
                        <p class="hawan-section-subtitle">{{ $pooja['name'] }} is performed with your personal sankalp.</p>
                        <div class="hawan-about-box">
                            <p class="mb-0">{{ $pooja['full_description'] }}</p>
                        </div>
                    </div>

                    <div class="mb-5">
                        <h2 class="hawan-section-title">Benefits</h2>
                        <div class="row g-3">
                            @foreach ($pooja['benefits'] as $benefit)
                                <div class="col-md-4">
                                    <div class="hawan-benefit-card">
                                        <i class="bi bi-check-circle"></i>
                                        <h6>{{ $benefit }}</h6>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="hawan-stepper-wrapper">
                        <div class="hawan-stepper-header">
                            @foreach (['Pooja Details', 'Sankalp', 'Donation', 'Date & Slot', 'Review & Pay'] as $index => $label)
                                <div class="hawan-step-item {{ $index === 0 ? 'hawan-active' : '' }}" data-step="{{ $index + 1 }}">
                                    <div class="hawan-step-circle">{{ $index + 1 }}</div>
                                    <span class="hawan-step-label">{{ $label }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="hawan-step-content hawan-active" id="step1">
                            <h3 class="hawan-step-title">Selected Pooja Details</h3>
                            <p class="hawan-step-desc">Confirm your selected pooja and proceed to sankalp</p>
                            <div class="hawan-hawan-confirm-box mb-4">
                                <div class="d-flex align-items-center gap-4 flex-wrap">
                                    <img src="{{ asset($pooja['featured_image']) }}" class="hawan-hawan-confirm-img" alt="{{ $pooja['name'] }}">
                                    <div>
                                        <h4 class="hawan-hawan-confirm-title">{{ $pooja['name'] }}</h4>
                                        <p class="hawan-hawan-confirm-desc">{{ $pooja['short_description'] }}</p>
                                        <div class="d-flex gap-3 flex-wrap">
                                            <span class="hawan-badge-saffron">Rs.{{ number_format($pooja['base_price']) }}</span>
                                            <span class="hawan-badge-gold">{{ $pooja['duration'] }}</span>
                                            <span class="hawan-badge-saffron">{{ $pooja['mode'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button class="hawan-btn hawan-btn-saffron w-100" onclick="nextStep(2)">
                                Continue to Sankalp <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                        </div>

                        <div class="hawan-step-content" id="step2">
                            <h3 class="hawan-step-title">Sankalp Details</h3>
                            <p class="hawan-step-desc">Enter your details for personalized sankalp</p>
                            <form id="sankalpForm" class="row g-3">
                                <div class="col-md-6">
                                    <label class="hawan-form-label">Full Name *</label>
                                    <input type="text" class="hawan-form-control" name="full_name" placeholder="Enter your full name">
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
                                        @foreach (['Health', 'Protection', 'Peace', 'Family Well-being', 'Business', 'Career', 'Marriage', 'Spiritual Growth', 'Other'] as $purpose)
                                            <span class="hawan-purpose-tag" onclick="selectPurpose(this)"><i class="bi bi-stars"></i> {{ $purpose }}</span>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="hawan-form-label">Mannokamna / Special Message</label>
                                    <textarea class="hawan-form-control" name="mannokamna" rows="3" placeholder="Enter your special wish or message for the pooja"></textarea>
                                </div>
                            </form>
                            <div class="d-flex gap-3 mt-4">
                                <button class="hawan-btn hawan-btn-outline-saffron" onclick="prevStep(1)"><i class="bi bi-arrow-left me-2"></i>Back</button>
                                <button class="hawan-btn hawan-btn-saffron flex-fill" onclick="nextStep(3)">Continue to Package <i class="bi bi-arrow-right ms-2"></i></button>
                            </div>
                        </div>

                        <div class="hawan-step-content" id="step3">
                            <h3 class="hawan-step-title">Choose Package & Offering</h3>
                            <p class="hawan-step-desc">Select the pooja experience and add optional dakshina if you wish</p>
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <div class="hawan-mode-card hawan-package-card hawan-selected" onclick="selectPackage(this, 'Standard Pooja', {{ $pooja['base_price'] }})">
                                        <i class="bi bi-flower1"></i>
                                        <h6>Standard Pooja</h6>
                                        <p>Personalized sankalp, live access, digital receipt</p>
                                        <strong>Rs.{{ number_format($pooja['base_price']) }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="hawan-mode-card hawan-package-card" onclick="selectPackage(this, 'Premium Pooja', {{ $pooja['base_price'] + 1000 }})">
                                        <i class="bi bi-camera-video"></i>
                                        <h6>Premium Pooja</h6>
                                        <p>Live access, replay, family join, certificate</p>
                                        <strong>Rs.{{ number_format($pooja['base_price'] + 1000) }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="hawan-mode-card hawan-package-card" onclick="selectPackage(this, 'Special Pooja', {{ $pooja['base_price'] + 2500 }})">
                                        <i class="bi bi-stars"></i>
                                        <h6>Special Pooja</h6>
                                        <p>Priority slot, extended ritual, family join, replay</p>
                                        <strong>Rs.{{ number_format($pooja['base_price'] + 2500) }}</strong>
                                    </div>
                                </div>
                            </div>

                            <h5 class="hawan-review-box-title mb-3">Add Custom Offering (Optional)</h5>
                            <div class="row g-3 mb-4">
                                @foreach ($pooja['donation_options'] as $index => $option)
                                    <div class="col-6 col-md-3">
                                        <div class="hawan-donation-option" onclick="selectDonation(this, {{ $option }})">
                                            <div class="hawan-amount">Rs.{{ number_format($option) }}</div>
                                            <div class="hawan-label">{{ ['Blessing', 'Support', 'Popular', 'Premium'][$index] ?? 'Offering' }}</div>
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
                                <button class="hawan-btn hawan-btn-outline-saffron" onclick="prevStep(2)"><i class="bi bi-arrow-left me-2"></i>Back</button>
                                <button class="hawan-btn hawan-btn-saffron flex-fill" onclick="nextStep(4)">Continue to Slot <i class="bi bi-arrow-right ms-2"></i></button>
                            </div>
                        </div>

                        <div class="hawan-step-content" id="step4">
                            <h3 class="hawan-step-title">Date & Slot</h3>
                            <p class="hawan-step-desc">Select your preferred date and time for the pooja</p>
                            <div class="mb-4">
                                <label class="hawan-form-label">Select Date *</label>
                                <input type="date" class="hawan-form-control" id="poojaDate" onchange="renderAllowedSlots()" min="{{ date('Y-m-d') }}">
                            </div>
                            <div class="mb-4">
                                <label class="hawan-form-label">Select Time Slot *</label>
                                <div class="row g-3" id="poojaSlotList">
                                    <div class="col-12"><p class="hawan-step-desc mb-0">Select a date to see available slots.</p></div>
                                </div>
                            </div>
                            <div class="d-flex gap-3">
                                <button class="hawan-btn hawan-btn-outline-saffron" onclick="prevStep(3)"><i class="bi bi-arrow-left me-2"></i>Back</button>
                                <button class="hawan-btn hawan-btn-saffron flex-fill" onclick="choosePandit()">Choose Pandit <i class="bi bi-arrow-right ms-2"></i></button>
                            </div>
                        </div>

                        <div class="hawan-step-content" id="step5">
                            <h3 class="hawan-step-title">Review & Pay</h3>
                            <p class="hawan-step-desc">Review your booking details and complete payment</p>
                            <div class="hawan-review-box mb-4">
                                <h5 class="hawan-review-box-title">Booking Summary</h5>
                                <div class="hawan-summary-row"><span class="hawan-label">Pooja:</span><span class="hawan-value">{{ $pooja['name'] }}</span></div>
                                <div class="hawan-summary-row"><span class="hawan-label">Pandit:</span><span class="hawan-value">{{ $selectedPandit ? ($selectedPandit->pandit_name ?: $selectedPandit->full_name) : 'Please select pandit' }}</span></div>
                                <div class="hawan-summary-row"><span class="hawan-label">Package:</span><span class="hawan-value" id="reviewPackage">Standard Pooja</span></div>
                                <div class="hawan-summary-row"><span class="hawan-label">Name:</span><span class="hawan-value" id="reviewName">-</span></div>
                                <div class="hawan-summary-row"><span class="hawan-label">Purpose:</span><span class="hawan-value" id="reviewPurpose">-</span></div>
                                <div class="hawan-summary-row"><span class="hawan-label">Date:</span><span class="hawan-value" id="reviewDate">-</span></div>
                                <div class="hawan-summary-row"><span class="hawan-label">Time:</span><span class="hawan-value" id="reviewSlot">-</span></div>
                                <div class="hawan-summary-row"><span class="hawan-label">Package Amount:</span><span class="hawan-value" id="reviewPackageAmount">Rs.{{ number_format($pooja['base_price']) }}</span></div>
                                <div class="hawan-summary-row"><span class="hawan-label">Dakshina:</span><span class="hawan-value" id="reviewDakshina">None</span></div>
                                <div class="hawan-summary-row hawan-total"><span class="hawan-label">Total Amount:</span><span class="hawan-value" id="reviewTotal">Rs.{{ number_format($pooja['base_price']) }}</span></div>
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
                                    <button class="hawan-btn hawan-btn-saffron w-100" onclick="sendOTP()"><i class="bi bi-send me-2"></i>Send OTP</button>
                                </div>
                                <div class="hawan-otp-section" id="otpSection">
                                    <div class="text-center mb-3">
                                        <i class="bi bi-check-circle-fill hawan-otp-success-icon"></i>
                                        <p class="mt-2 mb-0 hawan-otp-sent-text">OTP sent to <strong id="otpMobile">+91 XXXXX XXXXX</strong></p>
                                    </div>
                                    <label class="hawan-form-label text-center d-block">Enter 6 digit OTP</label>
                                    <div class="hawan-otp-boxes">
                                        @for ($i = 0; $i < 6; $i++)
                                            <input type="text" class="hawan-otp-box" maxlength="1" oninput="moveOTP(this, {{ $i }})" onkeydown="moveOTPBack(event, {{ $i }})">
                                        @endfor
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
                                <input type="checkbox" id="poojaConsentCheckbox" onchange="checkPaymentReadiness()">
                                I agree to BhaktiDeep's terms and consent to my sankalp being used for this pooja booking.
                            </label>
                            <button class="hawan-btn hawan-btn-saffron w-100" id="payButton" disabled onclick="proceedToPay()">
                                <i class="bi bi-credit-card me-2"></i>Confirm & Pay
                            </button>
                            <div class="d-flex gap-3 mt-3">
                                <button class="hawan-btn hawan-btn-outline-saffron" onclick="prevStep(4)"><i class="bi bi-arrow-left me-2"></i>Back</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="hawan-order-summary" id="orderSummary">
                        <h5><i class="bi bi-receipt me-2"></i>Order Summary</h5>
                        <div class="hawan-summary-row"><span class="hawan-label">Pooja:</span><span class="hawan-value">{{ $pooja['name'] }}</span></div>
                        <div class="hawan-summary-row"><span class="hawan-label">Pandit:</span><span class="hawan-value">{{ $selectedPandit ? ($selectedPandit->pandit_name ?: $selectedPandit->full_name) : 'Please select pandit' }}</span></div>
                        <div class="hawan-summary-row"><span class="hawan-label">Purpose:</span><span class="hawan-value" id="summaryPurpose">-</span></div>
                        <div class="hawan-summary-row"><span class="hawan-label">Date:</span><span class="hawan-value" id="summaryDate">-</span></div>
                        <div class="hawan-summary-row"><span class="hawan-label">Slot:</span><span class="hawan-value" id="summarySlot">-</span></div>
                        <div class="hawan-summary-row"><span class="hawan-label">Package:</span><span class="hawan-value" id="summaryPackage">Standard Pooja</span></div>
                        <div class="hawan-summary-row"><span class="hawan-label">Package Price:</span><span class="hawan-value" id="summaryBase">Rs.{{ number_format($pooja['base_price']) }}</span></div>
                        <div class="hawan-summary-row"><span class="hawan-label">Dakshina:</span><span class="hawan-value" id="summaryDakshina">None</span></div>
                        <div class="hawan-summary-row hawan-total"><span class="hawan-label">Total:</span><span class="hawan-value" id="summaryTotal">Rs.{{ number_format($pooja['base_price']) }}</span></div>
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
    </section>
</main>
@endsection

@push('scripts')
<script>
let currentStep = 1;
let selectedPackage = 'Standard Pooja';
let selectedPackagePrice = {{ $pooja['base_price'] }};
let selectedDonation = 0;
let selectedSlot = '';
let selectedPurpose = '';
const selectedPanditId = @json($selectedPandit?->id);
const openReviewStep = @json($openReviewStep ?? false);
const savedBookingDate = @json(session('pooja_booking.date'));
const savedBookingSlot = @json(session('pooja_booking.slot'));
const savedBookingMode = @json(session('pooja_booking.mode'));
const weeklyAvailability = @json($pooja['weekly_availability'] ?? []);
const draftKey = 'pooja_booking_draft_' + @json($pooja['slug']);
const weekDays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

function setText(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value;
}

function formatAmount(amount) {
    return 'Rs.' + Number(amount || 0).toLocaleString('en-IN');
}

function scrollToBooking() {
    document.getElementById('bookingSection').scrollIntoView({ behavior: 'smooth' });
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
}

function selectPackage(el, packageName, amount) {
    document.querySelectorAll('.hawan-package-card').forEach(card => card.classList.remove('hawan-selected'));
    el.classList.add('hawan-selected');
    selectedPackage = packageName;
    selectedPackagePrice = parseInt(amount) || {{ $pooja['base_price'] }};
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

function slotLabel(slot) {
    const fmt = (time) => {
        const [h, m] = time.split(':').map(Number);
        const hour = h % 12 || 12;
        return hour + ':' + String(m).padStart(2, '0') + ' ' + (h >= 12 ? 'PM' : 'AM');
    };
    return fmt(slot.from) + ' - ' + fmt(slot.to);
}

function renderAllowedSlots() {
    const date = document.getElementById('poojaDate')?.value;
    const list = document.getElementById('poojaSlotList');
    const day = date ? weekDays[new Date(date + 'T00:00:00').getDay()] : null;
    const dayData = day ? weeklyAvailability.days?.[day] : null;
    const labels = weeklyAvailability.accept_new_bookings && dayData?.available ? (dayData.slots || []).map(slotLabel) : [];

    if (selectedSlot && !labels.includes(selectedSlot)) selectedSlot = '';
    list.innerHTML = labels.length ? '' : '<div class="col-12"><p class="hawan-step-desc mb-0">' + (date ? 'No slots available for this date.' : 'Select a date to see available slots.') + '</p></div>';
    labels.forEach((slot, index) => {
        const col = document.createElement('div');
        col.className = 'col-md-4';
        col.innerHTML = `<div class="hawan-slot-card ${slot === selectedSlot ? 'hawan-selected' : ''}">
            <div class="hawan-time"><i class="bi bi-${index === 0 ? 'sunrise' : (index === 1 ? 'sun' : 'sunset')} me-2"></i>${slot}</div>
            <div class="hawan-period">${index === 0 ? 'Morning' : (index === 1 ? 'Afternoon' : 'Evening')} Session</div>
            <div class="hawan-availability"><i class="bi bi-check-circle me-1"></i>Available</div>
        </div>`;
        col.firstElementChild.addEventListener('click', () => selectSlot(col.firstElementChild, slot));
        list.appendChild(col);
    });
    updateSummary();
}

function selectSlot(el, slot) {
    document.querySelectorAll('.hawan-slot-card').forEach(card => card.classList.remove('hawan-selected'));
    el.classList.add('hawan-selected');
    selectedSlot = slot;
    updateSummary();
}

function saveBookingDraft() {
    const draft = {
        selectedPackage,
        selectedPackagePrice,
        selectedDonation,
        selectedSlot,
        selectedPurpose,
        bookingDate: document.getElementById('poojaDate')?.value || '',
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

    selectedPackage = savedBookingMode || draft.selectedPackage || selectedPackage;
    selectedPackagePrice = parseInt(draft.selectedPackagePrice) || selectedPackagePrice;
    selectedDonation = parseInt(draft.selectedDonation) || selectedDonation;
    selectedSlot = savedBookingSlot || draft.selectedSlot || selectedSlot;
    selectedPurpose = draft.selectedPurpose || selectedPurpose;

    const dateInput = document.getElementById('poojaDate');
    if (dateInput) dateInput.value = savedBookingDate || draft.bookingDate || dateInput.value;

    updateSummary();
}

function choosePandit() {
    const bookingDate = document.getElementById('poojaDate')?.value;

    if (!bookingDate) {
        alert('Please select pooja date');
        return;
    }

    if (!selectedSlot) {
        alert('Please select a time slot');
        return;
    }

    saveBookingDraft();

    const url = new URL(@json(route('pooja.pandits', ['slug' => $pooja['slug']])), window.location.origin);
    url.searchParams.set('date', bookingDate);
    url.searchParams.set('slot', selectedSlot);
    url.searchParams.set('mode', selectedPackage);
    url.searchParams.set('booking_mode', 'online');
    window.location.href = url.toString();
}

function updateSummary() {
    const date = document.getElementById('poojaDate')?.value;
    const total = selectedPackagePrice + selectedDonation;

    setText('summaryDate', date || '-');
    setText('summarySlot', selectedSlot || '-');
    setText('summaryPackage', selectedPackage);
    setText('summaryBase', formatAmount(selectedPackagePrice));
    setText('summaryDakshina', selectedDonation ? formatAmount(selectedDonation) : 'None');
    setText('summaryTotal', formatAmount(total));
}

function updateReviewSummary() {
    const date = document.getElementById('poojaDate')?.value || '-';
    const total = selectedPackagePrice + selectedDonation;
    const mobile = getSankalpValue('mobile');

    setText('reviewPackage', selectedPackage);
    setText('reviewPackageAmount', formatAmount(selectedPackagePrice));
    setText('reviewName', getSankalpValue('full_name') || '-');
    setText('reviewPurpose', selectedPurpose || '-');
    setText('reviewDate', date);
    setText('reviewSlot', selectedSlot || '-');
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

function checkPaymentReadiness() {
    document.getElementById('payButton').disabled = !document.getElementById('poojaConsentCheckbox').checked;
}

async function proceedToPay() {
    const fullName = getSankalpValue('full_name');
    const mobile = document.getElementById('mobileNumber')?.value || getSankalpValue('mobile');
    const otp = Array.from(document.querySelectorAll('.hawan-otp-box')).map(box => box.value).join('');
    const bookingDate = document.getElementById('poojaDate')?.value || new Date().toISOString().slice(0, 10);

    if (!fullName) {
        alert('Please enter full name in sankalp details');
        return;
    }

    if (!selectedPurpose) {
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

    if (!selectedPanditId) {
        alert('Please select a pandit first');
        choosePandit();
        return;
    }

    const bookingData = {
        pooja_slug: @json($pooja['slug']),
        pooja_name: @json($pooja['name']),
        package_name: selectedPackage,
        package_amount: selectedPackagePrice,
        full_name: fullName,
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
        slot: selectedSlot,
        otp: otp || 'demo',
    };

    const payButton = document.getElementById('payButton');
    const originalText = payButton.innerHTML;
    payButton.disabled = true;
    payButton.innerHTML = '<i class="bi bi-hourglass-split"></i> Starting Payment...';

    try {
        const response = await fetch(@json(route('pooja.store')), {
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

        if (result.payment) {
            window.startBhaktiDeepPayment(result.payment, payButton, function () {
                payButton.innerHTML = originalText;
                checkPaymentReadiness();
            });
            return;
        }

        window.location.href = result.redirect_url;
    } catch (error) {
        alert(error.message || 'Booking save failed. Please try again.');
        payButton.innerHTML = originalText;
        checkPaymentReadiness();
    }
}

restoreBookingDraft();
renderAllowedSlots();
if (openReviewStep) {
    nextStep(5);
    updateReviewSummary();
}
updateSummary();
</script>
@endpush
