@extends('layouts.pandit-dashboard')

@section('title', 'My Availability - BhaktiDeep')

@php $activeMenu = 'availability'; @endphp

@section('content')
@php
    $setup = $onlineSetup;
    $dayNames = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
    $availabilitySetting = $availabilitySetting ?? null;
    $savedDayStatuses = $availabilitySetting?->day_statuses ?? [];
    $oldDays = old('days');
    $days = collect($dayNames)->map(function($day) use ($slots, $savedDayStatuses, $oldDays) {
        $oldDay = is_array($oldDays) ? ($oldDays[$day] ?? null) : null;
        $daySlots = $oldDay
            ? collect($oldDay['from'] ?? [])->map(fn($from, $i) => [$from, $oldDay['to'][$i] ?? ''])->toArray()
            : (isset($slots[$day]) ? $slots[$day]->map(fn($s) => [substr((string) $s->start_time, 0, 5), substr((string) $s->end_time, 0, 5)])->toArray() : []);
        $available = $oldDay
            ? (($oldDay['status'] ?? 'Unavailable') === 'Available')
            : (array_key_exists($day, $savedDayStatuses) ? (bool) $savedDayStatuses[$day] : (isset($slots[$day]) && $slots[$day]->first()?->is_available !== false));
        return [$day, $available, $daySlots];
    })->toArray();
    $bookingOptions = [0 => 'Same Day', 1 => '1 Day', 2 => '2 Days', 3 => '3 Days', 7 => '7 Days Before'];
    $selectedAdvanceBookingDays = (int) old('advance_booking_days', $availabilitySetting?->advance_booking_days ?? 0);
    $acceptNewBookings = (bool) old('accept_new_bookings', $availabilitySetting?->accept_new_bookings ?? true);
    $onlinePlatforms = ['Zoom', 'Google Meet', 'WhatsApp Video', 'BhaktiDeep Live'];
    $onlineDevices = ['Smartphone', 'Laptop', 'Desktop', 'Tablet'];
    $onlineEquipment = ['Tripod', 'Microphone', 'Lighting', 'Hawan Kund', 'Pooja Samagri', 'Pooja/Hawan Area', 'Shankh', 'Ghanti'];
    $selectedPlatforms = $setup?->platforms ?? [];
    $selectedDevices   = $setup?->devices ?? [];
    $selectedEquipment = $setup?->equipment ?? [];
    $offlineHawan = (bool) old('offline_hawan', $availabilitySetting?->offline_hawan ?? false);
    $offlinePooja = (bool) old('offline_pooja', $availabilitySetting?->offline_pooja ?? false);
    $offlineCities = old('offline_cities', collect([$availabilitySetting?->service_city])->merge($availabilitySetting?->other_service_cities ?? [])->filter()->values()->all());
@endphp

@if(session('success'))<div style="color:green;margin-bottom:12px">{{ session('success') }}</div>@endif
@if($errors->any())
    <div style="color:#b42318;margin-bottom:12px">
        <strong>Please fix the following:</strong>
        <ul style="margin:6px 0 0 18px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<input type="checkbox" id="onlineSetupEditToggle" class="pandit-edit-toggle pandit-online-edit-toggle">
<div class="pandit-page-heading">
    <div>
        <p>Availability means free time, not ritual duration</p>
        <h1>My Availability</h1>
    </div>
    <label class="pandit-switch">
        <input type="hidden" name="accept_new_bookings" value="0" form="availabilityForm">
        <input type="checkbox" name="accept_new_bookings" value="1" form="availabilityForm" @checked($acceptNewBookings)>
        <span></span>
        <strong>Accept New Bookings</strong>
    </label>
</div>

<section class="pandit-profile-summary pandit-online-summary">
    <div class="pandit-online-icon"><i class="bi bi-camera-video"></i></div>
    <div>
        <span>Frontend only</span>
        <h2>Online Setup</h2>
        <p>Share your online ritual readiness, preferred platforms, devices, and equipment in one compact view.</p>
        <div class="pandit-progress">
            <div><span class="pandit-progress-75"></span></div>
            <strong>75%</strong>
        </div>
    </div>
    <label for="onlineSetupEditToggle" class="pandit-add-btn pandit-online-setup-edit-btn"><i class="bi bi-pencil-square"></i> Edit</label>
</section>

