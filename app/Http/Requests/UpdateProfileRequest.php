<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user()->id),
            ],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'profile_image' => [
                'nullable',
                'file',
                'mimetypes:image/jpeg,image/png,image/gif,image/webp,image/svg+xml,image/bmp,image/x-ms-bmp,image/avif,image/tiff',
                'max:4096',
            ],
        ];
    }
}
