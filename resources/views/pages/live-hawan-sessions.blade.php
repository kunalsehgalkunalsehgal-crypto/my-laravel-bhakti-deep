@extends('layouts.app')

@section('title', 'Booked Hawan Live Sessions - BhaktiDeep')
@section('description', 'View booked live hawan sessions with sankalp, package, date, slot and live room links.')

@push('styles')
<link href="{{ asset('css/light-diya.css') }}" rel="stylesheet">
<style>
    .hawan-live-list-hero h1 {
        max-width: 820px;
        font-size: clamp(32px, 5vw, 58px);
    }

    .hawan-live-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 360px));
        justify-content: center;
        gap: 18px;
    }

    .hawan-live-card {
        min-height: 100%;
        padding: 22px;
        border: 1px solid rgba(199, 141, 34, .24);
    }

    .hawan-live-card-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
    }

    .hawan-live-icon {
        width: 44px;
        height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: rgba(255, 122, 0, .14);
        color: var(--gold);
        font-size: 22px;
    }

    .hawan-live-status {
        padding: 5px 10px;
        border-radius: 999px;
        background: rgba(255, 122, 0, .14);
        color:var(--saffron);
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .hawan-live-card h3 {
        margin: 0 0 6px;
    }

    .hawan-live-card p {
        color: var(--muted);
        margin-bottom: 16px;
    }

    .hawan-live-meta {
        display: grid;
        gap: 10px;
        margin: 18px 0;
    }

    .hawan-live-meta span {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding-bottom: 10px;
        border-bottom: 1px solid rgba(199, 141, 34, .16);
        color: var(--muted);
        font-size: 13px;
    }

    .hawan-live-meta strong {
        color: var(--cream);
        text-align: right;
    }

    .empty-hawan-live {
        padding: 28px;
        text-align: center;
    }

    @media (min-width: 576px) and (max-width: 991.98px) {
        .hawan-live-grid {
            grid-template-columns: repeat(2, minmax(0, 360px));
        }
    }

    @media (max-width: 575.98px) {
        .hawan-live-grid {
            grid-template-columns: minmax(0, 360px);
        }
    }
</style>
@endpush

@section('body')
<main class="page-shell">
    <section class="container ld-hero hawan-live-list-hero">
        <span class="eyebrow"><i class="bi bi-fire"></i> BOOKED HAWAN SESSIONS</span>
        <h1 class="mt-4"><span>All <span class="gold-text">Live Hawan Bookings</span></span></h1>
        <p class="mt-4">Yahan par paid hawan bookings dikhenge: kisne hawan book kiya, kaunsa package select kiya, aur session kab hoga.</p>
        <div class="hero-buttons mt-4">
            <a href="{{ route('live.sessions') }}" class="btn btn-ghost-gold btn-lg"><i class="bi bi-arrow-left"></i> Live Sessions</a>
            <a href="{{ route('hawan') }}" class="btn btn-saffron btn-lg"><i class="bi bi-plus-circle"></i> Book New Hawan</a>
        </div>
    </section>

    <section class="container page-section">
        @if ($hawanBookings->count())
            <div class="hawan-live-grid">
                @foreach ($hawanBookings as $booking)
                    @php
                        $meta = $booking->admin_note ? (json_decode($booking->admin_note, true) ?: []) : [];
                        $hawanName = $meta['hawan_name'] ?? $booking->service?->name ?? 'Live Hawan';
                        $packageName = $booking->hawan_type_title ?? $meta['hawan_type_title'] ?? $meta['package_name'] ?? '-';
                        $totalAmount = isset($meta['total_amount']) ? 'Rs.'.number_format((float) $meta['total_amount']) : '-';
                        $dateLabel = $booking->booking_date ? $booking->booking_date->format('d M Y') : '-';
                        $canJoinMeeting = $booking->payment_status === 'paid'
                            && $booking->status === 'confirmed'
                            && $booking->videoMeeting;
                    @endphp
                    <article class="glass hawan-live-card">
                        <div class="hawan-live-card-head">
                            <span class="hawan-live-icon"><i class="bi bi-fire"></i></span>
                            <span class="hawan-live-status">{{ $booking->payment_status }}</span>
                        </div>
                        <h3>{{ $hawanName }}</h3>
                        {{-- <p>{{ $booking->sankalp?->full_name ?? 'Devotee' }} ke naam se booked hawan session.</p> --}}
                        <div class="hawan-live-meta">
                            <span>Booked By <strong>{{ $booking->sankalp?->full_name ?? '-' }}</strong></span>
                            <span>Mobile <strong>{{ $booking->sankalp?->mobile ?? '-' }}</strong></span>
                            <span>Purpose <strong>{{ $booking->sankalp?->purpose ?? '-' }}</strong></span>
                            <span>Hawan Type <strong>{{ $packageName }}</strong></span>
                            <span>Date <strong>{{ $dateLabel }}</strong></span>
                            <span>Slot <strong>{{ $booking->slot ?: '-' }}</strong></span>
                            <span>Total Paid <strong>{{ $totalAmount }}</strong></span>
                            <span>Status <strong>{{ ucfirst($booking->status) }}</strong></span>
                        </div>
                        @if ($canJoinMeeting)
                            <a href="{{ route('live.session.join', ['type' => 'hawan', 'id' => $booking->id]) }}" target="_blank" rel="noopener" class="btn btn-saffron w-100">
                                Join Hawan <i class="bi bi-arrow-right"></i>
                            </a>
                        @else
                            <span class="btn btn-ghost-gold w-100 disabled">Live link pending</span>
                        @endif
                    </article>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $hawanBookings->links() }}
            </div>
        @else
            <div class="glass empty-hawan-live">
                <h3>No Hawan Bookings Yet</h3>
                <p class="mt-2">Abhi koi paid hawan booking nahi mili.</p>
                <a href="{{ route('hawan') }}" class="btn btn-saffron mt-2"><i class="bi bi-fire"></i> Book Hawan</a>
            </div>
        @endif
    </section>
</main>
@endsection
