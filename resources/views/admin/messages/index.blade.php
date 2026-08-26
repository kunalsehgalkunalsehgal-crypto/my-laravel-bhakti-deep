@extends('admin.layout')
@section('title', 'Pandit Messages')
@section('content')

<h1>Pandit Messages</h1>

<div class="panel" style="padding:0">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Pandit Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Unread</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pandits as $p)
            <tr>
                <td>{{ $p->id }}</td>
                <td>{{ $p->full_name }}</td>
                <td>{{ $p->email }}</td>
                <td><span class="badge">{{ ucfirst(str_replace('_',' ',$p->status)) }}</span></td>
                <td>
                    @if($p->unread_count > 0)
                        <span class="badge active">{{ $p->unread_count }} New</span>
                    @else
                        <span style="color:var(--muted);font-size:13px">—</span>
                    @endif
                </td>
                <td>
                    <a class="btn small primary" href="{{ route('admin.pandit-messages.show', $p->id) }}">Open Chat</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;color:var(--muted)">No messages from any pandit yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
