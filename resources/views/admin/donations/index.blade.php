@extends('admin.layout')

@section('title', 'Donations')

@section('content')
    <div class="toolbar"><h1>Donations</h1><a class="btn primary" href="{{ route('admin.donations.export', request()->query()) }}">Export CSV</a></div>
    <div class="panel stat"><span>Total Revenue In Filter</span><strong>Rs {{ number_format((float) $totalRevenue, 2) }}</strong></div>
    <div class="panel">
        <form class="filters" method="GET">
            <select name="payment_status"><option value="">All statuses</option>@foreach(['pending','paid','failed','refunded'] as $status)<option value="{{ $status }}" @selected(request('payment_status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select>
            <input type="date" name="from" value="{{ request('from') }}">
            <input type="date" name="to" value="{{ request('to') }}">
            <button class="btn">Filter</button>
        </form>
    </div>
    <div class="panel">
        <table>
            <thead><tr><th>ID</th><th>Donor</th><th>Service</th><th>Amount</th><th>Payment</th><th>Razorpay</th><th>Receipt</th><th>Date</th></tr></thead>
            <tbody>
                @forelse($records as $donation)
                    <tr>
                        <td>#{{ $donation->id }}</td>
                        <td>{{ $donation->donor_name ?: $donation->user?->name ?? '-' }}</td>
                        <td>{{ $donation->service?->name ?? '-' }}</td>
                        <td>{{ $donation->currency }} {{ number_format((float) $donation->amount, 2) }}</td>
                        <td><span class="badge {{ $donation->payment_status }}">{{ $donation->payment_status }}</span></td>
                        <td>{{ $donation->razorpay_order_id }}<br>{{ $donation->razorpay_payment_id }}</td>
                        <td>{{ $donation->receipt_number ?: '-' }}</td>
                        <td>{{ $donation->created_at }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8">No donations found.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $records->links() }}
    </div>
@endsection
