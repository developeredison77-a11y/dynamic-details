<?php

namespace App\Http\Requests;

use App\Enums\AssetCondition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssetReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'returned_at' => ['required', 'date'],
            'condition' => ['required', Rule::enum(AssetCondition::class)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
