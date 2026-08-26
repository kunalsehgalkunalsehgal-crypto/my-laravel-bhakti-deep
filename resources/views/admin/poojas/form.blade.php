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
            <div class="full">
                <label>Available Slots</label>
                <textarea name="available_slots" placeholder="7:00 AM - 8:00 AM">{{ old('available_slots', $lineValue($record->available_slots)) }}</textarea>
                @error('available_slots') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
            </div>
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