<section class="pandit-online-setup-view pandit-panel">
    <div class="pandit-panel-heading">
        <span><i class="bi bi-camera-video"></i></span>
        <div>
            <h2>Online Ritual Setup</h2>
            <p>Compact summary for streaming readiness and online session requirements.</p>
        </div>
    </div>
    <div class="pandit-online-setup-grid">
        <div class="pandit-profile-item"><span>Online Hawan</span><strong>{{ $setup?->online_hawan ? 'Yes' : 'No' }}</strong></div>
        <div class="pandit-profile-item"><span>Online Pooja</span><strong>{{ $setup?->online_pooja ? 'Yes' : 'No' }}</strong></div>
        <div class="pandit-profile-item"><span>Stable Internet</span><strong>{{ $setup?->stable_internet ? 'Yes' : 'No' }}</strong></div>
        <div class="pandit-profile-item"><span>Platforms</span><strong>{{ $setup?->platforms ? implode(', ', $setup->platforms) : '—' }}</strong></div>
        <div class="pandit-profile-item"><span>Devices</span><strong>{{ $setup?->devices ? implode(', ', $setup->devices) : '—' }}</strong></div>
        <div class="pandit-profile-item pandit-wide"><span>Equipment</span><strong>{{ $setup?->equipment ? implode(', ', $setup->equipment) : '—' }}</strong></div>
    </div>
</section>

<section class="pandit-online-setup-edit pandit-panel">
    <div class="pandit-panel-heading">
        <span><i class="bi bi-pencil-square"></i></span>
        <div><h2>Edit Online Setup</h2></div>
    </div>
    <form class="pandit-dashboard-form" method="POST" action="{{ route('pandit.online-setup.save') }}">
        @csrf
        <div class="pandit-online-form-grid">
            <label>Online Hawan
                <select name="online_hawan">
                    <option {{ $setup?->online_hawan ? 'selected' : '' }}>Yes</option>
                    <option {{ !$setup?->online_hawan ? 'selected' : '' }}>No</option>
                </select>
            </label>
            <label>Online Pooja
                <select name="online_pooja">
                    <option {{ $setup?->online_pooja ? 'selected' : '' }}>Yes</option>
                    <option {{ !$setup?->online_pooja ? 'selected' : '' }}>No</option>
                </select>
            </label>
            <label>Stable Internet
                <select name="stable_internet">
                    <option {{ $setup?->stable_internet ? 'selected' : '' }}>Yes</option>
                    <option {{ !$setup?->stable_internet ? 'selected' : '' }}>No</option>
                </select>
            </label>
        </div>
        <div class="pandit-online-chip-section">
            <h3>Platforms</h3>
            <div class="pandit-chip-grid">
                @foreach ($onlinePlatforms as $platform)
                    <label class="pandit-chip">
                        <input type="checkbox" name="platforms[]" value="{{ $platform }}" {{ in_array($platform, $selectedPlatforms) ? 'checked' : '' }}>
                        <span>{{ $platform }}</span>
                    </label>
                @endforeach
            </div>
        </div>
        <div class="pandit-online-chip-section">
            <h3>Devices</h3>
            <div class="pandit-chip-grid">
                @foreach ($onlineDevices as $device)
                    <label class="pandit-chip">
                        <input type="checkbox" name="devices[]" value="{{ $device }}" {{ in_array($device, $selectedDevices) ? 'checked' : '' }}>
                        <span>{{ $device }}</span>
                    </label>
                @endforeach
            </div>
        </div>
        <div class="pandit-online-chip-section">
            <h3>Equipment</h3>
            <div class="pandit-chip-grid">
                @foreach ($onlineEquipment as $equipment)
                    <label class="pandit-chip">
                        <input type="checkbox" name="equipment[]" value="{{ $equipment }}" {{ in_array($equipment, $selectedEquipment) ? 'checked' : '' }}>
                        <span>{{ $equipment }}</span>
                    </label>
                @endforeach
            </div>
        </div>
        <div class="pandit-profile-actions">
            <label for="onlineSetupEditToggle" class="pandit-cancel-btn">Cancel</label>
            <button type="submit" class="pandit-submit-btn compact"><i class="bi bi-save"></i> Save Changes</button>
        </div>
    </form>
</section>

