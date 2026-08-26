@extends('admin.layout')

@section('title', $title)

@section('content')
    <div class="toolbar">
        <h1>{{ $title }}</h1>
        <a class="btn primary" href="{{ route($routePrefix.'.create') }}">Add New</a>
    </div>
    <div class="panel">
        <form class="filters" method="GET">
            <input style="max-width:260px" type="search" name="search" value="{{ request('search') }}" placeholder="Search">
            @if(isset($fields['status']))
                <select style="max-width:180px" name="status">
                    <option value="">All statuses</option>
                    @foreach($fields['status']['options'] ?? [] as $key => $label)
                        <option value="{{ $key }}" @selected(request('status') === (string) $key)>{{ $label }}</option>
                    @endforeach
                </select>
            @endif
            <button class="btn" type="submit">Filter</button>
        </form>
    </div>
    <div class="panel">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    @foreach(array_slice($fields, 0, 5, true) as $name => $field)
                        <th>{{ $field['label'] ?? str($name)->headline() }}</th>
                    @endforeach
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                    <tr>
                        <td>#{{ $record->id }}</td>
                        @foreach(array_slice($fields, 0, 5, true) as $name => $field)
                            <td>
                                @if($name === 'status')
                                    <span class="badge {{ $record->{$name} }}">{{ $record->{$name} }}</span>
                                @elseif(($field['type'] ?? '') === 'checkbox')
                                    {{ $record->{$name} ? 'Yes' : 'No' }}
                                @elseif(($field['type'] ?? '') === 'file' && $record->{$name})
                                    <a href="{{ asset('storage/'.$record->{$name}) }}" target="_blank">View file</a>
                                @else
                                    {{ str((string) $record->{$name})->limit(70) }}
                                @endif
                            </td>
                        @endforeach
                        <td>
                            <div class="actions">
                                <a class="btn small" href="{{ route($routePrefix.'.show', $record) }}">View</a>
                                <a class="btn small" href="{{ route($routePrefix.'.edit', $record) }}">Edit</a>
                                <form method="POST" action="{{ route($routePrefix.'.destroy', $record) }}" onsubmit="return confirm('Delete this record?')">
                                    @csrf @method('DELETE')
                                    <button class="btn small danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7">No records found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="pagination">{{ $records->links() }}</div>
    </div>
@endsection
