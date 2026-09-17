<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmployeeDepartment;
use App\Models\EmployeeJob;
use App\Models\Role;
use App\Models\User;
use App\Services\ImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
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
            'SL,Name,Nationality,ID No,Entity,email,Password,Job Title',
            '1,Ada Lovelace,British,784-1111-2222222-3,Engineering,ada@example.test,SecurePass123!,Analyst',
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
            'email' => 'ada@example.test',
        ]);
        $this->assertTrue(Hash::check('SecurePass123!', User::query()->where('email', 'ada@example.test')->firstOrFail()->password));
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

    public function test_it_logs_in_an_employee_created_before_user_account_sync(): void
    {
        $employee = Employee::query()->create([
            'eid' => '784-1111-2222222-4',
            'nationality' => 'British',
            'entity' => 'Engineering',
            'name_en' => 'Grace Hopper',
            'email' => 'grace@example.test',
            'password' => 'LegacyPass123!',
        ]);

        $response = $this->post(route('login.store'), [
            'email' => $employee->email,
            'password' => 'LegacyPass123!',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs(User::query()->where('email', $employee->email)->firstOrFail());
    }
}
