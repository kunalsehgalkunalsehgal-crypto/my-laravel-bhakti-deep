@extends('layouts.app')

@section('title', ($pandit->pandit_name ?: $pandit->full_name) . ' - Pandit Profile - BhaktiDeep')

@push('styles')
<link href="{{ asset('css/light-diya.css') }}" rel="stylesheet">
<link href="{{ asset('css/pandit-selection.css') }}" rel="stylesheet">
@endpush

@section('body')
@php
    $serviceType = $serviceType ?? 'hawan';
    $serviceLabel = ucfirst($serviceType);
    $name = $pandit->pandit_name ?: $pandit->full_name;
    $photo = $pandit->profile_photo ? asset('storage/'.$pandit->profile_photo) : asset('assets/small-deep.jpg');
    $qualification = $pandit->qualification;
    $document = $pandit->document;
    $knowledgeAreas = $qualification?->knowledge_areas ?? [];
    if (is_string($knowledgeAreas)) {
        $knowledgeAreas = json_decode($knowledgeAreas, true) ?: [];
    }
    $hawanPhotos = $document?->hawan_photos ?? [];
    $poojaPhotos = $document?->pooja_photos ?? [];
    $serviceSuffix = $serviceType === 'pooja' ? ' Pooja' : ' Hawan';
    $serviceNames = collect([$hawan['name'], trim(\Illuminate\Support\Str::replaceLast($serviceSuffix, '', $hawan['name']))])->unique();
    $selectedService = $selectedService ?? app(\App\Services\PanditBookingService::class)->approvedService($pandit, $serviceType, $serviceNames, request()->integer('service_id') ?: null, $hawan['id'] ?? null);
    $displayExperienceYears = $pandit->experienceYearsFor($selectedService);
    $displayApproxPerformed = (int) ($selectedService?->approx_performed ?: 0);
@endphp

