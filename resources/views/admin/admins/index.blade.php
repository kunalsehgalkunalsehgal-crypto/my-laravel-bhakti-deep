@extends('admin.layout')

@section('title', 'Admin Users')

@section('content')
    <div class="toolbar"><h1>Admin Users</h1><a class="btn primary" href="{{ route('admin.admins.create') }}">Add Admin</a></div>
    <div class="panel">
        <table>
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Last Login</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($records as $admin)
                    <tr>
                        <td>{{ $admin->name }}</td><td>{{ $admin->email }}</td><td>{{ $admin->role?->name }}</td>
                        <td><span class="badge {{ $admin->status }}">{{ $admin->status }}</span></td><td>{{ $admin->last_login_at }}</td>
                        <td class="actions"><a class="btn small" href="{{ route('admin.admins.edit', $admin) }}">Edit</a><form method="POST" action="{{ route('admin.admins.destroy', $admin) }}" onsubmit="return confirm('Delete this admin?')">@csrf @method('DELETE')<button class="btn small danger">Delete</button></form></td>
                    </tr>
                @empty
                    <tr><td colspan="6">No admins found.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $records->links() }}
    </div>
@endsection
