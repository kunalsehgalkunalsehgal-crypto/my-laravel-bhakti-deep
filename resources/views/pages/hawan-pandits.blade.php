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
    $switchOnlineUrl = route($serviceType.'.pandits', [
        'slug' => $hawan['slug'],
        'date' => $date,
        'slot' => $slot,
        'mode' => $mode,
        'hawan_type' => $hawanType,
        'booking_mode' => 'online',
        'language' => request('language'),
        'experience' => request('experience'),
        'qualification' => request('qualification'),
        'sort' => request('sort'),
        'view' => request('view'),
    ]);
@endphp

<main class="page-shell pandit-select-page">
    <section class="container page-section">
        <span class="eyebrow"><i class="bi bi-person-check"></i> Pandit Selection</span>
        <h1 class="mt-3">Choose Pandit for <span class="gold-text">{{ $hawan['name'] }}</span></h1>
        <p>{{ \Carbon\Carbon::parse($date)->format('d M Y') }} at {{ $slot }} - only matching verified pandits are shown.</p>

        @if(session('error'))
            <div class="alert alert-warning">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-warning">{{ $errors->first() }}</div>
        @endif
    </section>

    <section class="container pandit-filter-bar">
        <form method="GET" action="{{ route($serviceType.'.pandits', $hawan['slug']) }}" id="panditFilterForm">
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
                <input type="hidden" name="booking_mode" id="bookingModeFilter" value="{{ $bookingMode }}">
                <span class="d-flex gap-2 mt-1">
                    <button type="button" class="btn btn-sm {{ $bookingMode === 'online' ? 'btn-saffron' : 'btn-ghost-gold' }}" data-booking-mode="online">Online</button>
                    <button type="button" class="btn btn-sm {{ $bookingMode === 'offline' ? 'btn-saffron' : 'btn-ghost-gold' }}" data-booking-mode="offline">Offline</button>
                </span>
            </label>

            <span id="offlineLocationFilters" style="display:{{ $bookingMode === 'offline' ? 'contents' : 'none' }};">
                <label>State
                    <input type="text" name="state" value="{{ request('state') }}" placeholder="Punjab" data-offline-location>
                </label>
                <label>City
                    <input type="text" name="city" value="{{ request('city') }}" placeholder="Lalru" data-offline-location>
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
                        @if($bookingMode === 'offline')
                            <h3>{{ request('state') && request('city') ? 'No Pandits available in this location' : 'Select State and City' }}</h3>
                            <p>{{ request('state') && request('city') ? 'Try another city or continue with online '.strtolower($serviceLabel).'.' : 'Enter your offline service location to find matching Pandits.' }}</p>
                            <div class="d-flex flex-wrap gap-2 justify-content-center">
                                <a class="btn btn-ghost-gold" href="#offlineLocationFilters">Change City</a>
                                <a class="btn btn-saffron" href="{{ $switchOnlineUrl }}">Switch to Online</a>
                            </div>
                        @else
                            <h3>No pandit available</h3>
                            <p>Try another date, slot, language or booking mode.</p>
                        @endif
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
    const bookingModeFilter = document.getElementById('bookingModeFilter');
    const offlineLocationFilters = document.getElementById('offlineLocationFilters');
    const offlineInputs = offlineLocationFilters ? offlineLocationFilters.querySelectorAll('input') : [];
    const panditFilterForm = document.getElementById('panditFilterForm');

    function setBookingMode(mode) {
        if (!bookingModeFilter) return;

        bookingModeFilter.value = mode;
        if (offlineLocationFilters) offlineLocationFilters.style.display = mode === 'offline' ? 'contents' : 'none';
        offlineInputs.forEach(input => input.disabled = mode !== 'offline');
        document.querySelectorAll('[data-booking-mode]').forEach(button => {
            button.classList.toggle('btn-saffron', button.dataset.bookingMode === mode);
            button.classList.toggle('btn-ghost-gold', button.dataset.bookingMode !== mode);
        });
    }

    document.querySelectorAll('[data-booking-mode]').forEach(button => {
        button.addEventListener('click', function () {
            setBookingMode(this.dataset.bookingMode);
            if (this.dataset.bookingMode === 'online' || @json($bookingMode) !== this.dataset.bookingMode) {
                panditFilterForm?.submit();
            }
        });
    });

    document.querySelectorAll('[data-offline-location]').forEach(input => {
        input.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                panditFilterForm?.submit();
            }
        });
    });

    document.querySelectorAll('[data-pandit-select-form]').forEach(form => {
        form.addEventListener('submit', function (event) {
            const stateInput = document.querySelector('[name="state"]');
            const cityInput = document.querySelector('[name="city"]');

            form.querySelector('[data-select-booking-mode]').value = bookingModeFilter?.value || 'online';
            form.querySelector('[data-select-state]').value = stateInput?.value || '';
            form.querySelector('[data-select-city]').value = cityInput?.value || '';

            if (bookingModeFilter?.value === 'offline' && (!stateInput?.value || !cityInput?.value)) {
                event.preventDefault();
                panditFilterForm?.submit();
            }
        });
    });

    setBookingMode(bookingModeFilter?.value || 'online');
</script>
@endsection
