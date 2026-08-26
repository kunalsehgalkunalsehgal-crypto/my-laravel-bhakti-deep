<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $config = $this->route('resourceConfig') ?? [];
        $model = $this->route('record');
        $rules = [];

        foreach ($config['fields'] ?? [] as $field => $meta) {
            $fieldRules = $meta['rules'] ?? ['nullable'];

            if (($meta['unique'] ?? false) === true) {
                $fieldRules[] = Rule::unique($meta['table'] ?? $config['table'], $field)->ignore($model?->getKey());
            }

            $rules[$field] = $fieldRules;
        }

        return $rules;
    }
}
