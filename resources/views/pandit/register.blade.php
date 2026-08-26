@extends('layouts.app')

@section('title', 'Pandit Registration - BhaktiDeep')
@section('description', 'Register as a Pandit on BhaktiDeep with Gmail OTP.')

@push('styles')
    <link href="{{ asset('css/signup.css') }}" rel="stylesheet">
@endpush

@section('body')
@php
    $particles = 8;
    $otpFlow = session('otp_flow');
    $otpPending = (($otpFlow['context'] ?? null) === 'register' && ($otpFlow['type'] ?? null) === 'pandit') || old('_otp_pending') === 'pandit';
    $fullName = old('full_name', $otpFlow['full_name'] ?? '');
    $mobile = old('mobile', $otpFlow['mobile'] ?? '');
    $email = old('email', $otpFlow['email'] ?? '');
    $devOtp = $otpPending && (app()->environment('local') || config('mail.default') === 'log') ? ($otpFlow['otp_preview'] ?? null) : null;
@endphp

<main class="signup-page">
    <div class="signup-bg-temple"></div>
    <div class="signup-mandala"></div>
    <div class="signup-particles">
        @for ($i = 0; $i < $particles; $i++)
            <span style="left: {{ ($i * 53) % 100 }}%; animation-delay: {{ ($i * 0.5) % 4 }}s;"></span>
        @endfor
    </div>

    <div class="signup-card">
        <div class="signup-logo">
            <div class="signup-logo-icon"><i class="bi bi-flower1"></i></div>
            <div class="signup-logo-text">BhaktiDeep</div>
            <div class="signup-logo-tagline">Pandit Portal</div>
        </div>

        <h1 class="signup-heading">Register as Pandit</h1>
        <p class="signup-subtext">Name, phone aur Gmail verify karo. Uske baad dashboard me profile, services aur documents complete kar paoge.</p>

        @if (session('success'))
            <div class="signup-alert signup-alert-success">
                <i class="bi bi-check-circle-fill"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="signup-alert signup-alert-error">
                <i class="bi bi-exclamation-circle-fill"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('register.send-otp', ['type' => 'pandit']) }}" id="panditSignupForm">
            @csrf

            <div class="mb-3">
                <label class="signup-form-label">Pandit Full Name <span class="required">*</span></label>
                <input type="text" name="full_name" class="signup-input" placeholder="Pandit Rajesh Sharma" value="{{ $fullName }}" required @readonly($otpPending)>
            </div>

            <div class="mb-3">
                <label class="signup-form-label">Phone Number <span class="required">*</span></label>
                <div class="signup-mobile-group">
                    <div class="signup-country-code">+91</div>
                    <input type="tel" name="mobile" class="signup-input js-phone-input" placeholder="Enter your phone number" maxlength="10" value="{{ $mobile }}" required @readonly($otpPending)>
                </div>
            </div>

            <div class="mb-3">
                <label class="signup-form-label">Gmail Address <span class="required">*</span></label>
                <input type="email" name="email" class="signup-input" placeholder="pandit@gmail.com" value="{{ $email }}" autocomplete="email" required @readonly($otpPending)>
            </div>

            <div class="mb-3">
                <div class="signup-checkbox-wrap">
                    <input type="checkbox" id="panditTermsCheckbox" name="terms" value="1" required @checked(old('terms') || $otpPending) @disabled($otpPending)>
                    @if ($otpPending)
                        <input type="hidden" name="terms" value="1">
                    @endif
                    <label for="panditTermsCheckbox">
                        I agree to BhaktiDeep Pandit Terms, verification rules and Privacy Policy
                    </label>
                </div>
            </div>

            @if ($otpPending)
                <div class="signup-mobile-confirmed">
                    <i class="bi bi-envelope-check-fill"></i>
                    <span>{{ $email }}</span>
                    <a class="change-btn" href="{{ route('pandit.register', ['change' => 1]) }}">Change</a>
                </div>
            @endif

            <button type="submit" class="signup-btn-primary" @disabled($otpPending)>
                <i class="bi bi-send-fill"></i> {{ $otpPending ? 'OTP Sent' : 'Generate OTP' }}
            </button>
        </form>

        <div class="signup-otp-section {{ $otpPending ? 'show' : '' }}">
            <div class="signup-divider">
                <i class="bi bi-shield-lock" style="color: var(--gold);"></i>
            </div>

            <form method="POST" action="{{ route('register.verify-otp', ['type' => 'pandit']) }}" class="signup-otp-box otp-form">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">
                <input type="hidden" name="otp" class="otp-hidden">

                <div class="signup-otp-sent-info">
                    <i class="bi bi-envelope-check-fill"></i>
                    <span>OTP sent to <strong>{{ $email ?: 'your Gmail' }}</strong></span>
                </div>

                @if ($devOtp)
                    <div class="signup-dev-otp">
                        <span>Local dev OTP</span>
                        <strong>{{ $devOtp }}</strong>
                    </div>
                @endif

                <div class="signup-otp-heading">Enter 6 digit OTP</div>
                <p class="signup-otp-subtext">Verify karte hi pandit account create ho jayega.</p>

                <div class="signup-otp-inputs otp-inputs">
                    @for ($i = 0; $i < 6; $i++)
                        <input type="text" class="signup-otp-box-input" maxlength="1" inputmode="numeric" autocomplete="one-time-code">
                    @endfor
                </div>

                <button class="signup-btn-verify" type="submit">
                    <i class="bi bi-shield-check"></i> Verify & Create Pandit Account
                </button>

                <div class="signup-otp-links">
                    <button type="submit" form="panditSignupForm" class="signup-link-button">Resend OTP</button>
                    <span style="color: rgba(199, 141, 34, 0.3);">|</span>
                    <a href="{{ route('pandit.register', ['change' => 1]) }}">Change Gmail</a>
                </div>
            </form>
        </div>

        <div class="signup-bottom-link">
            Already registered? <a href="{{ route('login') }}">Login</a>
            <span class="signup-alt-link">User ho? <a href="{{ route('signup') }}">Register as User</a></span>
        </div>
    </div>
</main>

@push('scripts')
<script>
    document.querySelectorAll('.js-phone-input').forEach((input) => {
        input.addEventListener('input', () => {
            input.value = input.value.replace(/\D/g, '').slice(0, 10);
        });
    });

    document.querySelectorAll('.otp-form').forEach((form) => {
        const inputs = Array.from(form.querySelectorAll('.otp-inputs input'));
        const hidden = form.querySelector('.otp-hidden');

        inputs.forEach((input, index) => {
            input.addEventListener('input', () => {
                input.value = input.value.replace(/\D/g, '').slice(0, 1);
                input.classList.toggle('filled', Boolean(input.value));

                if (input.value && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            });

            input.addEventListener('keydown', (event) => {
                if (event.key === 'Backspace' && !input.value && index > 0) {
                    inputs[index - 1].focus();
                }
            });

            input.addEventListener('paste', (event) => {
                event.preventDefault();
                event.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6).split('').forEach((digit, digitIndex) => {
                    inputs[digitIndex].value = digit;
                    inputs[digitIndex].classList.add('filled');
                });
            });
        });

        form.addEventListener('submit', (event) => {
            hidden.value = inputs.map((input) => input.value).join('');

            if (hidden.value.length !== 6) {
                event.preventDefault();
                inputs.find((input) => !input.value)?.focus();
            }
        });
    });
</script>
@endpush

@endsection
