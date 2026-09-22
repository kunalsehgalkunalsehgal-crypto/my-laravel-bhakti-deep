@extends('layouts.pandit-live')

@section('title', strtoupper($sessionType).'-'.$bookingRecord->id.' Live Session - BhaktiDeep')

@push('styles')
<style>
    /* .pandit-live-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.65fr) minmax(320px, .75fr);
        gap: 20px;
        align-items: start;
    } */
     .pandit-live-grid {

    display: grid;

    grid-template-columns:
        1fr;

    gap:
        20px;

    align-items:
        start;

    min-width:
        0;

}

    .pandit-live-hero,
    .pandit-live-card {
        border: 1px solid rgba(199, 141, 34, .22);
        border-radius: 20px;
        background: rgba(255, 255, 255, .68);
        box-shadow: 0 20px 60px -54px rgba(63, 36, 23, .7);
    }

    .pandit-live-hero {
        display: flex;
        justify-content: space-between;
        gap: 18px;
        padding: clamp(22px, 4vw, 30px);
        margin-bottom: 20px;
    }

    .pandit-live-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        color: var(--gold);
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .pandit-live-hero h1 {
        margin: 0;
        color: var(--cream);
        font-family: "Cinzel", serif;
        font-size: clamp(30px, 4vw, 46px);
        line-height: 1.12;
    }

    .pandit-live-hero p,
    .pandit-live-card p {
        color: var(--muted);
        margin: 8px 0 0;
    }

    .pandit-live-status {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        align-self: flex-start;
        border-radius: 999px;
        background: linear-gradient(135deg, var(--saffron), var(--saffron-dark));
        color: #fff;
        flex: 0 0 auto;
        font-weight: 900;
        padding: 10px 14px;
        white-space: nowrap;
    }

    .pandit-live-status.waiting {
        border: 1px solid rgba(199, 141, 34, .24);
        background: rgba(255, 255, 255, .72);
        color: #9b4c14;
    }

    .pandit-live-status.ended {
        background: linear-gradient(135deg, var(--gold), var(--saffron));
    }

    .pandit-live-card {
        margin-bottom: 20px;
        padding: 24px;
    }

    .pandit-live-card-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
        margin-bottom: 20px;
    }

    .pandit-live-card-head h2 {
        color: var(--cream);
        font-family: "Cinzel", serif;
        font-size: 25px;
        letter-spacing: 0;
        margin: 0;
    }

    .pandit-live-card-head > span,
    .pandit-live-icon {
        width: 46px;
        height: 46px;
        display: grid;
        place-items: center;
        border-radius: 14px;
        background: linear-gradient(135deg, var(--gold), var(--saffron));
        color: #fff;
        flex: 0 0 auto;
    }

    .pandit-live-details {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .pandit-live-item {
        border: 1px solid rgba(199, 141, 34, .18);
        border-radius: 14px;
        background: rgba(251, 244, 223, .55);
        padding: 14px;
    }

    .pandit-live-item.wide {
        grid-column: 1 / -1;
    }

    .pandit-live-item span,
    .pandit-live-item strong {
        display: block;
    }

    .pandit-live-item span {
        color: var(--muted);
        font-size: 12px;
        font-weight: 900;
        margin-bottom: 6px;
    }

    .pandit-live-item strong {
        color: var(--cream);
        font-size: 14px;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    .pandit-live-presence {
        display: grid;
        gap: 12px;
    }

    .pandit-live-person {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 14px;
        align-items: center;
        border: 1px solid rgba(199, 141, 34, .18);
        border-radius: 16px;
        background: rgba(251, 244, 223, .5);
        padding: 14px;
    }

    .pandit-live-person h3 {
        color: var(--cream);
        font-size: 16px;
        margin: 9px 0 5px;
    }

    .pandit-live-person p {
        color: var(--muted);
        font-size: 12px;
        font-weight: 800;
        margin: 0;
    }

    .pandit-live-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid rgba(199, 141, 34, .24);
        border-radius: 999px;
        background: rgba(255, 255, 255, .72);
        color: #9b4c14;
        font-size: 12px;
        font-weight: 800;
        padding: 8px 12px;
    }

    .pandit-live-pill.present {
        border-color: rgba(47, 182, 109, .22);
        background: rgba(47, 182, 109, .12);
        color: #237a48;
    }

    .pandit-live-pill.left {
        border-color: rgba(232, 91, 33, .28);
        background: rgba(232, 91, 33, .08);
        color: var(--saffron-dark);
    }

    .pandit-live-video .embedded-meeting-panel {
        margin-top: 0 !important;
        border: 0;
        box-shadow: none;
        background: transparent;
        padding: 0;
    }

    .pandit-live-video .embedded-meeting-root {
        border-radius: 16px;
        border-color: rgba(199, 141, 34, .24);
        background: rgba(251, 244, 223, .55);
    }

    .pandit-live-video .embedded-meeting-toolbar h3 {
        color: var(--cream);
        font-family: "Cinzel", serif;
        font-size: 22px;
        letter-spacing: 0;
    }

    .pandit-live-video .embedded-meeting-message {
        color: var(--muted);
    }

    .pandit-live-empty {
        display: flex;
        align-items: center;
        gap: 10px;
        border: 1px dashed rgba(199, 141, 34, .34);
        border-radius: 15px;
        background: rgba(255, 255, 255, .42);
        color: var(--muted);
        font-size: 14px;
        font-weight: 800;
        padding: 16px;
    }

    .pandit-live-empty i {
        color: var(--gold);
        font-size: 18px;
    }

    @media (max-width: 991px) {
        .pandit-live-grid {
            grid-template-columns: 1fr;
        }

        .pandit-live-hero {
            flex-direction: column;
        }
    }

    @media (max-width: 575px) {
        .pandit-live-details,
        .pandit-live-person {
            grid-template-columns: 1fr;
        }

        .pandit-live-card,
        .pandit-live-hero {
            border-radius: 18px;
            padding: 22px;
        }
    }








    .pandit-bottom-grid {

    display: grid;

    grid-template-columns:
        repeat(
            3,
            minmax(0, 1fr)
        );

    gap:
        20px;

    align-items:
        start;

}


.pandit-bottom-grid > * {

    min-width:
        0;

    margin-bottom:
        0 !important;

}


.pandit-live-video {

    width:
        100%;

    min-width:
        0;

}


/* Tablet */

@media (
    max-width:
        1199.98px
) {

    .pandit-bottom-grid {

        grid-template-columns:
            repeat(
                2,
                minmax(0, 1fr)
            );

    }

}


/* Mobile */

@media (
    max-width:
        767.98px
) {

    .pandit-bottom-grid {

        grid-template-columns:
            1fr;

        gap:
            14px;

    }

}
</style>
@endpush

@section('content')
@php
    $sankalp = $bookingRecord->sankalp;
    $statusClass = $sessionProgress['status'] === 'Ended'
        ? 'ended'
        : ($sessionProgress['status'] === 'Live' ? 'live' : 'waiting');
    $snapshot = \App\Support\LiveSessionSnapshot::make($bookingRecord);
    $completionProof = $bookingRecord->completionProofs()->latest()->first();
    $canCompleteOnline = !$completionProof
        && ($bookingRecord->booking_mode ?: 'online') === 'online'
        && $bookingRecord->payment_status === 'paid'
        && $bookingRecord->status === 'confirmed';
@endphp

<section class="pandit-live-hero">
    <div>
        <span class="pandit-live-kicker">
            <i class="bi {{ $sessionType === 'hawan' ? 'bi-fire' : 'bi-flower1' }}"></i>
            {{ ucfirst($sessionType) }} Live Session
        </span>
        <h1>{{ $serviceName }}</h1>
        <p>
            Booking {{ strtoupper($sessionType) }}-{{ $bookingRecord->id }}
            @if($bookingRecord->booking_date)
                - {{ $bookingRecord->booking_date->format('d M Y') }}
            @endif
            @if($bookingRecord->slot)
                - {{ $bookingRecord->slot }}
            @endif
        </p>
    </div>
    <span class="pandit-live-status {{ $statusClass }}" data-live-session-pill>
        <i class="bi bi-broadcast"></i> <span data-live-session-status>{{ $sessionProgress['status'] }}</span>
    </span>
</section>

<div class="pandit-live-grid">
    <div>
        @if(session('success'))<div style="color:green;margin-bottom:12px">{{ session('success') }}</div>@endif
        @if($errors->any())<div style="color:#b42318;margin-bottom:12px">{{ $errors->first() }}</div>@endif

        <section class="pandit-live-card pandit-live-video">
            @if($embeddedMeetingView && $providerMeetingActionUrl && $sdkEndpointUrl)
                @include($embeddedMeetingView)
            @else
                <div class="pandit-live-card-head">
                    <div>
                        <h2>Zoom Video</h2>
                        <p>Same meeting room shared with the devotee.</p>
                    </div>
                    <span><i class="bi bi-camera-video"></i></span>
                </div>
                <div class="pandit-live-empty">
                    <i class="bi bi-camera-video-off"></i>
                    Zoom meeting is not ready for this booking yet.
                </div>
            @endif
        </section>

        @if($completionProof)
            <section class="pandit-live-card">
                <div class="pandit-live-card-head">
                    <div>
                        <h2>Completion Proof</h2>
                        <p>Submitted {{ $completionProof->submitted_at?->format('d M Y, h:i A') ?? '' }}</p>
                    </div>
                    <span><i class="bi bi-check2-circle"></i></span>
                </div>
                <div class="pandit-live-details">
                    @if($completionProof->file_path)
                        <div class="pandit-live-item wide">
                            <span>Image</span>
                            <strong><a href="{{ asset('storage/'.$completionProof->file_path) }}" target="_blank">View completion image</a></strong>
                        </div>
                    @endif
                    <div class="pandit-live-item wide">
                        <span>Note</span>
                        <strong>{{ $completionProof->notes ?: 'Not added' }}</strong>
                    </div>
                </div>
            </section>
        @elseif($canCompleteOnline)
            <section class="pandit-live-card" data-completion-after-ended style="{{ $sessionProgress['status'] === 'Ended' ? '' : 'display:none' }}">
                <div class="pandit-live-card-head">
                    <div>
                        <h2>Complete Session</h2>
                        <p>Upload proof after the Zoom meeting ends.</p>
                    </div>
                    <span><i class="bi bi-check2-circle"></i></span>
                </div>
                <form method="POST" action="{{ route('pandit.bookings.complete', ['type' => $sessionType, 'id' => $bookingRecord->id]) }}" enctype="multipart/form-data" class="pandit-live-details">
                    @csrf
                    <label class="pandit-live-item wide">
                        <span>Completion Image *</span>
                        <input type="file" name="completion_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required style="width:100%;margin-top:8px">
                    </label>
                    <label class="pandit-live-item wide">
                        <span>Completion Note</span>
                        <textarea name="completion_note" rows="3" style="width:100%;margin-top:8px">{{ old('completion_note') }}</textarea>
                    </label>
                    <button type="submit" class="pandit-live-pill present" style="border:0">
                        <i class="bi bi-check2-circle"></i> Complete Session
                    </button>
                </form>
            </section>
        @endif

        <section class="pandit-live-card">
            <div class="pandit-live-card-head">
                <div>
                    <h2>Who Is Present</h2>
                    <p><span data-live-total-present>{{ $snapshot['counts']['total_present'] }}</span> present</p>
                </div>
                <span><i class="bi bi-people"></i></span>
            </div>

            <div class="pandit-live-presence">
                @foreach($presenceRows as $person)
                    @php
                        $pillClass = $person['status'] === 'Present'
                            ? 'present'
                            : ($person['status'] === 'Left' ? 'left' : 'not-joined');
                    @endphp
                    <article class="pandit-live-person" @if($person['role'] === 'Main Devotee') data-presence-person="user" @elseif($person['role'] === 'Pandit') data-presence-person="pandit" @elseif(isset($person['id'])) data-family-presence-id="{{ $person['id'] }}" @endif>
                        <div>
                            <span class="pandit-live-pill {{ $pillClass }}" data-presence-status>{{ $person['status'] }}</span>
                            <h3>{{ $person['name'] }}</h3>
                            <p>
                                {{ $person['role'] }}
                                - Joined <span data-presence-joined-at>{{ $person['joined_at']?->format('d M Y, h:i A') ?? 'Not joined' }}</span>
                                - Left <span data-presence-left-at>{{ $person['left_at']?->format('d M Y, h:i A') ?? '-' }}</span>
                            </p>
                        </div>
                        <span class="pandit-live-pill {{ $pillClass }}">
                            <i class="bi {{ $person['status'] === 'Present' ? 'bi-check-circle' : ($person['status'] === 'Left' ? 'bi-box-arrow-right' : 'bi-dash-circle') }}"></i>
                            {{ $person['status'] }}
                        </span>
                    </article>
                @endforeach
            </div>
        </section>
    </div>

    <aside  class="pandit-bottom-grid">
        <section class="pandit-live-card">
            <div class="pandit-live-card-head">
                <div>
                    <h2>Session Progress</h2>
                    <p><span data-live-joined-count>{{ $snapshot['counts']['joined'] }}</span> joined / <span data-live-left-count>{{ $snapshot['counts']['left'] }}</span> left</p>
                </div>
                <span><i class="bi bi-activity"></i></span>
            </div>
            <div class="pandit-live-details">
                <div class="pandit-live-item">
                    <span>Meeting Status</span>
                    <strong>{{ $sessionProgress['meeting_status'] }}</strong>
                </div>
                <div class="pandit-live-item">
                    <span>Booking Status</span>
                    <strong>{{ $sessionProgress['booking_status'] }}</strong>
                </div>
                <div class="pandit-live-item">
                    <span>Started</span>
                    <strong data-live-started-at>{{ $sessionProgress['started_at']?->format('d M Y, h:i A') ?? 'Not started' }}</strong>
                </div>
                <div class="pandit-live-item">
                    <span>Ended</span>
                    <strong data-live-ended-at>{{ $sessionProgress['ended_at']?->format('d M Y, h:i A') ?? 'Not ended' }}</strong>
                </div>
                <div class="pandit-live-item">
                    <span>Actual Joins</span>
                    <strong data-live-joined-count>{{ $snapshot['counts']['joined'] }}</strong>
                </div>
                <div class="pandit-live-item">
                    <span>Actual Leaves</span>
                    <strong data-live-left-count>{{ $snapshot['counts']['left'] }}</strong>
                </div>
            </div>
        </section>

        <section class="pandit-live-card">
            <div class="pandit-live-card-head">
                <div>
                    <h2>Sankalp Details</h2>
                    <p>{{ $sankalp?->full_name ?: $bookingRecord->user?->name ?: 'Main Devotee' }}</p>
                </div>
                <span><i class="bi bi-file-text"></i></span>
            </div>
            <div class="pandit-live-details">
                <div class="pandit-live-item">
                    <span>Name</span>
                    <strong>{{ $sankalp?->full_name ?: $bookingRecord->user?->name ?: 'Not added' }}</strong>
                </div>
                <div class="pandit-live-item">
                    <span>Gotra</span>
                    <strong>{{ $sankalp?->gotra ?: 'Not added' }}</strong>
                </div>
                <div class="pandit-live-item wide">
                    <span>Purpose</span>
                    <strong>{{ $sankalp?->purpose ?: ($sankalp?->mannokamna ?: 'Not added') }}</strong>
                </div>
                <div class="pandit-live-item wide">
                    <span>Family Names</span>
                    <strong>{{ $sankalp?->family_names ?: 'Not added' }}</strong>
                </div>
            </div>
        </section>

        <section class="pandit-live-card">
            <div class="pandit-live-card-head">
                <div>
                    <h2>Booking Info</h2>
                    <p>{{ strtoupper($sessionType) }}-{{ $bookingRecord->id }}</p>
                </div>
                <span><i class="bi bi-receipt"></i></span>
            </div>
            <div class="pandit-live-details">
                <div class="pandit-live-item">
                    <span>Type</span>
                    <strong>{{ ucfirst($sessionType) }}</strong>
                </div>
                <div class="pandit-live-item">
                    <span>Date</span>
                    <strong>{{ $bookingRecord->booking_date?->format('d M Y') ?? 'Pending' }}</strong>
                </div>
                <div class="pandit-live-item wide">
                    <span>Slot</span>
                    <strong>{{ $bookingRecord->slot ?: 'Pending' }}</strong>
                </div>
            </div>
        </section>
    </aside>
</div>
@push('scripts')
    @include('live-sessions.realtime', ['channelName' => 'live-session.'.$sessionType.'.'.$bookingRecord->id])
@endpush
@endsection
