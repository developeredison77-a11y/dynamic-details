<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $permission = Permission::query()->updateOrCreate(
            ['key' => 'asset-handovers.update'],
            [
                'name' => 'Update Handovers',
                'group' => 'Asset Operations',
                'description' => null,
                'sort_order' => 47,
            ]
        );

        Role::query()
            ->whereIn('slug', ['super-admin', 'admin', 'manager', 'asset-custodian', 'it-officer'])
            ->get()
            ->each(function (Role $role) use ($permission): void {
                $role->permissions()->syncWithoutDetaching([$permission->id]);
            });
    }

    public function down(): void
    {
        $permission = Permission::query()->where('key', 'asset-handovers.update')->first();

        if (! $permission) {
            return;
        }

        $permission->roles()->detach();
        $permission->delete();
    }
};
