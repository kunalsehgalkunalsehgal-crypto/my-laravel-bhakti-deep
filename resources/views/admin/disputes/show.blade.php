@extends('admin.layout')

@section('title', 'Dispute #'.$dispute->id)

@section('content')
    @php
        $meta = $booking->admin_note ? (json_decode($booking->admin_note, true) ?: []) : [];
        $service = $meta[$type.'_name'] ?? $booking->service?->name ?? ucfirst($type).' Booking';
        $package = $booking->hawan_type_title ?? $meta['hawan_type_title'] ?? $meta['package_name'] ?? '-';
        $total = isset($meta['total_amount']) ? 'Rs '.number_format((float) $meta['total_amount']) : 'Not Available';
        $attempt = $booking->latestPaymentAttempt;
        $labels = ['open' => 'Open', 'under_review' => 'Under Review', 'resolved' => 'Resolved', 'rejected' => 'Rejected'];
        $resolutions = ['refund_user' => 'Refund User', 'pandit_favour' => 'Pandit Favour'];
    @endphp

    <div class="toolbar">
        <h1>Dispute #{{ $dispute->id }}</h1>
        <a class="btn" href="{{ route('admin.disputes.index') }}">Back</a>
    </div>

    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(320px,1fr));">
        <div class="panel">
            <h2>Booking + Payment</h2>
            <table><tbody>
                <tr><th>Booking ID</th><td>{{ strtoupper($type) }}-{{ $booking->id }}</td></tr>
                <tr><th>Service</th><td>{{ $service }}</td></tr>
                <tr><th>Package</th><td>{{ $package }}</td></tr>
                <tr><th>User</th><td>{{ $booking->user?->name ?? '-' }}</td></tr>
                <tr><th>Pandit</th><td>{{ $booking->pandit?->pandit_name ?: ($booking->pandit?->full_name ?: '-') }}</td></tr>
                <tr><th>Date</th><td>{{ $booking->booking_date?->format('d M Y') ?? '-' }}</td></tr>
                <tr><th>Slot</th><td>{{ $booking->slot ?: '-' }}</td></tr>
                <tr><th>Booking Status</th><td>{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</td></tr>
                <tr><th>Payment Status</th><td>{{ ucfirst(str_replace('_', ' ', $booking->payment_status)) }}</td></tr>
                <tr><th>Total Paid</th><td>{{ $total }}</td></tr>
                <tr><th>Gateway</th><td>{{ $attempt?->gateway ?? 'Not Available' }}</td></tr>
                <tr><th>Order ID</th><td>{{ $attempt?->gateway_order_id ?? 'Not Available' }}</td></tr>
                <tr><th>Payment ID</th><td>{{ $attempt?->gateway_payment_id ?? 'Not Available' }}</td></tr>
                <tr><th>Paid At</th><td>{{ $attempt?->paid_at?->format('d M Y, h:i A') ?? 'Not Available' }}</td></tr>
            </tbody></table>
        </div>

        <div class="panel">
            <h2>Report Status</h2>
            <table><tbody>
                <tr><th>Status</th><td><span class="badge {{ $dispute->status }}">{{ $labels[$dispute->status] ?? $dispute->status }}</span></td></tr>
                <tr><th>Resolution</th><td>{{ $dispute->resolution ? ($resolutions[$dispute->resolution] ?? ucfirst(str_replace('_', ' ', $dispute->resolution))) : 'Not Available' }}</td></tr>
                <tr><th>Reason</th><td>{{ ucfirst(str_replace('_', ' ', $dispute->reason)) }}</td></tr>
                <tr><th>Submitted</th><td>{{ $dispute->opened_at?->format('d M Y, h:i A') ?? $dispute->created_at?->format('d M Y, h:i A') }}</td></tr>
                <tr><th>Pandit Responded</th><td>{{ $dispute->pandit_responded_at?->format('d M Y, h:i A') ?? 'Not Available' }}</td></tr>
                <tr><th>Resolved By</th><td>{{ $dispute->resolvedByAdmin?->name ?? 'Not Available' }}</td></tr>
                <tr><th>Resolved At</th><td>{{ $dispute->resolved_at?->format('d M Y, h:i A') ?? 'Not Available' }}</td></tr>
                <tr><th>Admin Review Note</th><td>{{ $dispute->admin_review_note ?: 'Not Available' }}</td></tr>
                <tr><th>Razorpay Refund ID</th><td>{{ $dispute->paymentRefund?->gateway_refund_id ?? 'Not Available' }}</td></tr>
                <tr><th>Refund Status</th><td>{{ $dispute->paymentRefund?->gateway_status ? ucfirst($dispute->paymentRefund->gateway_status) : ($dispute->paymentRefund?->status ? ucfirst(str_replace('_', ' ', $dispute->paymentRefund->status)) : 'Not Available') }}</td></tr>
            </tbody></table>
        </div>
    </div>

    @if($dispute->status !== \App\Models\Dispute::STATUS_RESOLVED)
        <div class="panel">
            <h2>Admin Decision</h2>
            <div style="display:flex;flex-wrap:wrap;gap:12px;">
                <form method="POST" action="{{ route('admin.disputes.refund-user', $dispute) }}" onsubmit="return confirm('Refund this paid booking through Razorpay and resolve the dispute in user favour?')">
                    @csrf
                    <button class="btn primary">Refund User</button>
                </form>
                <form method="POST" action="{{ route('admin.disputes.resolve-pandit-favour', $dispute) }}" onsubmit="return confirm('Resolve this dispute in pandit favour without payout?')">
                    @csrf
                    <button class="btn">Resolve in Pandit Favour</button>
                </form>
            </div>
        </div>
    @endif

    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(320px,1fr));">
        <div class="panel">
            <h2>User Complaint</h2>
            <p>{{ $dispute->description }}</p>
            <h3>Proof</h3>
            @forelse($userProofs as $proof)
                <p><a class="btn small" href="{{ route('admin.disputes.evidence', [$dispute, $proof]) }}" target="_blank">{{ $proof->original_name }}</a></p>
            @empty
                <p>Not Available</p>
            @endforelse
        </div>

        <div class="panel">
            <h2>Pandit Response</h2>
            <p>{{ $dispute->pandit_response ?: 'Not Available' }}</p>
            <h3>Proof</h3>
            @forelse($panditProofs as $proof)
                <p><a class="btn small" href="{{ route('admin.disputes.evidence', [$dispute, $proof]) }}" target="_blank">{{ $proof->original_name }}</a></p>
            @empty
                <p>Not Available</p>
            @endforelse
        </div>
    </div>

    <div class="panel">
        <h2>Zoom Attendance</h2>
        @if($booking->videoMeetingAttendances->isEmpty())
            <p>Not Available</p>
        @else
            <table>
                <thead><tr><th>Participant</th><th>ID</th><th>Zoom ID</th><th>Event</th><th>Joined At</th><th>Left At</th><th>Duration</th></tr></thead>
                <tbody>
                    @foreach($booking->videoMeetingAttendances->sortByDesc('created_at') as $row)
                        <tr>
                            <td>{{ ucfirst($row->participant_type) }}</td>
                            <td>{{ $row->participant_id ?? 'Not Available' }}</td>
                            <td>{{ $row->zoom_participant_id ?? 'Not Available' }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $row->event_type)) }}</td>
                            <td>{{ $row->joined_at?->format('d M Y, h:i A') ?? 'Not Available' }}</td>
                            <td>{{ $row->left_at?->format('d M Y, h:i A') ?? 'Not Available' }}</td>
                            <td>{{ $row->duration !== null ? $row->duration.' sec' : 'Not Available' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <form class="panel" method="POST" action="{{ route('admin.disputes.update', $dispute) }}">
        @csrf
        @method('PATCH')
        <h2>Admin Review</h2>
        <div class="form-grid">
            @if($dispute->status === \App\Models\Dispute::STATUS_OPEN)
                <label>
                    Status
                    <select name="status">
                        <option value="">Keep Open</option>
                        <option value="under_review">Move to Under Review</option>
                    </select>
                </label>
            @endif
            <label class="full">
                Admin Review Note
                <textarea name="admin_review_note">{{ old('admin_review_note', $dispute->admin_review_note) }}</textarea>
            </label>
        </div>
        <button class="btn primary" style="margin-top:14px">Save Review</button>
    </form>
@endsection
