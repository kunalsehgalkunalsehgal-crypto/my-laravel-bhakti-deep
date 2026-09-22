@extends('layouts.app')

@section('title', 'Live Session - BhaktiDeep')
@section('description', 'Immersive live pooja and hawan session with mantras, aarti, family join and donations in one screen.')

@push('styles')
<style>

/*
|--------------------------------------------------------------------------
| ZOOM TOP + CARDS BELOW
|--------------------------------------------------------------------------
*/

.live-page {
    overflow-x: hidden;
}


.live-page .container {
    min-width: 0;
}


.live-bottom-grid {

    display: grid;

    grid-template-columns:
        repeat(
            3,
            minmax(0, 1fr)
        );

    gap: 20px;

    align-items: start;

}


.live-bottom-grid > * {

    min-width: 0;

    margin-top: 0 !important;

}


/* Tablet */

@media (
    max-width:
        1199.98px
) {

    .live-bottom-grid {

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

    .live-bottom-grid {

        grid-template-columns:
            1fr;

        gap: 14px;

    }


    .live-page
    .container {

        max-width: 100%;

        padding-left:
            12px;

        padding-right:
            12px;

    }

}

    .live-session-intro {
        min-height: clamp(300px, 42vw, 520px);
        padding: clamp(22px, 4vw, 46px);
        display: flex;
        align-items: flex-end;
        position: relative;
        overflow: hidden;
    }

    .live-session-intro img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .live-session-intro::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(18, 9, 4, .1), rgba(18, 9, 4, .86));
    }

    .live-session-intro .session-intro-copy {
        position: relative;
        z-index: 2;
        max-width: 680px;
    }

    .live-session-intro h1 {
        font-size: clamp(30px, 5vw, 58px);
        margin-bottom: 10px;
    }

    .live-topbar .container {
        min-width: 0;
    }

    .live-topbar-actions {
        min-width: 0;
    }

    .live-back-btn {
        white-space: nowrap;
        flex-shrink: 0;
    }

    .session-meta-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-top: 18px;
    }

    .session-meta-grid span {
        border: 1px solid rgba(199, 141, 34, .24);
        border-radius: 14px;
        padding: 12px;
        background: rgba(18, 9, 4, .42);
        color: var(--cream);
        font-size: 13px;
    }

    .session-meta-grid small {
        display: block;
        color: var(--muted);
        margin-bottom: 4px;
    }

    .live-pill.upcoming i {
        background: var(--gold);
        box-shadow: 0 0 0 6px rgba(199, 141, 34, .2);
    }

    .live-pill.completed i {
        background: #78d38a;
        box-shadow: 0 0 0 6px rgba(120, 211, 138, .18);
    }

    .donation-custom {
        display: none;
    }

    .donation-custom.show {
        display: block;
    }

    .donation-panel [data-donation-amount].active {
        border-color: var(--gold);
        background: var(--gold);
        color: #1f1206;
    }

    .report-issue-form {
        display: none;
        gap: 10px;
        margin-top: 16px;
    }

    .report-issue-form.show {
        display: grid;
    }

    .session-note {
        color: var(--muted);
        font-size: 13px;
        margin-top: 12px;
    }

    .live-selector-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }

    .live-selector-card {
        display: flex;
        flex-direction: column;
        min-height: 100%;
        padding: 18px;
        border: 1px solid rgba(199, 141, 34, .22);
        border-radius: 16px;
        background: rgba(251, 244, 223, .08);
        color: var(--cream);
        text-decoration: none;
        transition: border-color .2s ease, background .2s ease, transform .2s ease;
    }

    .live-selector-card:hover,
    .live-selector-card.active {
        border-color: var(--gold);
        background: rgba(199, 141, 34, .14);
        color: var(--cream);
        transform: translateY(-1px);
    }

    .live-selector-card span {
        width: fit-content;
        margin-bottom: 10px;
        padding: 4px 10px;
        border-radius: 999px;
        background: rgba(199, 141, 34, .18);
        color: var(--gold);
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .live-selector-card strong {
        font-size: 17px;
        margin-bottom: 5px;
    }

    .live-selector-card p {
        flex: 1;
        margin-bottom: 14px;
        color: var(--muted);
        font-size: 13px;
    }

    .family-invite-form {
        display: grid;
        gap: 10px;
        margin-top: 16px;
    }

    .family-invite-link {
        display: none;
        margin-top: 10px;
        overflow-wrap: anywhere;
    }

    .family-invite-link.show {
        display: block;
    }

    .family-row.revoked,
    .family-row.expired {
        opacity: .62;
    }

    .live-page .embedded-meeting-panel {
        margin-top: 0 !important;
    }

    @media (max-width: 767.98px) {
        .live-topbar .container {
            flex-wrap: nowrap;
            gap: 8px !important;
        }

        .live-topbar .brand-title {
            font-size: 18px;
        }

        .live-topbar-actions {
            gap: 6px !important;
            justify-content: flex-end;
        }

        .live-pill,
        .family-pill {
            font-size: 11px;
            padding-inline: 10px;
        }

        .live-player,
        .live-session-intro {
            min-height: 320px;
        }

        .player-copy {
            padding: 18px;
        }

        .audio-row {
            gap: 10px;
        }

        .audio-wave div {
            max-width: 100%;
            overflow: hidden;
        }

        .session-meta-grid {
            grid-template-columns: 1fr;
        }

        .side-panel,
        .donation-panel {
            padding: 18px;
        }

        .live-selector-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('body')
@php
    $sessionStatus = $sessionStatus ?? request('status', $bookingRecord ? 'live' : 'upcoming');
    $requestedSessionType = $sessionType ?? request()->route('type') ?? request('type', 'aarti');
    $sessionType = in_array($requestedSessionType, ['aarti', 'pooja', 'hawan', 'diya'], true) ? $requestedSessionType : 'aarti';
    $sessionId = $sessionId ?? request()->route('id') ?? 101;
    $bookingRecord = $bookingRecord ?? null;
    if ($bookingRecord && $bookingRecord->status === 'completed' && !request()->has('status')) {
        $sessionStatus = 'completed';
    }
    $embeddedMeetingView = $embeddedMeetingView ?? null;
    $bookingSankalp = $bookingRecord?->sankalp;
    $videoMeeting = $bookingRecord?->videoMeeting;
    $bookingMode = $bookingRecord?->booking_mode ?: 'online';
    $isOfflineBooking = in_array($sessionType, ['pooja', 'hawan'], true) && $bookingMode === 'offline';
    $bookingMeta = [];

    if ($bookingRecord?->admin_note) {
        $bookingMeta = json_decode($bookingRecord->admin_note, true) ?: [];
    }

    $multipleLiveSessions = false;
    $isPrivateBookedSession = in_array($sessionType, ['pooja', 'hawan'], true);
    $bookingIsReadyForMeeting = $bookingRecord
        && !$isOfflineBooking
        && $bookingRecord->payment_status === 'paid'
        && $bookingRecord->status === 'confirmed'
        && $videoMeeting;
    $canStartProviderMeeting = $bookingIsReadyForMeeting
        && \Illuminate\Support\Facades\Auth::guard('pandit')->check()
        && (int) \Illuminate\Support\Facades\Auth::guard('pandit')->id() === (int) $bookingRecord->pandit_id
        && filled($videoMeeting->external_meeting_id);
    $providerMeetingActionUrl = $providerMeetingActionUrl ?? ($bookingIsReadyForMeeting
        ? route($canStartProviderMeeting ? 'live.session.start' : 'live.session.join', ['type' => $sessionType, 'id' => $bookingRecord->id, 'token' => request('token')])
        : null);
    $providerMeetingAction = $providerMeetingAction ?? ($canStartProviderMeeting ? 'Start '.ucfirst($sessionType) : 'Join '.ucfirst($sessionType));
    $hasLiveRoomAccess = $sessionType === 'aarti'
        || !$isPrivateBookedSession
        || $bookingIsReadyForMeeting
        || ($bookingRecord && $bookingRecord->payment_status === 'paid' && $bookingRecord->status === 'completed')
        || ($isOfflineBooking && $bookingRecord->payment_status === 'paid');
    $isPaidSession = $isPrivateBookedSession && $hasLiveRoomAccess;
    $sankalpName = $bookingSankalp?->full_name ?: $bookingRecord?->user?->name ?: 'Devotee';
    $slotTime = $bookingRecord?->slot ?: 'Today - 7:00 PM IST';
    $bookingDateLabel = $bookingRecord?->booking_date ? $bookingRecord->booking_date->format('d M Y') : 'Today';
    $bookingMobile = $bookingSankalp?->mobile ?: '-';
    $bookingPurpose = $bookingSankalp?->purpose ?: '-';
    $bookingPackage = $bookingMeta['package_name'] ?? '-';
    $bookingState = $bookingRecord?->state ?: ($bookingMeta['state'] ?? '-');
    $bookingCity = $bookingRecord?->city ?: ($bookingMeta['city'] ?? '-');
    $bookingDakshina = (float) ($bookingMeta['dakshina'] ?? 0);
    $bookingTotal = (float) ($bookingMeta['total_amount'] ?? 0);

    $backUrl = \Illuminate\Support\Facades\Route::has('live.sessions') ? route('live.sessions') : route('home');
    $bookingRouteName = $sessionType === 'hawan' ? 'hawan.booking' : 'pooja.booking';
    $bookingFallbackRouteName = $sessionType === 'hawan' ? 'hawan' : 'personalized-pooja';
    $bookingUrl = \Illuminate\Support\Facades\Route::has($bookingRouteName)
        ? route($bookingRouteName)
        : (\Illuminate\Support\Facades\Route::has($bookingFallbackRouteName) ? route($bookingFallbackRouteName) : '#');

    $sessionLabels = [
        'aarti' => [
            'title' => 'Live Aarti',
            'completedTitle' => 'Your Aarti Is Completed',
            'completedText' => 'Your aarti has been offered with devotion.',
            'service' => 'Lakshmi Aarti',
            'deity' => 'Maa Lakshmi',
            'section' => 'Aarti',
            'nowPlaying' => 'Om Jai Jagdish Hare',
            'image' => 'assets/live-aarti.jpg',
            'fallbackImage' => 'assets/havan-live.jpg',
            'alt' => 'Live aarti with sacred diya and temple lamps',
        ],
        'pooja' => [
            'title' => 'Live Pooja',
            'completedTitle' => 'Your Pooja Is Completed',
            'completedText' => 'Blessings have been registered in your name.',
            'service' => 'Lakshmi Pooja',
            'deity' => 'Maa Lakshmi',
            'section' => 'Mantra',
            'nowPlaying' => 'Mahamrityunjaya Mantra',
            'image' => 'assets/live-aarti.jpg',
            'fallbackImage' => 'assets/havan-live.jpg',
            'alt' => 'Live pooja altar with diya and flowers',
        ],
        'hawan' => [
            'title' => 'Live Hawan',
            'completedTitle' => 'Your Hawan Is Completed',
            'completedText' => 'Your sankalp has been offered through the sacred fire.',
            'service' => 'Mahamrityunjaya Hawan',
            'deity' => 'Lord Shiva',
            'section' => 'Mantra Japa',
            'nowPlaying' => 'Mahamrityunjaya Mantra',
            'image' => 'assets/havan-live.jpg',
            'fallbackImage' => 'assets/havan-live.jpg',
            'alt' => 'Live havan kund with sacred fire',
        ],
        'diya' => [
            'title' => 'Live Diya',
            'completedTitle' => 'Your Diya Offering Is Completed',
            'completedText' => 'Your diya has been offered with devotion and prayers.',
            'service' => 'Evening Diya Offering',
            'deity' => 'Maa Lakshmi',
            'section' => 'Diya Aarti',
            'nowPlaying' => 'Lakshmi Aarti',
            'image' => 'assets/live-aarti.jpg',
            'fallbackImage' => 'assets/havan-live.jpg',
            'alt' => 'Live diya offering with temple lamps',
        ],
    ];

    $session = $sessionLabels[$sessionType] ?? $sessionLabels['aarti'];
    if ($sessionType === 'pooja' && $bookingRecord) {
        $session['service'] = $bookingMeta['pooja_name'] ?? $session['service'];
        $session['deity'] = str_replace(' Pooja', '', $session['service']);
    }

    if ($sessionType === 'hawan' && $bookingRecord) {
        $session['service'] = $bookingMeta['hawan_name'] ?? $session['service'];
        $session['deity'] = str_contains(strtolower($session['service']), 'lakshmi') ? 'Maa Lakshmi' : $session['deity'];
    }
    $sessionImage = public_path($session['image']);
    $playerImage = file_exists($sessionImage) ? $session['image'] : $session['fallbackImage'];
    $topbarLabel = ['upcoming' => 'Starting Soon', 'live' => 'LIVE', 'completed' => 'Completed'][$sessionStatus] ?? 'LIVE';
    $familyInvites = collect($familyInvites ?? []);
    $liveRealtimeSnapshot = $bookingRecord ? \App\Support\LiveSessionSnapshot::make($bookingRecord) : null;
    $liveRealtimeStatus = $liveRealtimeSnapshot['session']['status'] ?? $topbarLabel;
    $activeFamilyInvite = $activeFamilyInvite ?? null;
    $canManageFamily = $canManageFamily ?? false;
    $activeDispute = $activeDispute ?? null;
    $completionProof = $bookingRecord?->completionProofs()->latest()->first();
    $userConfirmation = $bookingRecord?->userConfirmations()->where('user_id', \Illuminate\Support\Facades\Auth::id())->latest()->first();
    $canConfirmCompletion = $canManageFamily
        && $completionProof
        && $bookingRecord?->status === 'completed'
        && !in_array($userConfirmation?->status, [
            \App\Models\BookingUserConfirmation::STATUS_CONFIRMED,
            \App\Models\BookingUserConfirmation::STATUS_AUTO_CONFIRMED,
        ], true);
    $canReportIssue = $canManageFamily && $isPrivateBookedSession && in_array($sessionType, ['pooja', 'hawan'], true) && $bookingRecord;
    $disputeStatusLabel = $activeDispute ? \Illuminate\Support\Str::of($activeDispute->status)->replace('_', ' ')->title() : null;
    $reportReasons = [
        'pandit_not_joined' => 'Pandit not joined',
        'pandit_joined_late' => 'Pandit joined late',
        'session_incomplete' => 'Session incomplete',
        'wrong_service' => 'Wrong service',
        'technical_issue' => 'Technical issue',
        'behaviour_issue' => 'Behaviour issue',
        'other' => 'Other',
    ];
    $joinedCount = $familyInvites->filter(fn ($invite) => $invite->statusLabel() === 'Joined')->count();

    $progressByType = [
        'aarti' => [
            ['Sankalp', 'done', 'Completed'],
            ['Mantra / Bhajan', 'active', 'In progress - devotional chanting'],
            ['Live Aarti', 'upcoming', 'Upcoming'],
            ['Blessing', 'upcoming', 'Upcoming'],
        ],
        'pooja' => [
            ['Sankalp', 'done', 'Completed'],
            ['Mantra Chanting', 'active', 'In progress - mantra jaap'],
            ['Aarti', 'upcoming', 'Upcoming'],
            ['Completion Blessing', 'upcoming', 'Upcoming'],
        ],
        'hawan' => [
            ['Kund Sthapana', 'done', 'Completed'],
            ['Sankalp', 'done', 'Completed'],
            ['Mantra Japa', 'active', 'In progress - sacred fire ritual'],
            ['Purna Ahuti', 'upcoming', 'Upcoming'],
            ['Aarti & Blessing', 'upcoming', 'Upcoming'],
        ],
        'diya' => [
            ['Sankalp', 'done', 'Completed'],
            ['Diya Lighting', 'active', 'In progress - offering'],
            ['Aarti', 'upcoming', 'Upcoming'],
            ['Blessing', 'upcoming', 'Upcoming'],
        ],
    ];

    $sessionProgress = $progressByType[$sessionType] ?? $progressByType['aarti'];
    $completedSteps = collect($sessionProgress)->where(1, 'done')->count();

    if ($sessionStatus === 'upcoming') {
        $sessionProgress = collect($sessionProgress)->map(fn ($row, $index) => [$row[0], $index === 0 ? 'active' : 'upcoming', $index === 0 ? 'Starting soon' : 'Upcoming'])->all();
        $completedSteps = 0;
    } elseif ($sessionStatus === 'completed') {
        $sessionProgress = collect($sessionProgress)->map(fn ($row) => [$row[0], 'done', 'Completed'])->all();
        $completedSteps = count($sessionProgress);
    }

    $comingUpItemsByType = [
        'aarti' => [['Live Aarti', '5:30'], ['Blessing', '2:15']],
        'pooja' => [['Aarti', '5:30'], ['Completion Blessing', '2:15']],
        'hawan' => [['Purna Ahuti', '5:30'], ['Aarti & Blessing', '2:15']],
        'diya' => [['Aarti', '5:30'], ['Blessing', '2:15']],
    ];

    $comingUpItems = $comingUpItemsByType[$sessionType] ?? $comingUpItemsByType['aarti'];

    $donationAmounts = [
        ['label' => '&#8377;101', 'value' => 101],
        ['label' => '&#8377;251', 'value' => 251],
        ['label' => '&#8377;501', 'value' => 501],
        ['label' => 'Custom', 'value' => 'custom'],
    ];
@endphp

<header class="site-header sticky-top live-topbar">
    <div class="container d-flex align-items-center justify-content-between gap-3 py-3">
        <div class="d-flex align-items-center gap-2 gap-md-3 min-w-0">
            <a class="btn btn-ghost-gold btn-sm rounded-pill live-back-btn" href="{{ $backUrl }}" data-live-exit="{{ $sessionStatus === 'live' ? 'true' : 'false' }}"><i class="bi bi-arrow-left"></i> Back</a>
            <a class="brand-wrap min-w-0" href="{{ route('home') }}">
                <span class="brand-icon"><i class="bi bi-fire"></i></span>
                <span class="brand-title gold-text">BhaktiDeep</span>
            </a>
        </div>
        <div class="d-flex align-items-center gap-2 gap-md-3 live-topbar-actions">
            <span class="live-pill {{ $sessionStatus }}" data-live-session-pill><i></i> <span data-live-session-status>{{ $liveRealtimeStatus }}</span></span>
            @if ($hasLiveRoomAccess && !$isOfflineBooking)
                <span class="family-pill d-none d-sm-inline-flex"><i class="bi bi-people"></i> <span data-family-top-count>{{ $joinedCount }}</span> family joined</span>
                <span class="family-pill d-none d-md-inline-flex"><i class="bi bi-broadcast"></i> <span data-live-total-present>{{ $liveRealtimeSnapshot['counts']['total_present'] ?? 0 }}</span> present</span>
            @endif
            @if ($hasLiveRoomAccess && !$isOfflineBooking && $sessionStatus !== 'completed')
                <button class="btn btn-gold btn-sm rounded-pill" data-scroll-donation><i class="bi bi-heart"></i> Donate</button>
            @endif
            @if ($canReportIssue)
                @if ($activeDispute)
                    <span class="family-pill d-none d-md-inline-flex"><i class="bi bi-exclamation-circle"></i> Issue Reported - Status: {{ $disputeStatusLabel }}</span>
                    <a class="btn btn-ghost-gold btn-sm rounded-pill" href="{{ route('user.reports.show', ['dispute' => $activeDispute]) }}"><i class="bi bi-eye"></i> View Report</a>
                @else
                    <button class="btn btn-ghost-gold btn-sm rounded-pill" data-toggle-report><i class="bi bi-exclamation-triangle"></i> Report an Issue</button>
                @endif
            @endif
        </div>
    </div>
</header>

<main class="page-shell live-page">
    <section class="container py-4 py-md-5">
        @unless ($hasLiveRoomAccess)
            <div class="glass completion-card">
                <span><i class="bi bi-lock"></i></span>
                <div>
                    <h3>{{ $session['title'] }} Access Required</h3>
                    <p>{{ $session['title'] }} requires a paid, confirmed booking and active video meeting before entering the live room.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ $bookingUrl }}" class="btn btn-saffron btn-sm">
                            {{ $sessionType === 'hawan' ? 'Book Hawan' : 'Book Pooja' }} <i class="bi bi-arrow-right"></i>
                        </a>
                        <a href="{{ $backUrl }}" class="btn btn-ghost-gold btn-sm"><i class="bi bi-broadcast"></i> Live Sessions</a>
                    </div>
                </div>
            </div>
        @else
        <div class="row g-4">
            <div class="col-12">
            {{-- <div class="col-lg-8"> --}}
                @if ($isOfflineBooking)
                    <div class="glass completion-card">
                        <span><i class="bi bi-geo-alt"></i></span>
                        <div>
                            <h3>{{ $session['service'] }}</h3>
                            <p>This is an offline {{ $sessionType }} booking. No Zoom meeting is needed.</p>
                            <div class="session-meta-grid">
                                <span><small>Pandit</small>{{ $bookingRecord?->pandit?->pandit_name ?: ($bookingRecord?->pandit?->full_name ?: '-') }}</span>
                                <span><small>Sankalp Name</small>{{ $sankalpName }}</span>
                                <span><small>Date and Time</small>{{ $bookingDateLabel }} - {{ $slotTime }}</span>
                                <span><small>State</small>{{ $bookingState }}</span>
                                <span><small>City</small>{{ $bookingCity }}</span>
                                <span><small>Booking Status</small>{{ ucfirst(str_replace('_', ' ', $bookingRecord?->status ?? 'pending')) }}</span>
                                <span><small>Payment Status</small>{{ ucfirst($bookingRecord?->payment_status ?? 'pending') }}</span>
                                <span><small>Package</small>{{ $bookingPackage }}</span>
                                <span><small>Purpose</small>{{ $bookingPurpose }}</span>
                                
                            </div>
                        </div>
                    </div>
                @elseif ($embeddedMeetingView && $bookingIsReadyForMeeting && $sessionStatus !== 'completed')
                    @include($embeddedMeetingView)
                @elseif ($sessionStatus === 'upcoming')
                    <div class="live-player live-session-intro">
                        @if ($sessionType !== 'hawan' && !file_exists(public_path($session['image'])))
                            {{-- TODO: replace with live aarti image. --}}
                        @endif
                        <img src="{{ asset($playerImage) }}" alt="{{ $session['alt'] }}">
                        <div class="particles">@for($i = 0; $i < 28; $i++)<span style="left: {{ ($i * 53) % 100 }}%; animation-delay: {{ $i * .18 }}s"></span>@endfor</div>
                        <div class="session-intro-copy">
                            <small class="now-playing"><i class="bi bi-clock"></i> Starting Soon</small>
                            <h1>Your {{ $session['title'] }} Begins In</h1>
                            <p>
                                @if ($sessionType === 'pooja')
                                    Your pooja will begin automatically at the selected time.
                                @elseif ($sessionType === 'hawan')
                                    Your sankalp is ready for the sacred fire ritual.
                                @else
                                    Your sankalp is ready. Invite your family before the session starts.
                                @endif
                            </p>
                            <div class="session-meta-grid">
                                <span><small>Selected Service</small>{{ $session['service'] }}</span>
                                <span><small>Sankalp Name</small>{{ $sankalpName }}</span>
                                <span><small>Slot Time</small>{{ $bookingDateLabel }} - {{ $slotTime }}</span>
                                <span><small>Selected Deity</small>{{ $session['deity'] }}</span>
                                @if ($bookingRecord)
                                    <span><small>Package</small>{{ $bookingPackage }}</span>
                                    <span><small>Purpose</small>{{ $bookingPurpose }}</span>
                                    <span><small>Mobile</small>{{ $bookingMobile }}</span>
                                    <span><small>Total Paid</small>Rs.{{ number_format($bookingTotal) }}</span>
                                @endif
                                <span><small>Countdown</small>08:24</span>
                                <span><small>Status</small>{{ $topbarLabel }}</span>
                            </div>
                            <div class="d-flex flex-wrap gap-2 mt-3">
                                @if ($providerMeetingActionUrl)
                                    <a href="{{ $providerMeetingActionUrl }}" target="_blank" rel="noopener" class="btn btn-gold btn-sm">
                                        <i class="bi bi-camera-video"></i> {{ $providerMeetingAction }}
                                    </a>
                                @endif
                                @if ($canManageFamily)
                                    <button class="btn btn-saffron btn-sm" data-focus-invite><i class="bi bi-whatsapp"></i> Invite Family</button>
                                @endif
                                @if ($activeFamilyInvite)
                                    <button class="btn btn-ghost-gold btn-sm" data-copy-current-link><i class="bi bi-link-45deg"></i> Copy Invite Link</button>
                                @endif
                                <button class="btn btn-ghost-gold btn-sm"><i class="bi bi-person-lines-fill"></i> View Sankalp</button>
                                <button class="btn btn-gold btn-sm" data-scroll-donation><i class="bi bi-heart"></i> Add Donation</button>
                            </div>
                        </div>
                    </div>
                @elseif ($sessionStatus === 'live')
                    <div class="live-player">
                        @if ($sessionType !== 'hawan' && !file_exists(public_path($session['image'])))
                            {{-- TODO: replace with live aarti image. --}}
                        @endif
                        <img src="{{ asset($playerImage) }}" alt="{{ $session['alt'] }}">
                        <div class="particles">@for($i = 0; $i < 28; $i++)<span style="left: {{ ($i * 53) % 100 }}%; animation-delay: {{ $i * .18 }}s"></span>@endfor</div>
                        <span class="now-playing"><i class="bi bi-music-note"></i> Now Playing</span>
                        <div class="player-copy">
                            <small>{{ $session['section'] }}</small>
                            <h1>{{ $session['nowPlaying'] }}</h1>
                            <div class="audio-row">
                                <button class="play-round"><i class="bi bi-pause-fill"></i></button>
                                <div class="audio-wave">
                                    <div>@for($i = 0; $i < 60; $i++)<span class="{{ $i < 36 ? 'active' : '' }}" style="height: {{ 6 + abs(sin($i * .6) * 22 + ($i % 5) * 2) }}px"></span>@endfor</div>
                                    <p><span>06:42</span><span>11:08</span></p>
                                </div>
                                <button class="volume-round"><i class="bi bi-volume-up"></i></button>
                            </div>
                            @if ($providerMeetingActionUrl)
                                <a href="{{ $providerMeetingActionUrl }}" target="_blank" rel="noopener" class="btn btn-gold btn-sm mt-3">
                                    <i class="bi bi-camera-video"></i> {{ $providerMeetingAction }}
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="glass countdown-card mt-4">
                        <span><i class="bi bi-bell"></i></span>
                        <strong>Up next<small>Aarti starts in</small></strong>
                        <em class="gold-text">08:24</em>
                    </div>
                @else
                    <div class="glass completion-card mt-4">
                        <span><i class="bi bi-stars"></i></span>
                        <div>
                            <h3>{{ $session['completedTitle'] }}</h3>
                            <p>{{ $session['completedText'] }} Donation receipt will be available with your booking record.</p>
                            <div class="d-flex flex-wrap gap-2">
                                <button class="btn btn-gold btn-sm"><i class="bi bi-download"></i> Download Receipt</button>
                                <button class="btn btn-ghost-gold btn-sm"><i class="bi bi-play-circle"></i> View Replay</button>
                                <button class="btn btn-ghost-gold btn-sm"><i class="bi bi-share"></i> Share Blessings</button>
                                @if ($sessionType === 'hawan' || ($sessionType === 'pooja' && $isPaidSession))
                                    <button class="btn btn-ghost-gold btn-sm"><i class="bi bi-award"></i> Download Certificate</button>
                                @endif
                            </div>
                            <p class="session-note"><i class="bi bi-people"></i> <span data-family-top-count>{{ $joinedCount }}</span> family members joined this session.</p>
                        </div>
                    </div>
                @endif

                @unless($isOfflineBooking)
                    <div class="glass timeline-card large mt-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3>Session Progress</h3><small><span data-live-joined-count>{{ $liveRealtimeSnapshot['counts']['joined'] ?? 0 }}</span> joined / <span data-live-left-count>{{ $liveRealtimeSnapshot['counts']['left'] ?? 0 }}</span> left</small>
                        </div>
                        @foreach ($sessionProgress as $i => $row)
                            <div class="timeline-row {{ $row[1] }}"><span>{!! $row[1] === 'done' ? '&#10003;' : $i + 1 !!}</span><strong>{{ $row[0] }}<small>{{ $row[2] }}</small></strong></div>
                        @endforeach
                    </div>
                @endunless
            </div>

