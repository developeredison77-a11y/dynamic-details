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
            'eid' => ['required', 'string', 'max:40', Rule::unique('employees', 'eid')->ignore($this->route('employee')?->id)],
            'nationality' => ['required', 'string', 'max:120'],
            'entity' => ['required', 'string', 'max:120'],
            'employee_department_id' => ['required', 'integer', Rule::exists('employee_departments', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'employee_job_id' => ['required', 'integer', Rule::exists('employee_jobs', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')->where('is_active', true)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('employees', 'email')->ignore($this->route('employee')?->id)],
            'phone' => ['nullable', 'string', 'max:40'],
            'status' => ['required', Rule::enum(EmployeeStatus::class)],
            'joined_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'eid' => 'EID No',
            'entity' => 'Entity (Company Name)',
            'employee_department_id' => 'department',
            'employee_job_id' => 'job title',
        ];
    }
}
