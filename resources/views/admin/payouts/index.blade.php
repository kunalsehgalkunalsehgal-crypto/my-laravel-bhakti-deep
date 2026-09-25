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

    @if($errors->any())
        <div class="alert error">{{ $errors->first() }}</div>
    @endif

    <div class="grid stats" style="margin-bottom:18px;">
        <div class="panel stat">
            <span>Ready Pandit Amount</span>
            <strong>Rs {{ number_format((float) $readyTotal) }}</strong>
        </div>
    </div>

    @if($payoutMode === 'manual')
        <h2>Ready Manual Settlements</h2>
        @forelse($readyGroups as $group)
            @php
                $pandit = $group->first()->pandit;
                $bank = $pandit?->bankDetail;
            @endphp
            <form class="panel" method="POST" action="{{ route('admin.payouts.manual-settle') }}">
                @csrf
                <div class="toolbar">
                    <div>
                        <strong>{{ $pandit?->pandit_name ?: ($pandit?->full_name ?: 'Unknown Pandit') }}</strong><br>
                        <small>{{ $bank?->bank_name ?: 'Bank not added' }} | A/C {{ $bank?->account_number ?: 'Not added' }} | IFSC {{ $bank?->ifsc_code ?: 'Not added' }} | UPI {{ $bank?->upi_id ?: 'Not added' }}</small>
                    </div>
                    <strong>Rs {{ number_format((float) $group->sum('pandit_amount'), 2) }}</strong>
                </div>

                @if($bank?->upi_qr_path)
                    <img src="{{ asset('storage/'.$bank->upi_qr_path) }}" alt="{{ $pandit?->pandit_name }} UPI QR" style="width:160px;max-width:100%;border-radius:10px;margin-bottom:12px">
                @endif

                <div style="margin-bottom:14px">
                    @foreach($group as $readyPayout)
                        <label style="display:block;margin-bottom:7px">
                            <input type="checkbox" name="payout_ids[]" value="{{ $readyPayout->id }}" checked style="width:auto">
                            Payout #{{ $readyPayout->id }} — Rs {{ number_format((float) $readyPayout->pandit_amount, 2) }}
                        </label>
                    @endforeach
                </div>

                <div class="form-grid">
                    <div>
                        <label>Payment Method</label>
                        <select name="payment_method" required>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="neft">NEFT</option>
                            <option value="rtgs">RTGS</option>
                            <option value="imps">IMPS</option>
                            <option value="upi">UPI</option>
                        </select>
                    </div>
                    <div>
                        <label>UTR / Transaction ID</label>
                        <input type="text" name="utr" maxlength="100" required>
                    </div>
                </div>
                <button class="btn primary" type="submit" style="margin-top:14px" onclick="return confirm('Confirm that this payment was completed?')">Mark Selected as Paid</button>
            </form>
        @empty
            <div class="panel">No READY payouts waiting for manual settlement.</div>
        @endforelse
    @endif

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
        <h2>Payout History</h2>
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
                    <th>Transfer ID / UTR</th>
                    <th>Payment Method</th>
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
                        $manualSettlement = data_get($payout->metadata, 'manual_settlement');
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
                        <td>{{ data_get($manualSettlement, 'payment_method') ? strtoupper(str_replace('_', ' ', data_get($manualSettlement, 'payment_method'))) : 'Not Available' }}</td>
                        <td>{{ $payout->last_error ?: 'Not Available' }}</td>
                        <td>{{ $payout->eligible_at?->format('d M Y, h:i A') ?? 'Not Available' }}</td>
                        <td>{{ $payout->paid_at?->format('d M Y, h:i A') ?? 'Not Available' }}</td>
                        <td>{{ $payout->cancelled_at?->format('d M Y, h:i A') ?? 'Not Available' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="16">No payouts found.</td></tr>
                @endforelse
            </tbody>
        </table>

        {{ $payouts->links() }}
    </div>
@endsection
