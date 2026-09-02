@extends('layouts.pandit-dashboard')

@section('title', 'Pandit Dashboard - BhaktiDeep')

@php $activeMenu = 'dashboard'; @endphp

@section('content')
@php
    $displayName = $pandit->pandit_name ?: $pandit->full_name;
    $statusLabel = ucfirst(str_replace('_', ' ', $pandit->status));
@endphp

<div class="pandit-dashboard-heading">
    <div>
        <p>{{ $statusLabel }}</p>
        <h1>Namaste, {{ $displayName }}</h1>
    </div>
    <span><i class="bi bi-shield-check"></i> Profile verification {{ $statusLabel }}</span>
</div>

@if($pandit->status === 'needs_correction' && $pandit->admin_remark)
<div style="background:#fff8e1;border:1px solid #f6c90e;border-radius:14px;padding:16px 20px;margin-bottom:20px">
    <strong style="color:#7a5c00"><i class="bi bi-exclamation-triangle"></i> Admin Remark - Action Required</strong>
    <p style="margin:8px 0 0;color:#5a4200">{{ $pandit->admin_remark }}</p>
    <form method="POST" action="{{ route('pandit.resubmit') }}" style="margin-top:12px">
        @csrf
        <button type="submit" style="background:#e85d04;color:#fff;border:0;border-radius:999px;padding:10px 20px;font-weight:800;cursor:pointer">Resubmit for Review</button>
    </form>
</div>
@endif

<section class="pandit-stats-grid">
    @foreach ($stats as [$label, $value, $icon])
        <article class="pandit-stat-card">
            <i class="bi {{ $icon }}"></i>
            <span>{{ $label }}</span>
            <strong>{{ $value }}</strong>
        </article>
    @endforeach
</section>

<section class="pandit-dashboard-meta-grid">
    <a href="{{ route('pandit.services') }}" class="pandit-mini-card">
        <i class="bi bi-stars"></i>
        <span>Active Services</span>
        <strong>{{ $dashboardCounts['services'] }}</strong>
    </a>
    <a href="{{ route('pandit.reports.index') }}" class="pandit-mini-card">
        <i class="bi bi-exclamation-triangle"></i>
        <span>Open Reports</span>
        <strong>{{ $dashboardCounts['open_reports'] }}</strong>
    </a>
    <a href="{{ route('pandit.notifications') }}" class="pandit-mini-card">
        <i class="bi bi-bell"></i>
        <span>Unread Notifications</span>
        <strong>{{ $dashboardCounts['unread_notifications'] }}</strong>
    </a>
    <a href="{{ route('pandit.messages') }}" class="pandit-mini-card">
        <i class="bi bi-chat-dots"></i>
        <span>Unread Messages</span>
        <strong>{{ $dashboardCounts['unread_messages'] }}</strong>
    </a>
</section>

@if($nextSession)
    <section class="pandit-session-card">
        <div class="pandit-session-top">
            <div>
                <span class="pandit-session-label">
                    {{ $nextSession['booking_date']?->isToday() ? 'Today' : 'Next Session' }} - {{ $nextSession['label'] }}
                </span>
                <h2>{{ $nextSession['service_name'] }}</h2>
                <p>
                    <i class="bi bi-calendar-event"></i>
                    {{ $nextSession['booking_date']?->format('d M Y') ?? 'Date pending' }}
                    @if($nextSession['slot'])
                        <i class="bi bi-clock ms-2"></i> {{ $nextSession['slot'] }}
                    @endif
                </p>
            </div>
            <div class="pandit-session-icon"><i class="bi {{ $nextSession['type'] === 'hawan' ? 'bi-fire' : 'bi-flower1' }}"></i></div>
        </div>
        <div class="pandit-session-details">
            <div><span>Yajman</span><strong>{{ $nextSession['yajman'] }}</strong></div>
            <div><span>Purpose</span><strong>{{ $nextSession['purpose'] }}</strong></div>
            <div><span>Status</span><strong>{{ ucfirst($nextSession['status']) }}</strong></div>
            <div><span>Dakshina</span><strong>Rs {{ number_format($nextSession['dakshina']) }}</strong></div>
        </div>
        <div class="pandit-session-actions">
            <a href="{{ $nextSession['detail_url'] }}">
                <i class="bi bi-eye"></i> View Details
            </a>
            @if($nextSession['mobile'])
                <a href="tel:{{ $nextSession['mobile'] }}"><i class="bi bi-telephone"></i> Call Yajman</a>
            @endif
            @if($nextSession['can_accept'])
                <form method="POST" action="{{ $nextSession['accept_url'] }}">
                    @csrf
                    <button type="submit" class="primary">
                        <i class="bi bi-check2-circle"></i> Accept Booking
                    </button>
                </form>
            @elseif($nextSession['can_start_meeting'])
                <a href="{{ $nextSession['meeting_start_url'] }}" class="primary">
                    <i class="bi bi-camera-video"></i> Start {{ $nextSession['label'] }}
                </a>
            @else
                <span class="pandit-muted-action">Live link pending</span>
            @endif
        </div>
    </section>
