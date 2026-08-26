@extends('layouts.pandit-dashboard')

@section('title', 'Documents - BhaktiDeep')

@php $activeMenu = 'documents'; @endphp

@section('content')

@if(session('success'))<div style="color:green;margin-bottom:12px">{{ session('success') }}</div>@endif

@php
    $documentRows = [
        ['Government ID Type', $document->government_id_type ?? null, null],
        ['Government ID File', $document?->government_id_file ? 'Uploaded' : null, $document?->government_id_file],
        ['PAN Number', $document->pan_number ?? null, null],
        ['PAN Card', $document?->pan_card_file ? 'Uploaded' : null, $document?->pan_card_file],
        ['Address Proof', $document?->address_proof ? 'Uploaded' : null, $document?->address_proof],
        ['Qualification Certificate', $document?->qualification_certificate ? 'Uploaded' : null, $document?->qualification_certificate],
        ['Mantra Chanting Sample', $document?->mantra_chanting_sample ? 'Uploaded' : null, $document?->mantra_chanting_sample],
        ['Hawan Performance Video', $document?->hawan_performance_video ? 'Uploaded' : null, $document?->hawan_performance_video],
        ['Verification Status', ucfirst($document->verification_status ?? 'pending'), null],
    ];
@endphp

<input type="checkbox" id="documentsEditToggle" class="pandit-edit-toggle">
<div class="pandit-page-heading">
    <div><p>Private for verification</p><h1>Documents</h1></div>
    <label for="documentsEditToggle" class="pandit-add-btn pandit-edit-profile-btn"><i class="bi bi-pencil-square"></i> Edit Documents</label>
</div>

<section class="pandit-documents-view pandit-panel">
    <div class="pandit-panel-heading">
        <span><i class="bi bi-file-earmark-lock"></i></span>
        <div>
            <h2>Document Details</h2>
            <p>Private files used only for BhaktiDeep verification.</p>
        </div>
    </div>

    <div class="pandit-profile-grid">
        @foreach ($documentRows as [$label, $value, $file])
            <div class="pandit-profile-item">
                <span>{{ $label }}</span>
                <strong>
                    @if ($file)
                        <a href="{{ asset('storage/'.$file) }}" target="_blank">View current</a>
                    @else
                        {{ $value ?? 'Not added' }}
                    @endif
                </strong>
            </div>
        @endforeach
    </div>
</section>

<section class="pandit-documents-edit pandit-panel">
    <div class="pandit-panel-heading">
        <span><i class="bi bi-pencil-square"></i></span>
        <div>
            <h2>Edit Documents</h2>
            <p>Upload new files only when you want to replace or add documents.</p>
        </div>
    </div>
    <form class="pandit-dashboard-form" method="POST" action="{{ route('pandit.documents.update') }}" enctype="multipart/form-data">
        @csrf
        <div class="pandit-documents-grid">
            <div class="pandit-document-card">
                <strong>Identity Proof</strong>
                <label>Government ID Type
                    <select name="government_id_type">
                        @foreach(['Aadhaar Card','Passport','Voter ID','Driving License'] as $opt)
                            <option @selected(($document->government_id_type ?? '') === $opt)>{{ $opt }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Government ID Upload<input type="file" name="government_id_file"></label>
                @if($document?->government_id_file)<small><a href="{{ asset('storage/'.$document->government_id_file) }}" target="_blank">View current</a></small>@endif
            </div>

            <div class="pandit-document-card">
                <strong>PAN Details</strong>
                <label>PAN Number<input type="text" name="pan_number" value="{{ $document->pan_number ?? '' }}"></label>
                <label>PAN Upload<input type="file" name="pan_card_file"></label>
                @if($document?->pan_card_file)<small><a href="{{ asset('storage/'.$document->pan_card_file) }}" target="_blank">View current</a></small>@endif
            </div>

            <div class="pandit-document-card">
                <strong>Address & Qualification</strong>
                <label>Address Proof<input type="file" name="address_proof"></label>
                @if($document?->address_proof)<small><a href="{{ asset('storage/'.$document->address_proof) }}" target="_blank">View current</a></small>@endif
                <label>Qualification Certificate<input type="file" name="qualification_certificate"></label>
                @if($document?->qualification_certificate)<small><a href="{{ asset('storage/'.$document->qualification_certificate) }}" target="_blank">View current</a></small>@endif
            </div>

            <div class="pandit-document-card">
                <strong>Ritual Media</strong>
                <label>Mantra Chanting Sample<input type="file" name="mantra_chanting_sample"></label>
                @if($document?->mantra_chanting_sample)<small><a href="{{ asset('storage/'.$document->mantra_chanting_sample) }}" target="_blank">View current</a></small>@endif
                <label>Hawan Performance Video<input type="file" name="hawan_performance_video"></label>
                @if($document?->hawan_performance_video)<small><a href="{{ asset('storage/'.$document->hawan_performance_video) }}" target="_blank">View current</a></small>@endif
            </div>
        </div>
        <div class="pandit-profile-actions">
            <label for="documentsEditToggle" class="pandit-cancel-btn">Cancel</label>
            <button type="submit" class="pandit-submit-btn compact"><i class="bi bi-save"></i> Save Changes</button>
        </div>
    </form>
</section>
@endsection
