@extends('admin.layout')

@section('title', 'Admin Roles')

@section('content')
    <div class="toolbar"><h1>Admin Roles</h1><a class="btn primary" href="{{ route('admin.roles.create') }}">Add Role</a></div>
    <div class="panel">
        <table>
            <thead><tr><th>Name</th><th>Status</th><th>Permissions</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($records as $role)
                    <tr>
                        <td>{{ $role->name }}<br><small>{{ $role->slug }}</small></td>
                        <td><span class="badge {{ $role->status }}">{{ $role->status }}</span></td>
                        <td>{{ $role->permissions_count }}</td>
                        <td class="actions">
                            <a class="btn small" href="{{ route('admin.roles.edit', $role) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" onsubmit="return confirm('Delete this role?')">@csrf @method('DELETE')<button class="btn small danger">Delete</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4">No roles found.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $records->links() }}
    </div>
@endsection
