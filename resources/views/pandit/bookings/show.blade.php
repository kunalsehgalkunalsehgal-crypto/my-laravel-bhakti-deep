@extends('layouts.pandit-dashboard')

@section('title', $booking['booking_id'].' - BhaktiDeep')

@php $activeMenu = 'bookings'; @endphp

@section('content')
<div class="pandit-page-heading">
    <div>
        <p>{{ $booking['label'] }} Booking</p>
        <h1>{{ $booking['service_name'] }}</h1>
    </div>
    <a href="{{ route('pandit.bookings.index') }}" class="pandit-add-btn">
        <i class="bi bi-arrow-left"></i> All Bookings
    </a>
</div>

<section class="pandit-panel">
    @if(session('success'))<div style="color:green;margin-bottom:12px">{{ session('success') }}</div>@endif
    @if(session('review_otp_preview'))<div style="color:#8a6d52;margin-bottom:12px">Dev OTP: {{ session('review_otp_preview') }}</div>@endif
    @if($errors->any())<div style="color:#b42318;margin-bottom:12px">{{ $errors->first() }}</div>@endif

    <div class="pandit-panel-heading">
        <div>
            <h2>Booking Details</h2>
            <p>{{ $booking['booking_id'] }}</p>
        </div>
        <span><i class="bi bi-receipt"></i></span>
    </div>

    <div class="pandit-profile-grid">
        <div class="pandit-profile-item">
            <span>Service Name</span>
            <strong>{{ $booking['service_name'] }}</strong>
        </div>
        <div class="pandit-profile-item">
            <span>Booking ID</span>
            <strong>{{ $booking['booking_id'] }}</strong>
        </div>
        <div class="pandit-profile-item">
            <span>Yajman</span>
            <strong>{{ $booking['yajman'] }}</strong>
        </div>
        <div class="pandit-profile-item">
            <span>Gotra</span>
            <strong>{{ $booking['gotra'] ?: 'Not added' }}</strong>
        </div>
        <div class="pandit-profile-item pandit-wide">
            <span>Purpose</span>
            <strong>{{ $booking['purpose'] }}</strong>
        </div>
        <div class="pandit-profile-item">
            <span>Date</span>
            <strong>{{ $booking['booking_date']?->format('d M Y') ?? 'Pending' }}</strong>
        </div>
        <div class="pandit-profile-item">
            <span>Slot</span>
            <strong>{{ $booking['slot'] ?: 'Pending' }}</strong>
        </div>
        <div class="pandit-profile-item">
            <span>Booking Mode</span>
            <strong>{{ ucfirst($booking['booking_mode']) }}</strong>
        </div>
        @if($booking['booking_mode'] === 'offline')
            <div class="pandit-profile-item">
                <span>State</span>
                <strong>{{ $booking['state'] ?: 'Not added' }}</strong>
            </div>
            <div class="pandit-profile-item">
                <span>City</span>
                <strong>{{ $booking['city'] ?: 'Not added' }}</strong>
            </div>
        @endif
        <div class="pandit-profile-item">
            <span>Package / Type</span>
            <strong>{{ $booking['package_name'] ?: $booking['label'] }}</strong>
        </div>
        <div class="pandit-profile-item">
            <span>Dakshina</span>
            <strong>Rs {{ number_format($booking['dakshina']) }}</strong>
        </div>
        <div class="pandit-profile-item">
            <span>Total Amount</span>
            <strong>Rs {{ number_format($booking['total_amount']) }}</strong>
        </div>
        <div class="pandit-profile-item">
            <span>Payment Status</span>
            <strong>{{ ucfirst(str_replace('_', ' ', $booking['payment_status'] ?? 'pending')) }}</strong>
        </div>
        <div class="pandit-profile-item">
            <span>Booking Status</span>
            <strong>{{ ucfirst(str_replace('_', ' ', $booking['status'])) }}</strong>
        </div>
        <div class="pandit-profile-item">
            <span>Pandit Name</span>
            <strong>{{ $booking['pandit_name'] }}</strong>
        </div>
    </div>

    @include('partials.review-form', [
        'booking' => $session,
        'bookingType' => $booking['type'],
        'reviewBy' => 'pandit',
        'title' => 'Review Yajman',
        'sendOtpRoute' => route('pandit.reviews.image-otp'),
        'verifyOtpRoute' => route('pandit.reviews.image-otp.verify'),
        'storeRoute' => route('pandit.reviews.store'),
    ])

    <div class="pandit-session-actions" style="margin-top:22px">
        @if($booking['can_accept'])
            <form method="POST" action="{{ $booking['accept_url'] }}">
                @csrf
                <button type="submit" class="primary">
                    <i class="bi bi-check2-circle"></i> Accept Booking
                </button>
            </form>
        @elseif($booking['can_start_meeting'])
            <a href="{{ $booking['meeting_start_url'] }}" class="primary">
                <i class="bi bi-camera-video"></i> Start {{ $booking['label'] }}
            </a>
        @else
            <span class="pandit-muted-action">Live link pending</span>
        @endif
        @if($booking['can_cancel'])
            <form method="POST" action="{{ $booking['cancel_url'] }}">
                @csrf
                <div class="pandit-profile-item pandit-wide" style="margin-bottom:12px">
                    <span>Cancel Warning</span>
                    <strong>Full refund will go to user. Pandit gets no payout. Same Pandit slot stays blocked until original slot ends.</strong>
                </div>
                <label class="pandit-profile-item pandit-wide" style="display:block;margin-bottom:12px">
                    <span>Cancellation Reason</span>
                    <textarea name="reason" rows="3" required style="width:100%;margin-top:8px"></textarea>
                </label>
                <button type="submit" class="primary">
                    <i class="bi bi-x-circle"></i> Cancel Booking
                </button>
            </form>
        @endif
    </div>
</section>
@endsection
