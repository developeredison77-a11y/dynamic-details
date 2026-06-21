<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AccessControlSeeder extends Seeder
{
    /**
     * @var array<string, array<int, array{key: string, name: string, description?: string}>>
     */
    public const PERMISSIONS = [
        'Dashboard' => [
            ['key' => 'dashboard.view', 'name' => 'View Dashboard'],
        ],
        'Employee Master' => [
            ['key' => 'clients.view', 'name' => 'View Clients'],
            ['key' => 'employees.view', 'name' => 'View Employees'],
            ['key' => 'employees.create', 'name' => 'Create Employees'],
            ['key' => 'employees.update', 'name' => 'Update Employees'],
            ['key' => 'employees.delete', 'name' => 'Delete Employees'],
            ['key' => 'employees.import', 'name' => 'Import Employees'],
        ],
        'Roles & Permissions' => [
            ['key' => 'roles.view', 'name' => 'View Roles'],
            ['key' => 'roles.create', 'name' => 'Create Roles'],
            ['key' => 'roles.update', 'name' => 'Update Roles'],
            ['key' => 'roles.delete', 'name' => 'Delete Roles'],
            ['key' => 'roles.permissions', 'name' => 'Assign Permissions'],
        ],
        'Asset Master' => [
            ['key' => 'assets.view', 'name' => 'View Assets'],
            ['key' => 'assets.create', 'name' => 'Create Assets'],
            ['key' => 'assets.update', 'name' => 'Update Assets'],
            ['key' => 'assets.delete', 'name' => 'Delete Assets'],
            ['key' => 'assets.import', 'name' => 'Import Assets'],
            ['key' => 'asset-brands.manage', 'name' => 'Manage Asset Brands'],
            ['key' => 'asset-categories.manage', 'name' => 'Manage Asset Categories'],
        ],
        'Asset Operations' => [
            ['key' => 'asset-handovers.view', 'name' => 'View Handovers'],
            ['key' => 'asset-handovers.create', 'name' => 'Create Handovers'],
            ['key' => 'asset-handovers.update', 'name' => 'Update Handovers'],
            ['key' => 'asset-returns.view', 'name' => 'View Returns'],
            ['key' => 'asset-returns.create', 'name' => 'Create Returns'],
            ['key' => 'declarations.view', 'name' => 'View Declarations'],
            ['key' => 'declarations.create', 'name' => 'Create Declarations'],
        ],
        'Data & Reports' => [
            ['key' => 'imports.view', 'name' => 'View Imports'],
            ['key' => 'reports.view', 'name' => 'View Reports'],
            ['key' => 'reports.export', 'name' => 'Export Reports'],
        ],
        'Settings' => [
            ['key' => 'settings.view', 'name' => 'View Settings'],
            ['key' => 'settings.update', 'name' => 'Update Settings'],
        ],
    ];

    public function run(): void
    {
        $sort = 1;

        foreach (self::PERMISSIONS as $group => $permissions) {
            foreach ($permissions as $permission) {
                Permission::query()->updateOrCreate(
                    ['key' => $permission['key']],
                    [
                        'name' => $permission['name'],
                        'group' => $group,
                        'description' => $permission['description'] ?? null,
                        'sort_order' => $sort++,
                    ]
                );
            }
        }

        $allPermissionIds = Permission::query()->pluck('id')->all();
        $managerPermissionIds = Permission::query()
            ->whereNotIn('key', ['roles.delete', 'settings.update'])
            ->pluck('id')
            ->all();
        $staffPermissionIds = Permission::query()
            ->whereIn('key', [
                'dashboard.view',
                'employees.view',
                'assets.view',
                'asset-handovers.view',
                'asset-returns.view',
                'declarations.view',
                'reports.view',
            ])
            ->pluck('id')
            ->all();
        $hrPermissionIds = Permission::query()
            ->whereIn('key', [
                'dashboard.view',
                'clients.view',
                'employees.view',
                'employees.create',
                'employees.update',
                'employees.import',
                'roles.view',
                'assets.view',
                'reports.view',
                'reports.export',
            ])
            ->pluck('id')
            ->all();
        $assetCustodianPermissionIds = Permission::query()
            ->whereIn('key', [
                'dashboard.view',
                'employees.view',
                'assets.view',
                'assets.create',
                'assets.update',
                'assets.import',
                'asset-brands.manage',
                'asset-categories.manage',
                'asset-handovers.view',
                'asset-handovers.create',
                'asset-handovers.update',
                'asset-returns.view',
                'asset-returns.create',
                'declarations.view',
                'declarations.create',
                'imports.view',
                'reports.view',
                'reports.export',
            ])
            ->pluck('id')
            ->all();
        $itOfficerPermissionIds = Permission::query()
            ->whereIn('key', [
                'dashboard.view',
                'employees.view',
                'assets.view',
                'assets.create',
                'assets.update',
                'asset-handovers.view',
                'asset-handovers.create',
                'asset-handovers.update',
                'asset-returns.view',
                'reports.view',
            ])
            ->pluck('id')
            ->all();
        $auditorPermissionIds = Permission::query()
            ->whereIn('key', [
                'dashboard.view',
                'clients.view',
                'employees.view',
                'roles.view',
                'assets.view',
                'asset-handovers.view',
                'asset-returns.view',
                'declarations.view',
                'imports.view',
                'reports.view',
                'reports.export',
                'settings.view',
            ])
            ->pluck('id')
            ->all();

        $superAdmin = $this->role('Super Admin', 'Full system access.', true);
        $superAdmin->syncPermissionIds($allPermissionIds);

        $admin = $this->role('Admin', 'Administrative user with all standard permissions.');
        $admin->syncPermissionIds($allPermissionIds);

        $manager = $this->role('Manager', 'Can manage daily asset and employee operations.');
        $manager->syncPermissionIds($managerPermissionIds);

        $staff = $this->role('Staff', 'Read-only access for standard operational users.');
        $staff->syncPermissionIds($staffPermissionIds);

        $hrOfficer = $this->role('HR Officer', 'Can manage employee records and employee reports.');
        $hrOfficer->syncPermissionIds($hrPermissionIds);

        $assetCustodian = $this->role('Asset Custodian', 'Can manage asset master data and daily asset movement.');
        $assetCustodian->syncPermissionIds($assetCustodianPermissionIds);

        $itOfficer = $this->role('IT Officer', 'Can maintain IT assets and issue equipment.');
        $itOfficer->syncPermissionIds($itOfficerPermissionIds);

        $auditor = $this->role('Auditor', 'Read-only audit access with report export.');
        $auditor->syncPermissionIds($auditorPermissionIds);

        User::query()
            ->whereNull('role_id')
            ->update(['role_id' => $superAdmin->id]);
    }

    private function role(string $name, string $description, bool $system = false): Role
    {
        return Role::query()->updateOrCreate(
            ['slug' => Str::slug($name)],
            [
                'name' => $name,
                'description' => $description,
                'is_active' => true,
                'is_system' => $system,
            ]
        );
    }
}
