@extends('layouts.dashboard')

@section('title', 'Employee Details')
@section('page-title', 'Employee Details')
@section('eyebrow', 'Employee Master')

@section('page-actions')
    <a href="{{ route('employees.index') }}" class="btn btn-secondary action-icon-btn action-icon-neutral" aria-label="Back to employees" data-tooltip="Back">
        <x-dashboard.icon name="chevron-left" />
    </a>
    @if (auth()->user()?->canAccess('employees.update'))
        <a href="{{ route('employees.edit', $employee) }}" class="btn btn-secondary action-icon-btn action-icon-edit" aria-label="Edit {{ $employee->name_en }}" data-tooltip="Edit">
            <x-dashboard.icon name="edit" />
        </a>
    @endif
@endsection

@section('content')
    @php
        $declarationDocument = $employee->declarationDocument;
        $statusChangedAt = $employee->status_changed_at ?: $employee->updated_at;
    @endphp

    <section class="dashboard-panel employee-detail-panel">
        <div class="employee-detail-hero">
            <div class="employee-detail-avatar">{{ strtoupper(substr($employee->name_en, 0, 2)) }}</div>
            <div class="handover-identity employee-detail-identity">
                <span class="handover-kicker">{{ $employee->employee_code }}</span>
                <h2>{{ $employee->name_en }}</h2>
                <p>{{ $employee->employeeJob?->name ?? $employee->designation ?? '-' }}{{ $employee->employeeDepartment?->name || $employee->department ? ' / '.($employee->employeeDepartment?->name ?? $employee->department) : '' }}</p>
            </div>
            <div class="handover-actions button-row">
                <span class="status-badge status-{{ $employee->status?->value }}">{{ $employee->status?->label() }}</span>
                <a class="btn btn-secondary action-icon-btn action-icon-view" href="{{ route('employees.handover-report', $employee) }}" target="_blank" aria-label="Open handover report" data-tooltip="Handover Report">
                    <x-dashboard.icon name="clipboard-list" />
                </a>
                <a class="btn btn-secondary action-icon-btn action-icon-neutral" href="{{ route('employees.declaration-form.print', $employee) }}" target="_blank" aria-label="Download declaration form" data-tooltip="Declaration Form">
                    <x-dashboard.icon name="file-text" />
                </a>
            </div>
        </div>

        <div class="handover-status-strip employee-status-strip">
            <div>
                <span>Active Assets</span>
                <strong>{{ $employee->active_assignments_count }}</strong>
            </div>
            <div>
                <span>Total Handovers</span>
                <strong>{{ $employee->assignments_count }}</strong>
            </div>
            <div>
                <span>Returned Assets</span>
                <strong>{{ $employee->returned_assignments_count }}</strong>
            </div>
            <div>
                <span>Filled Form</span>
                <strong>{{ $declarationDocument ? 'Uploaded' : 'Pending' }}</strong>
                <small>{{ $declarationDocument?->uploaded_at?->format('M d, Y') ?? '-' }}</small>
            </div>
        </div>

        <div class="handover-detail-layout employee-detail-layout">
            <article class="handover-info-card">
                <div class="handover-card-heading">
                    <span>Identity</span>
                    <strong>{{ $employee->name_en }}</strong>
                </div>
                <dl>
                    <div><dt>EID No</dt><dd>{{ $employee->eid ?: '-' }}</dd></div>
                    <div><dt>Arabic Name</dt><dd dir="rtl">{{ $employee->name_ar ?: '-' }}</dd></div>
                    <div><dt>Nationality</dt><dd>{{ $employee->nationality ?: '-' }}</dd></div>
                    <div><dt>Status Since</dt><dd>{{ $statusChangedAt?->format('M d, Y') ?? '-' }}</dd></div>
                </dl>
            </article>

            <article class="handover-info-card">
                <div class="handover-card-heading">
                    <span>Organization</span>
                    <strong>{{ $employee->entity ?: '-' }}</strong>
                </div>
                <dl>
                    <div><dt>Department</dt><dd>{{ $employee->employeeDepartment?->name ?? $employee->department ?? '-' }}</dd></div>
                    <div><dt>Job Title</dt><dd>{{ $employee->employeeJob?->name ?? $employee->designation ?? '-' }}</dd></div>
                    <div><dt>Role</dt><dd>{{ $employee->role?->name ?? '-' }}</dd></div>
                    <div><dt>Joined At</dt><dd>{{ $employee->joined_at?->format('M d, Y') ?? '-' }}</dd></div>
                </dl>
            </article>

            <article class="handover-info-card">
                <div class="handover-card-heading">
                    <span>Contact</span>
                    <strong>{{ $employee->email ?: 'No email' }}</strong>
                </div>
                <dl>
                    <div><dt>Email</dt><dd>{{ $employee->email ?: '-' }}</dd></div>
                    <div><dt>Phone</dt><dd>{{ $employee->phone ?: '-' }}</dd></div>
                    <div><dt>Created</dt><dd>{{ $employee->created_at?->format('M d, Y') ?? '-' }}</dd></div>
                    <div><dt>Updated</dt><dd>{{ $employee->updated_at?->format('M d, Y') ?? '-' }}</dd></div>
                </dl>
            </article>
        </div>

        <div class="employee-detail-grid">
            <article class="handover-info-card employee-notes-card">
                <div class="handover-card-heading">
                    <span>Notes</span>
                    <strong>Internal Record</strong>
                </div>
                <p>{{ $employee->notes ?: 'No notes recorded for this employee.' }}</p>
            </article>

            <article class="handover-info-card employee-document-card">
                <div class="handover-card-heading">
                    <span>Document</span>
                    <strong>{{ $declarationDocument ? $declarationDocument->original_name : 'Not uploaded' }}</strong>
                </div>
                <dl>
                    <div><dt>Status</dt><dd><span class="status-badge status-{{ $declarationDocument ? 'active' : 'pending' }}">{{ $declarationDocument ? 'Uploaded' : 'Pending' }}</span></dd></div>
                    <div><dt>Uploaded At</dt><dd>{{ $declarationDocument?->uploaded_at?->format('M d, Y') ?? '-' }}</dd></div>
                    <div><dt>File Size</dt><dd>{{ $declarationDocument ? number_format($declarationDocument->file_size / 1024, 1).' KB' : '-' }}</dd></div>
                    <div><dt>File Type</dt><dd>{{ $declarationDocument?->mime_type ?? '-' }}</dd></div>
                </dl>
            </article>
        </div>
    </section>

    <section class="dashboard-panel employee-assets-panel">
        <div class="panel-heading">
            <div>
                <p>Asset movement</p>
                <h2>Recent Handovers</h2>
            </div>
        </div>
        <div class="responsive-table">
            <table class="advanced-table">
                <thead><tr><th>Asset</th><th>Category</th><th>Brand</th><th>Handover Date</th><th>Expected Return</th><th>Status</th><th>Condition</th></tr></thead>
                <tbody>
                    @forelse($assignments as $assignment)
                        <tr>
                            <td><strong>{{ $assignment->asset?->asset_tag ?? '-' }}</strong><br><small>{{ $assignment->asset?->name ?? '-' }}{{ $assignment->asset?->model ? ' / '.$assignment->asset?->model : '' }}</small></td>
                            <td>{{ $assignment->asset?->category?->name ?? '-' }}</td>
                            <td>{{ $assignment->asset?->brand?->name ?? '-' }}</td>
                            <td>{{ $assignment->handover_date?->format('M d, Y') ?? '-' }}</td>
                            <td>{{ $assignment->expected_return_date?->format('M d, Y') ?? '-' }}</td>
                            <td><span class="status-badge status-{{ $assignment->status?->value }}">{{ $assignment->status?->label() }}</span></td>
                            <td><span class="status-badge status-{{ $assignment->asset?->condition?->value ?? 'inactive' }}">{{ $assignment->asset?->condition?->label() ?? '-' }}</span></td>
                        </tr>
                    @empty
                        <tr><td class="table-empty" colspan="7">No asset handovers found for this employee.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
