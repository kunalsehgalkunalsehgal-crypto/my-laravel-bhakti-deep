@extends('admin.layout')

@section('title', $record->exists ? 'Edit Admin' : 'Add Admin')

@section('content')
    <h1>{{ $record->exists ? 'Edit Admin' : 'Add Admin' }}</h1>
    <form class="panel" method="POST" action="{{ $record->exists ? route('admin.admins.update', $record) : route('admin.admins.store') }}">
        @csrf
        @if($record->exists) @method('PUT') @endif
        <div class="form-grid">
            <div><label>Name</label><input name="name" value="{{ old('name', $record->name) }}" required></div>
            <div><label>Email</label><input type="email" name="email" value="{{ old('email', $record->email) }}" required></div>
            <div><label>Mobile</label><input name="mobile" value="{{ old('mobile', $record->mobile) }}"></div>
            <div><label>Role</label><select name="role_id" required><option value="">Select</option>@foreach($roles as $id => $name)<option value="{{ $id }}" @selected((string) old('role_id', $record->role_id) === (string) $id)>{{ $name }}</option>@endforeach</select></div>
            <div><label>Status</label><select name="status"><option value="active" @selected(old('status', $record->status ?: 'active') === 'active')>Active</option><option value="inactive" @selected(old('status', $record->status) === 'inactive')>Inactive</option></select></div>
            <div><label>Password</label><input type="password" name="password" @required(!$record->exists)></div>
            <div><label>Confirm Password</label><input type="password" name="password_confirmation" @required(!$record->exists)></div>
        </div>
        <div class="actions" style="margin-top:18px"><button class="btn primary">Save</button><a class="btn" href="{{ route('admin.admins.index') }}">Cancel</a></div>
    </form>
@endsection
