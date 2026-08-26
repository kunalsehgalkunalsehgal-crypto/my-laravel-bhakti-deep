@extends('admin.layout')

@section('title', 'Diya Detail')

@section('content')
    <div class="toolbar">
        <h1>Diya Detail</h1>
        <div class="actions">
            <a class="btn" href="{{ route('admin.diyas.index') }}">Back</a>
            <a class="btn primary" href="{{ route('admin.diyas.edit', $diya) }}">Edit</a>
        </div>
    </div>

    <div class="panel">
        <table>
            <tbody>
                <tr><th>Name</th><td>{{ $diya->name }}</td></tr>
                <tr><th>Slug</th><td>{{ $diya->slug }}</td></tr>
                <tr><th>Image</th><td>@if($diya->imageUrl()) <a href="{{ $diya->imageUrl() }}" target="_blank">View image</a> @else - @endif</td></tr>
                <tr><th>Short Description</th><td>{{ $diya->short_description ?: '-' }}</td></tr>
                <tr><th>Full Description</th><td>{{ $diya->full_description ?: '-' }}</td></tr>
                <tr><th>Seva Amount</th><td>Rs.{{ number_format((float) $diya->seva_amount, 2) }}</td></tr>
                <tr><th>Duration</th><td>{{ $diya->duration ?: '-' }}</td></tr>
                <tr><th>Type / Category</th><td>{{ $diya->category ?: '-' }}</td></tr>
                <tr><th>Deity Selection Mode</th><td>{{ $diya->isFixedDeity() ? 'Fixed Deity' : 'User Can Select Deity' }}</td></tr>
                <tr><th>Fixed Deity</th><td>{{ $diya->fixedDeity?->name ?: '-' }}</td></tr>
                <tr><th>Mantra Audio Deity</th><td>{{ $diya->mantraAudio?->deity?->name ?: '-' }}</td></tr>
                <tr><th>Mantra Audio</th><td>{{ $diya->mantraAudio?->title ?: '-' }}</td></tr>
                <tr><th>Mantra / Ambience</th><td>{{ $diya->mantra_ambience ?: '-' }}</td></tr>
                <tr><th>Status</th><td><span class="badge {{ $diya->status }}">{{ ucfirst($diya->status) }}</span></td></tr>
            </tbody>
        </table>
    </div>
@endsection
