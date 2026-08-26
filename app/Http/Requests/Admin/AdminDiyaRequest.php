<?php

namespace App\Http\Requests\Admin;

use App\Models\Admin\Audio;
use App\Models\Admin\Diya;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminDiyaRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->filled('slug') ? str($this->input('slug'))->slug()->toString() : str($this->input('name', ''))->slug()->toString(),
        ]);
    }

    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        $diya = $this->route('diya');
        $diyaId = is_object($diya) ? $diya->getKey() : $diya;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('diyas', 'slug')->ignore($diyaId),
            ],
            'image' => ['nullable', 'image', 'max:2048'],
            'short_description' => ['nullable', 'string'],
            'full_description' => ['nullable', 'string'],
            'seva_amount' => ['required', 'numeric', 'min:0'],
            'duration' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'deity_selection_mode' => ['required', Rule::in([Diya::MODE_FIXED, Diya::MODE_USER_SELECT])],
            'mantra_deity_id' => [
                'required',
                Rule::exists('deities', 'id')->where(fn ($query) => $query->where('status', 'active')->whereNull('deleted_at')),
            ],
            'fixed_deity_id' => [
                'exclude_unless:deity_selection_mode,'.Diya::MODE_FIXED,
                Rule::requiredIf(fn () => $this->input('deity_selection_mode') === Diya::MODE_FIXED),
                'nullable',
                Rule::exists('deities', 'id')->where(fn ($query) => $query->where('status', 'active')->whereNull('deleted_at')),
            ],
            'mantra_audio_id' => [
                'required',
                Rule::exists('audio_library', 'id')->where(fn ($query) => $query
                    ->where('category', 'mantra')
                    ->where('status', 'active')
                    ->whereNotNull('audio_file')
                    ->whereNull('deleted_at')),
            ],
            'mantra_ambience' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if (
                $this->input('deity_selection_mode') === Diya::MODE_FIXED
                && (int) $this->input('mantra_deity_id') !== (int) $this->input('fixed_deity_id')
            ) {
                $validator->errors()->add('mantra_deity_id', 'Mantra audio deity must match the fixed deity.');
                return;
            }

            $audio = Audio::active()
                ->where('category', 'mantra')
                ->whereNotNull('audio_file')
                ->find($this->input('mantra_audio_id'));

            if (!$audio || (int) $audio->deity_id !== (int) $this->input('mantra_deity_id')) {
                $validator->errors()->add('mantra_audio_id', 'Please select an active mantra audio for the selected deity.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'fixed_deity_id.required' => 'Please select an active deity for fixed deity mode.',
            'fixed_deity_id.exists' => 'Selected deity must be active.',
            'mantra_deity_id.required' => 'Please select a deity to load mantra audio.',
            'mantra_audio_id.required' => 'Please select a mantra audio for this diya.',
        ];
    }
}