<section class="pandit-panel">
    <div class="pandit-panel-heading">
        <span><i class="bi bi-clock-history"></i></span>
        <div>
            <h2>Shubh Muhurat Time Slots</h2>
            <p>Add separate slots after checking shubh muhurat. Example: 8:00 AM - 10:00 AM, 2:00 PM - 4:00 PM</p>
        </div>
    </div>
    
    <form class="pandit-dashboard-form" method="POST" action="{{ route('pandit.availability.save') }}" id="availabilityForm">
        @csrf
        <div class="pandit-advance-box">
            <h2>Offline Service Settings</h2>
            <div class="pandit-online-form-grid">
                <label>
                    <input type="hidden" name="offline_hawan" value="0">
                    <input style="width:auto" type="checkbox" name="offline_hawan" value="1" @checked($offlineHawan)> Offline Hawan
                </label>
                <label>
                    <input type="hidden" name="offline_pooja" value="0">
                    <input style="width:auto" type="checkbox" name="offline_pooja" value="1" @checked($offlinePooja)> Offline Pooja
                </label>
                <label>State
                    <input type="text" name="service_state" value="{{ old('service_state', $availabilitySetting?->service_state) }}" placeholder="Punjab">
                </label>
            </div>
            <h3>Cities</h3>
            <div id="offlineCities" class="pandit-online-form-grid">
                @forelse($offlineCities as $city)
                    <label>City
                        <input type="text" name="offline_cities[]" value="{{ $city }}" placeholder="Lalru">
                        <button type="button" class="pandit-remove-offline-city">Remove</button>
                    </label>
                @empty
                    <label>City
                        <input type="text" name="offline_cities[]" placeholder="Lalru">
                        <button type="button" class="pandit-remove-offline-city">Remove</button>
                    </label>
                @endforelse
            </div>
            <button type="button" class="pandit-add-slot-btn" id="addOfflineCity"><i class="bi bi-plus-lg"></i> Add City</button>
        </div>

        <div class="pandit-availability-list">
            @foreach ($days as [$day, $available, $savedSlots])
                <div class="pandit-availability-day">
                    <div class="pandit-availability-day-head">
                        <strong>{{ $day }}</strong>
                        <label class="pandit-small-select">Status
                            <select name="days[{{ $day }}][status]">
                                <option {{ $available ? 'selected' : '' }}>Available</option>
                                <option {{ ! $available ? 'selected' : '' }}>Unavailable</option>
                            </select>
                        </label>
                    </div>

                    <div class="pandit-muhurat-slots">
                        @forelse ($savedSlots as [$from, $to])
                            <div class="pandit-muhurat-slot">
                                <label>Slot From<input type="time" name="days[{{ $day }}][from][]" value="{{ $from }}"></label>
                                <label>Slot To<input type="time" name="days[{{ $day }}][to][]" value="{{ $to }}"></label>
                                <button type="button" class="pandit-remove-slot"><i class="bi bi-trash"></i> Remove</button>
                            </div>
                        @empty
                            <div class="pandit-empty-slot">
                                <i class="bi bi-calendar-x"></i>
                                <span>No muhurat slots added for this day.</span>
                            </div>
                        @endforelse
                    </div>

                    <button type="button" class="pandit-add-slot-btn" data-day="{{ $day }}">
                        <i class="bi bi-plus-lg"></i>
                        Add Muhurat Slot
                    </button>
                </div>
            @endforeach
        </div>

        <div class="pandit-advance-box">
            <h2>Advance Booking Required</h2>
            <div class="pandit-chip-grid">
                @foreach ($bookingOptions as $daysBefore => $option)
                    <label class="pandit-chip">
                        <input type="radio" name="advance_booking_days" value="{{ $daysBefore }}" @checked($selectedAdvanceBookingDays === $daysBefore)>
                        <span>{{ $option }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <button type="submit" class="pandit-submit-btn compact"><i class="bi bi-save"></i> Save Availability</button>
    </form>
</section>

<script>
    document.querySelectorAll('.pandit-add-slot-btn').forEach((button) => {
        button.addEventListener('click', () => {
            const day = button.dataset.day;
            const slots = button.closest('.pandit-availability-day').querySelector('.pandit-muhurat-slots');
            const emptyState = slots.querySelector('.pandit-empty-slot');
            if (emptyState) emptyState.remove();

            const slot = document.createElement('div');
            slot.className = 'pandit-muhurat-slot';
            slot.innerHTML = `
                <label>Slot From<input type="time" name="days[${day}][from][]" value="08:00"></label>
                <label>Slot To<input type="time" name="days[${day}][to][]" value="10:00"></label>
                <button type="button" class="pandit-remove-slot"><i class="bi bi-trash"></i> Remove</button>
            `;
            slots.appendChild(slot);
        });
    });

    document.addEventListener('click', (event) => {
        if (event.target.closest('#addOfflineCity')) {
            document.getElementById('offlineCities').insertAdjacentHTML('beforeend', `
                <label>City
                    <input type="text" name="offline_cities[]" placeholder="Lalru">
                    <button type="button" class="pandit-remove-offline-city">Remove</button>
                </label>
            `);
        }

        const cityButton = event.target.closest('.pandit-remove-offline-city');
        if (cityButton) cityButton.closest('label').remove();

        const removeButton = event.target.closest('.pandit-remove-slot');
        if (!removeButton) return;

        const slots = removeButton.closest('.pandit-muhurat-slots');
        removeButton.closest('.pandit-muhurat-slot').remove();

        if (!slots.querySelector('.pandit-muhurat-slot')) {
            slots.innerHTML = `
                <div class="pandit-empty-slot">
                    <i class="bi bi-calendar-x"></i>
                    <span>No muhurat slots added for this day.</span>
                </div>
            `;
        }
    });
</script>
@endsection
