@extends('admin.layout')

@section('title', ($mode === 'create' ? 'Add ' : 'Edit ').$title)

@section('content')
    <h1>{{ $mode === 'create' ? 'Add ' : 'Edit ' }}{{ $title }}</h1>
    <form class="panel" method="POST" enctype="multipart/form-data" action="{{ $mode === 'create' ? route($routePrefix.'.store') : route($routePrefix.'.update', $record) }}">
        @csrf
        @if($mode !== 'create') @method('PUT') @endif
        <div class="form-grid">
            @foreach($fields as $name => $field)
                @php
                    $type = $field['type'] ?? 'text';
                    $value = old($name, $record->{$name});
                    if ($value instanceof \Illuminate\Support\Carbon) {
                        $value = $type === 'datetime-local' ? $value->format('Y-m-d\TH:i') : $value->toDateTimeString();
                    }
                    if (is_array($value)) {
                        $value = json_encode($value, JSON_PRETTY_PRINT);
                    }
                    $callback = $field['options_callback'] ?? null;
                    $options = $field['options'] ?? ($callback ? ($$callback ?? []) : []);
                @endphp
                <div class="{{ in_array($type, ['textarea', 'richtext', 'file'], true) ? 'full' : '' }}">
                    <label for="{{ $name }}">{{ $field['label'] ?? str($name)->headline() }}</label>
                    @if($type === 'textarea' || $type === 'richtext')
                        <textarea id="{{ $name }}" name="{{ $name }}">{{ $value }}</textarea>
                    @elseif($type === 'select')
                        <select id="{{ $name }}" name="{{ $name }}">
                            <option value="">Select</option>
                            @foreach($options as $key => $label)
                                <option value="{{ $key }}" @selected((string) $value === (string) $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    @elseif($type === 'checkbox')
                        <input type="hidden" name="{{ $name }}" value="0">
                        <label><input style="width:auto" type="checkbox" name="{{ $name }}" value="1" @checked((bool) $value)> {{ $field['label'] ?? str($name)->headline() }}</label>
                    @elseif($type === 'file')
                        <input id="{{ $name }}" type="file" name="{{ $name }}">
                        @if($record->{$name})
                            <p><a href="{{ asset('storage/'.$record->{$name}) }}" target="_blank">Current file</a></p>
                        @endif
                    @else
                        <input id="{{ $name }}" type="{{ $type }}" name="{{ $name }}" value="{{ $value }}">
                    @endif
                </div>
            @endforeach
        </div>
        <div class="actions" style="margin-top:18px">
            <button class="btn primary" type="submit">Save</button>
            <a class="btn" href="{{ route($routePrefix.'.index') }}">Cancel</a>
        </div>
    </form>
@endsection
