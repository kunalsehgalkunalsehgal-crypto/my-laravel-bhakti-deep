@extends('admin.layout')

@section('title', ucfirst($type).' Bookings')

@section('content')
    <h1>{{ ucfirst($type) }} Bookings</h1>
    <div class="panel">
        <form class="filters" method="GET">
            <select name="status"><option value="">All statuses</option>@foreach(['pending','scheduled','confirmed','active','completed','cancelled'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select>
            <select name="payment_status"><option value="">All payments</option>@foreach(['pending','paid','failed','refunded'] as $status)<option value="{{ $status }}" @selected(request('payment_status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select>
            <button class="btn">Filter</button>
        </form>
    </div>
    <div class="panel">
        <table>
            <thead><tr><th>ID</th><th>User</th><th>Selected {{ ucfirst($type) }}</th><th>Pandit</th><th>{{ $type === 'hawan' ? 'Hawan Type' : 'Package' }}</th><th>Sankalp</th><th>Date/Slot</th><th>Status</th><th>Payment</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($records as $record)
                    @php
                        $bookingMeta = $record->admin_note ? json_decode($record->admin_note, true) : [];
                        $selectedService = $bookingMeta['diya_name'] ?? $bookingMeta['pooja_name'] ?? $bookingMeta['hawan_name'] ?? $record->service?->name ?? '-';
                        $selectedPackage = $record->hawan_type_title ?? $bookingMeta['hawan_type_title'] ?? $bookingMeta['package_name'] ?? '-';
                        $selectedPandit = $record->pandit?->pandit_name ?: ($record->pandit?->full_name ?: ($bookingMeta['pandit_name'] ?? '-'));
                        $totalAmount = isset($bookingMeta['total_amount']) ? 'Rs.'.number_format((float) $bookingMeta['total_amount']) : null;
                    @endphp
                    <tr>
                        <td>#{{ $record->id }}</td>
                        <td>{{ $record->user?->name ?? '-' }}</td>
                        <td>
                            {{ $selectedService }}
                            @if(!empty($bookingMeta['deity_name']))
                                <br><small>{{ $bookingMeta['deity_name'] }}</small>
                            @endif
                        </td>
                        <td>{{ $selectedPandit }}</td>
                        <td>{{ $selectedPackage }}@if($totalAmount)<br><small>{{ $totalAmount }}</small>@endif</td>
                        <td>{{ $record->sankalp?->full_name ?? '-' }}</td>
                        <td>{{ $record->booking_date?->toDateString() ?? '-' }}<br>{{ $record->slot }}</td>
                        <td><span class="badge {{ $record->status }}">{{ $record->status }}</span></td>
                        <td><span class="badge {{ $record->payment_status }}">{{ $record->payment_status }}</span></td>
                        <td><a class="btn small" href="{{ route('admin.bookings.show', [$type, $record]) }}">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="10">No bookings found.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $records->links() }}
    </div>
@endsection
