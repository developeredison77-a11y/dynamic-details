@extends('layouts.dashboard')

@section('title', 'Employees')
@section('page-title', 'Employee Master')
@section('eyebrow', 'Employees')

@section('content')
    <section class="dashboard-panel client-listing-panel">
        <div class="panel-heading">
            <div><p>Employee management</p><h2>All Employees</h2></div>
            <div class="button-row">
                <a href="{{ route('imports.index') }}" class="btn btn-secondary">Import Employees</a>
                <a href="{{ route('employees.create') }}" class="btn btn-primary btn-lg">Add Employee</a>
            </div>
        </div>
        <form class="client-toolbar" method="GET">
            <label class="client-search"><x-dashboard.icon name="search" /><input name="search" value="{{ request('search') }}" placeholder="Search employees, email, department"></label>
            <select name="status"><option value="">All Status</option>@foreach ($statuses as $status)<option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>@endforeach</select>
            <button class="btn btn-secondary" type="submit">Filter</button>
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
                                <div class="button-row">
                                    <a class="btn btn-sm btn-outline" href="{{ route('employees.edit', $employee) }}">Edit</a>
                                    <form method="POST" action="{{ route('employees.destroy', $employee) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" type="submit">Delete</button></form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No employees found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="table-footer">{{ $employees->links() }}</div>
    </section>
@endsection
