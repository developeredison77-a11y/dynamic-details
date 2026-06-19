<?php

namespace App\Http\Requests;

use App\Enums\EmployeeStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name_en' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:120'],
            'role_id' => ['nullable', 'integer', Rule::exists('roles', 'id')->where('is_active', true)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('employees', 'email')->ignore($this->route('employee')?->id)],
            'phone' => ['nullable', 'string', 'max:40'],
            'status' => ['required', Rule::enum(EmployeeStatus::class)],
            'joined_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
