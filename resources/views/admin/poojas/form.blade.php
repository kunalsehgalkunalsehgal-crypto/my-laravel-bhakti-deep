@extends('admin.layout')

@section('title', ($mode === 'create' ? 'Add ' : 'Edit ').'Pooja')

@php
    $lineValue = function ($value) {
        return is_array($value) ? implode("\n", $value) : $value;
    };
    $timelineValue = function ($value) {
        if (!is_array($value)) {
            return $value;
        }

        return collect($value)->map(function ($item) {
            return trim(($item['title'] ?? '').' | '.($item['duration'] ?? ''));
        })->implode("\n");
    };
@endphp

@section('content')
    <h1>{{ $mode === 'create' ? 'Add New Pooja' : 'Edit Pooja' }}</h1>

    <form class="panel" method="POST" enctype="multipart/form-data"
        action="{{ $mode === 'create' ? route('admin.poojas.store') : route('admin.poojas.update', $record) }}">
        @csrf
        @if($mode !== 'create') @method('PUT') @endif

        <div class="form-grid">
            <div>
                <label>Name *</label>
                <input type="text" name="name" id="nameInput" value="{{ old('name', $record->name) }}" required>
                @error('name') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Slug</label>
                <input type="text" name="slug" id="slugInput" value="{{ old('slug', $record->slug) }}">
                @error('slug') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Base Price *</label>
                <input type="number" name="base_price" value="{{ old('base_price', $record->base_price ?: 0) }}" min="0" required>
                @error('base_price') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
            </div>
            <div class="full" style="border:1px solid #eadfd3;border-radius:10px;padding:18px;margin-top:8px;">
                <label style="display:flex;align-items:center;gap:8px;margin-bottom:14px;">
                    <input style="width:auto" type="checkbox" name="live_pooja_enabled" value="1" @checked(old('live_pooja_enabled', $record->exists ? $record->live_pooja_enabled : true))>
                    Enable Live Pooja
                </label>
                <div class="form-grid">
                    <div>
                        <label>Live Pooja Title</label>
                        <input type="text" name="live_pooja_title" value="{{ old('live_pooja_title', $record->live_pooja_title ?: 'Live Pooja') }}">
                        @error('live_pooja_title') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label>Live Pooja Price</label>
                        <input type="number" name="live_pooja_price" value="{{ old('live_pooja_price', $record->live_pooja_price ?: $record->base_price) }}" min="0">
                        @error('live_pooja_price') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
                    </div>
                    <div class="full">
                        <label>Live Pooja Description</label>
                        <textarea name="live_pooja_description">{{ old('live_pooja_description', $record->live_pooja_description) }}</textarea>
                        @error('live_pooja_description') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
            <div class="full" style="border:1px solid #eadfd3;border-radius:10px;padding:18px;margin-top:8px;">
                <label style="display:flex;align-items:center;gap:8px;margin-bottom:14px;">
                    <input style="width:auto" type="checkbox" name="digital_pooja_enabled" value="1" @checked(old('digital_pooja_enabled', $record->exists ? $record->digital_pooja_enabled : true))>
                    Enable Digital Pooja
                </label>
                <div class="form-grid">
                    <div>
                        <label>Digital Pooja Title</label>
                        <input type="text" name="digital_pooja_title" value="{{ old('digital_pooja_title', $record->digital_pooja_title ?: 'Digital Pooja') }}">
                        @error('digital_pooja_title') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label>Digital Pooja Price</label>
                        <input type="number" name="digital_pooja_price" value="{{ old('digital_pooja_price', $record->digital_pooja_price ?: $record->base_price) }}" min="0">
                        @error('digital_pooja_price') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label>Digital Access Duration (minutes)</label>
                        <input type="number" name="digital_pooja_access_minutes" value="{{ old('digital_pooja_access_minutes', $record->digital_pooja_access_minutes ?: 120) }}" min="1" required>
                        @error('digital_pooja_access_minutes') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
                    </div>
                    <div class="full">
                        <label>Digital Pooja Description</label>
                        <textarea name="digital_pooja_description">{{ old('digital_pooja_description', $record->digital_pooja_description) }}</textarea>
                        @error('digital_pooja_description') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
                    </div>
                    <div class="full">
                        <label>Digital Pooja Video</label>
                        <input type="file" name="digital_pooja_video" accept="video/mp4,video/webm,video/quicktime">
                        @if($record->digital_pooja_video)
                            <p style="margin-top:8px;"><a href="{{ asset('storage/'.$record->digital_pooja_video) }}" target="_blank" style="color:#8a3ffc;">View current video</a></p>
                        @endif
                        @error('digital_pooja_video') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
                    </div>
                    <div class="full">
                        <label>Digital Mantra Audio</label>
                        <select name="digital_pooja_audio_id">
                            <option value="">Select mantra audio</option>
                            @foreach($digitalAudioOptions as $audioId => $audioTitle)
                                <option value="{{ $audioId }}" @selected((string) old('digital_pooja_audio_id', $record->digital_pooja_audio_id) === (string) $audioId)>{{ $audioTitle }}</option>
                            @endforeach
                        </select>
                        <p style="color:#667085;font-size:12px;margin:6px 0 0">Uses an active mantra from the existing Audio Library.</p>
                        @error('digital_pooja_audio_id') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
            <div>
                <label>Duration</label>
                <input type="text" name="duration" value="{{ old('duration', $record->duration) }}" placeholder="45 min">
                @error('duration') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Mode *</label>
                <input type="text" name="mode" value="{{ old('mode', $record->mode ?: 'Live + Replay') }}" required>
                @error('mode') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Status *</label>
                <select name="status" required>
                    <option value="active" @selected(old('status', $record->status ?: 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $record->status) === 'inactive')>Inactive</option>
                </select>
                @error('status') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
            </div>
            <div class="full">
                <label><input style="width:auto" type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $record->is_featured))> Featured Pooja</label>
            </div>
            <div class="full">
                <label>Short Description</label>
                <textarea name="short_description">{{ old('short_description', $record->short_description) }}</textarea>
                @error('short_description') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
            </div>
            <div class="full">
                <label>Full Description</label>
                <textarea name="full_description">{{ old('full_description', $record->full_description) }}</textarea>
                @error('full_description') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
            </div>
            <div class="full">
                <label>Featured Image</label>
                <input type="file" name="featured_image" accept="image/*">
                @if($record->featured_image)
                    <p style="margin-top:8px;"><a href="{{ $record->imageUrl() }}" target="_blank" style="color:#8a3ffc;">View current image</a></p>
                @endif
                @error('featured_image') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
            </div>
            <div class="full">
                <label>Benefits</label>
                <textarea name="benefits" placeholder="One benefit per line">{{ old('benefits', $lineValue($record->benefits)) }}</textarea>
                @error('benefits') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
            </div>
            <div class="full">
                <label>Included Items</label>
                <textarea name="included_items" placeholder="One item per line">{{ old('included_items', $lineValue($record->included_items)) }}</textarea>
                @error('included_items') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
            </div>
            <div class="full">
                <label>Session Timeline</label>
                <textarea name="session_timeline" placeholder="Sankalp | 5 min">{{ old('session_timeline', $timelineValue($record->session_timeline)) }}</textarea>
                @error('session_timeline') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
            </div>
            <div class="full">
                <label>Donation Options</label>
                <input type="text" name="donation_options" value="{{ old('donation_options', is_array($record->donation_options) ? implode(', ', $record->donation_options) : $record->donation_options) }}" placeholder="101, 251, 501, 1100">
                @error('donation_options') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
            </div>
            @include('admin.partials.weekly-booking-availability')
        </div>

        <div class="actions" style="margin-top:28px;">
            <button class="btn primary" type="submit">Save Pooja</button>
            <a class="btn" href="{{ route('admin.poojas.index') }}">Cancel</a>
        </div>
    </form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('nameInput').addEventListener('input', function() {
        document.getElementById('slugInput').value = this.value.toLowerCase().trim()
            .replace(/[^\w\s-]/g,'').replace(/\s+/g,'-').replace(/-+/g,'-');
    });
});
</script>
@endpush