@else
    <section class="pandit-session-card pandit-empty-dashboard">
        <div class="pandit-session-top">
            <div>
                <span class="pandit-session-label">No Assigned Session</span>
                <h2>No upcoming booking yet</h2>
                <p>New pooja and hawan bookings assigned by admin will appear here automatically.</p>
            </div>
            <div class="pandit-session-icon"><i class="bi bi-calendar-plus"></i></div>
        </div>
    </section>
@endif

<section class="pandit-panel pandit-booking-panel">
    <div class="pandit-panel-heading">
        <div>
            <h2>Reported Bookings</h2>
            <p>Latest user issue reports for your assigned pooja and hawan bookings.</p>
        </div>
        <span><i class="bi bi-exclamation-triangle"></i></span>
    </div>

    <div class="pandit-booking-list">
        @forelse($reportedBookings as $report)
            <article class="pandit-booking-row">
                <div>
                    <span class="pandit-status-pill">{{ $report['label'] }}</span>
                    <h3>{{ $report['service_name'] }}</h3>
                    <p>{{ $report['booking_id'] }} - {{ $report['yajman'] }}</p>
                </div>
                <div>
                    <span>Date</span>
                    <strong>{{ $report['booking_date']?->format('d M Y') ?? 'Pending' }}</strong>
                </div>
                <div>
                    <span>Slot</span>
                    <strong>{{ $report['slot'] ?: 'Pending' }}</strong>
                </div>
                <div>
                    <span>Reason</span>
                    <strong>{{ $report['reason'] }}</strong>
                </div>
                <div>
                    <span>Status</span>
                    <strong>{{ ucfirst(str_replace('_', ' ', $report['status'])) }}</strong>
                </div>
                <div>
                    <span>Response</span>
                    <strong>{{ $report['has_response'] ? 'Submitted' : 'Pending' }}</strong>
                </div>
                <div>
                    <span>Reported</span>
                    <strong>{{ $report['reported_at']?->format('d M Y') ?? 'Recent' }}</strong>
                </div>
                <div class="pandit-booking-actions">
                    <a href="{{ $report['report_url'] }}" class="secondary">
                        <i class="bi bi-eye"></i> View Report
                    </a>
                </div>
            </article>
        @empty
            <div class="pandit-empty-slot">
                <i class="bi bi-inbox"></i>
                No user reports for your assigned bookings.
            </div>
        @endforelse
    </div>
</section>

<section class="pandit-panel pandit-booking-panel" id="assigned-bookings">
    <div class="pandit-panel-heading">
        <div>
            <h2>Latest Assigned Bookings</h2>
            <p>Your latest 5 pooja and hawan bookings.</p>
        </div>
        <span><i class="bi bi-calendar2-check"></i></span>
    </div>

    <div class="pandit-booking-list">
        @forelse($latestBookings as $session)
            @include('pandit.bookings.partials.booking-row', ['session' => $session])
        @empty
            <div class="pandit-empty-slot">
                <i class="bi bi-inbox"></i>
                No assigned bookings yet.
            </div>
        @endforelse
    </div>

    @if($latestBookings->isNotEmpty())
        <div class="pandit-session-actions" style="margin-top:16px">
            <a href="{{ route('pandit.bookings.index') }}" class="primary">
                <i class="bi bi-calendar2-week"></i> View All Bookings
            </a>
        </div>
    @endif
</section>
@endsection
