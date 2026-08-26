@extends('admin.layout')

@section('title', $record->exists ? 'Edit Role' : 'Add Role')

@section('content')
    <h1>{{ $record->exists ? 'Edit Role' : 'Add Role' }}</h1>
    <form class="panel" method="POST" action="{{ $record->exists ? route('admin.roles.update', $record) : route('admin.roles.store') }}">
        @csrf
        @if($record->exists) @method('PUT') @endif
        <div class="form-grid">
            <div><label>Name</label><input name="name" value="{{ old('name', $record->name) }}" required></div>
            <div><label>Slug</label><input name="slug" value="{{ old('slug', $record->slug) }}"></div>
            <div><label>Status</label><select name="status"><option value="active" @selected(old('status', $record->status) === 'active')>Active</option><option value="inactive" @selected(old('status', $record->status) === 'inactive')>Inactive</option></select></div>
            <div class="full"><label>Description</label><textarea name="description">{{ old('description', $record->description) }}</textarea></div>
        </div>
        <h2>Permissions</h2>
        <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(240px,1fr));">
            @foreach($permissions as $module => $items)
                <div class="panel">
                    <strong>{{ $module }}</strong>
                    @foreach($items as $permission)
                        <label style="font-weight:400;margin-top:10px">
                            <input style="width:auto" type="checkbox" name="permissions[]" value="{{ $permission->id }}" @checked(in_array($permission->id, old('permissions', $selected), true))>
                            {{ $permission->name }}
                        </label>
                    @endforeach
                </div>
            @endforeach
        </div>
        <div class="actions"><button class="btn primary">Save</button><a class="btn" href="{{ route('admin.roles.index') }}">Cancel</a></div>
    </form>
@endsection
