<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeDeclarationDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAccess('employees.update') === true;
    }

    public function rules(): array
    {
        $employee = $this->route('employee');

        return [
            'upload_employee_id' => ['required', Rule::in([(string) $employee?->getKey()])],
            'filled_declaration_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function attributes(): array
    {
        return [
            'filled_declaration_file' => 'filled declaration form',
        ];
    }
}
