<?php

use App\Enums\AssetAssignmentStatus;
use App\Enums\AssetCondition;
use App\Enums\AssetStatus;
use App\Enums\EmployeeStatus;
use App\Enums\ImportType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table): void {
            $table->id();
            $table->string('employee_code')->unique();
            $table->string('name_en');
            $table->string('name_ar')->nullable();
            $table->string('department')->nullable();
            $table->string('designation')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->enum('status', array_map(fn (EmployeeStatus $status): string => $status->value, EmployeeStatus::cases()))->default(EmployeeStatus::Active->value);
            $table->date('joined_at')->nullable();
            $table->date('status_changed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_brands', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('asset_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->nullable()->unique();
            $table->boolean('requires_serial')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('assets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('asset_brand_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('asset_category_id')->constrained()->restrictOnDelete();
            $table->string('asset_tag')->unique();
            $table->string('name');
            $table->string('serial_number')->nullable()->unique();
            $table->string('model')->nullable();
            $table->enum('status', array_map(fn (AssetStatus $status): string => $status->value, AssetStatus::cases()))->default(AssetStatus::Available->value);
            $table->enum('condition', array_map(fn (AssetCondition $condition): string => $condition->value, AssetCondition::cases()))->default(AssetCondition::Good->value);
            $table->date('purchased_at')->nullable();
            $table->decimal('purchase_value', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', array_map(fn (AssetAssignmentStatus $status): string => $status->value, AssetAssignmentStatus::cases()))->default(AssetAssignmentStatus::Assigned->value);
            $table->date('handover_date');
            $table->date('expected_return_date')->nullable();
            $table->date('returned_at')->nullable();
            $table->text('handover_notes')->nullable();
            $table->text('return_notes')->nullable();
            $table->enum('return_condition', array_map(fn (AssetCondition $condition): string => $condition->value, AssetCondition::cases()))->nullable();
            $table->timestamps();
            $table->index(['asset_id', 'status']);
            $table->index(['employee_id', 'status']);
        });

        Schema::create('asset_returns', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('asset_assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('returned_at');
            $table->enum('condition', array_map(fn (AssetCondition $condition): string => $condition->value, AssetCondition::cases()));
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_declarations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('asset_assignment_id')->constrained()->cascadeOnDelete();
            $table->string('declaration_number')->unique();
            $table->date('issued_at');
            $table->text('terms')->nullable();
            $table->timestamps();
        });

        Schema::create('import_batches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', array_map(fn (ImportType $type): string => $type->value, ImportType::cases()));
            $table->string('file_name');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('successful_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);
            $table->json('errors')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_batches');
        Schema::dropIfExists('asset_declarations');
        Schema::dropIfExists('asset_returns');
        Schema::dropIfExists('asset_assignments');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('asset_categories');
        Schema::dropIfExists('asset_brands');
        Schema::dropIfExists('employees');
    }
};
