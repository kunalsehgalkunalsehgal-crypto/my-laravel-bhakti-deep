@extends('admin.layout')

@section('title', 'Reports')

@section('content')
    <h1>Reports</h1>
    <div class="grid stats">
        @foreach($reports as $label => $value)
            <div class="panel stat"><span>{{ $label }}</span><strong>{{ $value }}</strong></div>
        @endforeach
    </div>
@endsection
