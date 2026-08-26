@extends('layouts.app')

@section('title', 'Live Session - BhaktiDeep')
@section('description', 'Immersive live pooja and hawan session with mantras, aarti, family join and donations in one screen.')

@push('styles')
<style>
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
    // Demo state. Accepted values: upcoming, live, completed.
    $sessionStatus = $sessionStatus ?? request('status', 'upcoming');
    $requestedSessionType = $sessionType ?? request()->route('type') ?? request('type', 'aarti');
    $sessionType = in_array($requestedSessionType, ['aarti', 'pooja', 'hawan', 'diya'], true) ? $requestedSessionType : 'aarti';
    $sessionId = $sessionId ?? request()->route('id') ?? 101;
    $bookingRecord = $bookingRecord ?? null;
    $embeddedMeetingView = $embeddedMeetingView ?? null;
    $bookingSankalp = $bookingRecord?->sankalp;
    $videoMeeting = $bookingRecord?->videoMeeting;
    $bookingMeta = [];

    if ($bookingRecord?->admin_note) {
        $bookingMeta = json_decode($bookingRecord->admin_note, true) ?: [];
    }

    $multipleLiveSessions = false;
    $isPrivateBookedSession = in_array($sessionType, ['pooja', 'hawan'], true);
    $bookingIsReadyForMeeting = $bookingRecord
        && $bookingRecord->payment_status === 'paid'
        && $bookingRecord->status === 'confirmed'
        && $videoMeeting;
    $canStartProviderMeeting = $bookingIsReadyForMeeting
        && \Illuminate\Support\Facades\Auth::guard('pandit')->check()
        && (int) \Illuminate\Support\Facades\Auth::guard('pandit')->id() === (int) $bookingRecord->pandit_id
        && filled($videoMeeting->external_meeting_id);
    $providerMeetingActionUrl = $bookingIsReadyForMeeting
        ? route($canStartProviderMeeting ? 'live.session.start' : 'live.session.join', ['type' => $sessionType, 'id' => $bookingRecord->id, 'token' => request('token')])
        : null;
    $providerMeetingAction = $canStartProviderMeeting ? 'Start '.ucfirst($sessionType) : 'Join '.ucfirst($sessionType);
    $hasLiveRoomAccess = $sessionType === 'aarti' || (!$isPrivateBookedSession || $bookingIsReadyForMeeting);
    $isPaidSession = $isPrivateBookedSession && $hasLiveRoomAccess;
    $sankalpName = $bookingSankalp?->full_name ?: 'Rahul Sharma';
    $slotTime = $bookingRecord?->slot ?: 'Today - 7:00 PM IST';
    $bookingDateLabel = $bookingRecord?->booking_date ? $bookingRecord->booking_date->format('d M Y') : 'Today';
    $bookingMobile = $bookingSankalp?->mobile ?: '-';
    $bookingPurpose = $bookingSankalp?->purpose ?: '-';
    $bookingPackage = $bookingMeta['package_name'] ?? '-';
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
    $joinedCount = 4;

    $familyMembers = [
        ['relation' => 'Mother', 'name' => 'Smt. Sunita'],
        ['relation' => 'Father', 'name' => 'Shri Ramesh'],
        ['relation' => 'Spouse', 'name' => 'Aarav'],
        ['relation' => 'Guest', 'name' => 'Priya'],
    ];

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
        ['label' => '&#8377;11', 'value' => 11],
        ['label' => '&#8377;51', 'value' => 51],
        ['label' => '&#8377;101', 'value' => 101],
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
            <span class="live-pill {{ $sessionStatus }}"><i></i> {{ $topbarLabel }}</span>
            @if ($hasLiveRoomAccess)
                <span class="family-pill d-none d-sm-inline-flex"><i class="bi bi-people"></i> {{ $joinedCount }} family joined</span>
            @endif
            @if ($hasLiveRoomAccess && $sessionStatus !== 'completed')
                <button class="btn btn-gold btn-sm rounded-pill" data-scroll-donation><i class="bi bi-heart"></i> Donate</button>
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
            <div class="col-lg-8">
                @if ($sessionStatus === 'upcoming')
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
                                <button class="btn btn-saffron btn-sm"><i class="bi bi-whatsapp"></i> Invite Family</button>
                                <button class="btn btn-ghost-gold btn-sm" data-copy-join-link><i class="bi bi-link-45deg"></i> Copy Join Link</button>
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
                            <p class="session-note"><i class="bi bi-people"></i> {{ $joinedCount }} family members joined this session.</p>
                        </div>
                    </div>
                @endif

                @if ($embeddedMeetingView && $providerMeetingActionUrl && $sessionStatus !== 'completed')
                    @include($embeddedMeetingView)
                @endif

                <div class="glass timeline-card large mt-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3>Session Progress</h3><small>{{ $completedSteps }} of {{ count($sessionProgress) }} complete</small>
                    </div>
                    @foreach ($sessionProgress as $i => $row)
                        <div class="timeline-row {{ $row[1] }}"><span>{!! $row[1] === 'done' ? '&#10003;' : $i + 1 !!}</span><strong>{{ $row[0] }}<small>{{ $row[2] }}</small></strong></div>
                    @endforeach
                </div>
            </div>

            <aside class="col-lg-4">
                <div class="glass side-panel">
                    <div class="d-flex justify-content-between align-items-center"><h3>{{ $sessionStatus === 'completed' ? 'Family Summary' : 'Family Present' }}</h3><span>{{ $joinedCount }} joined</span></div>
                    @foreach ($familyMembers as $i => $member)
                        <div class="family-row"><b class="{{ $i % 2 ? 'saffron' : '' }}">{{ substr($member['name'], 0, 1) }}</b><strong>{{ $member['relation'] }}<small>{{ $member['name'] }}</small></strong><em><i class="bi bi-check-circle"></i> {{ $sessionStatus === 'upcoming' ? 'Invited' : ($sessionStatus === 'completed' ? 'Joined' : 'Live') }}</em></div>
                    @endforeach
                    @if ($sessionStatus !== 'completed')
                        <button class="btn btn-saffron w-100 mt-3"><i class="bi bi-whatsapp"></i> Invite Family on WhatsApp</button>
                        <button class="btn btn-ghost-gold w-100 mt-2" data-copy-join-link><i class="bi bi-link-45deg"></i> Copy Join Link</button>
                        <p class="session-note"><i class="bi bi-broadcast"></i> Family members can join live session.</p>
                    @else
                        <p class="session-note"><i class="bi bi-receipt"></i> Donation receipt message has been added to this completed session.</p>
                    @endif
                </div>

                @if ($bookingRecord)
                    <div class="glass side-panel mt-4">
                        <h3>Booking Details</h3>
                        <div class="coming-row"><i class="bi bi-person"></i><strong>Sankalp Name<small>{{ $sankalpName }}</small></strong><em class="bi bi-check-circle"></em></div>
                        <div class="coming-row"><i class="bi bi-heart"></i><strong>Purpose<small>{{ $bookingPurpose }}</small></strong><em class="bi bi-check-circle"></em></div>
                        <div class="coming-row"><i class="bi bi-box"></i><strong>Package<small>{{ $bookingPackage }}</small></strong><em class="bi bi-check-circle"></em></div>
                        <div class="coming-row"><i class="bi bi-phone"></i><strong>Mobile<small>{{ $bookingMobile }}</small></strong><em class="bi bi-check-circle"></em></div>
                        <div class="coming-row"><i class="bi bi-receipt"></i><strong>Total Paid<small>Rs.{{ number_format($bookingTotal) }}</small></strong><em class="bi bi-check-circle"></em></div>
                    </div>
                @endif

                @if ($sessionStatus !== 'completed')
                    <div class="donation-panel mt-4" data-donation-panel>
                        <h3><i class="bi bi-heart"></i> Quick Donation</h3>
                        <p>Offer a small contribution as the aarti unfolds.</p>
                        <div class="row g-2">
                            @foreach($donationAmounts as $i => $amount)
                                <div class="col-6 col-sm-3 col-lg-3"><button type="button" class="btn {{ $i === 1 ? 'btn-gold active' : 'btn-ghost-gold' }} w-100" data-donation-amount="{{ $amount['value'] }}">{!! $amount['label'] !!}</button></div>
                            @endforeach
                        </div>
                        <div class="donation-custom mt-3" data-custom-donation>
                            <input type="number" min="1" class="form-control sacred-input" placeholder="Enter amount" data-custom-donation-input>
                        </div>
                        <button class="btn btn-light w-100 mt-3" data-donate-button>Donate Securely</button>
                        <p class="session-note">TODO: connect Razorpay donation API</p>
                    </div>
                @endif

                @if ($sessionStatus === 'live')
                    <div class="glass side-panel mt-4">
                        <h3>Coming Up</h3>
                        @foreach ($comingUpItems as $item)
                            <div class="coming-row"><i class="bi bi-play-circle"></i><strong>{{ $item[0] }}<small>{{ $item[1] }}</small></strong><em class="bi bi-arrow-right"></em></div>
                        @endforeach
                    </div>
                @elseif ($sessionStatus === 'upcoming')
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
    const copyJoinButtons = document.querySelectorAll('[data-copy-join-link]');

    if (liveExitLink) {
        liveExitLink.addEventListener('click', function (event) {
            const shouldLeave = window.confirm('Live session chal raha hai. Kya aap bahar jana chahte hain?');

            if (!shouldLeave) {
                event.preventDefault();
            }
        });
    }

    scrollDonationButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const panel = document.querySelector('[data-donation-panel]');

            if (panel) {
                panel.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    });

    copyJoinButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const joinUrl = window.location.href;

            if (navigator.clipboard) {
                navigator.clipboard.writeText(joinUrl);
            }

            button.innerHTML = '<i class="bi bi-check-circle"></i> Link Copied';
            window.setTimeout(function () {
                button.innerHTML = '<i class="bi bi-link-45deg"></i> Copy Join Link';
            }, 1800);
        });
    });

    if (!donationPanel) {
        return;
    }

    const amountButtons = donationPanel.querySelectorAll('[data-donation-amount]');
    const customWrap = donationPanel.querySelector('[data-custom-donation]');
    const customInput = donationPanel.querySelector('[data-custom-donation-input]');
    const donateButton = donationPanel.querySelector('[data-donate-button]');
    let selectedAmount = '51';

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
            alert('Please enter a valid donation amount');
            return;
        }

        console.log('Selected donation amount:', amount);
        // TODO: connect Razorpay donation API
    });
});
</script>
@endpush
@endsection
