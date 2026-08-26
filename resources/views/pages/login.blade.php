@extends('layouts.app')

@section('title', 'Login - BhaktiDeep | Har Deep Mein Bhakti')
@section('description', 'Login to BhaktiDeep with your Gmail OTP as a user or pandit.')

@push('styles')
    <link href="{{ asset('css/login.css') }}" rel="stylesheet">
@endpush

@section('body')
@php
    $particles = 8;
    $otpFlow = session('otp_flow');
    $otpPending = (($otpFlow['context'] ?? null) === 'login') || old('_otp_pending') === 'login';
    $loginEmail = old('email', $otpFlow['email'] ?? '');
    $devOtp = $otpPending && (app()->environment('local') || config('mail.default') === 'log') ? ($otpFlow['otp_preview'] ?? null) : null;
@endphp

<main class="login-page">
    <div class="login-bg-temple"></div>
    <div class="login-mandala"></div>
    <div class="login-particles">
        @for ($i = 0; $i < $particles; $i++)
            <span style="left: {{ ($i * 53) % 100 }}%; animation-delay: {{ ($i * 0.5) % 4 }}s;"></span>
        @endfor
    </div>

    <div class="login-card">
        <div class="login-logo">
            <div class="login-logo-icon"><i class="bi bi-fire"></i></div>
            <div class="login-logo-text">BhaktiDeep</div>
            <div class="login-logo-tagline">Har Deep Mein Bhakti</div>
        </div>

        <h1 class="login-heading">Welcome Back</h1>
        <p class="login-subtext">User aur Pandit dono ek hi page se Gmail OTP ke through login kar sakte hain.</p>

        @if (session('success'))
            <div class="login-alert login-alert-success">
                <i class="bi bi-check-circle-fill"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="login-alert login-alert-error">
                <i class="bi bi-exclamation-circle-fill"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login.send-otp') }}" id="loginEmailForm">
            @csrf
            <label class="login-form-label">Gmail Address <span class="required">*</span></label>
            <div class="login-mobile-group">
                <div class="login-country-code"><i class="bi bi-envelope-at"></i></div>
                <input
                    type="email"
                    name="email"
                    class="login-mobile-input"
                    placeholder="yourname@gmail.com"
                    value="{{ $loginEmail }}"
                    autocomplete="email"
                    required
                    @readonly($otpPending)
                >
            </div>

            @if ($otpPending)
                <div class="login-mobile-confirmed show">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>{{ $loginEmail }}</span>
                    <a class="change-btn" href="{{ route('login', ['change' => 1]) }}">Change</a>
                </div>
            @endif

            <button class="login-btn-primary" type="submit" @disabled($otpPending)>
                <i class="bi bi-send-fill"></i> {{ $otpPending ? 'OTP Sent' : 'Generate OTP' }}
            </button>
        </form>

        <div class="login-otp-section {{ $otpPending ? 'show' : '' }}" id="otpSection">
            <div class="login-divider">
                <i class="bi bi-shield-lock" style="color: var(--gold);"></i>
            </div>

            <form method="POST" action="{{ route('login.verify-otp') }}" class="login-otp-box otp-form">
                @csrf
                <input type="hidden" name="email" value="{{ $loginEmail }}">
                <input type="hidden" name="otp" class="otp-hidden">

                <div class="login-otp-sent-info">
                    <i class="bi bi-envelope-check-fill"></i>
                    <span>OTP sent to <strong>{{ $loginEmail ?: 'your Gmail' }}</strong></span>
                </div>

                @if ($devOtp)
                    <div class="login-dev-otp">
                        <span>Local dev OTP</span>
                        <strong>{{ $devOtp }}</strong>
                    </div>
                @endif

                <div class="login-otp-heading">Enter 6 digit OTP</div>
                <p class="login-otp-subtext">Code 10 minutes ke liye valid hai.</p>

                <div class="login-otp-inputs otp-inputs">
                    @for ($i = 0; $i < 6; $i++)
                        <input type="text" class="login-otp-box-input" maxlength="1" inputmode="numeric" autocomplete="one-time-code">
                    @endfor
                </div>

                <button class="login-btn-verify" type="submit">
                    <i class="bi bi-shield-check"></i> Verify & Continue
                </button>

                <div class="login-otp-links">
                    <button type="submit" form="loginEmailForm" class="login-link-button">Resend OTP</button>
                    <span style="color: rgba(199, 141, 34, 0.3);">|</span>
                    <a href="{{ route('login', ['change' => 1]) }}">Change Gmail</a>
                </div>
            </form>
        </div>

        <div class="login-bottom-link">
            <span>New to BhaktiDeep?</span>
            <div class="login-register-actions">
                <a href="{{ route('signup') }}">Register as User</a>
                <a href="{{ route('pandit.register') }}">Register as Pandit</a>
            </div>
        </div>
    </div>
</main>

@push('scripts')
<script>
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
