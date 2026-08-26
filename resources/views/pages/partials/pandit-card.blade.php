@php
    $serviceType = $serviceType ?? 'hawan';
    $serviceLabel = ucfirst($serviceType);
    $serviceSuffix = $serviceType === 'pooja' ? ' Pooja' : ' Hawan';
    $serviceNames = collect([$hawan['name'], trim(\Illuminate\Support\Str::replaceLast($serviceSuffix, '', $hawan['name']))])->unique();
    $service = app(\App\Services\PanditBookingService::class)->approvedService($pandit, $serviceType, $serviceNames, null, $hawan['id'] ?? null);
    $displayExperienceYears = $pandit->experienceYearsFor($service);
    $displayApproxPerformed = (int) ($service?->approx_performed ?: 0);
    $photo = $pandit->profile_photo ? asset('storage/'.$pandit->profile_photo) : asset('assets/small-deep.jpg');
    $name = $pandit->pandit_name ?: $pandit->full_name;
    $languageText = $pandit->languages->pluck('language')->join(', ');
    $slotText = $pandit->availabilitySlots->map(fn($slot) => substr($slot->start_time, 0, 5).' - '.substr($slot->end_time, 0, 5))->join(', ');
    $hawanType = $hawanType ?? request('hawan_type');
@endphp

<div class="col-md-6 col-xl-3">
    <div class="pandit-card">
        <img src="{{ $photo }}" alt="{{ $name }}">
        <div class="pandit-card-body">
            <div class="pandit-card-title">
                <h3>{{ $name }}</h3>
                <span><i class="bi bi-patch-check-fill"></i> Verified</span>
            </div>

            <p class="pandit-muted">{{ $pandit->city ?: 'Online' }}</p>

            <div class="pandit-card-stats">
                <strong>{{ $displayExperienceYears }} yrs</strong>
                <span>{{ $hawan['name'] }} exp.</span>
            </div>
            <div class="pandit-card-stats">
                <strong>{{ number_format($displayApproxPerformed) }}</strong>
                <span>approx performed</span>
            </div>

            <p><i class="bi bi-translate"></i> {{ $languageText ?: 'Language not added' }}</p>
            <p><i class="bi bi-clock"></i> {{ $slotText ?: 'Available' }}</p>

            <div class="pandit-card-actions">
                <a class="btn btn-ghost-gold" href="{{ route($serviceType.'.pandits.show', ['slug' => $hawan['slug'], 'pandit' => $pandit->id, 'date' => $date, 'slot' => $slot, 'mode' => $mode, 'hawan_type' => $hawanType, 'booking_mode' => $bookingMode, 'service_id' => $service?->id]) }}">View Profile</a>
                <form method="POST" action="{{ route($serviceType.'.pandits.select', ['slug' => $hawan['slug'], 'pandit' => $pandit->id]) }}">
                    @csrf
                    <input type="hidden" name="pandit_service_id" value="{{ $service?->id }}">
                    <input type="hidden" name="date" value="{{ $date }}">
                    <input type="hidden" name="slot" value="{{ $slot }}">
                    <input type="hidden" name="mode" value="{{ $mode }}">
                    <input type="hidden" name="hawan_type" value="{{ $hawanType }}">
                    <input type="hidden" name="booking_mode" value="{{ $bookingMode }}">
                    <button class="btn btn-saffron" type="submit">Select Pandit</button>
                </form>
            </div>
        </div>
    </div>
</div>
