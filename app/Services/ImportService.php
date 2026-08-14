<?php

namespace App\Services;

use App\Enums\AssetCondition;
use App\Enums\AssetStatus;
use App\Enums\EmployeeStatus;
use App\Enums\ImportType;
use App\Models\Asset;
use App\Models\Employee;
use App\Models\EmployeeDepartment;
use App\Models\EmployeeJob;
use App\Models\ImportBatch;
use App\Models\Role;
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
        $emiratesIdCounts = collect($rows)
            ->map(fn (array $row): string => $this->normalizedImportValue($row['eid_no'] ?? $row['emirates_id'] ?? $row['eid'] ?? null))
            ->filter(fn (string $value): bool => $value !== '')
            ->countBy();

        foreach ($rows as $index => $row) {
            $row['eid'] = $row['eid'] ?? $row['eid_no'] ?? $row['emirates_id'] ?? null;

            $validator = Validator::make($row, [
                'name_en' => ['required', 'string', 'max:255'],
                'name_ar' => ['nullable', 'string', 'max:255'],
                'eid' => ['nullable', 'string', 'max:40', Rule::unique('employees', 'eid')],
                'nationality' => ['required', 'string', 'max:120'],
                'entity' => ['required', 'string', 'max:120'],
                'email' => ['required', 'email', 'max:255', Rule::unique('employees', 'email')],
                'employee_department_id' => ['nullable', 'integer', Rule::exists('employee_departments', 'id')->where('is_active', true)->whereNull('deleted_at')],
                'department' => ['nullable', 'string', 'max:120'],
                'employee_job_id' => ['nullable', 'integer', Rule::exists('employee_jobs', 'id')->where('is_active', true)->whereNull('deleted_at')],
                'job_title' => ['nullable', 'string', 'max:120'],
                'role_id' => ['nullable', 'integer', Rule::exists('roles', 'id')->where('is_active', true)],
                'role' => ['nullable', 'string', 'max:120'],
                'designation' => ['nullable', 'string', 'max:120'],
                'phone' => ['nullable', 'string', 'max:40'],
                'status' => ['nullable', Rule::enum(EmployeeStatus::class)],
            ]);

            $validator->after(function ($validator) use ($row, $emailCounts, $emiratesIdCounts): void {
                $email = strtolower(trim((string) ($row['email'] ?? '')));
                $eid = $this->normalizedImportValue($row['eid'] ?? null);
                $roleName = trim((string) ($row['role'] ?? $row['designation'] ?? ''));
                $departmentName = trim((string) ($row['department'] ?? ''));
                $jobName = trim((string) ($row['job_title'] ?? ''));

                if ($email !== '' && ($emailCounts[$email] ?? 0) > 1) {
                    $validator->errors()->add('email', 'The email must not be duplicated in the uploaded file.');
                }

                if ($eid !== '' && ($emiratesIdCounts[$eid] ?? 0) > 1) {
                    $validator->errors()->add('eid', 'The Emirates ID must not be duplicated in the uploaded file.');
                }

                if ($roleName !== '' && ! Role::query()->active()->where(function ($query) use ($roleName): void {
                    $query->where('name', $roleName)->orWhere('slug', $roleName);
                })->exists()) {
                    $validator->errors()->add('role', 'The selected role does not exist or is inactive.');
                }

                if (blank($row['role_id'] ?? null) && $roleName === '') {
                    $validator->errors()->add('role', 'The role field is required.');
                }

                if (blank($row['employee_department_id'] ?? null) && $departmentName === '') {
                    $validator->errors()->add('department', 'The department field is required.');
                }

                if (blank($row['employee_department_id'] ?? null) && $departmentName !== '' && ! EmployeeDepartment::query()->active()->where('name', $departmentName)->exists()) {
                    $validator->errors()->add('department', 'The selected department does not exist or is inactive.');
                }

                if (blank($row['employee_job_id'] ?? null) && $jobName === '') {
                    $validator->errors()->add('job_title', 'The job title field is required.');
                }

                if (blank($row['employee_job_id'] ?? null) && $jobName !== '' && ! EmployeeJob::query()->active()->where('name', $jobName)->exists()) {
                    $validator->errors()->add('job_title', 'The selected job title does not exist or is inactive.');
                }
            });

            if ($validator->fails()) {
                $errors[] = ['row' => $index + 2, 'messages' => $validator->errors()->all()];
                continue;
            }

            $data = $validator->validated();
            $data['status'] = ($data['status'] ?? null) ?: EmployeeStatus::Active->value;
            $role = $this->employeeRole($data);
            $department = $this->employeeDepartment($data);
            $job = $this->employeeJob($data);

            if ($role) {
                $data['role_id'] = $role->id;
            }

            if ($department) {
                $data['employee_department_id'] = $department->id;
                $data['department'] = $department->name;
            }

            if ($job) {
                $data['employee_job_id'] = $job->id;
                $data['designation'] = $job->name;
            }

            unset($data['role'], $data['job_title']);

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
        $assetTagCounts = $this->filledValueCounts($rows, 'asset_tag');
        $serialNumberCounts = $this->filledValueCounts($rows, 'serial_number');

        foreach ($rows as $index => $row) {
            $validator = Validator::make($row, [
                'asset_tag' => ['required', 'string', 'max:120', Rule::unique('assets', 'asset_tag')],
                'name' => ['required', 'string', 'max:255'],
                'asset_category_id' => ['required', 'integer', Rule::exists('asset_categories', 'id')->where('is_active', true)],
                'asset_brand_id' => ['nullable', 'integer', Rule::exists('asset_brands', 'id')->where('is_active', true)->whereNull('deleted_at')],
                'serial_number' => ['nullable', 'string', 'max:120', Rule::unique('assets', 'serial_number')],
                'model' => ['nullable', 'string', 'max:120'],
                'condition' => ['nullable', Rule::enum(AssetCondition::class)],
            ], [
                'asset_category_id.required' => 'The asset category ID is required. Use an active category ID from the import reference list.',
                'asset_category_id.integer' => 'The asset category ID must be a number.',
                'asset_category_id.exists' => 'The selected asset category ID does not exist or is inactive.',
                'asset_brand_id.integer' => 'The asset brand ID must be a number.',
                'asset_brand_id.exists' => 'The selected asset brand ID does not exist, is inactive, or has been deleted.',
                'condition' => 'The condition must be one of: new, good, fair, damaged.',
            ]);

            $validator->after(function ($validator) use ($row, $assetTagCounts, $serialNumberCounts): void {
                $assetTag = $this->normalizedImportValue($row['asset_tag'] ?? null);
                $serialNumber = $this->normalizedImportValue($row['serial_number'] ?? null);

                if ($assetTag !== '' && ($assetTagCounts[$assetTag] ?? 0) > 1) {
                    $validator->errors()->add('asset_tag', 'The asset tag must not be duplicated in the uploaded file.');
                }

                if ($serialNumber !== '' && ($serialNumberCounts[$serialNumber] ?? 0) > 1) {
                    $validator->errors()->add('serial_number', 'The serial number must not be duplicated in the uploaded file.');
                }
            });

            if ($validator->fails()) {
                $errors[] = ['row' => $index + 2, 'messages' => $validator->errors()->all()];
                continue;
            }

            $data = $validator->validated();
            $data['condition'] = ($data['condition'] ?? null) ?: AssetCondition::Good->value;

            Asset::query()->create([
                'asset_category_id' => $data['asset_category_id'],
                'asset_brand_id' => $data['asset_brand_id'] ?? null,
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
     */
    private function filledValueCounts(array $rows, string $key)
    {
        return collect($rows)
            ->pluck($key)
            ->map(fn ($value): string => $this->normalizedImportValue($value))
            ->filter(fn (string $value): bool => $value !== '')
            ->countBy();
    }

    private function normalizedImportValue(mixed $value): string
    {
        return strtolower(trim((string) $value));
    }

    private function employeeRole(array $data): ?Role
    {
        if (filled($data['role_id'] ?? null)) {
            return Role::query()->active()->find($data['role_id']);
        }

        $roleName = trim((string) ($data['role'] ?? $data['designation'] ?? ''));

        if ($roleName === '') {
            return null;
        }

        return Role::query()
            ->active()
            ->where(fn ($query) => $query->where('name', $roleName)->orWhere('slug', $roleName))
            ->first();
    }

    private function employeeDepartment(array $data): ?EmployeeDepartment
    {
        if (filled($data['employee_department_id'] ?? null)) {
            return EmployeeDepartment::query()->active()->find($data['employee_department_id']);
        }

        $departmentName = trim((string) ($data['department'] ?? ''));

        if ($departmentName === '') {
            return null;
        }

        return EmployeeDepartment::query()
            ->active()
            ->where('name', $departmentName)
            ->first();
    }

    private function employeeJob(array $data): ?EmployeeJob
    {
        if (filled($data['employee_job_id'] ?? null)) {
            return EmployeeJob::query()->active()->find($data['employee_job_id']);
        }

        $jobName = trim((string) ($data['job_title'] ?? ''));

        if ($jobName === '') {
            return null;
        }

        return EmployeeJob::query()
            ->active()
            ->where('name', $jobName)
            ->first();
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
