@extends('layouts.app')

@section('title', 'Light a Virtual Diya - BhaktiDeep')
@section('description', 'Choose an active diya, add deity and sankalp, complete seva payment, and watch your virtual diya glow.')

@push('styles')
<link href="{{ asset('css/light-diya.css') }}" rel="stylesheet">
@endpush

@section('body')
@php
    $initialDiya = $diyas->first();
    $purposeOptions = ['Health', 'Prosperity', 'Family Peace', 'Protection', 'Career', 'Marriage', 'Child Blessing', 'Spiritual Growth'];
@endphp

<main class="page-shell ld-page">
    <div class="particles subtle">
        @for ($i = 0; $i < 22; $i++)
            <span style="left: {{ ($i * 53) % 100 }}%; animation-delay: {{ $i * 0.28 }}s;"></span>
        @endfor
    </div>
    <div class="ld-hero-glow"></div>

    <section class="container ld-hero">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="eyebrow"><i class="bi bi-fire"></i> LIGHT A VIRTUAL DIYA</span>
                <h1 class="mt-4">
                    <span>Har Deep Mein </span><span class="gold-text">Bhakti</span>
                </h1>
                <p class="mt-4">
                    Choose a diya from BhaktiDeep's active offerings, add your sankalp, complete seva, and watch your diya glow with mantra ambience.
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
                    <div class="col-4">
                        <div class="glass rounded-3 px-3 py-2 text-center">
                            <div class="gold-text fw-bold" style="font-family:'Cinzel',serif;font-size:18px;">{{ number_format($diyaStats['scheduled']) }}</div>
                            <div class="ld-stat-label">scheduled</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="glass rounded-3 px-3 py-2 text-center">
                            <div class="gold-text fw-bold" style="font-family:'Cinzel',serif;font-size:18px;">{{ number_format($diyaStats['glowing']) }}</div>
                            <div class="ld-stat-label">glowing</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="glass rounded-3 px-3 py-2 text-center">
                            <div class="gold-text fw-bold" style="font-family:'Cinzel',serif;font-size:18px;">{{ number_format($diyaStats['completed']) }}</div>
                            <div class="ld-stat-label">completed</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="ld-hero-img-wrap">
                    <div class="ld-img-glow"></div>
                    <div class="ld-img-card glass">
                        <img id="heroDiyaImage" src="{{ asset($initialDiya?->imagePath() ?? 'assets/diya.jpg') }}" alt="{{ $initialDiya?->name ?? 'Glowing diya' }}">
                        <div class="ld-img-overlay"></div>
                        <div class="ld-img-badge">
                            <div class="d-flex align-items-center gap-2">
                                <span class="ld-pulse-dot"></span>
                                <span id="heroDiyaName">{{ $initialDiya?->name ?? 'Diya offering' }}</span>
                            </div>
                            <span class="ld-akhand-tag" id="heroDiyaDuration">{{ $initialDiya?->duration ?? 'Select Diya' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="choose" class="container page-section">
        <div class="mb-4">
            <div class="ld-step-label">Step 1</div>
            <h2>Choose your diya</h2>
            <p>Only active diya offerings from the admin panel are shown here.</p>
        </div>

        @if($diyas->isEmpty())
            <div class="glass rounded-4 p-4 p-md-5 text-center">
                <i class="bi bi-fire gold-text" style="font-size:42px;"></i>
                <h3 class="ld-form-title mt-3">No active diyas available</h3>
                <p class="ld-form-sub mt-2">Please add and activate diyas from the admin panel.</p>
            </div>
        @else
            <form id="diyaOfferingForm">
                <input type="hidden" name="diya_id" id="selectedDiyaId" value="{{ $initialDiya->id }}">

                <div class="row g-3" id="diyaGrid">
                    @foreach ($diyas as $index => $diya)
                        <div class="col-sm-6 col-xl-3">
                            <button type="button" class="ld-diya-card {{ $index === 0 ? 'active' : '' }}" data-diya-id="{{ $diya->id }}">
                                <div class="ld-diya-thumb">
                                    <img src="{{ asset($diya->imagePath()) }}" alt="{{ $diya->name }}">
                                    <i class="bi bi-check ld-check-icon {{ $index === 0 ? '' : 'd-none' }}"></i>
                                </div>
                                <div class="ld-diya-name mt-3">{{ $diya->name }}</div>
                                <div class="ld-diya-desc mt-1">{{ $diya->short_description }}</div>
                                <div class="d-flex align-items-center justify-content-between mt-3 gap-2">
                                    <span class="ld-dur">{{ $diya->duration ?: 'Duration set by temple' }}</span>
                                    <span class="ld-price-tag">Rs.{{ number_format((float) $diya->seva_amount, 2) }}</span>
                                </div>
                            </button>
                        </div>
                    @endforeach
                </div>

                <section class="container px-0 pb-5 mt-4">
                    <div class="row g-4">
                        <div class="col-lg-7">
                            <div class="glass rounded-4 p-4 p-md-5">
                                <div class="ld-step-label">Step 2</div>
                                <h3 class="mt-2 ld-form-title">Choose Deity</h3>
                                <p class="ld-form-sub mt-1" id="deityModeText">Deity selection is based on the selected diya.</p>

                                <div class="ld-fixed-deity-box mt-3" id="fixedDeityBox">
                                    <i class="bi bi-shield-lock"></i>
                                    <div>
                                        <span>Fixed Deity</span>
                                        <strong id="fixedDeityName">-</strong>
                                    </div>
                                </div>

                                <div class="mt-3" id="deitySelectBox">
                                    <label class="small-label d-block mb-1">Select Deity *</label>
                                    <select class="form-control sacred-input" name="deity_id" id="deitySelect">
                                        <option value="">Select active deity</option>
                                        @foreach($activeDeities as $deity)
                                            <option value="{{ $deity->id }}">{{ $deity->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="glass rounded-4 p-4 p-md-5 mt-4">
                                <div class="ld-step-label">Step 3</div>
                                <h3 class="mt-2 ld-form-title">Add your Sankalp</h3>
                                <p class="ld-form-sub mt-1">Your diya is lit in your name with your prayer details.</p>

                                <div class="row g-3 mt-2">
                                    <div class="col-sm-6">
                                        <label class="small-label d-block mb-1">Full Name *</label>
                                        <input type="text" name="full_name" class="form-control sacred-input" required>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="small-label d-block mb-1">Mobile *</label>
                                        <input type="tel" name="mobile" class="form-control sacred-input" maxlength="20" required>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="small-label d-block mb-1">Gotra</label>
                                        <input type="text" name="gotra" class="form-control sacred-input">
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="small-label d-block mb-1">Date of Birth</label>
                                        <input type="date" name="dob" class="form-control sacred-input">
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="small-label d-block mb-1">Birth Time</label>
                                        <input type="time" name="birth_time" class="form-control sacred-input">
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="small-label d-block mb-1">Birth Place</label>
                                        <input type="text" name="birth_place" class="form-control sacred-input">
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="small-label d-block mb-1">Father's Name</label>
                                        <input type="text" name="father_name" class="form-control sacred-input">
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="small-label d-block mb-1">Mother's Name</label>
                                        <input type="text" name="mother_name" class="form-control sacred-input">
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="small-label d-block mb-1">Spouse Name</label>
                                        <input type="text" name="spouse_name" class="form-control sacred-input">
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="small-label d-block mb-1">Family Names</label>
                                        <input type="text" name="family_names" class="form-control sacred-input">
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <div class="small-label mb-2">Spiritual purpose *</div>
                                    <input type="hidden" name="purpose" id="selectedPurpose" value="{{ $purposeOptions[0] }}">
                                    <div class="intentions" id="purposeGroup">
                                        @foreach ($purposeOptions as $index => $purpose)
                                            <button type="button" class="ld-purpose-btn {{ $index === 0 ? 'active' : '' }}" data-purpose="{{ $purpose }}">{{ $purpose }}</button>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <label class="small-label d-block mb-1">Mannokamna</label>
                                    <textarea rows="3" name="mannokamna" placeholder="Likhiye apni prarthana..." class="form-control sacred-input"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <div class="glass rounded-4 p-4 p-md-5 ld-summary-sticky">
                                <div class="ld-step-label">Step 4</div>
                                <h3 class="mt-2 ld-form-title">Payment</h3>
                                <p class="ld-form-sub mt-1">Seva amount is locked from the selected diya record.</p>

                                <div class="ld-order-summary mt-4">
                                    <div class="ld-summary-row"><span>Diya</span><strong id="sumDiya">{{ $initialDiya->name }}</strong></div>
                                    <div class="ld-summary-row"><span>Deity</span><strong id="sumDeity">{{ $initialDiya->fixedDeity?->name ?? 'Select deity' }}</strong></div>
                                    <div class="ld-summary-row"><span>Duration</span><strong id="sumDuration">{{ $initialDiya->duration ?: '-' }}</strong></div>
                                    <div class="ld-summary-row"><span>Purpose</span><strong id="sumPurpose">{{ $purposeOptions[0] }}</strong></div>
                                    <hr class="ld-divider">
                                    <div class="ld-summary-row">
                                        <span>Total Seva</span>
                                        <strong class="gold-text ld-total" id="sumTotal">Rs.{{ number_format((float) $initialDiya->seva_amount, 2) }}</strong>
                                    </div>
                                </div>

                                <label class="ld-consent mt-4">
                                    <input type="checkbox" name="consent" value="1" id="diyaConsent">
                                    <span>I agree to use my sankalp for this diya offering.</span>
                                </label>

                                <button type="submit" class="btn btn-saffron w-100 py-3 rounded-3 mt-4 fw-semibold" id="payButton">
                                    <i class="bi bi-fire me-2"></i> Pay & Light My Diya
                                </button>
                                <p class="ld-secure-note mt-2">
                                    <i class="bi bi-shield-check text-warning"></i>
                                    Secure payment flow. Amount verified on server.
                                </p>
                                <div class="alert alert-danger mt-3 d-none" id="diyaError"></div>
                            </div>
                        </div>
                    </div>
                </section>
            </form>
        @endif
    </section>

    <section id="wall" class="container page-section">
        <div class="diya-panel glass">
            <div class="row align-items-center g-4">
                <div class="col-lg-4">
                    <span class="eyebrow"><i class="bi bi-fire"></i> LIVE WALL</span>
                    <h2 class="mt-3">Live Diya <span class="gold-text">Wall</span></h2>
                    <p class="mt-2">Your diya appears here after payment and stays glowing for its selected duration.</p>
                    <div class="row g-3 mt-2">
                        <div class="col-4">
                            <div class="stat-box text-center"><i class="bi bi-calendar-event"></i><strong>{{ number_format($diyaStats['scheduled']) }}</strong><span>Scheduled</span></div>
                        </div>
                        <div class="col-4">
                            <div class="stat-box text-center"><i class="bi bi-fire"></i><strong>{{ number_format($diyaStats['glowing']) }}</strong><span>Glowing</span></div>
                        </div>
                        <div class="col-4">
                            <div class="stat-box text-center"><i class="bi bi-check-circle"></i><strong>{{ number_format($diyaStats['completed']) }}</strong><span>Completed</span></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="diya-grid">
                        @for ($i = 0; $i < 48; $i++)
                            <span style="animation-delay: {{ ($i * 0.15) % 3 }}s;">
                                <img src="{{ asset('assets/small-deep.png') }}" alt="Glowing diya">
                            </span>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

@push('scripts')
<script>
const diyaOptions = @json($diyaOptions);
const deities = @json($activeDeities->map(fn ($deity) => ['id' => $deity->id, 'name' => $deity->name])->values());

function formatAmount(amount) {
    return 'Rs.' + Number(amount || 0).toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function selectedDiya() {
    const selectedId = Number(document.getElementById('selectedDiyaId')?.value || 0);
    return diyaOptions.find(diya => Number(diya.id) === selectedId) || diyaOptions[0];
}

function setText(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value;
}

function showError(message) {
    const errorBox = document.getElementById('diyaError');
    if (!errorBox) return;
    errorBox.textContent = message;
    errorBox.classList.remove('d-none');
}

function clearError() {
    const errorBox = document.getElementById('diyaError');
    if (!errorBox) return;
    errorBox.textContent = '';
    errorBox.classList.add('d-none');
}

function syncDiyaUi() {
    const diya = selectedDiya();
    if (!diya) return;

    document.querySelectorAll('.ld-diya-card').forEach(card => {
        const isActive = Number(card.dataset.diyaId) === Number(diya.id);
        card.classList.toggle('active', isActive);
        card.querySelector('.ld-check-icon')?.classList.toggle('d-none', !isActive);
    });

    const heroImage = document.getElementById('heroDiyaImage');
    if (heroImage) {
        heroImage.src = diya.image_url;
        heroImage.alt = diya.name;
    }

    setText('heroDiyaName', diya.name);
    setText('heroDiyaDuration', diya.duration || 'Temple duration');
    setText('sumDiya', diya.name);
    setText('sumDuration', diya.duration || '-');
    setText('sumTotal', formatAmount(diya.seva_amount));

    const fixedBox = document.getElementById('fixedDeityBox');
    const selectBox = document.getElementById('deitySelectBox');
    const deitySelect = document.getElementById('deitySelect');

    if (diya.deity_selection_mode === 'fixed') {
        fixedBox.style.display = 'flex';
        selectBox.style.display = 'none';
        deitySelect.value = '';
        setText('fixedDeityName', diya.fixed_deity_name || '-');
        setText('sumDeity', diya.fixed_deity_name || '-');
        setText('deityModeText', 'This diya is assigned to a fixed deity by the temple.');
    } else {
        fixedBox.style.display = 'none';
        selectBox.style.display = 'block';
        const selectedOption = deities.find(deity => String(deity.id) === String(deitySelect.value));
        setText('sumDeity', selectedOption ? selectedOption.name : 'Select deity');
        setText('deityModeText', 'Choose any active deity for this diya offering.');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.ld-diya-card').forEach(card => {
        card.addEventListener('click', function () {
            document.getElementById('selectedDiyaId').value = this.dataset.diyaId;
            clearError();
            syncDiyaUi();
        });
    });

    document.querySelectorAll('.ld-purpose-btn').forEach(button => {
        button.addEventListener('click', function () {
            document.querySelectorAll('.ld-purpose-btn').forEach(item => item.classList.remove('active'));
            this.classList.add('active');
            document.getElementById('selectedPurpose').value = this.dataset.purpose;
            setText('sumPurpose', this.dataset.purpose);
        });
    });

    document.getElementById('deitySelect')?.addEventListener('change', syncDiyaUi);

    document.getElementById('diyaOfferingForm')?.addEventListener('submit', async function (event) {
        event.preventDefault();
        clearError();

        const diya = selectedDiya();
        const payButton = document.getElementById('payButton');

        if (!diya) {
            showError('Please select a diya.');
            return;
        }

        if (diya.deity_selection_mode === 'user_select' && !document.getElementById('deitySelect').value) {
            showError('Please select a deity.');
            return;
        }

        if (!document.getElementById('diyaConsent').checked) {
            showError('Please accept the consent before payment.');
            return;
        }

        const originalText = payButton.innerHTML;
        payButton.disabled = true;
        payButton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Lighting Diya...';

        const payload = Object.fromEntries(new FormData(this).entries());

        try {
            const response = await fetch(@json(route('diya.store')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': @json(csrf_token()),
                },
                body: JSON.stringify(payload),
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                const message = result.message || Object.values(result.errors || {})[0]?.[0] || 'Diya offering failed.';
                throw new Error(message);
            }

            window.location.href = result.redirect_url;
        } catch (error) {
            showError(error.message || 'Diya offering failed. Please try again.');
            payButton.disabled = false;
            payButton.innerHTML = originalText;
        }
    });

    syncDiyaUi();
});
</script>
@endpush
@endsection
