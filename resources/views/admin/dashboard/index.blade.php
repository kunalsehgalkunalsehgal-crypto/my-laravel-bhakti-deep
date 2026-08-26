@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
    <h1>Dashboard</h1>
    <div class="grid stats">
        @foreach($stats as $label => $value)
            <div class="panel stat">
                <span>{{ $label }}</span>
                <strong>{{ $value }}</strong>
            </div>
        @endforeach
    </div>

    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(320px,1fr));">
        <div class="panel">
            <h2>Latest Hawan Bookings</h2>
            <table>
                <thead><tr><th>ID</th><th>Selected Hawan</th><th>Hawan Type</th><th>Sankalp</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($latestHawan as $booking)
                        @php
                            $bookingMeta = $booking->admin_note ? json_decode($booking->admin_note, true) : [];
                            $selectedService = $bookingMeta['hawan_name'] ?? $booking->service?->name ?? 'Service';
                            $selectedPackage = $booking->hawan_type_title ?? $bookingMeta['hawan_type_title'] ?? $bookingMeta['package_name'] ?? '-';
                        @endphp
                        <tr>
                            <td>#{{ $booking->id }}</td>
                            <td>{{ $selectedService }}</td>
                            <td>{{ $selectedPackage }}</td>
                            <td>{{ $booking->sankalp?->full_name ?? '-' }}</td>
                            <td><span class="badge {{ $booking->status }}">{{ $booking->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5">No bookings yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="panel">
            <h2>Latest Donations</h2>
            <table>
                <thead><tr><th>ID</th><th>Donor</th><th>Amount</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($latestDonations as $donation)
                        <tr>
                            <td>#{{ $donation->id }}</td>
                            <td>{{ $donation->donor_name ?: $donation->user?->name ?? '-' }}</td>
                            <td>{{ $donation->currency }} {{ number_format((float) $donation->amount, 2) }}</td>
                            <td><span class="badge {{ $donation->payment_status }}">{{ $donation->payment_status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4">No donations yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
