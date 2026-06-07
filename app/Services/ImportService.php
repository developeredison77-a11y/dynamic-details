<?php

namespace App\Services;

use App\Enums\AssetCondition;
use App\Enums\AssetStatus;
use App\Enums\EmployeeStatus;
use App\Enums\ImportType;
use App\Models\Asset;
use App\Models\AssetBrand;
use App\Models\AssetCategory;
use App\Models\Employee;
use App\Models\ImportBatch;
use App\Support\AdmsSpreadsheet;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ImportService
{
    public function employees(UploadedFile $file, ?int $userId): ImportBatch
    {
        $rows = AdmsSpreadsheet::rows($file);
        $errors = [];
        $success = 0;
        $emailCounts = collect($rows)
            ->pluck('email')
            ->filter(fn ($email): bool => filled($email))
            ->map(fn ($email): string => strtolower(trim((string) $email)))
            ->countBy();

        foreach ($rows as $index => $row) {
            $validator = Validator::make($row, [
                'employee_code' => ['required', 'string', 'max:60', Rule::unique('employees', 'employee_code')],
                'name_en' => ['required', 'string', 'max:255'],
                'name_ar' => ['nullable', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', Rule::unique('employees', 'email')],
                'department' => ['nullable', 'string', 'max:120'],
                'designation' => ['nullable', 'string', 'max:120'],
                'phone' => ['nullable', 'string', 'max:40'],
                'status' => ['nullable', Rule::enum(EmployeeStatus::class)],
            ]);

            $validator->after(function ($validator) use ($row, $emailCounts): void {
                $email = strtolower(trim((string) ($row['email'] ?? '')));

                if ($email !== '' && ($emailCounts[$email] ?? 0) > 1) {
                    $validator->errors()->add('email', 'The email must not be duplicated in the uploaded file.');
                }
            });

            if ($validator->fails()) {
                $errors[] = ['row' => $index + 2, 'messages' => $validator->errors()->all()];
                continue;
            }

            $data = $validator->validated();
            $data['status'] = ($data['status'] ?? null) ?: EmployeeStatus::Active->value;

            Employee::query()->create($data);
            $success++;
        }

        return $this->batch(ImportType::Employees, $file, $rows, $success, $errors, $userId);
    }

    public function assets(UploadedFile $file, ?int $userId): ImportBatch
    {
        $rows = AdmsSpreadsheet::rows($file);
        $errors = [];
        $success = 0;

        foreach ($rows as $index => $row) {
            $validator = Validator::make($row, [
                'asset_tag' => ['required', 'string', 'max:120', Rule::unique('assets', 'asset_tag')],
                'name' => ['required', 'string', 'max:255'],
                'category' => ['required', 'string', 'max:120'],
                'brand' => ['nullable', 'string', 'max:120'],
                'serial_number' => ['nullable', 'string', 'max:120', Rule::unique('assets', 'serial_number')],
                'model' => ['nullable', 'string', 'max:120'],
                'condition' => ['nullable', Rule::enum(AssetCondition::class)],
            ]);

            if ($validator->fails()) {
                $errors[] = ['row' => $index + 2, 'messages' => $validator->errors()->all()];
                continue;
            }

            $data = $validator->validated();
            $data['condition'] = ($data['condition'] ?? null) ?: AssetCondition::Good->value;
            $category = AssetCategory::query()->firstOrCreate(['name' => $data['category']]);
            $brand = filled($data['brand'] ?? null) ? AssetBrand::query()->firstOrCreate(['name' => $data['brand']]) : null;

            Asset::query()->create([
                'asset_category_id' => $category->id,
                'asset_brand_id' => $brand?->id,
                'asset_tag' => $data['asset_tag'],
                'name' => $data['name'],
                'serial_number' => $data['serial_number'] ?? null,
                'model' => $data['model'] ?? null,
                'condition' => $data['condition'],
                'status' => AssetStatus::Available,
            ]);

            $success++;
        }

        return $this->batch(ImportType::Assets, $file, $rows, $success, $errors, $userId);
    }

    /**
     * @param array<int, array<string, string|null>> $rows
     * @param array<int, array<string, mixed>> $errors
     */
    private function batch(ImportType $type, UploadedFile $file, array $rows, int $success, array $errors, ?int $userId): ImportBatch
    {
        return ImportBatch::query()->create([
            'created_by' => $userId,
            'type' => $type,
            'file_name' => $file->getClientOriginalName(),
            'total_rows' => count($rows),
            'successful_rows' => $success,
            'failed_rows' => count($errors),
            'errors' => $errors,
        ]);
    }
}
