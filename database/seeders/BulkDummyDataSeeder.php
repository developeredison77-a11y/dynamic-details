<?php

namespace Database\Seeders;

use App\Enums\AssetAssignmentStatus;
use App\Enums\AssetCondition;
use App\Enums\AssetStatus;
use App\Enums\EmployeeStatus;
use App\Enums\ImportType;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetBrand;
use App\Models\AssetCategory;
use App\Models\AssetDeclaration;
use App\Models\AssetReturn;
use App\Models\Employee;
use App\Models\ImportBatch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class BulkDummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::query()->orderBy('name')->get();

        if ($roles->isEmpty()) {
            $this->call(AccessControlSeeder::class);
            $roles = Role::query()->orderBy('name')->get();
        }

        $users = $this->users($roles);
        $brands = $this->brands();
        $categories = $this->categories();
        $employees = $this->employees($roles);
        $assets = $this->assets($brands, $categories);
        $assignments = $this->assignments($employees, $assets, $users);

        $this->returns($assignments, $users);
        $this->declarations($assignments);
        $this->imports($users);
    }

    private function users($roles)
    {
        return collect(range(1, 10))->map(function (int $index) use ($roles): User {
            $role = $roles->values()->get(($index - 1) % max(1, $roles->count()));

            return User::query()->updateOrCreate(
                ['email' => "demo.user{$index}@adms.test"],
                [
                    'name' => "Demo User {$index}",
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'role_id' => $role?->id,
                ]
            );
        });
    }

    private function brands()
    {
        $names = ['Dell', 'HP', 'Lenovo', 'Apple', 'Samsung', 'Cisco', 'Canon', 'Epson', 'Logitech', 'Microsoft'];

        return collect($names)->map(fn (string $name): AssetBrand => AssetBrand::query()->updateOrCreate(
            ['name' => $name],
            ['is_active' => true]
        ));
    }

    private function categories()
    {
        $categories = [
            ['name' => 'Laptop', 'code' => 'LAP'],
            ['name' => 'Desktop', 'code' => 'DESK'],
            ['name' => 'Monitor', 'code' => 'MON'],
            ['name' => 'Printer', 'code' => 'PRN'],
            ['name' => 'Network Device', 'code' => 'NET'],
            ['name' => 'Mobile Phone', 'code' => 'MOB'],
            ['name' => 'Tablet', 'code' => 'TAB'],
            ['name' => 'Accessory', 'code' => 'ACC'],
            ['name' => 'Storage Device', 'code' => 'STO'],
            ['name' => 'Office Equipment', 'code' => 'OFF'],
        ];

        return collect($categories)->map(fn (array $category): AssetCategory => AssetCategory::query()->updateOrCreate(
            ['code' => $category['code']],
            [
                'name' => $category['name'],
                'requires_serial' => true,
                'is_active' => true,
            ]
        ));
    }

    private function employees($roles)
    {
        $departments = ['Administration', 'Finance', 'Human Resources', 'IT', 'Operations', 'Procurement', 'Sales', 'Support'];
        $statuses = [EmployeeStatus::Active, EmployeeStatus::Active, EmployeeStatus::Active, EmployeeStatus::Leave, EmployeeStatus::Resigned];

        return collect(range(1, 20))->map(function (int $index) use ($roles, $departments, $statuses): Employee {
            $role = $roles->values()->get(($index - 1) % max(1, $roles->count()));
            $status = $statuses[($index - 1) % count($statuses)];

            return Employee::query()->updateOrCreate(
                ['email' => "employee{$index}@adms.test"],
                [
                    'employee_code' => 'DUMMY'.str_pad((string) $index, 5, '0', STR_PAD_LEFT),
                    'name_en' => "Demo Employee {$index}",
                    'name_ar' => "Demo Employee Arabic {$index}",
                    'department' => $departments[($index - 1) % count($departments)],
                    'designation' => $role?->name,
                    'role_id' => $role?->id,
                    'phone' => '+97150000'.str_pad((string) $index, 4, '0', STR_PAD_LEFT),
                    'status' => $status,
                    'joined_at' => now()->subDays(30 + $index * 12)->toDateString(),
                    'status_changed_at' => $status === EmployeeStatus::Active ? null : now()->subDays($index)->toDateString(),
                    'notes' => 'Dummy employee generated for module testing.',
                ]
            );
        });
    }

    private function assets($brands, $categories)
    {
        $statuses = [
            AssetStatus::Assigned,
            AssetStatus::Assigned,
            AssetStatus::Returned,
            AssetStatus::Available,
            AssetStatus::Maintenance,
            AssetStatus::Retired,
        ];
        $conditions = [AssetCondition::New, AssetCondition::Good, AssetCondition::Good, AssetCondition::Fair, AssetCondition::Damaged];

        return collect(range(1, 20))->map(function (int $index) use ($brands, $categories, $statuses, $conditions): Asset {
            $category = $categories->values()->get(($index - 1) % max(1, $categories->count()));
            $brand = $brands->values()->get(($index - 1) % max(1, $brands->count()));
            $categoryName = $category?->name ?? 'Asset';
            $brandName = $brand?->name ?? 'Generic';

            return Asset::query()->updateOrCreate(
                ['asset_tag' => 'AST-DUMMY-'.str_pad((string) $index, 4, '0', STR_PAD_LEFT)],
                [
                    'asset_brand_id' => $brand?->id,
                    'asset_category_id' => $category?->id,
                    'name' => "{$categoryName} Demo Asset {$index}",
                    'serial_number' => 'SN-DUMMY-'.str_pad((string) $index, 6, '0', STR_PAD_LEFT),
                    'model' => "Model-{$brandName}-{$index}",
                    'status' => $statuses[($index - 1) % count($statuses)],
                    'condition' => $conditions[($index - 1) % count($conditions)],
                    'purchased_at' => now()->subMonths($index)->toDateString(),
                    'purchase_value' => 850 + ($index * 175),
                    'notes' => 'Dummy asset generated for module testing.',
                ]
            );
        });
    }

    private function assignments($employees, $assets, $users)
    {
        return collect(range(1, 16))->map(function (int $index) use ($employees, $assets, $users): AssetAssignment {
            $asset = $assets->values()->get($index - 1);
            $employee = $employees->values()->get(($index - 1) % max(1, $employees->count()));
            $user = $users->values()->get(($index - 1) % max(1, $users->count()));
            $isReturned = $index > 6;
            $handoverDate = Carbon::now()->subDays(45 - $index);

            $assignment = AssetAssignment::query()->updateOrCreate(
                ['asset_id' => $asset?->id, 'employee_id' => $employee?->id],
                [
                    'created_by' => $user?->id,
                    'status' => $isReturned ? AssetAssignmentStatus::Returned : AssetAssignmentStatus::Assigned,
                    'handover_date' => $handoverDate->toDateString(),
                    'expected_return_date' => $handoverDate->copy()->addMonths(6)->toDateString(),
                    'returned_at' => $isReturned ? $handoverDate->copy()->addDays(18)->toDateString() : null,
                    'handover_notes' => 'Dummy handover generated for module testing.',
                    'return_notes' => $isReturned ? 'Dummy asset return completed.' : null,
                    'return_condition' => $isReturned ? AssetCondition::Good : null,
                ]
            );

            $asset?->update([
                'status' => $isReturned ? AssetStatus::Returned : AssetStatus::Assigned,
            ]);

            return $assignment;
        });
    }

    private function returns($assignments, $users): void
    {
        $assignments
            ->filter(fn (AssetAssignment $assignment): bool => $assignment->status === AssetAssignmentStatus::Returned)
            ->take(10)
            ->values()
            ->each(function (AssetAssignment $assignment, int $index) use ($users): void {
                $user = $users->values()->get($index % max(1, $users->count()));

                AssetReturn::query()->updateOrCreate(
                    ['asset_assignment_id' => $assignment->id],
                    [
                        'asset_id' => $assignment->asset_id,
                        'employee_id' => $assignment->employee_id,
                        'received_by' => $user?->id,
                        'returned_at' => $assignment->returned_at ?? now()->subDays($index)->toDateString(),
                        'condition' => AssetCondition::Good,
                        'notes' => 'Dummy return generated for module testing.',
                    ]
                );
            });
    }

    private function declarations($assignments): void
    {
        $assignments->take(16)->values()->each(function (AssetAssignment $assignment, int $index): void {
            AssetDeclaration::query()->updateOrCreate(
                ['asset_assignment_id' => $assignment->id],
                [
                    'declaration_number' => 'DEC-DUMMY-'.str_pad((string) ($index + 1), 5, '0', STR_PAD_LEFT),
                    'issued_at' => $assignment->handover_date,
                    'terms' => 'Dummy declaration terms for module testing and print preview.',
                ]
            );
        });
    }

    private function imports($users): void
    {
        collect(range(1, 10))->each(function (int $index) use ($users): void {
            $type = $index % 2 === 0 ? ImportType::Assets : ImportType::Employees;
            $user = $users->values()->get(($index - 1) % max(1, $users->count()));

            ImportBatch::query()->updateOrCreate(
                ['file_name' => "dummy-{$type->value}-import-{$index}.csv"],
                [
                    'created_by' => $user?->id,
                    'type' => $type,
                    'total_rows' => 20,
                    'successful_rows' => $index % 3 === 0 ? 18 : 20,
                    'failed_rows' => $index % 3 === 0 ? 2 : 0,
                    'errors' => $index % 3 === 0 ? [
                        ['row' => 4, 'messages' => ['Dummy duplicate value detected.']],
                        ['row' => 11, 'messages' => ['Dummy required field missing.']],
                    ] : null,
                ]
            );
        });
    }
}
