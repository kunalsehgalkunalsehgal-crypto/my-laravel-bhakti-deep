@extends('layouts.app')

@section('title', 'Issue Report - BhaktiDeep')
@section('description', 'View your submitted BhaktiDeep issue report.')

@push('styles')
<style>
    .report-page {
        color: var(--cream);
    }

    .report-hero {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 18px;
        margin-bottom: 24px;
    }

    .report-hero h1 {
        margin-bottom: 8px;
        font-size: clamp(28px, 4vw, 46px);
    }

    .report-status {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border: 1px solid rgba(199, 141, 34, .28);
        border-radius: 999px;
        color: var(--gold);
        background: rgba(199, 141, 34, .12);
        white-space: nowrap;
    }

    .report-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(280px, .8fr);
        gap: 24px;
    }

    .report-panel {
        padding: 24px;
    }

    .report-detail-list {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .report-detail {
        border: 1px solid rgba(199, 141, 34, .18);
        border-radius: 12px;
        padding: 14px;
        background: rgba(251, 244, 223, .06);
        min-width: 0;
    }

    .report-detail small {
        display: block;
        margin-bottom: 6px;
        color: var(--muted);
    }

    .report-detail strong {
        display: block;
        overflow-wrap: anywhere;
    }

    .report-description {
        margin-top: 18px;
        padding-top: 18px;
        border-top: 1px solid rgba(199, 141, 34, .18);
        white-space: pre-wrap;
    }

    .proof-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid rgba(199, 141, 34, .14);
    }

    .proof-row:last-child {
        border-bottom: 0;
    }

    .proof-row strong {
        min-width: 0;
        overflow-wrap: anywhere;
    }

    .proof-row small {
        display: block;
        color: var(--muted);
        margin-top: 3px;
    }

    @media (max-width: 991.98px) {
        .report-hero,
        .report-grid {
            display: block;
        }

        .report-status {
            margin-top: 12px;
        }

        .report-detail-list {
            grid-template-columns: 1fr;
        }

        .report-panel + .report-panel {
            margin-top: 24px;
        }
    }
</style>
@endpush

@section('body')
@php
    $bookingDate = $booking?->booking_date ? $booking->booking_date->format('d M Y') : '-';
    $bookingSlot = $booking?->slot ?: '-';
    $panditName = $dispute->pandit?->pandit_name ?: ($dispute->pandit?->full_name ?: ($booking?->pandit?->pandit_name ?: ($booking?->pandit?->full_name ?: '-')));
@endphp

<main class="page-shell report-page">
    <section class="container py-4 py-md-5">
        <div class="report-hero">
            <div>
                <a href="{{ $booking ? route('live.session', ['type' => strtolower($reportType), 'id' => $booking->id]) : route('live.sessions') }}" class="btn btn-ghost-gold btn-sm mb-3">
                    <i class="bi bi-arrow-left"></i> Live Session
                </a>
                <h1>Issue Report #{{ $dispute->id }}</h1>
                <p class="session-note mb-0">Submitted {{ $dispute->opened_at?->format('d M Y, h:i A') ?: $dispute->created_at?->format('d M Y, h:i A') }}</p>
            </div>
            <span class="report-status"><i class="bi bi-exclamation-circle"></i> {{ $reportStatus }}</span>
        </div>

        <div class="report-grid">
            <div class="glass report-panel">
                <h3>Report Details</h3>
                <div class="report-detail-list mt-3">
                    <div class="report-detail"><small>Report ID</small><strong>#{{ $dispute->id }}</strong></div>
                    <div class="report-detail"><small>Booking ID</small><strong>#{{ $booking?->id ?: '-' }}</strong></div>
                    <div class="report-detail"><small>Type</small><strong>{{ $reportType }}</strong></div>
                    <div class="report-detail"><small>Service</small><strong>{{ $serviceName }}</strong></div>
                    <div class="report-detail"><small>Pandit</small><strong>{{ $panditName }}</strong></div>
                    <div class="report-detail"><small>Date / Slot</small><strong>{{ $bookingDate }} / {{ $bookingSlot }}</strong></div>
                    <div class="report-detail"><small>Reason</small><strong>{{ $reportReason }}</strong></div>
                    <div class="report-detail"><small>Status</small><strong>{{ $reportStatus }}</strong></div>
                </div>
                <div class="report-description">
                    <small class="d-block text-muted mb-2">Description</small>
                    {{ $dispute->description }}
                </div>
            </div>

            <aside class="glass report-panel">
                <h3>Uploaded Proof</h3>
                @forelse ($dispute->evidences as $evidence)
                    <div class="proof-row">
                        <strong>
                            {{ $evidence->original_name }}
                            <small>{{ $evidence->mime_type }} - {{ number_format($evidence->file_size / 1024, 1) }} KB</small>
                        </strong>
                        <a href="{{ route('user.reports.evidence', ['dispute' => $dispute, 'evidence' => $evidence]) }}" class="btn btn-ghost-gold btn-sm" target="_blank" rel="noopener">
                            <i class="bi bi-eye"></i> View
                        </a>
                    </div>
                @empty
                    <p class="session-note mb-0">No proof uploaded.</p>
                @endforelse
            </aside>
        </div>
    </section>
</main>
@endsection
