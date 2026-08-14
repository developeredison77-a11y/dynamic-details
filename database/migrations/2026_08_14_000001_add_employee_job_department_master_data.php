<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_departments', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('employee_jobs', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('employees', function (Blueprint $table): void {
            $table->foreignId('employee_department_id')->nullable()->after('department')->constrained('employee_departments')->nullOnDelete();
            $table->foreignId('employee_job_id')->nullable()->after('designation')->constrained('employee_jobs')->nullOnDelete();
            $table->string('nationality')->nullable()->after('eid');
            $table->string('entity')->nullable()->after('nationality');
        });

        $now = now();

        DB::table('employees')
            ->whereNotNull('department')
            ->where('department', '<>', '')
            ->distinct()
            ->pluck('department')
            ->each(function (string $department) use ($now): void {
                DB::table('employee_departments')->updateOrInsert(
                    ['name' => Str::limit(trim($department), 120, '')],
                    ['is_active' => true, 'created_at' => $now, 'updated_at' => $now]
                );
            });

        DB::table('employees')
            ->whereNotNull('designation')
            ->where('designation', '<>', '')
            ->distinct()
            ->pluck('designation')
            ->each(function (string $designation) use ($now): void {
                DB::table('employee_jobs')->updateOrInsert(
                    ['name' => Str::limit(trim($designation), 120, '')],
                    ['is_active' => true, 'created_at' => $now, 'updated_at' => $now]
                );
            });

        DB::table('employees')->orderBy('id')->eachById(function (object $employee): void {
            $departmentId = filled($employee->department)
                ? DB::table('employee_departments')->where('name', Str::limit(trim($employee->department), 120, ''))->value('id')
                : null;
            $jobId = filled($employee->designation)
                ? DB::table('employee_jobs')->where('name', Str::limit(trim($employee->designation), 120, ''))->value('id')
                : null;

            DB::table('employees')
                ->where('id', $employee->id)
                ->update([
                    'employee_department_id' => $departmentId,
                    'employee_job_id' => $jobId,
                ]);
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('employee_department_id');
            $table->dropConstrainedForeignId('employee_job_id');
            $table->dropColumn(['nationality', 'entity']);
        });

        Schema::dropIfExists('employee_jobs');
        Schema::dropIfExists('employee_departments');
    }
};
