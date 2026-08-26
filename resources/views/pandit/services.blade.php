@extends('layouts.pandit-dashboard')

@section('title', 'My Services - BhaktiDeep')

@php $activeMenu = 'services'; @endphp

@section('content')
@php
    $selectedServiceType = old('service_type', 'hawan');
    $selectedServiceId = old('service_id');
@endphp

@if(session('success'))<div style="color:green;margin-bottom:12px">{{ session('success') }}</div>@endif

<div class="pandit-page-heading">
    <div><h1>My Services</h1></div>
</div>

<section class="pandit-service-grid">
    @forelse($services as $service)
        <article class="pandit-service-card">
            <div class="pandit-service-top">
                <div>
                    <span class="pandit-status {{ $service->status === 'verified' ? 'verified' : '' }}">{{ ucfirst($service->status) }}</span>
                    <h2>{{ $service->service_name }}</h2>
                </div>
                <i class="bi bi-stars"></i>
            </div>
            <dl>
                <div><dt>Type</dt><dd>{{ ucfirst($service->service_type) }}</dd></div>
                <div><dt>Experience</dt><dd>{{ $service->experience_years ?? '—' }} yrs</dd></div>
                <div><dt>Performed</dt><dd>{{ $service->approx_performed ?? '—' }}</dd></div>
                <div><dt>Duration</dt><dd>{{ $service->duration_minutes ?? '—' }} min</dd></div>
            </dl>
            <div class="pandit-card-actions">
                <form method="POST" action="{{ route('pandit.services.delete', $service->id) }}" onsubmit="return confirm('Remove this service?')">
                    @csrf @method('DELETE')
                    <button type="submit">Remove</button>
                </form>
            </div>
        </article>
    @empty
        <p>No services added yet.</p>
    @endforelse
</section>

<section class="pandit-panel">
    <div class="pandit-panel-heading">
        <span><i class="bi bi-plus-circle"></i></span>
        <div><h2>Add Service</h2></div>
    </div>
    <form class="pandit-dashboard-form" method="POST" action="{{ route('pandit.services.add') }}">
        @csrf
        <div class="pandit-form-row">
            <label>Service Type
                <select name="service_type" id="serviceTypeSelect">
                    <option value="hawan" @selected($selectedServiceType === 'hawan')>Hawan</option>
                    <option value="pooja" @selected($selectedServiceType === 'pooja')>Pooja</option>
                </select>
            </label>
            <label>Select Service
                <select name="service_id" id="serviceIdSelect">
                    @foreach ($hawanServices as $service)
                        <option value="{{ $service->id }}" data-service-type="hawan" @selected($selectedServiceType === 'hawan' && (string) $selectedServiceId === (string) $service->id)>{{ $service->name }}</option>
                    @endforeach
                    @foreach ($poojaServices as $service)
                        <option value="{{ $service->id }}" data-service-type="pooja" @selected($selectedServiceType === 'pooja' && (string) $selectedServiceId === (string) $service->id)>{{ $service->name }}</option>
                    @endforeach
                </select>
            </label>
            <label>Experience Years<input type="number" name="experience_years" min="0" placeholder="8"></label>
            <label>Approx Performed<input type="number" name="approx_performed" min="0" placeholder="200"></label>
            <label>Duration (minutes)<input type="number" name="duration_minutes" min="0" placeholder="90"></label>
        </div>
        <button type="submit" class="pandit-submit-btn compact"><i class="bi bi-save"></i> Save Service</button>
    </form>
</section>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('serviceTypeSelect');
    const serviceSelect = document.getElementById('serviceIdSelect');

    function syncServiceOptions() {
        const selectedType = typeSelect.value;
        let firstVisible = null;
        let selectedStillVisible = false;

        Array.from(serviceSelect.options).forEach(function (option) {
            const isVisible = option.dataset.serviceType === selectedType;
            option.hidden = !isVisible;
            option.disabled = !isVisible;

            if (isVisible && !firstVisible) firstVisible = option;
            if (isVisible && option.selected) selectedStillVisible = true;
        });

        if (!selectedStillVisible && firstVisible) {
            firstVisible.selected = true;
        }
    }

    typeSelect.addEventListener('change', syncServiceOptions);
    syncServiceOptions();
});
</script>
@endsection
