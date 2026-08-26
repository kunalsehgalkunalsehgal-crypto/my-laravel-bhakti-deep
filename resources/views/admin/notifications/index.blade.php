@extends('admin.layout')

@section('title', 'Notifications')

@section('content')
    <h1>Notifications</h1>
    <div class="panel">
        <form class="filters" method="GET">
            <select name="channel"><option value="">All channels</option>@foreach(['email','whatsapp','sms'] as $channel)<option value="{{ $channel }}" @selected(request('channel') === $channel)>{{ ucfirst($channel) }}</option>@endforeach</select>
            <select name="delivery_status"><option value="">All statuses</option>@foreach(['pending','sent','failed'] as $status)<option value="{{ $status }}" @selected(request('delivery_status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select>
            <button class="btn">Filter</button>
        </form>
    </div>
    <div class="panel">
        <table>
            <thead><tr><th>ID</th><th>Channel</th><th>Type</th><th>Recipient</th><th>Status</th><th>Failure</th><th>Sent</th></tr></thead>
            <tbody>
                @forelse($records as $record)
                    <tr><td>#{{ $record->id }}</td><td>{{ $record->channel }}</td><td>{{ $record->message_type }}</td><td>{{ $record->recipient }}</td><td><span class="badge {{ $record->delivery_status }}">{{ $record->delivery_status }}</span></td><td>{{ $record->failure_reason }}</td><td>{{ $record->sent_at }}</td></tr>
                @empty
                    <tr><td colspan="7">No notifications found.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $records->links() }}
    </div>
@endsection
