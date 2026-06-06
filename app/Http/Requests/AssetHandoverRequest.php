<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssetHandoverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'asset_id' => ['required', 'exists:assets,id'],
            'handover_date' => ['required', 'date'],
            'expected_return_date' => ['nullable', 'date', 'after_or_equal:handover_date'],
            'handover_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
