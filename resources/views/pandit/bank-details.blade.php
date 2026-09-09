@extends('layouts.pandit-dashboard')

@section('title', 'Bank Details - BhaktiDeep')

@php $activeMenu = 'bank-details'; @endphp

@section('content')

@if(session('success'))<div style="color:green;margin-bottom:12px">{{ session('success') }}</div>@endif
@if($errors->has('razorpay'))<div style="color:red;margin-bottom:12px">{{ $errors->first('razorpay') }}</div>@endif

@php
    $bankRows = [
        ['Account Holder Name', $bank->account_holder_name ?? null],
        ['Bank Name', $bank->bank_name ?? null],
        ['Account Number', $bank->account_number ?? null],
        ['IFSC', $bank->ifsc_code ?? null],
        ['UPI ID', $bank->upi_id ?? null],
        ['PAN Number', $bank->pan_number ?? null],
        ['Verification Status', ucfirst($bank->verification_status ?? 'pending')],
    ];
@endphp

<input type="checkbox" id="bankEditToggle" class="pandit-edit-toggle">
<div class="pandit-page-heading">
    <div><p>Private and secure</p><h1>Bank Details</h1></div>
    <label for="bankEditToggle" class="pandit-add-btn pandit-edit-profile-btn"><i class="bi bi-pencil-square"></i> Edit Bank Details</label>
</div>

<section class="pandit-bank-view pandit-panel">
    <div class="pandit-panel-heading">
        <span><i class="bi bi-bank"></i></span>
        <div>
            <h2>Bank & Payment Details</h2>
            <p>These details are private and never shown publicly.</p>
        </div>
    </div>

    <div class="pandit-profile-grid">
        @foreach ($bankRows as [$label, $value])
            <div class="pandit-profile-item">
                <span>{{ $label }}</span>
                <strong>{{ $value ?? 'Not added' }}</strong>
            </div>
        @endforeach
    </div>
    @if($bank && !$bank->razorpay_linked_account_id)
        <form method="POST" action="{{ route('pandit.bank-details.razorpay-linked-account') }}" class="mt-3">@csrf<button class="pandit-submit-btn compact" type="submit">Create Razorpay Linked Account</button></form>
    @endif
</section>

<section class="pandit-bank-edit pandit-panel">
    <div class="pandit-panel-heading">
        <span><i class="bi bi-pencil-square"></i></span>
        <div>
            <h2>Edit Bank & Payment Details</h2>
            <p>Update payout and verification information.</p>
        </div>
    </div>
    <form class="pandit-dashboard-form" method="POST" action="{{ route('pandit.bank-details.update') }}">
        @csrf
        <div class="pandit-bank-grid">
            <label>Account Holder Name<input type="text" name="account_holder_name" value="{{ $bank->account_holder_name ?? '' }}"></label>
            <label>Bank Name<input type="text" name="bank_name" value="{{ $bank->bank_name ?? '' }}"></label>
            <label>Account Number<input type="text" name="account_number" value="{{ $bank->account_number ?? '' }}"></label>
            <label>IFSC<input type="text" name="ifsc_code" value="{{ $bank->ifsc_code ?? '' }}"></label>
            <label>UPI ID<input type="text" name="upi_id" value="{{ $bank->upi_id ?? '' }}"></label>
            <label>PAN Number<input type="text" name="pan_number" value="{{ $bank->pan_number ?? '' }}"></label>
        </div>
        <div class="pandit-profile-actions">
            <label for="bankEditToggle" class="pandit-cancel-btn">Cancel</label>
            <button type="submit" class="pandit-submit-btn compact"><i class="bi bi-save"></i> Save Changes</button>
        </div>
    </form>
</section>
@endsection
