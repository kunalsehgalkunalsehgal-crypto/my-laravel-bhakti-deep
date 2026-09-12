@extends('layouts.app')

@section('title', 'My Profile - BhaktiDeep')
@section('description', 'View and edit your BhaktiDeep profile and bookings.')

@push('styles')
<style>
.profile-page { background: linear-gradient(180deg, #fff8e8, #fbf4df); }
.profile-wrap { padding: 46px 12px 70px; }
.profile-head { display: flex; align-items: center; justify-content: space-between; gap: 18px; flex-wrap: wrap; }
.profile-actions { display: flex; gap: 10px; flex-wrap: wrap; }
.profile-head h1 { color: var(--cream); font-size: 38px; margin: 0; }
.profile-card { border: 1px solid rgba(199,141,34,.22); border-radius: 18px; background: rgba(255,255,255,.52); padding: 22px; height: 100%; }
.profile-avatar { width: 58px; height: 58px; display: grid; place-items: center; border-radius: 50%; background: linear-gradient(135deg, var(--gold), var(--saffron)); color: #fff; font-size: 28px; }
.profile-field span, .booking-item span { color: var(--muted); display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; }
.profile-field strong, .booking-item strong { color: var(--cream); display: block; margin-top: 3px; overflow-wrap: anywhere; }
.booking-item { border-top: 1px solid rgba(199,141,34,.18); padding: 14px 0; }
.booking-item:first-child { border-top: 0; padding-top: 0; }
.booking-meta { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 8px; }
.booking-pill { border-radius: 999px; background: rgba(232,91,33,.1); color: #9b4c14; font-size: 12px; font-weight: 700; padding: 5px 10px; }
.booking-details { display: grid; gap: 8px; margin-top: 12px; }
.booking-details div { display: flex; justify-content: space-between; gap: 12px; border-top: 1px dashed rgba(199,141,34,.16); padding-top: 8px; }
.booking-details small { color: var(--muted); font-weight: 700; text-transform: uppercase; }
.booking-details b { color: var(--cream); font-size: 13px; text-align: right; overflow-wrap: anywhere; }
.booking-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
.profile-form-grid { display: grid; gap: 14px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
.profile-form-grid .full { grid-column: 1 / -1; }
@media (max-width: 575px) {
    .profile-head h1 { font-size: 30px; }
    .profile-form-grid { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('body')
@include('partials.razorpay-checkout')

<main class="profile-page">
    <section class="container profile-wrap">
        <div class="profile-head mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="profile-avatar"><i class="bi bi-person"></i></div>
                <div>
                    <h1>My Profile</h1>
                    <p class="mb-0 text-muted">Manage your details and bookings.</p>
                </div>
            </div>
            <div class="profile-actions">
                <button class="btn btn-saffron rounded-pill" type="button" data-bs-toggle="collapse" data-bs-target="#editProfile">
                    <i class="bi bi-pencil-square"></i> Edit Profile
                </button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-outline-saffron rounded-pill" type="submit">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('review_otp_preview'))
            <div class="alert alert-info">Dev OTP: {{ session('review_otp_preview') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="profile-card">
                    <div class="row g-3">
                        @foreach ([
                            'Name' => $user->name,
                            'Email' => $user->email,
                            'Mobile' => $user->mobile,
                            'DOB' => $user->dob?->toDateString(),
                            'Gotra' => $user->gotra,
                            'Birth Place' => $user->birth_place,
                            'Address' => $user->address,
                        ] as $label => $value)
                            <div class="col-sm-6 {{ $label === 'Address' ? 'col-12' : '' }}">
                                <div class="profile-field">
                                    <span>{{ $label }}</span>
                                    <strong>{{ filled($value) ? $value : '-' }}</strong>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="collapse" id="editProfile">
                    <div class="profile-card">
                        <form method="POST" action="{{ route('user.profile.update') }}">
                            @csrf
                            @method('PUT')

                            <div class="profile-form-grid">
                                <label class="small-label">Name
                                    <input class="form-control sacred-input mt-1" name="name" value="{{ old('name', $user->name) }}" required>
                                </label>
                                <label class="small-label">Mobile
                                    <input class="form-control sacred-input mt-1" name="mobile" value="{{ old('mobile', $user->mobile) }}">
                                </label>
                                <label class="small-label">DOB
                                    <input type="date" class="form-control sacred-input mt-1" name="dob" value="{{ old('dob', $user->dob?->toDateString()) }}">
                                </label>
                                <label class="small-label">Gotra
                                    <input class="form-control sacred-input mt-1" name="gotra" value="{{ old('gotra', $user->gotra) }}">
                                </label>
                                <label class="small-label">Birth Place
                                    <input class="form-control sacred-input mt-1" name="birth_place" value="{{ old('birth_place', $user->birth_place) }}">
                                </label>
                                <label class="small-label full">Address
                                    <textarea class="form-control sacred-input mt-1" name="address" rows="3">{{ old('address', $user->address) }}</textarea>
                                </label>
                            </div>

                            @if ($errors->any())
                                <div class="alert alert-danger mt-3 mb-0">{{ $errors->first() }}</div>
                            @endif

                            <button class="btn btn-saffron rounded-pill mt-3" type="submit">
                                <i class="bi bi-check2-circle"></i> Save Profile
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5">
            <h2 class="mb-3">My Bookings</h2>
            @foreach($bookingNotifications as $notification)
                <div class="alert alert-info">{{ $notification->message }}</div>
            @endforeach

            <div class="row g-4">
                @foreach ([
                    'Diya' => $diyaBookings,
                    'Pooja' => $poojaBookings,
                    'Hawan' => $hawanBookings,
                ] as $type => $bookings)
                    <div class="col-lg-4">
                        <div class="profile-card">
                            <h3 class="h5 mb-3">{{ $type }} Bookings</h3>

                            @forelse ($bookings as $booking)
                                @php
                                    $meta = $booking->admin_note ? (json_decode($booking->admin_note, true) ?: []) : [];
                                    $title = $type === 'Diya'
                                        ? ($booking->diya?->name ?? $meta['diya_name'] ?? 'Diya Offering')
                                        : ($booking->service?->name ?? $meta[strtolower($type).'_name'] ?? $type.' Booking');
                                    $canRetryPayment = !in_array($booking->status, ['cancelled', 'cancelled_by_pandit', 'completed', 'refunded'], true)
                                        && in_array($booking->payment_status, ['pending', 'failed'], true);
                                    $diyaAmount = $meta['donation_amount'] ?? $meta['total_amount'] ?? $booking->latestPaymentAttempt?->amount;
                                @endphp

                                <div class="booking-item">
                                    <span>{{ $booking->booking_date?->format('d M Y') ?? '-' }}</span>
                                    <strong>{{ $title }}</strong>
                                    <div class="booking-meta">
                                        <em class="booking-pill">{{ ucfirst($booking->status) }}</em>
                                        <em class="booking-pill">{{ ucfirst($booking->payment_status) }}</em>
                                        @if($booking->latestPaymentAttempt)
                                            <em class="booking-pill">{{ ucfirst(str_replace('_', ' ', $booking->latestPaymentAttempt->status)) }}</em>
                                        @endif
                                    </div>

                                    @if($type === 'Diya')
                                        <div class="booking-details">
                                            <div><small>Deity</small><b>{{ $booking->deity?->name ?? $meta['deity_name'] ?? '-' }}</b></div>
                                            <div><small>Donation</small><b>{{ $diyaAmount ? 'Rs.'.number_format((float) $diyaAmount, 2) : '-' }}</b></div>
                                            <div><small>Payment Status</small><b>{{ ucfirst($booking->payment_status ?? 'pending') }}</b></div>
                                            <div><small>Diya Status</small><b>{{ ucfirst($booking->status ?? 'pending') }}</b></div>
                                            <div><small>Start Time</small><b>{{ $booking->start_at?->format('d M Y, h:i A') ?? '-' }}</b></div>
                                            <div><small>End Time</small><b>{{ $booking->end_at?->format('d M Y, h:i A') ?? '-' }}</b></div>
                                        </div>
                                    @else
                                        <div class="booking-details">
                                            <div><small>Pandit</small><b>{{ $booking->pandit?->pandit_name ?: ($booking->pandit?->full_name ?: ($meta['pandit_name'] ?? '-')) }}</b></div>
                                            <div><small>Date and Time</small><b>{{ $booking->booking_date?->format('d M Y') ?? '-' }} {{ $booking->slot ?: '' }}</b></div>
                                            <div><small>Mode</small><b>{{ ucfirst($booking->booking_mode ?: ($meta['booking_mode'] ?? 'online')) }}</b></div>
                                            @if(($booking->booking_mode ?: ($meta['booking_mode'] ?? 'online')) === 'offline')
                                                <div><small>Location</small><b>{{ collect([$booking->city ?: ($meta['city'] ?? null), $booking->state ?: ($meta['state'] ?? null)])->filter()->join(', ') ?: '-' }}</b></div>
                                            @endif
                                        </div>
                                    @endif

                                    @if($type !== 'Diya')
                                        @include('partials.review-form', [
                                            'booking' => $booking,
                                            'bookingType' => strtolower($type),
                                            'reviewBy' => 'user',
                                            'title' => 'Review Pandit',
                                            'sendOtpRoute' => route('reviews.image-otp'),
                                            'verifyOtpRoute' => route('reviews.image-otp.verify'),
                                            'storeRoute' => route('reviews.store'),
                                        ])
                                    @endif

                                    <div class="booking-actions">
                                        @if($type === 'Diya')
                                            <a class="btn btn-outline-saffron btn-sm rounded-pill" href="{{ route('diya.session', $booking) }}">
                                                <i class="bi bi-eye"></i> View Diya
                                            </a>
                                        @else
                                            <a class="btn btn-outline-saffron btn-sm rounded-pill" href="{{ route('live.session', ['type' => strtolower($type), 'id' => $booking->id]) }}">
                                                <i class="bi bi-eye"></i> View Details
                                            </a>
                                        @endif

                                        @if($canRetryPayment)
                                            <button
                                                class="btn btn-saffron btn-sm rounded-pill"
                                                type="button"
                                                data-retry-payment
                                                data-retry-url="{{ route('payments.bookings.retry', ['type' => strtolower($type), 'id' => $booking->id]) }}"
                                            >
                                                <i class="bi bi-arrow-clockwise"></i> Retry Payment
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted mb-0">No {{ strtolower($type) }} bookings yet.</p>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</main>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-retry-payment]').forEach(function (button) {
        button.addEventListener('click', async function () {
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="bi bi-hourglass-split"></i> Starting Payment...';

            try {
                const response = await fetch(button.dataset.retryUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Payment retry failed.');
                }

                window.startBhaktiDeepPayment(result.payment, button, function () {
                    button.disabled = false;
                    button.innerHTML = originalText;
                });
            } catch (error) {
                alert(error.message || 'Payment retry failed.');
                button.disabled = false;
                button.innerHTML = originalText;
            }
        });
    });
</script>
@endpush
