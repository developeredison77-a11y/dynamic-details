@extends('layouts.dashboard')

@section('title', 'Jobs')
@section('page-title', 'Job Master')
@section('eyebrow', 'Employee Master')

@section('content')
    @php($isEditing = $editJob->exists)
    @php($hasFilters = request()->filled('search') || request()->filled('status'))

    <section class="dashboard-panel brand-create-panel">
        <div class="panel-heading"><div><p>Job master</p><h2>{{ $isEditing ? 'Edit Job' : 'Add Job' }}</h2></div></div>
        <form class="settings-form brand-create-form" method="POST" action="{{ $isEditing ? route('employee-jobs.update', $editJob) : route('employee-jobs.store') }}">
            @csrf
            @if($isEditing)
                @method('PUT')
            @endif
            <label class="form-field brand-name-field"><span>Job Title</span><input name="name" value="{{ $isEditing ? ($errors->any() ? old('name', $editJob->name) : $editJob->name) : old('name') }}" placeholder="Enter job title">@error('name')<small>{{ $message }}</small>@enderror</label>
            <div class="form-actions brand-create-actions">
                @if($isEditing)
                    <a class="btn btn-secondary" href="{{ route('employee-jobs.index') }}">Cancel</a>
                @endif
                <button class="btn btn-primary" type="submit">{{ $isEditing ? 'Update' : 'Save' }}</button>
            </div>
        </form>
    </section>

    <section class="dashboard-panel client-listing-panel {{ $hasFilters ? 'is-open' : '' }}" data-listing-filter>
        <div class="panel-heading">
            <label class="client-search listing-global-search"><x-dashboard.icon name="search" /><input form="job-filter-form" value="{{ request('search') }}" placeholder="Search all columns..." data-auto-filter-control data-filter-proxy="search"></label>
            <div class="button-row">
                <button class="btn btn-secondary action-icon-btn action-icon-neutral" type="button" aria-label="Filters" data-tooltip="Filters" data-filter-toggle aria-expanded="{{ $hasFilters ? 'true' : 'false' }}"><x-dashboard.icon name="funnel" /></button>
                @if ($hasFilters)
                    <a class="btn btn-secondary action-icon-btn action-icon-neutral" href="{{ route('employee-jobs.index') }}" aria-label="Reset Filter" data-tooltip="Reset Filter"><x-dashboard.icon name="x" /></a>
                @endif
            </div>
        </div>
        <form id="job-filter-form" class="client-toolbar listing-filter-fields" method="GET" data-filter-panel data-auto-filter-form @unless($hasFilters) hidden @endunless>
            <span class="filter-label">Filter by:</span>
            <label class="client-search"><input name="search" value="{{ request('search') }}" placeholder="Job Title"></label>
            <select name="status" aria-label="Filter by status">
                <option value="">All Status</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>
        </form>
        <div class="responsive-table">
            <table class="advanced-table">
                <thead><tr><th>Job Title</th><th>Employees</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse($jobs as $job)
                        <tr>
                            <td><div class="client-person"><span>{{ strtoupper(substr($job->name, 0, 2)) }}</span><div><strong>{{ $job->name }}</strong><small>Job master</small></div></div></td>
                            <td>{{ $job->employees_count }}</td>
                            <td><span class="status-badge status-{{ $job->is_active ? 'active' : 'inactive' }}">{{ $job->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td>
                                <div class="table-action-row">
                                    <form method="POST" action="{{ route('employee-jobs.status', $job) }}">@csrf @method('PATCH')<button class="status-toggle {{ $job->is_active ? 'is-active' : '' }}" type="submit" aria-label="{{ $job->is_active ? 'Deactivate' : 'Activate' }} {{ $job->name }}" data-tooltip="{{ $job->is_active ? 'Deactivate' : 'Activate' }}"><span></span></button></form>
                                    <a class="btn btn-sm btn-outline table-action-btn action-icon-btn action-icon-edit" href="{{ route('employee-jobs.edit', $job) }}" aria-label="Edit {{ $job->name }}" data-tooltip="Edit"><x-dashboard.icon name="edit" /></a>
                                    <form method="POST" action="{{ route('employee-jobs.destroy', $job) }}" data-confirm-delete>@csrf @method('DELETE')<button class="btn btn-sm btn-danger table-action-btn action-icon-btn action-icon-delete" type="submit" aria-label="Delete {{ $job->name }}" data-tooltip="Delete"><x-dashboard.icon name="trash" /></button></form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="table-empty" colspan="4">No jobs found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-dashboard.pagination :paginator="$jobs" form-id="job-filter-form" label="item(s)" />
    </section>
@endsection
