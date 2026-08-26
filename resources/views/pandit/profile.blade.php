@extends('layouts.pandit-dashboard')

@section('title', 'My Profile - BhaktiDeep')
@php $activeMenu = 'profile'; @endphp
@section('content')

@if(session('success'))<div style="color:green;margin-bottom:12px">{{ session('success') }}</div>@endif

<input type="checkbox" id="profileEditToggle" class="pandit-edit-toggle">
<div class="pandit-page-heading">
    <div>
        <p>Profile Section Complete: 80%</p>
        <h1>My Profile</h1>
    </div>
    <label for="profileEditToggle" class="pandit-add-btn pandit-edit-profile-btn"><i class="bi bi-pencil-square"></i> Edit Profile</label>
</div>

<section class="pandit-profile-summary">
    <div class="pandit-profile-photo-wrap">
        <img src="{{ $pandit->profile_photo ? asset('storage/'.$pandit->profile_photo) : asset('assets/small-deep.jpg') }}" alt="{{ $pandit->pandit_name }}">
    </div>
    <div>
        <span>{{ ucfirst(str_replace('_',' ',$pandit->status)) }}</span>
        <h2>{{ $pandit->pandit_name ?? $pandit->full_name }}</h2>
        <p>{{ $pandit->about }}</p>
    </div>
</section>

<section class="pandit-profile-view pandit-panel">
    <div class="pandit-panel-heading">
        <span><i class="bi bi-person-vcard"></i></span>
        <div><h2>Profile Details</h2></div>
    </div>
    <div class="pandit-profile-grid">
        @foreach ([
            ['Full Name', $pandit->full_name],
            ['Pandit/Acharya Name', $pandit->pandit_name],
            ['Mobile', $pandit->mobile],
            ['WhatsApp', $pandit->whatsapp_number],
            ['Email', $pandit->email],
            ['DOB', $pandit->date_of_birth?->format('d M Y')],
            ['Gender', $pandit->gender],
            ['Address', $pandit->full_address],
            ['City', $pandit->city],
            ['State', $pandit->state],
            ['Country', $pandit->country],
            ['Experience Years', $pandit->total_experience_years],
            ['Institution', $pandit->institution_name],
            ['Position', $pandit->position_role],
            ['Independent Pandit', $pandit->is_independent ? 'Yes' : 'No'],
        ] as [$label, $value])
            <div class="pandit-profile-item"><span>{{ $label }}</span><strong>{{ $value ?? '—' }}</strong></div>
        @endforeach
        <div class="pandit-profile-item pandit-wide"><span>About</span><strong>{{ $pandit->about ?? '—' }}</strong></div>
    </div>
</section>

<section class="pandit-profile-edit pandit-panel">
    <div class="pandit-panel-heading">
        <span><i class="bi bi-pencil-square"></i></span>
        <div><h2>Edit Profile</h2></div>
    </div>
    <form class="pandit-dashboard-form" method="POST" action="{{ route('pandit.profile.update') }}" enctype="multipart/form-data">
        @csrf
        <div class="pandit-form-row">
            <label>Profile Photo<input type="file" name="profile_photo"></label>
            <label>Full Name<input type="text" name="full_name" value="{{ $pandit->full_name }}"></label>
            <label>Pandit/Acharya Name<input type="text" name="pandit_name" value="{{ $pandit->pandit_name }}"></label>
            <label>Mobile<input type="tel" name="mobile" value="{{ $pandit->mobile }}"></label>
            <label>WhatsApp<input type="tel" name="whatsapp_number" value="{{ $pandit->whatsapp_number }}"></label>
            <label>Email<input type="email" name="email" value="{{ $pandit->email }}"></label>
            <label>DOB<input type="date" name="date_of_birth" value="{{ $pandit->date_of_birth?->format('Y-m-d') }}"></label>
            <label>Gender
                <select name="gender">
                    @foreach(['Male','Female','Other'] as $g)
                        <option @selected($pandit->gender === $g)>{{ $g }}</option>
                    @endforeach
                </select>
            </label>
            <label>Address<input type="text" name="full_address" value="{{ $pandit->full_address }}"></label>
            <label>City<input type="text" name="city" value="{{ $pandit->city }}"></label>
            <label>State<input type="text" name="state" value="{{ $pandit->state }}"></label>
            <label>Country<input type="text" name="country" value="{{ $pandit->country }}"></label>
            <label>Experience Years<input type="number" name="total_experience_years" value="{{ $pandit->total_experience_years }}"></label>
            <label>Temple/Gurukul Association
                <select name="temple_association">
                    <option @selected($pandit->associated_with_institution)>Yes</option>
                    <option @selected(!$pandit->associated_with_institution)>No</option>
                </select>
            </label>
            <label>Institution Name<input type="text" name="institution_name" value="{{ $pandit->institution_name }}"></label>
            <label>Position/Role<input type="text" name="position_role" value="{{ $pandit->position_role }}"></label>
            <label>Institution City<input type="text" name="institution_city" value="{{ $pandit->institution_city }}"></label>
            <label>Independent Pandit
                <select name="independent_pandit">
                    <option @selected($pandit->is_independent)>Yes</option>
                    <option @selected(!$pandit->is_independent)>No</option>
                </select>
            </label>
            <label class="pandit-form-wide">About Pandit Ji<textarea name="about" rows="4">{{ $pandit->about }}</textarea></label>
        </div>
        <div class="pandit-profile-actions">
            <label for="profileEditToggle" class="pandit-cancel-btn">Cancel</label>
            <button type="submit" class="pandit-submit-btn compact"><i class="bi bi-save"></i> Save Changes</button>
        </div>
    </form>
</section>
@endsection
