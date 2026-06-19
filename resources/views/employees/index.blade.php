@extends('layouts.dashboard')

@section('title', 'Employees')
@section('page-title', 'Employee Master')
@section('eyebrow', 'Employees')
@section('page-actions')
    @if (auth()->user()?->canAccess('employees.create'))
        <a href="{{ route('employees.create') }}" class="btn btn-primary listing-create-btn">
            <x-dashboard.icon name="plus" />
            <span>Create</span>
        </a>
    @endif
@endsection

@section('content')
    @php
        $hasFilters = request()->filled('search') || request()->filled('status') || request()->filled('role_id');
        $canUpdateEmployees = auth()->user()?->canAccess('employees.update');
        $canDeleteEmployees = auth()->user()?->canAccess('employees.delete');
    @endphp
    <section class="dashboard-panel client-listing-panel {{ $hasFilters ? 'is-open' : '' }}" data-listing-filter>
        <div class="panel-heading">
            <label class="client-search listing-global-search"><x-dashboard.icon name="search" /><input form="employee-filter-form" value="{{ request('search') }}" placeholder="Search all columns..." data-auto-filter-control data-filter-proxy="search"></label>
            <div class="button-row">
                <button class="btn btn-secondary action-icon-btn action-icon-neutral" type="button" aria-label="Filters" data-tooltip="Filters" data-filter-toggle aria-expanded="{{ $hasFilters ? 'true' : 'false' }}"><x-dashboard.icon name="funnel" /></button>
                @if ($hasFilters)
                    <a class="btn btn-secondary action-icon-btn action-icon-neutral" href="{{ route('employees.index') }}" aria-label="Reset Filter" data-tooltip="Reset Filter"><x-dashboard.icon name="x" /></a>
                @endif
                @if (auth()->user()?->canAccess('employees.import'))
                    <a href="{{ route('imports.employees.index') }}" class="btn btn-secondary action-icon-btn action-icon-neutral" aria-label="Import Employees" data-tooltip="Import Employees"><x-dashboard.icon name="upload" /></a>
                @endif
            </div>
        </div>
        <form id="employee-filter-form" class="client-toolbar listing-filter-fields" method="GET" data-filter-panel data-auto-filter-form @unless($hasFilters) hidden @endunless>
                <span class="filter-label">Filter by:</span>
                <label class="client-search"><input name="search" value="{{ request('search') }}" placeholder="Full Name"></label>
                <select name="role_id" aria-label="Filter by role"><option value="">All Roles</option>@foreach ($roles as $role)<option value="{{ $role->id }}" @selected((string) request('role_id') === (string) $role->id)>{{ $role->name }}</option>@endforeach</select>
                <select name="status" aria-label="Filter by status"><option value="">All Status</option>@foreach ($statuses as $status)<option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>@endforeach</select>
        </form>
        <div class="responsive-table">
            <table class="advanced-table">
                <thead><tr><th>Employee</th><th>Arabic Name</th><th>Department</th><th>Role</th><th>Status</th><th>Assets</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse ($employees as $employee)
                        <tr>
                            <td><div class="client-person"><span>{{ strtoupper(substr($employee->name_en, 0, 2)) }}</span><div><strong>{{ $employee->name_en }}</strong><small>{{ $employee->employee_code }}</small></div></div></td>
                            <td dir="rtl">{{ $employee->name_ar ?: '-' }}</td>
                            <td>{{ $employee->department ?: '-' }}</td>
                            <td>{{ $employee->role?->name ?? $employee->designation ?? '-' }}</td>
                            <td><span class="status-badge status-{{ $employee->status->value }}">{{ $employee->status->label() }}</span></td>
                            <td>{{ $employee->active_assignments_count }}</td>
                            <td>
                                <div class="table-action-row">
                                    @if ($canUpdateEmployees)
                                        <a class="btn btn-sm btn-outline table-action-btn action-icon-btn action-icon-edit" href="{{ route('employees.edit', $employee) }}" aria-label="Edit {{ $employee->name_en }}" data-tooltip="Edit">
                                            <x-dashboard.icon name="edit" />
                                        </a>
                                    @endif
                                    @if ($canDeleteEmployees)
                                        <form method="POST" action="{{ route('employees.destroy', $employee) }}" data-confirm-delete>
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger table-action-btn action-icon-btn action-icon-delete" type="submit" aria-label="Delete {{ $employee->name_en }}" data-tooltip="Delete">
                                                <x-dashboard.icon name="trash" />
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="table-empty" colspan="7">No employees found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="table-footer">
            <span>Total {{ $employees->total() }} item(s)</span>
            <div class="table-pagination">
                <label class="pagination-size">
                    <span>Items per page</span>
                    <select name="per_page" form="employee-filter-form" aria-label="Items per page" data-auto-filter-control data-native-select>
                        @foreach ([10, 20, 30, 40, 50] as $size)
                            <option value="{{ $size }}" @selected($employees->perPage() === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </label>
                <span class="pagination-page">Page {{ $employees->currentPage() }} of {{ $employees->lastPage() }}</span>
                <div class="pagination-controls">
                    <a class="action-icon-btn action-icon-neutral {{ $employees->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $employees->url(1) }}" aria-label="First page"><x-dashboard.icon name="chevrons-left" /></a>
                    <a class="action-icon-btn action-icon-neutral {{ $employees->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $employees->previousPageUrl() ?? '#' }}" aria-label="Previous page"><x-dashboard.icon name="chevron-left" /></a>
                    <a class="action-icon-btn action-icon-neutral {{ $employees->hasMorePages() ? '' : 'is-disabled' }}" href="{{ $employees->nextPageUrl() ?? '#' }}" aria-label="Next page"><x-dashboard.icon name="chevron-right" /></a>
                    <a class="action-icon-btn action-icon-neutral {{ $employees->hasMorePages() ? '' : 'is-disabled' }}" href="{{ $employees->url($employees->lastPage()) }}" aria-label="Last page"><x-dashboard.icon name="chevrons-right" /></a>
                </div>
            </div>
        </div>
    </section>

    @if (session('generated_employee_code'))
        <div class="modal-backdrop is-open" data-session-modal>
            <div class="modal-card generated-code-modal" role="dialog" aria-modal="true" aria-labelledby="generated-code-title">
                <div class="modal-heading">
                    <div>
                        <p>Employee created</p>
                        <h2 id="generated-code-title">Generated Employee Code</h2>
                    </div>
                    <button type="button" class="action-icon-btn action-icon-neutral" aria-label="Close generated code popup" data-session-modal-close><x-dashboard.icon name="x" /></button>
                </div>
                <div class="generated-code-summary">
                    <span>{{ session('generated_employee_code') }}</span>
                    <p>This code is unique and cannot be edited.</p>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-primary" data-session-modal-close>Done</button>
                </div>
            </div>
        </div>
    @endif
@endsection
