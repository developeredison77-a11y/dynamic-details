<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmployeeDepartment;
use App\Models\EmployeeJob;
use App\Models\Role;
use App\Services\ImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class EmployeeImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_the_legacy_empm_layout_without_an_email_address(): void
    {
        Role::query()->firstOrCreate(['slug' => 'staff'], [
            'name' => 'Staff',
            'is_active' => true,
        ]);

        $file = UploadedFile::fake()->createWithContent('EMPM.csv', implode("\n", [
            'SL,Name,Nationality,ID No,Entity,Job Title',
            '1,Ada Lovelace,British,784-1111-2222222-3,Engineering,Analyst',
        ]));

        $batch = app(ImportService::class)->employees($file, null);

        $this->assertSame(1, $batch->total_rows);
        $this->assertSame(1, $batch->successful_rows);
        $this->assertSame(0, $batch->failed_rows);
        $this->assertDatabaseHas('employees', [
            'name_en' => 'Ada Lovelace',
            'eid' => '784-1111-2222222-3',
            'nationality' => 'British',
            'entity' => 'Engineering',
            'department' => 'Engineering',
            'designation' => 'Analyst',
            'email' => null,
        ]);
        $this->assertDatabaseHas('employee_departments', [
            'name' => 'Engineering',
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('employee_jobs', [
            'name' => 'Analyst',
            'is_active' => true,
        ]);

        $employee = Employee::query()->firstOrFail();

        $this->assertSame('Staff', $employee->role?->name);
        $this->assertSame('Engineering', EmployeeDepartment::query()->findOrFail($employee->employee_department_id)->name);
        $this->assertSame('Analyst', EmployeeJob::query()->findOrFail($employee->employee_job_id)->name);
    }
}
