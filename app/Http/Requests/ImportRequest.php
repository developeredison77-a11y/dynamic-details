<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportRequest extends FormRequest
{
    public static function importRules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:5120'],
        ];
    }

    public static function importMessages(): array
    {
        return [
            'file.required' => 'Please upload a file before starting the import.',
            'file.file' => 'Please upload a valid file.',
            'file.uploaded' => 'The file could not be uploaded. Please make sure it is a valid CSV/XLSX file and smaller than 5 MB.',
            'file.mimes' => 'The file must be a CSV or XLSX document.',
            'file.max' => 'The file may not be larger than 5 MB.',
        ];
    }

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return self::importRules();
    }

    public function messages(): array
    {
        return self::importMessages();
    }
}