<aside class="col-12 live-bottom-grid">    
            {{-- <aside class="col-lg-4"> --}}
                @unless($isOfflineBooking)
                    <div class="glass side-panel">
                    <div class="d-flex justify-content-between align-items-center"><h3>{{ $sessionStatus === 'completed' ? 'Family Summary' : 'Family Invites' }}</h3><span data-family-count>{{ $joinedCount }} joined</span></div>

                    <div data-family-list>
                        @forelse ($familyInvites as $invite)
                            @php
                                $inviteExpired = $invite->expires_at && $invite->expires_at->isPast();
                                $inviteStatus = $invite->revoked_at ? 'Revoked' : ($inviteExpired ? 'Expired' : $invite->statusLabel());
                            @endphp
                            <div class="family-row {{ strtolower($inviteStatus) }}" data-invite-row="{{ $invite->id }}" data-family-presence-id="{{ $invite->id }}">
                                <b>{{ substr($invite->name, 0, 1) }}</b>
                                <strong>{{ $invite->relation }}<small>{{ $invite->name }}</small></strong>
                                <em><i class="bi bi-check-circle"></i> <span data-invite-status data-presence-status>{{ $inviteStatus }}</span></em>
                                <span hidden data-presence-joined-at>{{ $invite->joined_at?->format('d M Y, h:i A') ?? 'Not joined' }}</span>
                                <span hidden data-presence-left-at>{{ $invite->left_at?->format('d M Y, h:i A') ?? '-' }}</span>
                                @if ($canManageFamily && !$invite->revoked_at)
                                    <button type="button" class="btn btn-ghost-gold btn-sm" data-revoke-invite="{{ route('live.family.revoke', ['type' => $sessionType, 'id' => $bookingRecord->id, 'invite' => $invite]) }}">Revoke</button>
                                @endif
                            </div>
                        @empty
                            <p class="session-note mb-0" data-empty-family>No family invites yet.</p>
                        @endforelse
                    </div>

                    @if ($canManageFamily && $sessionStatus !== 'completed')
                        <form class="family-invite-form" data-invite-form action="{{ route('live.family.store', ['type' => $sessionType, 'id' => $bookingRecord->id]) }}">
                            <input class="form-control sacred-input" name="name" placeholder="Family member name" required>
                            <input class="form-control sacred-input" name="relation" placeholder="Relation" required>
                            <button class="btn btn-saffron w-100" type="submit"><i class="bi bi-person-plus"></i> Create Invite Link</button>
                        </form>
                        <div class="alert alert-success family-invite-link" data-invite-link></div>
                        <p class="session-note"><i class="bi bi-shield-check"></i> Invite links are booking-specific and expire automatically.</p>
                    @elseif ($activeFamilyInvite)
                        <p class="session-note"><i class="bi bi-shield-check"></i> You joined as {{ $activeFamilyInvite->name }}.</p>
                        <button class="btn btn-ghost-gold w-100 mt-2" type="button" data-leave-session>
                            <i class="bi bi-box-arrow-left"></i> Leave Session
                        </button>
                    @endif
                    </div>
                @endunless

                @if ($bookingRecord)
                    <div class="glass side-panel mt-4">
                        <h3>Booking Details</h3>
                        @if($isOfflineBooking)
                            <div class="coming-row"><i class="bi bi-person-badge"></i><strong>Pandit<small>{{ $bookingRecord?->pandit?->pandit_name ?: ($bookingRecord?->pandit?->full_name ?: '-') }}</small></strong><em class="bi bi-check-circle"></em></div>
                            <div class="coming-row"><i class="bi bi-calendar-event"></i><strong>Date and Time<small>{{ $bookingDateLabel }} - {{ $slotTime }}</small></strong><em class="bi bi-check-circle"></em></div>
                            <div class="coming-row"><i class="bi bi-geo-alt"></i><strong>Service Location<small>{{ collect([$bookingCity, $bookingState])->filter(fn ($value) => $value !== '-')->join(', ') ?: '-' }}</small></strong><em class="bi bi-check-circle"></em></div>
                            <div class="coming-row"><i class="bi bi-check2-circle"></i><strong>Booking Status<small>{{ ucfirst(str_replace('_', ' ', $bookingRecord?->status ?? 'pending')) }}</small></strong><em class="bi bi-check-circle"></em></div>
                        @else
                            <div class="coming-row"><i class="bi bi-broadcast"></i><strong>Meeting Status<small data-live-session-status>{{ $liveRealtimeSnapshot['session']['status'] ?? $topbarLabel }}</small></strong><em class="bi bi-check-circle"></em></div>
                            <div class="coming-row"><i class="bi bi-play-circle"></i><strong>Started<small data-live-started-at>{{ $liveRealtimeSnapshot['session']['started_at'] ?? 'Not started' }}</small></strong><em class="bi bi-check-circle"></em></div>
                            <div class="coming-row"><i class="bi bi-stop-circle"></i><strong>Ended<small data-live-ended-at>{{ $liveRealtimeSnapshot['session']['ended_at'] ?? 'Not ended' }}</small></strong><em class="bi bi-check-circle"></em></div>
                            <div class="coming-row" data-presence-person="user"><i class="bi bi-person"></i><strong>User<small><span data-presence-status>{{ $liveRealtimeSnapshot['people']['user']['status'] ?? 'Not Joined' }}</span> - <span data-presence-joined-at>{{ $liveRealtimeSnapshot['people']['user']['joined_at'] ?? 'Not joined' }}</span></small></strong><em class="bi bi-check-circle"></em></div>
                            <div class="coming-row" data-presence-person="pandit"><i class="bi bi-person-badge"></i><strong>Pandit<small><span data-presence-status>{{ $liveRealtimeSnapshot['people']['pandit']['status'] ?? 'Not Joined' }}</span> - <span data-presence-joined-at>{{ $liveRealtimeSnapshot['people']['pandit']['joined_at'] ?? 'Not joined' }}</span></small></strong><em class="bi bi-check-circle"></em></div>
                        @endif
                        <div class="coming-row"><i class="bi bi-person"></i><strong>Sankalp Name<small>{{ $sankalpName }}</small></strong><em class="bi bi-check-circle"></em></div>
                        <div class="coming-row"><i class="bi bi-heart"></i><strong>Purpose<small>{{ $bookingPurpose }}</small></strong><em class="bi bi-check-circle"></em></div>
                        <div class="coming-row"><i class="bi bi-box"></i><strong>Package<small>{{ $bookingPackage }}</small></strong><em class="bi bi-check-circle"></em></div>
                        <div class="coming-row"><i class="bi bi-phone"></i><strong>Mobile<small>{{ $bookingMobile }}</small></strong><em class="bi bi-check-circle"></em></div>
                        <div class="coming-row"><i class="bi bi-receipt"></i><strong>Total Paid<small>Rs.{{ number_format($bookingTotal) }}</small></strong><em class="bi bi-check-circle"></em></div>
                    </div>
                    @if($bookingRecord && $bookingRecord->payment_status === 'paid' && $bookingRecord->pandit)
    @php
        $panditInfo = $bookingRecord->pandit;
    @endphp

    <div class="glass side-panel mt-4">
        <h3>Pandit Details</h3>

        <div class="coming-row">
            <i class="bi bi-person"></i>
            <strong>
                Pandit Name
                <small>{{ $panditInfo->pandit_name ?: $panditInfo->full_name }}</small>
            </strong>
        </div>

        <div class="coming-row">
            <i class="bi bi-telephone"></i>
            <strong>
                Mobile
                <small>{{ $panditInfo->mobile ?: '-' }}</small>
            </strong>
        </div>

        <div class="coming-row">
            <i class="bi bi-envelope"></i>
            <strong>
                Email
                <small>{{ $panditInfo->email ?: '-' }}</small>
            </strong>
        </div>

        <div class="coming-row">
            <i class="bi bi-geo-alt"></i>
            <strong>
                Address
                <small>{{ $panditInfo->full_address ?: '-' }}</small>
            </strong>
        </div>

        <div class="coming-row">
            <i class="bi bi-pin-map"></i>
            <strong>
                Location
                <small>
                    {{ collect([$panditInfo->city, $panditInfo->state])->filter()->join(', ') ?: '-' }}
                </small>
            </strong>
        </div>

        <div class="coming-row">
            <i class="bi bi-award"></i>
            <strong>
                Experience
                <small>{{ $panditInfo->total_experience_years ?: 0 }} years</small>
            </strong>
        </div>
    </div>
