@extends('admin.layout')

@section('title', 'Users')

@section('content')
    <h1>Users</h1>
    <div class="panel">
        <form class="filters" method="GET"><input style="max-width:260px" name="search" value="{{ request('search') }}" placeholder="Search users"><button class="btn">Search</button></form>
    </div>
    <div class="panel">
        <table>
            <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Joined</th></tr></thead>
            <tbody>
                @forelse($records as $user)
                    <tr><td>#{{ $user->id }}</td><td>{{ $user->name }}</td><td>{{ $user->email }}</td><td>{{ $user->created_at }}</td></tr>
                @empty
                    <tr><td colspan="4">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $records->links() }}
    </div>
@endsection
