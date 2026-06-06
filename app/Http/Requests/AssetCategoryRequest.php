<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssetCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('asset_categories', 'name')->ignore($this->route('category')?->id)],
            'code' => ['nullable', 'string', 'max:40', Rule::unique('asset_categories', 'code')->ignore($this->route('category')?->id)],
            'requires_serial' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
