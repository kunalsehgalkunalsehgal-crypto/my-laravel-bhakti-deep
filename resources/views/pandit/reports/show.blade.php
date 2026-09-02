@extends('layouts.pandit-dashboard')

@section('title', 'Issue Report #'.$dispute->id.' - BhaktiDeep')

@php $activeMenu = 'reports'; @endphp

@section('content')
@php
    $bookingDate = $booking?->booking_date ? $booking->booking_date->format('d M Y') : 'Pending';
    $bookingSlot = $booking?->slot ?: 'Pending';
    $submittedAt = $dispute->opened_at ?: $dispute->created_at;
@endphp

<div class="pandit-page-heading">
    <div>
        <p>{{ $reportType }} Report</p>
        <h1>Issue Report #{{ $dispute->id }}</h1>
    </div>
    <a href="{{ route('pandit.reports.index') }}" class="pandit-add-btn">
        <i class="bi bi-arrow-left"></i> Reports
    </a>
</div>

@if(session('success'))
    <div class="pandit-note">{{ session('success') }}</div>
@endif

@if(session('info'))
    <div class="pandit-note">{{ session('info') }}</div>
@endif

<section class="pandit-panel">
    <div class="pandit-panel-heading">
        <div>
            <h2>User Report</h2>
            <p>Submitted {{ $submittedAt?->format('d M Y, h:i A') ?? 'recently' }}</p>
        </div>
        <span><i class="bi bi-exclamation-circle"></i></span>
    </div>

    <div class="pandit-profile-grid">
        <div class="pandit-profile-item">
            <span>Booking ID</span>
            <strong>{{ strtoupper(strtolower($reportType)).'-'.$booking?->id }}</strong>
        </div>
        <div class="pandit-profile-item">
            <span>Type</span>
            <strong>{{ $reportType }}</strong>
        </div>
        <div class="pandit-profile-item">
            <span>Date and Slot</span>
            <strong>{{ $bookingDate }} / {{ $bookingSlot }}</strong>
        </div>
        <div class="pandit-profile-item">
            <span>Service</span>
            <strong>{{ $serviceName }}</strong>
        </div>
        <div class="pandit-profile-item">
            <span>Reason</span>
            <strong>{{ $reportReason }}</strong>
        </div>
        <div class="pandit-profile-item">
            <span>Report Status</span>
            <strong>{{ $reportStatus }}</strong>
        </div>
        <div class="pandit-profile-item pandit-wide">
            <span>User Description</span>
            <strong style="white-space:pre-wrap">{{ $dispute->description }}</strong>
        </div>
    </div>
</section>

<section class="pandit-panel">
    <div class="pandit-panel-heading">
        <div>
            <h2>User Uploaded Proof</h2>
            <p>{{ $userEvidences->count() }} proof file{{ $userEvidences->count() === 1 ? '' : 's' }}</p>
        </div>
        <span><i class="bi bi-paperclip"></i></span>
    </div>

    <div class="pandit-booking-list">
        @forelse($userEvidences as $evidence)
            <article class="pandit-booking-row" style="grid-template-columns:minmax(220px,1fr) minmax(120px,.4fr) minmax(120px,.4fr) minmax(130px,.4fr)">
                <div>
                    <span class="pandit-status-pill">{{ strtoupper(pathinfo($evidence->original_name, PATHINFO_EXTENSION)) }}</span>
                    <h3>{{ $evidence->original_name }}</h3>
                    <p>{{ $evidence->mime_type }}</p>
                </div>
                <div><span>Size</span><strong>{{ number_format($evidence->file_size / 1024, 1) }} KB</strong></div>
                <div><span>Uploaded By</span><strong>User</strong></div>
                <div class="pandit-booking-actions">
                    <a href="{{ route('pandit.reports.evidence', ['dispute' => $dispute, 'evidence' => $evidence]) }}" class="secondary" target="_blank" rel="noopener">
                        <i class="bi bi-eye"></i> View
                    </a>
                </div>
            </article>
        @empty
            <div class="pandit-empty-slot">
                <i class="bi bi-inbox"></i>
                No user proof uploaded.
            </div>
        @endforelse
    </div>
</section>

<section class="pandit-panel">
    <div class="pandit-panel-heading">
        <div>
            <h2>Pandit Response</h2>
            <p>{{ $dispute->pandit_responded_at ? 'Submitted '.$dispute->pandit_responded_at->format('d M Y, h:i A') : 'Add your response for admin review.' }}</p>
        </div>
        <span><i class="bi bi-reply"></i></span>
    </div>

    @if($dispute->pandit_responded_at)
        <div class="pandit-profile-grid">
            <div class="pandit-profile-item pandit-wide">
                <span>Your Response</span>
                <strong style="white-space:pre-wrap">{{ $dispute->pandit_response }}</strong>
            </div>
        </div>

        <div class="pandit-booking-list" style="margin-top:16px">
            @forelse($panditEvidences as $evidence)
                <article class="pandit-booking-row" style="grid-template-columns:minmax(220px,1fr) minmax(120px,.4fr) minmax(130px,.4fr)">
                    <div>
                        <span class="pandit-status-pill">{{ strtoupper(pathinfo($evidence->original_name, PATHINFO_EXTENSION)) }}</span>
                        <h3>{{ $evidence->original_name }}</h3>
                        <p>{{ $evidence->mime_type }}</p>
                    </div>
                    <div><span>Size</span><strong>{{ number_format($evidence->file_size / 1024, 1) }} KB</strong></div>
                    <div class="pandit-booking-actions">
                        <a href="{{ route('pandit.reports.evidence', ['dispute' => $dispute, 'evidence' => $evidence]) }}" class="secondary" target="_blank" rel="noopener">
                            <i class="bi bi-eye"></i> View
                        </a>
                    </div>
                </article>
            @empty
                <div class="pandit-empty-slot">
                    <i class="bi bi-inbox"></i>
                    No Pandit proof uploaded.
                </div>
            @endforelse
        </div>
    @else
        <form method="POST" action="{{ route('pandit.reports.respond', ['dispute' => $dispute]) }}" enctype="multipart/form-data" class="pandit-dashboard-form">
            @csrf
            <label class="pandit-form-wide">
                Response
                <textarea name="response" rows="5" required>{{ old('response') }}</textarea>
                @error('response')
                    <span style="display:block;color:#b42318;margin-top:8px">{{ $message }}</span>
                @enderror
            </label>

            <label class="pandit-form-wide">
                Optional Proof
                <input type="file" name="proof" accept=".jpg,.jpeg,.png,.webp,.pdf,image/jpeg,image/png,image/webp,application/pdf">
                @error('proof')
                    <span style="display:block;color:#b42318;margin-top:8px">{{ $message }}</span>
                @enderror
            </label>

            <button type="submit" class="pandit-submit-btn compact">
                <i class="bi bi-send"></i> Submit Response
            </button>
        </form>
    @endif
</section>
@endsection
