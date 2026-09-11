@extends('admin.layout')

@section('title', ($mode === 'create' ? 'Add ' : 'Edit ').'Hawan')

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
    <h1>{{ $mode === 'create' ? 'Add New Hawan' : 'Edit Hawan' }}</h1>

    <form class="panel" method="POST" enctype="multipart/form-data"
        action="{{ $mode === 'create' ? route('admin.hawans.store') : route('admin.hawans.update', $record) }}">
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
                    <input style="width:auto" type="checkbox" name="samuhik_hawan_enabled" value="1" @checked(old('samuhik_hawan_enabled', $record->exists ? $record->samuhik_hawan_enabled : true))>
                    Enable Samuhik Hawan
                </label>
                <div class="form-grid">
                    <div>
                        <label>Samuhik Title</label>
                        <input type="text" name="samuhik_hawan_title" value="{{ old('samuhik_hawan_title', $record->samuhik_hawan_title ?: 'Samuhik Hawan') }}">
                        @error('samuhik_hawan_title') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label>Samuhik Price</label>
                        <input type="number" name="samuhik_hawan_price" value="{{ old('samuhik_hawan_price', $record->samuhik_hawan_price ?: $record->base_price) }}" min="0">
                        @error('samuhik_hawan_price') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
                    </div>
                    <div class="full">
                        <label>Samuhik Description</label>
                        <textarea name="samuhik_hawan_description">{{ old('samuhik_hawan_description', $record->samuhik_hawan_description) }}</textarea>
                        @error('samuhik_hawan_description') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
            <div class="full" style="border:1px solid #eadfd3;border-radius:10px;padding:18px;margin-top:8px;">
                <label style="display:flex;align-items:center;gap:8px;margin-bottom:14px;">
                    <input style="width:auto" type="checkbox" name="special_hawan_enabled" value="1" @checked(old('special_hawan_enabled', $record->exists ? $record->special_hawan_enabled : true))>
                    Enable Special Hawan
                </label>
                <div class="form-grid">
                    <div>
                        <label>Special Title</label>
                        <input type="text" name="special_hawan_title" value="{{ old('special_hawan_title', $record->special_hawan_title ?: 'Special Hawan') }}">
                        @error('special_hawan_title') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label>Special Price</label>
                        <input type="number" name="special_hawan_price" value="{{ old('special_hawan_price', $record->special_hawan_price) }}" min="0">
                        @error('special_hawan_price') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
                    </div>
                    <div class="full">
                        <label>Special Description</label>
                        <textarea name="special_hawan_description">{{ old('special_hawan_description', $record->special_hawan_description) }}</textarea>
                        @error('special_hawan_description') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
            <div>
                <label>Duration</label>
                <input type="text" name="duration" value="{{ old('duration', $record->duration) }}" placeholder="75 min">
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
                <label><input style="width:auto" type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $record->is_featured))> Featured Hawan</label>
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
                <textarea name="session_timeline" placeholder="Kund Sthapana | 8 min">{{ old('session_timeline', $timelineValue($record->session_timeline)) }}</textarea>
                @error('session_timeline') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
            </div>
            <div class="full">
                <label>Donation Options</label>
                <input type="text" name="donation_options" value="{{ old('donation_options', is_array($record->donation_options) ? implode(', ', $record->donation_options) : $record->donation_options) }}" placeholder="501, 1100, 2100, 5100">
                @error('donation_options') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
            </div>
            @include('admin.partials.weekly-booking-availability')
        </div>

        <div class="actions" style="margin-top:28px;">
            <button class="btn primary" type="submit">Save Hawan</button>
            <a class="btn" href="{{ route('admin.hawans.index') }}">Cancel</a>
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
