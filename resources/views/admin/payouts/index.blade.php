@extends('admin.layout')

@section('title', 'Pandit Payouts')

@section('content')
    <div class="toolbar">
        <h1>Pandit Payouts</h1>
        <div class="badge {{ $providerConfigured ? 'active' : 'failed' }}">
            {{ $providerConfigured ? 'Provider Configured' : 'Provider Pending' }}
        </div>
    </div>

    @unless($providerConfigured)
        <div class="alert error">
            Real bank payout provider is not configured. Ready payouts stop at ready and must not be marked paid here.
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
                    <th>Pandit Amount</th>
                    <th>Platform Amount</th>
                    <th>Status</th>
                    <th>Provider Payout ID</th>
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
                        <td>Rs {{ number_format((float) $payout->pandit_amount) }}</td>
                        <td>Rs {{ number_format((float) $payout->platform_amount) }}</td>
                        <td>
                            <span class="badge {{ $payout->status }}">{{ ucfirst($payout->status) }}</span>
                            @if(!$providerConfigured && $payout->status === \App\Models\PanditPayout::STATUS_READY)
                                <br><small>Bank payout pending</small>
                            @endif
                        </td>
                        <td>{{ $payout->provider_payout_id ?: ($payout->payout_reference ?: 'Not Available') }}</td>
                        <td>{{ $payout->eligible_at?->format('d M Y, h:i A') ?? 'Not Available' }}</td>
                        <td>{{ $payout->paid_at?->format('d M Y, h:i A') ?? 'Not Available' }}</td>
                        <td>{{ $payout->cancelled_at?->format('d M Y, h:i A') ?? 'Not Available' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="11">No payouts found.</td></tr>
                @endforelse
            </tbody>
        </table>

        {{ $payouts->links() }}
    </div>
@endsection
