@extends('layouts.dashboard')

@section('title', 'Roles & Permissions')
@section('page-title', 'Roles & Permissions')
@section('eyebrow', 'Access Control')

@php
    $isEditing = $editRole->exists;
    $selectedPermissionIds = collect(old('permissions', $isEditing ? $editRole->permissions->pluck('id')->all() : []))->map(fn ($id) => (string) $id);
    $hasFilters = request()->filled('search') || request()->filled('status');
    $canCreateRoles = auth()->user()?->canAccess('roles.create');
    $canUpdateRoles = auth()->user()?->canAccess('roles.update');
    $canDeleteRoles = auth()->user()?->canAccess('roles.delete');
    $canAssignPermissions = auth()->user()?->canAccess('roles.permissions');
@endphp

@section('content')
    @if (($isEditing && $canUpdateRoles) || (! $isEditing && $canCreateRoles))
        <section class="dashboard-panel role-permission-panel">
            <div class="panel-heading">
                <div>
                    <p>{{ $isEditing ? 'Update role access' : 'Create role access' }}</p>
                    <h2>{{ $isEditing ? $editRole->name : 'New Role' }}</h2>
                </div>
                @if ($isEditing)
                    <a class="btn btn-outline" href="{{ route('roles.index') }}">New Role</a>
                @endif
            </div>

            <form class="settings-form" method="POST" action="{{ $isEditing ? route('roles.update', $editRole) : route('roles.store') }}" data-permission-form>
                @csrf
                @if ($isEditing) @method('PUT') @endif

                <div class="form-grid">
                    <label class="form-field">
                        <span>Role Name</span>
                        <input name="name" value="{{ old('name', $editRole->name) }}" placeholder="Example: Store Manager">
                        @error('name')<small>{{ $message }}</small>@enderror
                    </label>
                    <label class="form-field">
                        <span>Status</span>
                        @if ($editRole->is_system)
                            <input type="hidden" name="is_active" value="1">
                            <select disabled><option>Active</option></select>
                        @else
                            <select name="is_active">
                                <option value="1" @selected((string) old('is_active', (int) ($editRole->is_active ?? true)) === '1')>Active</option>
                                <option value="0" @selected((string) old('is_active', (int) ($editRole->is_active ?? true)) === '0')>Inactive</option>
                            </select>
                        @endif
                        @error('is_active')<small>{{ $message }}</small>@enderror
                    </label>
                    <label class="form-field form-field-wide">
                        <span>Description</span>
                        <textarea name="description" placeholder="Short internal note for this role">{{ old('description', $editRole->description) }}</textarea>
                        @error('description')<small>{{ $message }}</small>@enderror
                    </label>
                </div>

                @if ($canAssignPermissions)
                    <div class="permission-toolbar">
                        <button class="btn btn-secondary btn-sm" type="button" data-permission-select="all">Select All</button>
                        <button class="btn btn-outline btn-sm" type="button" data-permission-select="none">Clear All</button>
                    </div>

                    <div class="permission-group-grid">
                        @foreach ($permissionGroups as $group => $permissions)
                            @php($groupKey = \Illuminate\Support\Str::slug($group))
                            <div class="permission-group-card">
                                <div class="permission-group-heading">
                                    <label class="check-field permission-group-toggle">
                                        <input type="checkbox" data-permission-group-toggle="{{ $groupKey }}">
                                        <span>{{ $group }}</span>
                                    </label>
                                    <small>{{ $permissions->count() }} permissions</small>
                                </div>
                                <div class="permission-checkbox-list">
                                    @foreach ($permissions as $permission)
                                        <label class="check-field permission-check">
                                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" data-permission-group="{{ $groupKey }}" @checked($selectedPermissionIds->contains((string) $permission->id))>
                                            <span>
                                                <strong>{{ $permission->name }}</strong>
                                                <small>{{ $permission->key }}</small>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('permissions')<small class="form-error">{{ $message }}</small>@enderror
                @endif

                <div class="form-actions">
                    <a class="btn btn-outline" href="{{ route('roles.index') }}">Cancel</a>
                    <button class="btn btn-primary btn-lg" type="submit">{{ $isEditing ? 'Update Role' : 'Save Role' }}</button>
                </div>
            </form>
        </section>
    @endif

    <section class="dashboard-panel client-listing-panel {{ $hasFilters ? 'is-open' : '' }}" data-listing-filter>
        <div class="panel-heading">
            <label class="client-search listing-global-search"><x-dashboard.icon name="search" /><input form="role-filter-form" value="{{ request('search') }}" placeholder="Search roles..." data-auto-filter-control data-filter-proxy="search"></label>
            <div class="button-row">
                <button class="btn btn-secondary action-icon-btn action-icon-neutral" type="button" aria-label="Filters" data-tooltip="Filters" data-filter-toggle aria-expanded="{{ $hasFilters ? 'true' : 'false' }}"><x-dashboard.icon name="funnel" /></button>
                @if ($hasFilters)
                    <a class="btn btn-secondary action-icon-btn action-icon-neutral" href="{{ route('roles.index') }}" aria-label="Reset Filter" data-tooltip="Reset Filter"><x-dashboard.icon name="x" /></a>
                @endif
            </div>
        </div>

        <form id="role-filter-form" class="client-toolbar listing-filter-fields" method="GET" data-filter-panel data-auto-filter-form @unless($hasFilters) hidden @endunless>
            <span class="filter-label">Filter by:</span>
            <label class="client-search"><input name="search" value="{{ request('search') }}" placeholder="Role name"></label>
            <select name="status" aria-label="Filter by status">
                <option value="">All Status</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>
        </form>

        <div class="responsive-table">
            <table class="advanced-table">
                <thead><tr><th>Role</th><th>Status</th><th>Permissions</th><th>Users</th><th>Employees</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr>
                            <td><div class="client-person"><span>{{ strtoupper(substr($role->name, 0, 2)) }}</span><div><strong>{{ $role->name }}</strong><small>{{ $role->description ?: $role->slug }}</small></div></div></td>
                            <td><span class="status-badge status-{{ $role->is_active ? 'active' : 'inactive' }}">{{ $role->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td>{{ $role->permissions_count }}</td>
                            <td>{{ $role->users_count }}</td>
                            <td>{{ $role->employees_count }}</td>
                            <td>
                                <div class="table-action-row">
                                    @if ($canUpdateRoles)
                                        <a class="btn btn-sm btn-outline table-action-btn action-icon-btn action-icon-edit" href="{{ route('roles.edit', $role) }}" aria-label="Edit {{ $role->name }}" data-tooltip="Edit"><x-dashboard.icon name="edit" /></a>
                                        @unless($role->is_system)
                                            <form method="POST" action="{{ route('roles.status', $role) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline table-action-btn action-icon-btn action-icon-neutral" type="submit" aria-label="Toggle {{ $role->name }} status" data-tooltip="Toggle status"><x-dashboard.icon name="rotate-ccw" /></button>
                                            </form>
                                        @endunless
                                    @endif
                                    @if ($canDeleteRoles && ! $role->is_system && $role->users_count === 0 && $role->employees_count === 0)
                                        <form method="POST" action="{{ route('roles.destroy', $role) }}" data-confirm-delete>
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger table-action-btn action-icon-btn action-icon-delete" type="submit" aria-label="Delete {{ $role->name }}" data-tooltip="Delete"><x-dashboard.icon name="trash" /></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="table-empty" colspan="6">No roles found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <span>Total {{ $roles->total() }} item(s)</span>
            <div class="table-pagination">
                <label class="pagination-size">
                    <span>Items per page</span>
                    <select name="per_page" form="role-filter-form" aria-label="Items per page" data-auto-filter-control data-native-select>
                        @foreach ([10, 20, 30, 40, 50] as $size)
                            <option value="{{ $size }}" @selected($roles->perPage() === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </label>
                <span class="pagination-page">Page {{ $roles->currentPage() }} of {{ $roles->lastPage() }}</span>
                <div class="pagination-controls">
                    <a class="action-icon-btn action-icon-neutral {{ $roles->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $roles->url(1) }}" aria-label="First page"><x-dashboard.icon name="chevrons-left" /></a>
                    <a class="action-icon-btn action-icon-neutral {{ $roles->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $roles->previousPageUrl() ?? '#' }}" aria-label="Previous page"><x-dashboard.icon name="chevron-left" /></a>
                    <a class="action-icon-btn action-icon-neutral {{ $roles->hasMorePages() ? '' : 'is-disabled' }}" href="{{ $roles->nextPageUrl() ?? '#' }}" aria-label="Next page"><x-dashboard.icon name="chevron-right" /></a>
                    <a class="action-icon-btn action-icon-neutral {{ $roles->hasMorePages() ? '' : 'is-disabled' }}" href="{{ $roles->url($roles->lastPage()) }}" aria-label="Last page"><x-dashboard.icon name="chevrons-right" /></a>
                </div>
            </div>
        </div>
    </section>
@endsection
