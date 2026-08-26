@extends('admin.layout')

@section('title', ($mode === 'create' ? 'Add ' : 'Edit ').'Diya')

@section('content')
    @php
        $selectedMode = old('deity_selection_mode', $record->deity_selection_mode ?: 'user_select');
        $selectedMantraDeityId = old('mantra_deity_id', $record->mantraAudio?->deity_id ?: $record->fixed_deity_id);
        $selectedMantraAudioId = old('mantra_audio_id', $record->mantra_audio_id);
    @endphp

    <h1>{{ $mode === 'create' ? 'Add New Diya' : 'Edit Diya' }}</h1>

    <form class="panel" method="POST" enctype="multipart/form-data"
        action="{{ $mode === 'create' ? route('admin.diyas.store') : route('admin.diyas.update', $record) }}">
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
                <label>Seva Amount *</label>
                <input type="number" step="0.01" min="0" name="seva_amount" value="{{ old('seva_amount', $record->seva_amount ?: 0) }}" required>
                @error('seva_amount') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Duration</label>
                <input type="text" name="duration" value="{{ old('duration', $record->duration) }}" placeholder="24 hours">
                @error('duration') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Type / Category</label>
                <input type="text" name="category" value="{{ old('category', $record->category) }}" placeholder="Akhand Diya">
                @error('category') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Status *</label>
                <select name="status" required>
                    <option value="active" @selected(old('status', $record->status ?: 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $record->status) === 'inactive')>Inactive</option>
                </select>
                @error('status') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Deity Selection Mode *</label>
                <select name="deity_selection_mode" id="deityMode" required>
                    <option value="user_select" @selected($selectedMode === 'user_select')>User Can Select Deity</option>
                    <option value="fixed" @selected($selectedMode === 'fixed')>Fixed Deity</option>
                </select>
                @error('deity_selection_mode') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
            </div>
            <div id="fixedDeityField">
                <label>Fixed Deity</label>
                <select name="fixed_deity_id" id="fixedDeitySelect">
                    <option value="">Select active deity</option>
                    @foreach($activeDeities as $deity)
                        <option value="{{ $deity->id }}" @selected((string) old('fixed_deity_id', $record->fixed_deity_id) === (string) $deity->id)>{{ $deity->name }}</option>
                    @endforeach
                </select>
                @error('fixed_deity_id') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
                <p style="color:#667085;font-size:12px;margin:6px 0 0">Required only for fixed deity diyas.</p>
            </div>
            <div>
                <label>Mantra Audio Deity *</label>
                <select name="mantra_deity_id" id="mantraDeitySelect" required>
                    <option value="">Select deity first</option>
                    @foreach($activeDeities as $deity)
                        <option value="{{ $deity->id }}" @selected((string) $selectedMantraDeityId === (string) $deity->id)>{{ $deity->name }}</option>
                    @endforeach
                </select>
                @error('mantra_deity_id') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
                <p style="color:#667085;font-size:12px;margin:6px 0 0">This deity filters the mantra audio list.</p>
            </div>
            <div>
                <label>Mantra Audio *</label>
                <select name="mantra_audio_id" id="mantraAudioSelect" required data-selected="{{ $selectedMantraAudioId }}">
                    <option value="">Select deity to load mantra audio</option>
                </select>
                @error('mantra_audio_id') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
                <p style="color:#667085;font-size:12px;margin:6px 0 0">Only active mantra audios from the selected deity are shown.</p>
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
                <label>Mantra / Ambience</label>
                <textarea name="mantra_ambience">{{ old('mantra_ambience', $record->mantra_ambience) }}</textarea>
                @error('mantra_ambience') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
            </div>
            <div class="full">
                <label>Image</label>
                <input type="file" name="image" accept="image/*">
                @if($record->imageUrl())
                    <p style="margin-top:8px;"><a href="{{ $record->imageUrl() }}" target="_blank" style="color:#8a3ffc;">View current image</a></p>
                @endif
                @error('image') <span style="color:#b42318;font-size:12px;">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="actions" style="margin-top:28px;">
            <button class="btn primary" type="submit">Save Diya</button>
            <a class="btn" href="{{ route('admin.diyas.index') }}">Cancel</a>
        </div>
    </form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('nameInput');
    const slugInput = document.getElementById('slugInput');
    const deityMode = document.getElementById('deityMode');
    const fixedDeityField = document.getElementById('fixedDeityField');
    const fixedDeitySelect = document.getElementById('fixedDeitySelect');
    const mantraDeitySelect = document.getElementById('mantraDeitySelect');
    const mantraAudioSelect = document.getElementById('mantraAudioSelect');
    const mantraAudioUrl = @json(route('admin.diyas.mantra-audios'));

    nameInput.addEventListener('input', function() {
        if (slugInput.dataset.edited === '1') {
            return;
        }

        slugInput.value = this.value.toLowerCase().trim()
            .replace(/[^\w\s-]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-');
    });

    slugInput.addEventListener('input', function() {
        this.dataset.edited = '1';
    });

    function syncFixedDeityField() {
        const isFixed = deityMode.value === 'fixed';
        fixedDeityField.style.display = isFixed ? 'block' : 'none';

        if (isFixed && fixedDeitySelect.value) {
            mantraDeitySelect.value = fixedDeitySelect.value;
            loadMantraAudios();
        }
    }

    async function loadMantraAudios() {
        const deityId = mantraDeitySelect.value;
        const selectedAudioId = mantraAudioSelect.dataset.selected || '';

        mantraAudioSelect.innerHTML = '<option value="">Select mantra audio</option>';

        if (!deityId) {
            mantraAudioSelect.innerHTML = '<option value="">Select deity to load mantra audio</option>';
            return;
        }

        mantraAudioSelect.disabled = true;
        mantraAudioSelect.innerHTML = '<option value="">Loading mantra audio...</option>';

        try {
            const url = new URL(mantraAudioUrl, window.location.origin);
            url.searchParams.set('deity_id', deityId);

            const response = await fetch(url.toString(), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const audios = await response.json();
            mantraAudioSelect.innerHTML = '<option value="">Select mantra audio</option>';

            if (!audios.length) {
                mantraAudioSelect.innerHTML = '<option value="">No active mantra audio found</option>';
                return;
            }

            audios.forEach(function (audio) {
                const option = document.createElement('option');
                option.value = audio.id;
                option.textContent = audio.title;
                option.selected = String(audio.id) === String(selectedAudioId);
                mantraAudioSelect.appendChild(option);
            });
        } catch (error) {
            mantraAudioSelect.innerHTML = '<option value="">Could not load mantra audio</option>';
        } finally {
            mantraAudioSelect.disabled = false;
        }
    }

    deityMode.addEventListener('change', syncFixedDeityField);
    fixedDeitySelect.addEventListener('change', function () {
        if (deityMode.value === 'fixed') {
            mantraDeitySelect.value = this.value;
            mantraAudioSelect.dataset.selected = '';
            loadMantraAudios();
        }
    });
    mantraDeitySelect.addEventListener('change', function () {
        mantraAudioSelect.dataset.selected = '';
        loadMantraAudios();
    });

    syncFixedDeityField();
    loadMantraAudios();
});
</script>
@endpush
