@extends('layouts.dashboard')

@section('title', $employee->exists ? 'Edit Employee' : 'Add Employee')
@section('page-title', $employee->exists ? 'Edit Employee' : 'Add Employee')
@section('eyebrow', 'Employee Master')

@section('content')
    <section class="dashboard-panel">
        <div class="panel-heading"><div><p>Employee details</p><h2>{{ $employee->exists ? $employee->employee_code : 'New employee' }}</h2></div></div>
        <form class="settings-form" method="POST" action="{{ $employee->exists ? route('employees.update', $employee) : route('employees.store') }}">
            @csrf
            @if($employee->exists) @method('PUT') @endif
            <div class="form-grid">
                <div class="form-field generated-code-field">
                    <span>Employee Code</span>
                    <strong>{{ $employee->employee_code }}</strong>
                </div>
                <label class="form-field"><span>EID No</span><input name="eid" value="{{ old('eid', $employee->eid) }}" placeholder="784-1995-4375063-4">@error('eid')<small>{{ $message }}</small>@enderror</label>
                <label class="form-field"><span>Nationality *</span><input name="nationality" value="{{ old('nationality', $employee->nationality) }}">@error('nationality')<small>{{ $message }}</small>@enderror</label>
                <label class="form-field"><span>Entity (Company Name) *</span><input name="entity" value="{{ old('entity', $employee->entity) }}">@error('entity')<small>{{ $message }}</small>@enderror</label>
                <label class="form-field"><span>Name English</span><input name="name_en" value="{{ old('name_en', $employee->name_en) }}">@error('name_en')<small>{{ $message }}</small>@enderror</label>
                <label class="form-field"><span>Name Arabic</span><input name="name_ar" dir="rtl" value="{{ old('name_ar', $employee->name_ar) }}">@error('name_ar')<small>{{ $message }}</small>@enderror</label>
                <label class="form-field"><span>Email</span><input name="email" type="email" value="{{ old('email', $employee->email) }}">@error('email')<small>{{ $message }}</small>@enderror</label>
                <label class="form-field"><span>Phone</span><input name="phone" value="{{ old('phone', $employee->phone) }}">@error('phone')<small>{{ $message }}</small>@enderror</label>
                <label class="form-field"><span>Department *</span><select name="employee_department_id"><option value="">{{ $departments->isEmpty() ? 'No departments available' : 'Select department' }}</option>@foreach($departments as $department)<option value="{{ $department->id }}" @selected((string) old('employee_department_id', $employee->employee_department_id) === (string) $department->id)>{{ $department->name }}</option>@endforeach</select>@if($departments->isEmpty())<small>Add departments from Employee Master first.</small>@endif @error('employee_department_id')<small>{{ $message }}</small>@enderror</label>
                <label class="form-field"><span>Job Title *</span><select name="employee_job_id"><option value="">{{ $jobs->isEmpty() ? 'No jobs available' : 'Select job title' }}</option>@foreach($jobs as $job)<option value="{{ $job->id }}" @selected((string) old('employee_job_id', $employee->employee_job_id) === (string) $job->id)>{{ $job->name }}</option>@endforeach</select>@if($jobs->isEmpty())<small>Add jobs from Employee Master first.</small>@endif @error('employee_job_id')<small>{{ $message }}</small>@enderror</label>
                <label class="form-field"><span>Role *</span><select name="role_id"><option value="">{{ $roles->isEmpty() ? 'No roles available' : 'Select role' }}</option>@foreach($roles as $role)<option value="{{ $role->id }}" @selected((string) old('role_id', $employee->role_id) === (string) $role->id)>{{ $role->name }}</option>@endforeach</select>@if($roles->isEmpty())<small>Run migrations or seed access control to create default roles.</small>@endif @error('role_id')<small>{{ $message }}</small>@enderror</label>
                <label class="form-field"><span>Joined At</span><input name="joined_at" type="date" value="{{ old('joined_at', optional($employee->joined_at)->format('Y-m-d')) }}">@error('joined_at')<small>{{ $message }}</small>@enderror</label>
                <label class="form-field"><span>Status</span><select name="status">@foreach($statuses as $status)<option value="{{ $status->value }}" @selected(old('status', $employee->status?->value ?? 'active') === $status->value)>{{ $status->label() }}</option>@endforeach</select>@error('status')<small>{{ $message }}</small>@enderror</label>
                <label class="form-field form-field-wide"><span>Notes</span><textarea name="notes">{{ old('notes', $employee->notes) }}</textarea>@error('notes')<small>{{ $message }}</small>@enderror</label>
            </div>
            <div class="form-actions"><a class="btn btn-outline" href="{{ route('employees.index') }}">Cancel</a><button class="btn btn-primary btn-lg" type="submit">{{ $employee->exists ? 'Update' : 'Save' }}</button></div>
        </form>
    </section>
@endsection
