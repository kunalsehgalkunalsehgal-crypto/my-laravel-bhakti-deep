@extends('layouts.app')

@section('title', 'Booked Pooja Live Sessions - BhaktiDeep')
@section('description', 'View booked live pooja sessions with sankalp, package, date, slot and live room links.')

@push('styles')
<link href="{{ asset('css/light-diya.css') }}" rel="stylesheet">
<style>
    .pooja-live-list-hero h1 {
        max-width: 820px;
        font-size: clamp(32px, 5vw, 58px);
    }

    .pooja-live-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 360px));
        justify-content: center;
        gap: 18px;
    }

    .pooja-live-card {
        min-height: 100%;
        padding: 22px;
        border: 1px solid rgba(199, 141, 34, .24);
    }

    .pooja-live-card-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
    }

    .pooja-live-icon {
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

    .pooja-live-status {
        padding: 5px 10px;
        border-radius: 999px;
        background: rgba(255, 122, 0, .14);
        color: var(--saffron);
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .pooja-live-card h3 {
        margin: 0 0 6px;
    }

    .pooja-live-meta {
        display: grid;
        gap: 10px;
        margin: 18px 0;
    }

    .pooja-live-meta span {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding-bottom: 10px;
        border-bottom: 1px solid rgba(199, 141, 34, .16);
        color: var(--muted);
        font-size: 13px;
    }

    .pooja-live-meta strong {
        color: var(--cream);
        text-align: right;
    }

    .empty-pooja-live {
        padding: 28px;
        text-align: center;
    }

    @media (min-width: 576px) and (max-width: 991.98px) {
        .pooja-live-grid {
            grid-template-columns: repeat(2, minmax(0, 360px));
        }
    }

    @media (max-width: 575.98px) {
        .pooja-live-grid {
            grid-template-columns: minmax(0, 360px);
        }
    }
</style>
@endpush

@section('body')
<main class="page-shell">
    <section class="container ld-hero pooja-live-list-hero">
        <span class="eyebrow"><i class="bi bi-flower1"></i> BOOKED POOJA SESSIONS</span>
        <h1 class="mt-4"><span>All <span class="gold-text">Live Pooja Bookings</span></span></h1>
        <p class="mt-4">Yahan par paid pooja bookings dikhenge: kisne pooja book ki, kaunsa package select kiya, aur session kab hoga.</p>
        <div class="hero-buttons mt-4">
            <a href="{{ route('live.sessions') }}" class="btn btn-ghost-gold btn-lg"><i class="bi bi-arrow-left"></i> Live Sessions</a>
            <a href="{{ route('personalized-pooja') }}" class="btn btn-saffron btn-lg"><i class="bi bi-plus-circle"></i> Book New Pooja</a>
        </div>
    </section>

    <section class="container page-section">
        @if ($poojaBookings->count())
            <div class="pooja-live-grid">
                @foreach ($poojaBookings as $booking)
                    @php
                        $meta = $booking->admin_note ? (json_decode($booking->admin_note, true) ?: []) : [];
                        $poojaName = $meta['pooja_name'] ?? $booking->service?->name ?? 'Live Pooja';
                        $packageName = $meta['package_name'] ?? '-';
                        $totalAmount = isset($meta['total_amount']) ? 'Rs.'.number_format((float) $meta['total_amount']) : '-';
                        $dateLabel = $booking->booking_date ? $booking->booking_date->format('d M Y') : '-';
                        $canJoinMeeting = $booking->payment_status === 'paid'
                            && $booking->status === 'confirmed'
                            && $booking->videoMeeting;
                    @endphp
                    <article class="glass pooja-live-card">
                        <div class="pooja-live-card-head">
                            <span class="pooja-live-icon"><i class="bi bi-flower1"></i></span>
                            <span class="pooja-live-status">{{ $booking->payment_status }}</span>
                        </div>
                        <h3>{{ $poojaName }}</h3>
                        <div class="pooja-live-meta">
                            <span>Booked By <strong>{{ $booking->sankalp?->full_name ?? '-' }}</strong></span>
                            <span>Mobile <strong>{{ $booking->sankalp?->mobile ?? '-' }}</strong></span>
                            <span>Purpose <strong>{{ $booking->sankalp?->purpose ?? '-' }}</strong></span>
                            <span>Package <strong>{{ $packageName }}</strong></span>
                            <span>Date <strong>{{ $dateLabel }}</strong></span>
                            <span>Slot <strong>{{ $booking->slot ?: '-' }}</strong></span>
                            <span>Total Paid <strong>{{ $totalAmount }}</strong></span>
                            <span>Status <strong>{{ ucfirst($booking->status) }}</strong></span>
                        </div>
                        @if ($canJoinMeeting)
                            <a href="{{ route('live.session.join', ['type' => 'pooja', 'id' => $booking->id]) }}" target="_blank" rel="noopener" class="btn btn-gold w-100">
                                Join Pooja <i class="bi bi-arrow-right"></i>
                            </a>
                        @else
                            <span class="btn btn-ghost-gold w-100 disabled">Live link pending</span>
                        @endif
                    </article>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $poojaBookings->links() }}
            </div>
        @else
            <div class="glass empty-pooja-live">
                <h3>No Pooja Bookings Yet</h3>
                <p class="mt-2">Abhi koi paid pooja booking nahi mili.</p>
                <a href="{{ route('personalized-pooja') }}" class="btn btn-saffron mt-2"><i class="bi bi-flower1"></i> Book Pooja</a>
            </div>
        @endif
    </section>
</main>
@endsection
