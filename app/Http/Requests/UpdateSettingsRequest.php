<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'site_name' => ['required', 'string', 'max:120'],
            'theme_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'site_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'site_favicon' => ['nullable', 'file', 'mimes:ico,png,svg,webp', 'max:512'],
        ];
    }
}
