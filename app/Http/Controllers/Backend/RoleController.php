<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(Request $request): View
    {
        $requestedPerPage = $request->integer('per_page', 10);
        $perPage = in_array($requestedPerPage, [10, 20, 30, 40, 50], true) ? $requestedPerPage : 10;
        $search = $request->string('search')->toString();
        $status = $request->input('status');

        return view('roles.index', [
            'roles' => Role::query()
                ->withCount(['permissions', 'users', 'employees'])
                ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
                ->when(in_array($status, ['active', 'inactive'], true), fn ($query) => $query->where('is_active', $status === 'active'))
                ->orderBy('name')
                ->paginate($perPage)
                ->withQueryString(),
            'editRole' => new Role(['is_active' => true]),
            'permissionGroups' => $this->permissionGroups(),
        ]);
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $role = Role::query()->create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);
        if ($request->user()?->canAccess('roles.permissions')) {
            $role->syncPermissionIds($data['permissions'] ?? []);
        }

        return redirect()->route('roles.index')->with('success', 'Role saved successfully.');
    }

    public function edit(Request $request, Role $role): View
    {
        $requestedPerPage = $request->integer('per_page', 10);
        $perPage = in_array($requestedPerPage, [10, 20, 30, 40, 50], true) ? $requestedPerPage : 10;
        $search = $request->string('search')->toString();
        $status = $request->input('status');

        return view('roles.index', [
            'roles' => Role::query()
                ->withCount(['permissions', 'users', 'employees'])
                ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
                ->when(in_array($status, ['active', 'inactive'], true), fn ($query) => $query->where('is_active', $status === 'active'))
                ->orderBy('name')
                ->paginate($perPage)
                ->withQueryString(),
            'editRole' => $role->load('permissions'),
            'permissionGroups' => $this->permissionGroups(),
        ]);
    }

    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        $data = $request->validated();

        $role->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $role->is_system ? true : $request->boolean('is_active'),
        ]);
        if ($request->user()?->canAccess('roles.permissions')) {
            $role->syncPermissionIds($data['permissions'] ?? []);
        }

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    public function toggleStatus(Role $role): RedirectResponse
    {
        if ($role->is_system) {
            return back()->with('error', 'System roles must remain active.');
        }

        $role->update(['is_active' => ! $role->is_active]);

        return back()->with('success', 'Role status updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->is_system || $role->users()->exists() || $role->employees()->exists()) {
            return back()->with('error', 'This role cannot be deleted while it is protected or in use.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }

    private function permissionGroups()
    {
        return Permission::query()
            ->orderBy('sort_order')
            ->get()
            ->groupBy('group');
    }
}
