@extends('admin.layout')

@section('title', ucfirst($type).' Booking Detail')

@section('content')
    @php
        $bookingMeta = $record->admin_note ? json_decode($record->admin_note, true) : [];
        $selectedService = $bookingMeta['diya_name'] ?? $bookingMeta['pooja_name'] ?? $bookingMeta['hawan_name'] ?? $record->service?->name ?? '-';
        $selectedPandit = $record->pandit?->pandit_name ?: ($record->pandit?->full_name ?: ($bookingMeta['pandit_name'] ?? '-'));
        $selectedPackage = $record->hawan_type_title ?? $bookingMeta['hawan_type_title'] ?? $bookingMeta['package_name'] ?? '-';
        $packageAmountValue = $record->hawan_type_price ?? $bookingMeta['hawan_type_price'] ?? $bookingMeta['package_amount'] ?? null;
        $packageAmount = $packageAmountValue !== null ? 'Rs.'.number_format((float) $packageAmountValue) : '-';
        $packageLabel = $type === 'hawan' ? 'Hawan Type' : 'Package';
        $dakshina = isset($bookingMeta['dakshina']) ? 'Rs.'.number_format((float) $bookingMeta['dakshina']) : '-';
        $totalAmount = isset($bookingMeta['total_amount']) ? 'Rs.'.number_format((float) $bookingMeta['total_amount']) : '-';
    @endphp
    <div class="toolbar"><h1>{{ ucfirst($type) }} Booking #{{ $record->id }}</h1><a class="btn" href="{{ route('admin.bookings.'.$type) }}">Back</a></div>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(320px,1fr));">
        <div class="panel">
            <h2>Booking</h2>
            <table><tbody>
                <tr><th>Selected {{ ucfirst($type) }}</th><td>{{ $selectedService }}</td></tr>
                @if(!empty($bookingMeta['deity_name']))
                    <tr><th>Selected Deity</th><td>{{ $bookingMeta['deity_name'] }}</td></tr>
                @endif
                <tr><th>Pandit</th><td>{{ $selectedPandit }}</td></tr>
                <tr><th>{{ $packageLabel }}</th><td>{{ $selectedPackage }}</td></tr>
                <tr><th>{{ $packageLabel }} Amount</th><td>{{ $packageAmount }}</td></tr>
                <tr><th>Dakshina</th><td>{{ $dakshina }}</td></tr>
                <tr><th>Total Paid</th><td>{{ $totalAmount }}</td></tr>
                <tr><th>User</th><td>{{ $record->user?->name ?? '-' }}</td></tr>
                <tr><th>Date</th><td>{{ $record->booking_date?->toDateString() ?? '-' }}</td></tr>
                <tr><th>Slot</th><td>{{ $record->slot }}</td></tr>
                <tr><th>Live Link</th><td>{{ $record->live_session_link ?: '-' }}</td></tr>
            </tbody></table>
        </div>
        <div class="panel">
            <h2>Sankalp Details</h2>
            <table><tbody>
                <tr><th>Name</th><td>{{ $record->sankalp?->full_name ?? '-' }}</td></tr>
                <tr><th>Mobile</th><td>{{ $record->sankalp?->mobile ?? '-' }}</td></tr>
                <tr><th>Gotra</th><td>{{ $record->sankalp?->gotra ?? '-' }}</td></tr>
                <tr><th>DOB</th><td>{{ $record->sankalp?->dob?->toDateString() ?? '-' }}</td></tr>
                <tr><th>Birth Place</th><td>{{ $record->sankalp?->birth_place ?? '-' }}</td></tr>
                <tr><th>Purpose</th><td>{{ $record->sankalp?->purpose ?? '-' }}</td></tr>
                <tr><th>Mannokamna</th><td>{{ $record->sankalp?->mannokamna ?? '-' }}</td></tr>
            </tbody></table>
        </div>
    </div>
    <form class="panel" method="POST" action="{{ route('admin.bookings.update', [$type, $record]) }}">
        @csrf @method('PUT')
        <h2>Update Session</h2>
        <div class="form-grid">
            <div><label>Status</label><select name="status">@foreach(['pending','scheduled','confirmed','active','completed','cancelled'] as $status)<option value="{{ $status }}" @selected($record->status === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
            <div><label>Payment Status</label><select name="payment_status">@foreach(['pending','paid','failed','refunded'] as $status)<option value="{{ $status }}" @selected($record->payment_status === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
            <div class="full"><label>Live Session Link</label><input name="live_session_link" value="{{ old('live_session_link', $record->live_session_link) }}"></div>
            <div class="full"><label>Admin Note</label><textarea name="admin_note">{{ old('admin_note', $record->admin_note) }}</textarea></div>
        </div>
        <button class="btn primary" style="margin-top:14px">Save Update</button>
    </form>
@endsection
