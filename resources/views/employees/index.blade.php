@extends('layouts.dashboard')

@section('title', 'Employees')
@section('page-title', 'Employee Master')
@section('eyebrow', 'Employees')

@section('content')
    <section class="dashboard-panel client-listing-panel {{ request()->hasAny(['search', 'status']) ? 'is-open' : '' }}" data-listing-filter>
        <div class="panel-heading">
            <div><p>Employee management</p><h2>All Employees</h2></div>
            <div class="button-row">
                <button class="btn btn-secondary action-icon-btn action-icon-neutral" type="button" aria-label="Show filters" data-tooltip="Filters" data-filter-toggle aria-expanded="{{ request()->hasAny(['search', 'status']) ? 'true' : 'false' }}"><x-dashboard.icon name="funnel" /></button>
                <a href="{{ route('imports.employees.index') }}" class="btn btn-secondary action-icon-btn action-icon-neutral" aria-label="Import Employees" data-tooltip="Import Employees"><x-dashboard.icon name="upload" /></a>
                <a href="{{ route('employees.create') }}" class="btn btn-primary btn-lg action-icon-btn action-icon-edit" aria-label="Add Employee" data-tooltip="Add Employee"><x-dashboard.icon name="plus" /></a>
            </div>
        </div>
        <form class="client-toolbar listing-filter-fields" method="GET" data-filter-panel {{ request()->hasAny(['search', 'status']) ? '' : 'hidden' }}>
                <label class="client-search"><x-dashboard.icon name="search" /><input name="search" value="{{ request('search') }}" placeholder="Search employees, email, department"></label>
                <select name="status" aria-label="Filter by status"><option value="">All Status</option>@foreach ($statuses as $status)<option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>@endforeach</select>
                <div class="listing-filter-actions">
                    <button class="btn btn-primary" type="submit">Apply</button>
                    <a class="btn btn-secondary" href="{{ route('employees.index') }}">Reset</a>
                </div>
        </form>
        <div class="responsive-table">
            <table class="advanced-table">
                <thead><tr><th>Employee</th><th>Arabic Name</th><th>Department</th><th>Email</th><th>Status</th><th>Assets</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse ($employees as $employee)
                        <tr>
                            <td><div class="client-person"><span>{{ strtoupper(substr($employee->name_en, 0, 2)) }}</span><div><strong>{{ $employee->name_en }}</strong><small>{{ $employee->employee_code }}</small></div></div></td>
                            <td dir="rtl">{{ $employee->name_ar ?: '-' }}</td>
                            <td>{{ $employee->department ?: '-' }}</td>
                            <td>{{ $employee->email ?: '-' }}</td>
                            <td><span class="status-badge status-{{ $employee->status->value }}">{{ $employee->status->label() }}</span></td>
                            <td>{{ $employee->active_assignments_count }}</td>
                            <td>
                                <div class="table-action-row">
                                    <a class="btn btn-sm btn-outline table-action-btn action-icon-btn action-icon-edit" href="{{ route('employees.edit', $employee) }}" aria-label="Edit {{ $employee->name_en }}" data-tooltip="Edit">
                                        <x-dashboard.icon name="edit" />
                                    </a>
                                    <form method="POST" action="{{ route('employees.destroy', $employee) }}" data-confirm-delete>
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger table-action-btn action-icon-btn action-icon-delete" type="submit" aria-label="Delete {{ $employee->name_en }}" data-tooltip="Delete">
                                            <x-dashboard.icon name="trash" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="table-empty" colspan="7">No employees found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="table-footer">{{ $employees->links() }}</div>
    </section>
@endsection
