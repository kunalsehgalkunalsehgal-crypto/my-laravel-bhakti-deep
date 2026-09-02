@extends('layouts.pandit-dashboard')

@section('title', 'Reports - BhaktiDeep')

@php $activeMenu = 'reports'; @endphp

@section('content')
<div class="pandit-page-heading">
    <div>
        <p>User Issue Reports</p>
        <h1>Reports</h1>
    </div>
    <a href="{{ route('pandit.dashboard') }}" class="pandit-add-btn">
        <i class="bi bi-grid-1x2-fill"></i> Dashboard
    </a>
</div>

<section class="pandit-panel pandit-booking-panel">
    <div class="pandit-panel-heading">
        <div>
            <h2>Reported Bookings</h2>
            <p>{{ $reports->total() }} report{{ $reports->total() === 1 ? '' : 's' }} for your assigned bookings.</p>
        </div>
        <span><i class="bi bi-exclamation-triangle"></i></span>
    </div>

    <div class="pandit-booking-list">
        @forelse($reports as $report)
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
                    <strong>{{ $report['status'] }}</strong>
                </div>
                <div>
                    <span>Response</span>
                    <strong>{{ $report['response_status'] }}</strong>
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
                No reports for your assigned bookings.
            </div>
        @endforelse
    </div>

    @if($reports->hasPages())
        <div style="margin-top:18px">
            {{ $reports->links() }}
        </div>
    @endif
</section>
@endsection
