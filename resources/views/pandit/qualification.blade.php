@extends('layouts.pandit-dashboard')

@section('title', 'Qualification - BhaktiDeep')

@php $activeMenu = 'qualification'; @endphp

@section('content')
@php
    $knowledgeAreas = ['Vedic Mantras', 'Sanskrit Pronunciation', 'Sankalp', 'Hawan Vidhi', 'Pooja Vidhi', 'Aarti', 'Rudrabhishek', 'Karmakand', 'Panchang', 'Muhurat', 'Jyotish', 'Vastu', 'Temple Rituals', 'Sanskar Vidhi'];
    $selectedAreas = $qualification ? (json_decode($qualification->knowledge_areas, true) ?? []) : [];
@endphp

@if(session('success'))<div style="color:green;margin-bottom:12px">{{ session('success') }}</div>@endif

<input type="checkbox" id="qualificationEditToggle" class="pandit-edit-toggle">
<div class="pandit-page-heading">
    <div><h1>Qualification</h1></div>
    <label for="qualificationEditToggle" class="pandit-add-btn"><i class="bi bi-pencil-square"></i> Edit</label>
</div>

<section class="pandit-qualification-view pandit-panel">
    <div class="pandit-panel-heading">
        <span><i class="bi bi-award"></i></span>
        <div><h2>Qualification Details</h2></div>
    </div>
    @if($qualification)
    <div class="pandit-profile-grid">
        @foreach ([
            ['Highest Qualification', $qualification->highest_qualification],
            ['Course/Training', $qualification->course_name],
            ['Institute/Gurukul', $qualification->institute_name],
            ['Guru/Acharya Name', $qualification->guru_name],
            ['Completion Year', $qualification->completion_year],
            ['Certificate No.', $qualification->certificate_number],
            ['Sampradaya', $qualification->sampradaya],
            ['Ritual Tradition', $qualification->ritual_tradition],
            ['Sanskrit Level', $qualification->sanskrit_level],
        ] as [$label, $value])
            <div class="pandit-profile-item"><span>{{ $label }}</span><strong>{{ $value ?? '—' }}</strong></div>
        @endforeach
    </div>
    <div class="pandit-knowledge-view">
        <h2>Knowledge Areas</h2>
        <div class="pandit-chip-grid">
            @foreach ($knowledgeAreas as $area)
                <span class="pandit-readonly-chip {{ in_array($area, $selectedAreas) ? 'selected' : '' }}">{{ $area }}</span>
            @endforeach
        </div>
    </div>
    @else<p>No qualification added yet.</p>@endif
</section>

<section class="pandit-qualification-edit pandit-panel">
    <div class="pandit-panel-heading">
        <span><i class="bi bi-pencil-square"></i></span>
        <div><h2>Edit Qualification</h2></div>
    </div>
    <form class="pandit-dashboard-form" method="POST" action="{{ route('pandit.qualification.update') }}" enctype="multipart/form-data">
        @csrf
        <div class="pandit-form-row">
            <label>Highest Qualification
                <select name="highest_qualification">
                    @foreach(['Traditional Guru Training','Gurukul Training','Pathshala','Karmakand','Shastri','Acharya','Veda Studies','Sanskrit Degree','Jyotish','Other'] as $opt)
                        <option @selected(($qualification->highest_qualification ?? '') === $opt)>{{ $opt }}</option>
                    @endforeach
                </select>
            </label>
            <label>Course/Training<input type="text" name="course_name" value="{{ $qualification->course_name ?? '' }}"></label>
            <label>Institute/Gurukul<input type="text" name="institute_name" value="{{ $qualification->institute_name ?? '' }}"></label>
            <label>Guru/Acharya Name<input type="text" name="guru_name" value="{{ $qualification->guru_name ?? '' }}"></label>
            <label>Completion Year<input type="number" name="completion_year" value="{{ $qualification->completion_year ?? '' }}"></label>
            <label>Certificate No.<input type="text" name="certificate_number" value="{{ $qualification->certificate_number ?? '' }}"></label>
            <label>Certificate Upload<input type="file" name="certificate_file"></label>
            <label>Sampradaya<input type="text" name="sampradaya" value="{{ $qualification->sampradaya ?? '' }}"></label>
            <label>Ritual Tradition<input type="text" name="ritual_tradition" value="{{ $qualification->ritual_tradition ?? '' }}"></label>
            <label>Sanskrit Level
                <select name="sanskrit_level">
                    @foreach(['Beginner','Intermediate','Advanced','Scholar'] as $lvl)
                        <option @selected(($qualification->sanskrit_level ?? '') === $lvl)>{{ $lvl }}</option>
                    @endforeach
                </select>
            </label>
        </div>
        <div class="pandit-advance-box">
            <h2>Knowledge Areas</h2>
            <div class="pandit-chip-grid">
                @foreach ($knowledgeAreas as $area)
                    <label class="pandit-chip">
                        <input type="checkbox" name="knowledge_areas[]" value="{{ $area }}" {{ in_array($area, $selectedAreas) ? 'checked' : '' }}>
                        <span>{{ $area }}</span>
                    </label>
                @endforeach
            </div>
        </div>
        <div class="pandit-profile-actions">
            <label for="qualificationEditToggle" class="pandit-cancel-btn">Cancel</label>
            <button type="submit" class="pandit-submit-btn compact"><i class="bi bi-save"></i> Save Changes</button>
        </div>
    </form>
</section>
@endsection
