@extends('layouts.pandit-dashboard')

@section('title', 'Assigned Bookings - BhaktiDeep')

@php $activeMenu = 'bookings'; @endphp

@section('content')
<div class="pandit-page-heading">
    <div>
        <p>Assigned Bookings</p>
        <h1>All Bookings</h1>
    </div>
    <a href="{{ route('pandit.dashboard') }}" class="pandit-add-btn">
        <i class="bi bi-grid-1x2-fill"></i> Dashboard
    </a>
</div>

<section class="pandit-panel pandit-booking-panel">
    <div class="pandit-panel-heading">
        <div>
            <h2>Pooja and Hawan</h2>
            {{-- <p>{{ $bookings->count() }} assigned booking{{ $bookings->count() === 1 ? '' : 's' }}</p> --}}
            <p>{{ $bookings->total() }} assigned booking{{ $bookings->total() === 1 ? '' : 's' }}</p>
        </div>
        <span><i class="bi bi-calendar2-check"></i></span>
    </div>

    <div class="pandit-booking-list">
        @forelse($bookings as $session)
            @include('pandit.bookings.partials.booking-row', ['session' => $session])
        @empty
            <div class="pandit-empty-slot">
                <i class="bi bi-inbox"></i>
                No assigned bookings yet.
            </div>
        @endforelse
    </div>
    @if($bookings->hasPages())
    <div class="pandit-pagination">
        @if($bookings->onFirstPage())
            <span>← Previous</span>
        @else
            <a href="{{ $bookings->previousPageUrl() }}">← Previous</a>
        @endif

        <strong>
            Page {{ $bookings->currentPage() }} of {{ $bookings->lastPage() }}
        </strong>

        @if($bookings->hasMorePages())
            <a href="{{ $bookings->nextPageUrl() }}">Next →</a>
        @else
            <span>Next →</span>
        @endif
    </div>
@endif
</section>
@endsection
