@extends('admin.layout')

@section('title', $title.' Detail')

@section('content')
    <div class="toolbar">
        <h1>{{ $title }} Detail</h1>
        <a class="btn" href="{{ route($routePrefix.'.index') }}">Back</a>
    </div>
    <div class="panel">
        <table>
            <tbody>
                @foreach($fields as $name => $field)
                    <tr>
                        <th>{{ $field['label'] ?? str($name)->headline() }}</th>
                        <td>
                            @if(($field['type'] ?? '') === 'file' && $record->{$name})
                                <a href="{{ asset('storage/'.$record->{$name}) }}" target="_blank">View file</a>
                            @elseif(is_array($record->{$name}))
                                <pre>{{ json_encode($record->{$name}, JSON_PRETTY_PRINT) }}</pre>
                            @else
                                {{ $record->{$name} }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
