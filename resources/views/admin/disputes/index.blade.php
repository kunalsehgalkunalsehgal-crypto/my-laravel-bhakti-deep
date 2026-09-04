@extends('admin.layout')

@section('title', 'Disputes')

@section('content')
    @php
        $labels = [
            'open' => 'Open',
            'under_review' => 'Under Review',
            'resolved' => 'Resolved',
            'rejected' => 'Rejected',
        ];
    @endphp

    <div class="toolbar">
        <h1>Disputes</h1>
        <div class="filters">
            <a class="btn small {{ !$status ? 'primary' : '' }}" href="{{ route('admin.disputes.index') }}">All</a>
            @foreach($statuses as $item)
                <a class="btn small {{ $status === $item ? 'primary' : '' }}" href="{{ route('admin.disputes.index', ['status' => $item]) }}">{{ $labels[$item] }}</a>
            @endforeach
        </div>
    </div>

    <div class="panel">
        <table>
            <thead>
                <tr>
                    <th>Dispute ID</th>
                    <th>Booking ID</th>
                    <th>User</th>
                    <th>Pandit</th>
                    <th>Service</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($disputes as $dispute)
                    @php
                        $booking = $dispute->disputable;
                        $type = $booking instanceof \App\Models\Admin\HawanSession ? 'hawan' : 'pooja';
                        $meta = $booking?->admin_note ? (json_decode($booking->admin_note, true) ?: []) : [];
                        $service = $meta[$type.'_name'] ?? $booking?->service?->name ?? ucfirst($type).' Booking';
                    @endphp
                    <tr>
                        <td>#{{ $dispute->id }}</td>
                        <td>{{ strtoupper($type) }}-{{ $booking?->id }}</td>
                        <td>{{ $dispute->user?->name ?? '-' }}</td>
                        <td>{{ $dispute->pandit?->pandit_name ?: ($dispute->pandit?->full_name ?: '-') }}</td>
                        <td>{{ $service }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $dispute->reason)) }}</td>
                        <td><span class="badge {{ $dispute->status }}">{{ $labels[$dispute->status] ?? ucfirst($dispute->status) }}</span></td>
                        <td><a class="btn small" href="{{ route('admin.disputes.show', $dispute) }}">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8">No disputes found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="pagination">{{ $disputes->links() }}</div>
    </div>
@endsection
