@extends('layouts.app')

@php
    $serviceType = $serviceType ?? 'hawan';
    $serviceLabel = ucfirst($serviceType);
@endphp

@section('title', 'Select Pandit - ' . $hawan['name'] . ' - BhaktiDeep')

@push('styles')
<link href="{{ asset('css/light-diya.css') }}" rel="stylesheet">
<link href="{{ asset('css/pandit-selection.css') }}" rel="stylesheet">
@endpush

@section('body')
@php
    $date = request('date');
    $slot = request('slot');
    $mode = request('mode', 'Live + Replay');
    $hawanType = request('hawan_type');
    $bookingMode = request('booking_mode', 'online');
@endphp

<main class="page-shell pandit-select-page">
    <section class="container page-section">
        <span class="eyebrow"><i class="bi bi-person-check"></i> Pandit Selection</span>
        <h1 class="mt-3">Choose Pandit for <span class="gold-text">{{ $hawan['name'] }}</span></h1>
        <p>{{ \Carbon\Carbon::parse($date)->format('d M Y') }} at {{ $slot }} - only matching verified pandits are shown.</p>

        @if(session('error'))
            <div class="alert alert-warning">{{ session('error') }}</div>
        @endif
    </section>

    <section class="container pandit-filter-bar">
        <form method="GET" action="{{ route($serviceType.'.pandits', $hawan['slug']) }}">
            <input type="hidden" name="date" value="{{ $date }}">
            <input type="hidden" name="slot" value="{{ $slot }}">
            <input type="hidden" name="mode" value="{{ $mode }}">
            <input type="hidden" name="hawan_type" value="{{ $hawanType }}">
            <input type="hidden" name="view" value="{{ request('view') }}">

            <label>Language
                <select name="language">
                    <option value="">All</option>
                    @foreach($languages as $language)
                        <option value="{{ $language }}" @selected(request('language') === $language)>{{ $language }}</option>
                    @endforeach
                </select>
            </label>

            <label>{{ $serviceLabel }} Experience
                <select name="experience">
                    <option value="">Any</option>
                    <option value="1" @selected(request('experience') === '1')>1+ years</option>
                    <option value="3" @selected(request('experience') === '3')>3+ years</option>
                    <option value="5" @selected(request('experience') === '5')>5+ years</option>
                    <option value="10" @selected(request('experience') === '10')>10+ years</option>
                </select>
            </label>

            <label>Qualification
                <select name="qualification">
                    <option value="">All</option>
                    @foreach($qualifications as $qualification)
                        <option value="{{ $qualification }}" @selected(request('qualification') === $qualification)>{{ $qualification }}</option>
                    @endforeach
                </select>
            </label>

            <label>Online/Offline
                <select name="booking_mode" id="bookingModeFilter">
                    <option value="online" @selected($bookingMode === 'online')>Online</option>
                    <option value="offline" @selected($bookingMode === 'offline')>Offline</option>
                </select>
            </label>

            <span id="offlineLocationFilters" style="display:{{ $bookingMode === 'offline' ? 'contents' : 'none' }};">
                <label>State
                    <input type="text" name="state" value="{{ request('state') }}" placeholder="Punjab">
                </label>
                <label>City
                    <input type="text" name="city" value="{{ request('city') }}" placeholder="Lalru">
                </label>
            </span>

            <label>Sort By
                <select name="sort">
                    <option value="recommended" @selected(request('sort', 'recommended') === 'recommended')>Recommended</option>
                    <option value="experience" @selected(request('sort') === 'experience')>Experience</option>
                    <option value="performed" @selected(request('sort') === 'performed')>Performed</option>
                </select>
            </label>

            <button class="btn btn-saffron" type="submit"><i class="bi bi-funnel"></i> Apply</button>
        </form>
    </section>

    <section class="container page-section">
        <div class="section-heading">
            <div>
                <h2>Recommended Pandits</h2>
                <p>First 4 best matches for your selected {{ strtolower($serviceLabel) }}, date and time.</p>
            </div>
            <a class="btn btn-ghost-gold" href="{{ request()->fullUrlWithQuery(['view' => 'all', 'page' => null]) }}">View All</a>
        </div>

        <div class="row g-4 mt-2">
            @forelse($recommendedPandits as $pandit)
                @include('pages.partials.pandit-card', ['pandit' => $pandit, 'hawan' => $hawan, 'date' => $date, 'slot' => $slot, 'mode' => $mode, 'hawanType' => $hawanType, 'bookingMode' => $bookingMode, 'serviceType' => $serviceType])
            @empty
                <div class="col-12">
                    <div class="empty-pandit-box">
                        <i class="bi bi-calendar-x"></i>
                        <h3>No pandit available</h3>
                        <p>Try another date, slot, language or booking mode.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </section>

    @if($pandits)
        <section class="container page-section">
            <h2>All Matching Pandits</h2>
            <div class="row g-4 mt-2">
                @foreach($pandits as $pandit)
                    @include('pages.partials.pandit-card', ['pandit' => $pandit, 'hawan' => $hawan, 'date' => $date, 'slot' => $slot, 'mode' => $mode, 'hawanType' => $hawanType, 'bookingMode' => $bookingMode, 'serviceType' => $serviceType])
                @endforeach
            </div>
            <div class="mt-4">
                {{ $pandits->links() }}
            </div>
        </section>
    @endif
</main>
<script>
    document.getElementById('bookingModeFilter')?.addEventListener('change', function () {
        document.getElementById('offlineLocationFilters').style.display = this.value === 'offline' ? 'contents' : 'none';
    });
</script>
@endsection
