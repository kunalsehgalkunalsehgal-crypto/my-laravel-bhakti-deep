@extends('admin.layout')

@section('title', 'Pandit Payouts')

@section('content')
    <div class="toolbar">
        <h1>Pandit Payouts</h1>
        <div class="badge {{ $routeAutomatic ? 'active' : 'failed' }}">
            {{ $routeAutomatic ? 'Razorpay Route Automatic' : 'Provider Pending - Manual Mode' }}
        </div>
    </div>

    @unless($routeAutomatic)
        <div class="alert error">
            Payout mode is {{ $payoutMode }}. READY payouts will not be transferred automatically.
        </div>
    @endunless

    <div class="grid stats" style="margin-bottom:18px;">
        <div class="panel stat">
            <span>Ready Pandit Amount</span>
            <strong>Rs {{ number_format((float) $readyTotal) }}</strong>
        </div>
    </div>

    <div class="panel">
        <form class="filters" method="GET">
            <select name="status">
                <option value="">All payout statuses</option>
                @foreach($statuses as $option)
                    <option value="{{ $option }}" @selected($status === $option)>{{ ucfirst($option) }}</option>
                @endforeach
            </select>
            <button class="btn">Filter</button>
        </form>
    </div>

    <div class="panel">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Booking</th>
                    <th>Pandit</th>
                    <th>Booking Amount</th>
                    <th>Service Amount</th>
                    <th>Dakshina</th>
                    <th>Pandit Amount</th>
                    <th>Platform Amount</th>
                    <th>Commission</th>
                    <th>Status</th>
                    <th>Transfer ID</th>
                    <th>Transfer Error</th>
                    <th>Eligible At</th>
                    <th>Paid At</th>
                    <th>Cancelled At</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payouts as $payout)
                    @php
                        $bookingType = $payout->session_type === $hawanSessionClass ? 'hawan' : ($payout->session_type === $poojaSessionClass ? 'pooja' : null);
                    @endphp
                    <tr>
                        <td>#{{ $payout->id }}</td>
                        <td>
                            @if($bookingType && $payout->session_id)
                                <a class="btn small" href="{{ route('admin.bookings.show', [$bookingType, $payout->session_id]) }}">
                                    {{ strtoupper($bookingType) }}-{{ $payout->session_id }}
                                </a>
                            @else
                                Not Available
                            @endif
                        </td>
                        <td>{{ $payout->pandit?->pandit_name ?: ($payout->pandit?->full_name ?: 'Not Available') }}</td>
                        <td>Rs {{ number_format((float) $payout->booking_amount) }}</td>
                        <td>Rs {{ number_format((float) $payout->service_amount) }}</td>
                        <td>Rs {{ number_format((float) $payout->dakshina_amount) }}</td>
                        <td>Rs {{ number_format((float) $payout->pandit_amount) }}</td>
                        <td>Rs {{ number_format((float) $payout->platform_amount) }}</td>
                        <td>{{ number_format((float) $payout->commission_percent, 2) }}%</td>
                        <td>
                            <span class="badge {{ $payout->status }}">{{ ucfirst($payout->status) }}</span>
                            @if(!$routeAutomatic && $payout->status === \App\Models\PanditPayout::STATUS_READY)
                                <br><small>Bank payout pending (manual)</small>
                            @endif
                        </td>
                        <td>{{ $payout->razorpay_transfer_id ?: ($payout->provider_payout_id ?: ($payout->payout_reference ?: 'Not Available')) }}</td>
                        <td>{{ $payout->last_error ?: 'Not Available' }}</td>
                        <td>{{ $payout->eligible_at?->format('d M Y, h:i A') ?? 'Not Available' }}</td>
                        <td>{{ $payout->paid_at?->format('d M Y, h:i A') ?? 'Not Available' }}</td>
                        <td>{{ $payout->cancelled_at?->format('d M Y, h:i A') ?? 'Not Available' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="15">No payouts found.</td></tr>
                @endforelse
            </tbody>
        </table>

        {{ $payouts->links() }}
    </div>
@endsection
