@extends('layouts.pandit-dashboard')

@section('title', 'Pandit Live Sessions - BhaktiDeep')

@php $activeMenu = 'live-sessions'; @endphp

@section('content')
<div class="pandit-page-heading">
    <div>
        <p>Secure Live Rooms</p>
        <h1>Live Hawan and Pooja</h1>
    </div>
    <a href="{{ route('pandit.dashboard') }}" class="pandit-add-btn">
        <i class="bi bi-grid-1x2-fill"></i> Dashboard
    </a>
</div>

<section class="pandit-panel pandit-booking-panel">
    <div class="pandit-panel-heading">
        <div>
            <h2>Ready Sessions</h2>
            <p>{{ $liveSessions->count() }} live room{{ $liveSessions->count() === 1 ? '' : 's' }} ready</p>
        </div>
        <span><i class="bi bi-camera-video"></i></span>
    </div>

    <div class="pandit-booking-list">
        @forelse($liveSessions as $session)
            <article class="pandit-booking-row">
                <div>
                    <span class="pandit-status-pill">{{ $session['label'] }}</span>
                    <h3>{{ $session['service_name'] }}</h3>
                    <p>{{ $session['booking_id'] }} - {{ $session['yajman'] }}</p>
                </div>
                <div>
                    <span>Date</span>
                    <strong>{{ $session['booking_date']?->format('d M Y') ?? 'Pending' }}</strong>
                </div>
                <div>
                    <span>Slot</span>
                    <strong>{{ $session['slot'] ?: 'Pending' }}</strong>
                </div>
                <div>
    <span>Mode</span>
    <strong>{{ ucfirst($session['booking_mode'] ?? 'online') }}</strong>
</div>
                <div>
                    <span>Status</span>
                    <strong>{{ ucfirst(str_replace('_', ' ', $session['status'])) }}</strong>
                </div>
                {{-- <div class="pandit-booking-actions">
                    <a href="{{ $session['url'] }}" class="primary">
                        <i class="bi bi-camera-video"></i> Open Live Room
                    </a>
                </div> --}}
                <div class="pandit-booking-actions">

    @if(($session['booking_mode'] ?? 'online') === 'offline')

        <a href="{{ $session['url'] }}" class="primary">
            <i class="bi bi-geo-alt"></i>
            View Booking Details
        </a>

    @else

        <a href="{{ $session['url'] }}" class="primary">
            <i class="bi bi-camera-video"></i>
            Open Live Room
        </a>

    @endif

</div>
            </article>
        @empty
            <div class="pandit-empty-slot">
                <i class="bi bi-camera-video-off"></i>
                No ready live rooms yet.
            </div>
        @endforelse
    </div>
</section>
@endsection
