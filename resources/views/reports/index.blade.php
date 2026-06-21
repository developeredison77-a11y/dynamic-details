@extends('layouts.dashboard')

@section('title', 'Reports')
@section('page-title', 'Reports')
@section('eyebrow', 'Analytics')

@section('content')
    @php($canExport = auth()->user()?->canAccess('reports.export'))

    <section class="report-center-hero dashboard-panel">
        <div>
            <span>Report Center</span>
            <h2>Operational reports and exports</h2>
            <p>Review asset allocation, employee records, handover activity, return documents, and declaration status from one organized workspace.</p>
        </div>
        @if ($canExport)
            <a class="btn btn-primary report-hero-cta" href="{{ route('reports.pdf', 'overview') }}" target="_blank"><x-dashboard.icon name="download" /><span>Download Overview PDF</span></a>
        @endif
    </section>

    <section class="report-summary-grid">
        <article class="metric-card"><span>Total Assets</span><strong>{{ $summary['assets'] }}</strong><small>Inventory records</small></article>
        <article class="metric-card"><span>Employees</span><strong>{{ $summary['employees'] }}</strong><small>Employee master</small></article>
        <article class="metric-card"><span>Active Handovers</span><strong>{{ $summary['activeHandovers'] }}</strong><small>Currently assigned</small></article>
        <article class="metric-card"><span>Returns</span><strong>{{ $summary['returns'] }}</strong><small>{{ $summary['signedReturns'] }} signed copies</small></article>
        <article class="metric-card"><span>Declarations</span><strong>{{ $summary['declarations'] }}</strong><small>Generated forms</small></article>
    </section>

    @if ($canExport)
        <section class="dashboard-panel report-download-panel">
            <div class="panel-heading">
                <div>
                    <p>Downloads</p>
                    <h2>Report Library</h2>
                </div>
            </div>
            <div class="report-download-grid">
                @foreach ($reportTypes as $type => $title)
                    <article class="report-download-card">
                        <div>
                            <span>{{ $type === 'overview' ? 'Summary' : 'Detailed' }}</span>
                            <strong>{{ $title }}</strong>
                            <small>{{ $type === 'overview' ? 'PDF snapshot of key metrics' : 'Export complete records for this module' }}</small>
                        </div>
                        <div class="button-row">
                            @if ($type !== 'overview')
                                <a class="btn btn-secondary action-icon-btn action-icon-neutral" href="{{ route('reports.export', $type) }}" aria-label="Download {{ $title }} CSV" data-tooltip="CSV"><x-dashboard.icon name="file-csv" /></a>
                            @endif
                            <a class="btn btn-primary action-icon-btn action-icon-neutral" href="{{ route('reports.pdf', $type) }}" target="_blank" aria-label="Download {{ $title }} PDF" data-tooltip="PDF"><x-dashboard.icon name="download" /></a>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <section class="report-insight-grid">
        <article class="dashboard-panel">
            <div class="panel-heading"><div><p>Asset Status</p><h2>Inventory Health</h2></div></div>
            <div class="report-status-list">
                @forelse($assetsByStatus as $status => $total)
                    <div><span class="status-badge status-{{ $status }}">{{ ucfirst($status) }}</span><strong>{{ $total }}</strong></div>
                @empty
                    <div class="empty-state">No asset status data.</div>
                @endforelse
            </div>
        </article>
        <article class="dashboard-panel">
            <div class="panel-heading"><div><p>Employee Status</p><h2>Workforce Snapshot</h2></div></div>
            <div class="report-status-list">
                @forelse($employeesByStatus as $status => $total)
                    <div><span class="status-badge status-{{ $status }}">{{ ucfirst($status) }}</span><strong>{{ $total }}</strong></div>
                @empty
                    <div class="empty-state">No employee status data.</div>
                @endforelse
            </div>
        </article>
    </section>

    <section class="dashboard-panel report-section-panel">
        <div class="panel-heading">
            <div><p>Asset Report</p><h2>Current Allocation</h2></div>
            @if ($canExport)<a class="btn btn-secondary" href="{{ route('reports.pdf', 'assets') }}" target="_blank">PDF</a>@endif
        </div>
        <div class="responsive-table">
            <table class="advanced-table">
                <thead><tr><th>Asset</th><th>Category</th><th>Brand</th><th>Status</th><th>Assigned To</th></tr></thead>
                <tbody>
                    @forelse($assets as $asset)
                        <tr><td><strong>{{ $asset->asset_tag }}</strong><br><small>{{ $asset->name }}</small></td><td>{{ $asset->category?->name }}</td><td>{{ $asset->brand?->name ?? '-' }}</td><td><span class="status-badge status-{{ $asset->status?->value }}">{{ $asset->status?->label() }}</span></td><td>{{ $asset->activeAssignment?->employee?->name_en ?? '-' }}</td></tr>
                    @empty
                        <tr><td class="table-empty" colspan="5">No asset data found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="report-two-column">
        <article class="dashboard-panel report-section-panel">
            <div class="panel-heading"><div><p>Employees</p><h2>Recent Records</h2></div>@if ($canExport)<a class="btn btn-secondary" href="{{ route('reports.pdf', 'employees') }}" target="_blank">PDF</a>@endif</div>
            <div class="responsive-table"><table class="advanced-table"><thead><tr><th>Employee</th><th>Role</th><th>Status</th></tr></thead><tbody>
                @forelse($employees as $employee)
                    <tr><td><strong>{{ $employee->name_en }}</strong><br><small>{{ $employee->employee_code }}</small></td><td>{{ $employee->role?->name ?? $employee->designation ?? '-' }}</td><td><span class="status-badge status-{{ $employee->status?->value }}">{{ $employee->status?->label() }}</span></td></tr>
                @empty
                    <tr><td class="table-empty" colspan="3">No employee data found.</td></tr>
                @endforelse
            </tbody></table></div>
        </article>

        <article class="dashboard-panel report-section-panel">
            <div class="panel-heading"><div><p>Declarations</p><h2>Document Status</h2></div>@if ($canExport)<a class="btn btn-secondary" href="{{ route('reports.pdf', 'declarations') }}" target="_blank">PDF</a>@endif</div>
            <div class="responsive-table"><table class="advanced-table"><thead><tr><th>No</th><th>Asset</th><th>Signed</th></tr></thead><tbody>
                @forelse($declarations as $declaration)
                    <tr><td><strong>{{ $declaration->declaration_number }}</strong><br><small>{{ $declaration->issued_at?->format('M d, Y') }}</small></td><td>{{ $declaration->assignment?->asset?->asset_tag }}</td><td>{{ $declaration->signed_file_path ? 'Uploaded' : 'Pending' }}</td></tr>
                @empty
                    <tr><td class="table-empty" colspan="3">No declaration data found.</td></tr>
                @endforelse
            </tbody></table></div>
        </article>
    </section>

    <section class="report-two-column">
        <article class="dashboard-panel report-section-panel">
            <div class="panel-heading"><div><p>Handovers</p><h2>Recent Activity</h2></div>@if ($canExport)<a class="btn btn-secondary" href="{{ route('reports.pdf', 'handovers') }}" target="_blank">PDF</a>@endif</div>
            <div class="responsive-table"><table class="advanced-table"><thead><tr><th>Asset</th><th>Employee</th><th>Status</th></tr></thead><tbody>
                @forelse($handovers as $assignment)
                    <tr><td><strong>{{ $assignment->asset?->asset_tag }}</strong><br><small>{{ $assignment->handover_date?->format('M d, Y') }}</small></td><td>{{ $assignment->employee?->name_en }}</td><td><span class="status-badge status-{{ $assignment->status?->value }}">{{ $assignment->status?->label() }}</span></td></tr>
                @empty
                    <tr><td class="table-empty" colspan="3">No handover data found.</td></tr>
                @endforelse
            </tbody></table></div>
        </article>

        <article class="dashboard-panel report-section-panel">
            <div class="panel-heading"><div><p>Returns</p><h2>Recent Closures</h2></div>@if ($canExport)<a class="btn btn-secondary" href="{{ route('reports.pdf', 'returns') }}" target="_blank">PDF</a>@endif</div>
            <div class="responsive-table"><table class="advanced-table"><thead><tr><th>Asset</th><th>Employee</th><th>Condition</th></tr></thead><tbody>
                @forelse($returns as $return)
                    <tr><td><strong>{{ $return->asset?->asset_tag }}</strong><br><small>{{ $return->returned_at?->format('M d, Y') }}</small></td><td>{{ $return->employee?->name_en }}</td><td>{{ $return->condition?->label() }}</td></tr>
                @empty
                    <tr><td class="table-empty" colspan="3">No return data found.</td></tr>
                @endforelse
            </tbody></table></div>
        </article>
    </section>
@endsection