@endif
                @endif

                @if ($completionProof && $bookingRecord)
                    <div class="glass side-panel mt-4">
                        <h3>Pandit has marked this {{ ucfirst($sessionType) }} as completed</h3>
                        @if($completionProof->file_path)
                            <p><a class="btn btn-ghost-gold w-100" href="{{ asset('storage/'.$completionProof->file_path) }}" target="_blank"><i class="bi bi-image"></i> View Completion Image</a></p>
                        @endif
                        <div class="coming-row"><i class="bi bi-card-text"></i><strong>Completion Note<small>{{ $completionProof->notes ?: 'Not added' }}</small></strong><em class="bi bi-check-circle"></em></div>
                        <div class="coming-row"><i class="bi bi-clock"></i><strong>Submitted<small>{{ $completionProof->submitted_at?->format('d M Y, h:i A') ?? '-' }}</small></strong><em class="bi bi-check-circle"></em></div>

                        @if($userConfirmation)
                            <div class="coming-row"><i class="bi bi-info-circle"></i><strong>Confirmation Status<small>{{ ucfirst(str_replace('_', ' ', $userConfirmation->status)) }}</small></strong><em class="bi bi-check-circle"></em></div>
                        @endif

                        @if($canConfirmCompletion)
                            <form method="POST" action="{{ route('live.completion.confirm', ['type' => $sessionType, 'id' => $bookingRecord->id]) }}" class="mt-3">
                                @csrf
                                <button class="btn btn-saffron w-100" type="submit">
                                    <i class="bi bi-check2-circle"></i> Confirm Completed
                                </button>
                            </form>
                        @endif
                    </div>

                    @if($bookingRecord->status === 'completed' && $canManageFamily)
                        @include('partials.review-form', [
                            'booking' => $bookingRecord,
                            'bookingType' => $sessionType,
                            'reviewBy' => 'user',
                            'title' => 'Review Pandit',
                            'sendOtpRoute' => route('reviews.image-otp'),
                            'verifyOtpRoute' => route('reviews.image-otp.verify'),
                            'storeRoute' => route('reviews.store'),
                        ])
                    @endif
                @endif

                @if ($canReportIssue)
                    <div class="glass side-panel mt-4" data-report-panel>
                        <h3>Report an Issue</h3>

                        @if (session('success'))
                            <div class="alert alert-success mb-3">{{ session('success') }}</div>
                        @endif

                        @if ($activeDispute)
                            <div class="alert alert-warning mb-3">Issue Reported - Status: {{ $disputeStatusLabel }}</div>
                            <a class="btn btn-ghost-gold w-100" href="{{ route('user.reports.show', ['dispute' => $activeDispute]) }}">
                                <i class="bi bi-eye"></i> View Report
                            </a>
                        @else
                            <button type="button" class="btn btn-ghost-gold w-100" data-toggle-report>
                                <i class="bi bi-exclamation-triangle"></i> Report an Issue
                            </button>

                            <form method="POST" action="{{ route('live.issue-report.store', ['type' => $sessionType, 'id' => $bookingRecord->id]) }}" enctype="multipart/form-data" class="report-issue-form {{ $errors->has('reason') || $errors->has('description') || $errors->has('proof') ? 'show' : '' }}" data-report-form>
                                @csrf
                                <select name="reason" class="form-control sacred-input" required>
                                    <option value="">Select reason</option>
                                    @foreach ($reportReasons as $value => $label)
                                        <option value="{{ $value }}" @selected(old('reason') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('reason')
                                    <p class="text-warning small mb-0">{{ $message }}</p>
                                @enderror

                                <textarea name="description" rows="4" class="form-control sacred-input" placeholder="Describe the issue" required>{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="text-warning small mb-0">{{ $message }}</p>
                                @enderror

                                <input type="file" name="proof" class="form-control sacred-input" accept=".jpg,.jpeg,.png,.webp,.pdf,image/jpeg,image/png,image/webp,application/pdf">
                                @error('proof')
                                    <p class="text-warning small mb-0">{{ $message }}</p>
                                @enderror

                                <button class="btn btn-saffron w-100" type="submit">
                                    <i class="bi bi-send"></i> Submit Issue
                                </button>
                            </form>
                        @endif
                    </div>
                @endif

                @if ($canManageFamily && !$isOfflineBooking && $sessionStatus !== 'completed')
                    <div class="donation-panel mt-4" data-donation-panel data-dakshina-url="{{ route('live.dakshina.pay', ['type' => $sessionType, 'id' => $bookingRecord->id]) }}" data-csrf-token="{{ csrf_token() }}">
                        <h3><i class="bi bi-heart"></i> Dakshina</h3>
                        <p>Offer dakshina for this {{ $sessionType }} booking.</p>
                        <div class="row g-2">
                            @foreach($donationAmounts as $i => $amount)
                                <div class="col-6 col-sm-3 col-lg-3"><button type="button" class="btn {{ $i === 0 ? 'btn-gold active' : 'btn-ghost-gold' }} w-100" data-donation-amount="{{ $amount['value'] }}">{!! $amount['label'] !!}</button></div>
                            @endforeach
                        </div>
                        <div class="donation-custom mt-3" data-custom-donation>
                            <input type="number" min="1" max="100000" class="form-control sacred-input" placeholder="Enter amount" data-custom-donation-input>
                        </div>
                        <button class="btn btn-light w-100 mt-3" data-donate-button>Pay Dakshina</button>
                        <p class="session-note" data-donation-message>Payment amount is verified on server.</p>
                    </div>
                @endif

                @if (!$isOfflineBooking && $sessionStatus === 'live')
                    <div class="glass side-panel mt-4">
                        <h3>Coming Up</h3>
                        @foreach ($comingUpItems as $item)
                            <div class="coming-row"><i class="bi bi-play-circle"></i><strong>{{ $item[0] }}<small>{{ $item[1] }}</small></strong><em class="bi bi-arrow-right"></em></div>
                        @endforeach
                    </div>
                @elseif (!$isOfflineBooking && $sessionStatus === 'upcoming')
                    <div class="glass side-panel mt-4">
                        <h3>Upcoming Timeline</h3>
                        @foreach ($sessionProgress as $item)
                            <div class="coming-row"><i class="bi bi-clock"></i><strong>{{ $item[0] }}<small>{{ $item[2] }}</small></strong><em class="bi bi-arrow-right"></em></div>
                        @endforeach
                    </div>
                @endif
            </aside>
        </div>
        @endunless
    </section>
</main>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const donationPanel = document.querySelector('[data-donation-panel]');
    const liveExitLink = document.querySelector('[data-live-exit="true"]');
    const scrollDonationButtons = document.querySelectorAll('[data-scroll-donation]');
    const inviteForm = document.querySelector('[data-invite-form]');
    const inviteList = document.querySelector('[data-family-list]');
    const inviteLinkBox = document.querySelector('[data-invite-link]');
    const reportPanel = document.querySelector('[data-report-panel]');
    const reportForm = document.querySelector('[data-report-form]');
    const reportButtons = document.querySelectorAll('[data-toggle-report]');
    const csrfToken = @json(csrf_token());
    const familyLeaveUrl = @json($activeFamilyInvite ? route('live.family.leave', ['token' => request()->route('token')]) : null);
    const familyChannel = @json($canManageFamily && $bookingRecord ? 'live-session.'.$sessionType.'.'.$bookingRecord->id : null);

    const setFamilyCount = function (count) {
        document.querySelectorAll('[data-family-count]').forEach(function (item) {
            item.textContent = count + ' joined';
        });

        document.querySelectorAll('[data-family-top-count]').forEach(function (item) {
            item.textContent = count;
        });
    };

    const setInviteStatus = function (invite) {
        const row = document.querySelector('[data-invite-row="' + invite.id + '"]');

        if (!row) {
            return;
        }

        row.classList.remove('invited', 'joined', 'left');
        row.classList.add(invite.status.toLowerCase());
        row.querySelector('[data-invite-status]')?.replaceChildren(document.createTextNode(invite.status));
    };

    const copyText = function (text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text);
        }
    };

    const addInviteRow = function (invite) {
        const row = document.createElement('div');
        const avatar = document.createElement('b');
        const title = document.createElement('strong');
        const name = document.createElement('small');
        const status = document.createElement('em');
        const revoke = document.createElement('button');

        row.className = 'family-row';
        row.dataset.inviteRow = invite.id;
        avatar.textContent = invite.name.charAt(0);
        title.textContent = invite.relation;
        name.textContent = invite.name;
        status.innerHTML = '<i class="bi bi-check-circle"></i> <span data-invite-status></span>';
        status.querySelector('[data-invite-status]').textContent = invite.status;
        title.appendChild(name);
        row.append(avatar, title, status);

        if (invite.revoke_url) {
            revoke.type = 'button';
            revoke.className = 'btn btn-ghost-gold btn-sm';
            revoke.dataset.revokeInvite = invite.revoke_url;
            revoke.textContent = 'Revoke';
            row.appendChild(revoke);
        }

        inviteList.prepend(row);
    };

    if (liveExitLink) {
        liveExitLink.addEventListener('click', function (event) {
            const shouldLeave = window.confirm('Live session chal raha hai. Kya aap bahar jana chahte hain?');

            if (!shouldLeave) {
                event.preventDefault();
            }
        });
    }

    document.querySelectorAll('[data-focus-invite]').forEach(function (button) {
        button.addEventListener('click', function () {
            if (inviteForm) {
                inviteForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
                inviteForm.querySelector('input')?.focus();
            }
        });
    });

    reportButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            if (reportPanel) {
                reportPanel.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            if (reportForm) {
                reportForm.classList.add('show');
                reportForm.querySelector('select, textarea')?.focus();
            }
        });
    });

    scrollDonationButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const panel = document.querySelector('[data-donation-panel]');

            if (panel) {
                panel.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    });

    document.querySelectorAll('[data-copy-current-link]').forEach(function (button) {
        button.addEventListener('click', function () {
            copyText(window.location.href);
            button.innerHTML = '<i class="bi bi-check-circle"></i> Link Copied';
            window.setTimeout(function () {
                button.innerHTML = '<i class="bi bi-link-45deg"></i> Copy Invite Link';
            }, 1800);
        });
    });

    if (inviteForm && inviteList) {
        inviteForm.addEventListener('submit', function (event) {
            event.preventDefault();

            fetch(inviteForm.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: new FormData(inviteForm),
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Invite could not be created.');
                    }

                    return response.json();
                })
                .then(function (result) {
                    const invite = result.invite;
                    document.querySelector('[data-empty-family]')?.remove();
                    addInviteRow(invite);

                    inviteLinkBox.textContent = invite.join_url;
                    inviteLinkBox.classList.add('show');
                    copyText(invite.join_url);
                    inviteForm.reset();
                })
                .catch(function (error) {
                    inviteLinkBox.textContent = error.message || 'Invite could not be created.';
                    inviteLinkBox.classList.add('show');
                });
        });
    }

    document.addEventListener('click', function (event) {
        const button = event.target.closest('[data-revoke-invite]');

        if (button) {
            fetch(button.dataset.revokeInvite, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Invite could not be revoked.');
                    }

                    return response.json();
                })
                .then(function () {
                    const row = button.closest('[data-invite-row]');
                    row?.querySelector('[data-invite-status]')?.replaceChildren(document.createTextNode('Revoked'));
                    row?.classList.add('revoked');
                    button.remove();
                });
        }
    });

    if (familyLeaveUrl) {
        document.querySelector('[data-leave-session]')?.addEventListener('click', function () {
            fetch(familyLeaveUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            }).finally(function () {
                window.location.href = @json(route('home'));
            });
        });

        window.addEventListener('beforeunload', function () {
            fetch(familyLeaveUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                },
                keepalive: true,
            });
        });
    }

    if (donationPanel) {
        const amountButtons = donationPanel.querySelectorAll('[data-donation-amount]');
        const customWrap = donationPanel.querySelector('[data-custom-donation]');
        const customInput = donationPanel.querySelector('[data-custom-donation-input]');
        const donateButton = donationPanel.querySelector('[data-donate-button]');
        const donationMessage = donationPanel.querySelector('[data-donation-message]');
        let selectedAmount = '101';

        amountButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                amountButtons.forEach(function (item) {
                    item.classList.remove('active', 'btn-gold');
                    item.classList.add('btn-ghost-gold');
                });

                button.classList.add('active', 'btn-gold');
                button.classList.remove('btn-ghost-gold');
                selectedAmount = button.getAttribute('data-donation-amount');

                if (selectedAmount === 'custom') {
                    customWrap.classList.add('show');
                    customInput.focus();
                } else {
                    customWrap.classList.remove('show');
                    customInput.value = '';
                }
            });
        });

        donateButton.addEventListener('click', function () {
            const amount = selectedAmount === 'custom' ? customInput.value : selectedAmount;

            if (!amount || Number(amount) <= 0) {
                donationMessage.textContent = 'Please enter a valid dakshina amount.';
                return;
            }

            donateButton.disabled = true;
            donationMessage.textContent = 'Verifying payment on server...';

            fetch(donationPanel.dataset.dakshinaUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': donationPanel.dataset.csrfToken,
                },
                body: JSON.stringify({ amount: Number(amount) }),
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Dakshina payment failed.');
                    }

                    return response.json();
                })
                .then(function (result) {
                    donationMessage.textContent = result.message + ' Receipt: ' + result.receipt_number;
                    customInput.value = '';
                })
                .catch(function (error) {
                    donationMessage.textContent = error.message || 'Dakshina payment failed.';
                })
                .finally(function () {
                    donateButton.disabled = false;
                });
        });
    }
});
</script>
@if($canManageFamily && $bookingRecord && !$isOfflineBooking)
    @include('live-sessions.realtime', ['channelName' => 'live-session.'.$sessionType.'.'.$bookingRecord->id])
@endif
@endpush
@endsection