<main class="page-shell pandit-profile-page">
    <section class="container page-section">
        @if(session('error'))
            <div class="alert alert-warning">{{ session('error') }}</div>
        @endif

        <div class="public-profile-hero">
            <img src="{{ $photo }}" alt="{{ $name }}">
            <div>
                <span class="eyebrow"><i class="bi bi-patch-check-fill"></i> Verified Pandit</span>
                <h1>{{ $name }}</h1>
                <p>{{ $pandit->city }} {{ $pandit->state ? ', '.$pandit->state : '' }}</p>
                <div class="profile-tags">
                    <span>{{ $displayExperienceYears }} yrs {{ $hawan['name'] }} experience</span>
                    <span>{{ number_format($displayApproxPerformed) }} approx performed</span>
                    <span>{{ $serviceType === 'pooja' ? ($pandit->onlineSetup?->online_pooja ? 'Online Pooja' : 'Offline Only') : ($pandit->onlineSetup?->online_hawan ? 'Online Hawan' : 'Offline Only') }}</span>
                </div>
            </div>
        </div>
    </section>

    <section class="container page-section profile-grid">
        <div>
            <div class="profile-panel">
                <h2>About</h2>
                <p>{{ $pandit->about ?: 'About details not added yet.' }}</p>
            </div>

            <div class="profile-panel">
                <h2>Qualification</h2>
                <p><strong>{{ $qualification?->highest_qualification ?: 'Not added' }}</strong></p>
                <p>{{ $qualification?->course_name }} {{ $qualification?->institute_name ? '- '.$qualification->institute_name : '' }}</p>
                <p>Guru: {{ $qualification?->guru_name ?: 'Not added' }}</p>
            </div>

            <div class="profile-panel">
                <h2>Sampradaya</h2>
                <p>{{ $qualification?->sampradaya ?: 'Not added' }}</p>
                <p>{{ $qualification?->ritual_tradition }}</p>
            </div>

            <div class="profile-panel">
                <h2>Knowledge Areas</h2>
                <div class="profile-tags">
                    @forelse($knowledgeAreas as $area)
                        <span>{{ $area }}</span>
                    @empty
                        <span>Not added</span>
                    @endforelse
                </div>
            </div>

            <div class="profile-panel">
                <h2>Services</h2>
                <div class="service-list">
                    @foreach($pandit->services->where('status', 'approved') as $service)
                        <div>
                            <strong>{{ $service->service_name }}</strong>
                            <span>{{ ucfirst($service->service_type) }} - {{ $pandit->experienceYearsFor($service) }} yrs - {{ number_format($service->approx_performed ?? 0) }} performed</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <aside>
            <div class="profile-panel">
                <h2>Languages</h2>
                <div class="profile-tags">
                    @forelse($pandit->languages as $language)
                        <span>{{ $language->language }}</span>
                    @empty
                        <span>Not added</span>
                    @endforelse
                </div>
            </div>

            <div class="profile-panel">
                <h2>Availability</h2>
                @foreach($pandit->availabilitySlots->groupBy('day') as $day => $slots)
                    <p><strong>{{ $day }}</strong>: {{ $slots->map(fn($slot) => substr($slot->start_time, 0, 5).' - '.substr($slot->end_time, 0, 5))->join(', ') }}</p>
                @endforeach
                <p>{{ $pandit->availabilitySetting?->accept_new_bookings ? 'Accepting new bookings' : 'Not accepting new bookings' }}</p>
            </div>

            <div class="profile-panel">
                <h2>Online / Offline</h2>
                <p>Online {{ $serviceLabel }}: {{ $serviceType === 'pooja' ? ($pandit->onlineSetup?->online_pooja ? 'Yes' : 'No') : ($pandit->onlineSetup?->online_hawan ? 'Yes' : 'No') }}</p>
                <p>Offline {{ $serviceLabel }}: {{ $serviceType === 'pooja' ? ($pandit->availabilitySetting?->offline_pooja ? 'Yes' : 'No') : ($pandit->availabilitySetting?->offline_hawan ? 'Yes' : 'No') }}</p>
                <p>Service State: {{ $pandit->availabilitySetting?->service_state ?: 'Not added' }}</p>
                <p>Service Cities: {{ collect([$pandit->availabilitySetting?->service_city])->merge($pandit->availabilitySetting?->other_service_cities ?? [])->filter()->join(', ') ?: 'Not added' }}</p>
            </div>

            <div class="profile-panel">
                <h2>Reviews</h2>
                @forelse($pandit->reviews as $review)
                    <p><strong>{{ str_repeat('★', (int) $review->rating) }}</strong> {{ $review->comment ?: 'No comment' }}</p>
                    @if($review->image_path)
                        <p><a href="{{ asset('storage/'.$review->image_path) }}" target="_blank">View review image</a></p>
                    @endif
                @empty
                    <p>No reviews yet.</p>
                @endforelse
            </div>

            <div class="profile-panel">
                <h2>Work Media</h2>
                <div class="media-grid">
                    @foreach(array_merge($hawanPhotos, $poojaPhotos) as $image)
                        <img src="{{ asset('storage/'.$image) }}" alt="Pandit work photo">
                    @endforeach
                </div>
                @if($document?->mantra_chanting_sample)
                    <audio controls src="{{ asset('storage/'.$document->mantra_chanting_sample) }}"></audio>
                @endif
                @if($document?->hawan_performance_video)
                    <video controls src="{{ asset('storage/'.$document->hawan_performance_video) }}"></video>
                @endif
                @if(empty($hawanPhotos) && empty($poojaPhotos) && !$document?->mantra_chanting_sample && !$document?->hawan_performance_video)
                    <p>No public media added.</p>
                @endif
            </div>

            <form method="POST" action="{{ route($serviceType.'.pandits.select', ['slug' => $hawan['slug'], 'pandit' => $pandit->id]) }}" class="profile-select-form">
                @csrf
                <input type="hidden" name="pandit_service_id" value="{{ $selectedService?->id }}">
                <input type="hidden" name="date" value="{{ request('date') }}">
                <input type="hidden" name="slot" value="{{ request('slot') }}">
                <input type="hidden" name="mode" value="{{ request('mode', 'Live + Replay') }}">
                <input type="hidden" name="hawan_type" value="{{ request('hawan_type') }}">
                <input type="hidden" name="booking_mode" value="{{ request('booking_mode', 'online') }}">
                <input type="hidden" name="state" value="{{ request('state') }}">
                <input type="hidden" name="city" value="{{ request('city') }}">
                <button class="btn btn-saffron w-100" type="submit">Select Pandit</button>
                <a class="btn btn-ghost-gold w-100 mt-2" href="{{ route($serviceType.'.pandits', ['slug' => $hawan['slug'], 'date' => request('date'), 'slot' => request('slot'), 'mode' => request('mode', 'Live + Replay'), 'hawan_type' => request('hawan_type'), 'booking_mode' => request('booking_mode', 'online'), 'state' => request('state'), 'city' => request('city')]) }}">Back to Pandits</a>
            </form>
        </aside>
    </section>
</main>
@endsection
