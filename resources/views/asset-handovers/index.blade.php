@extends('layouts.dashboard')

@section('title', 'Asset Handovers')
@section('page-title', 'Asset Handover')
@section('eyebrow', 'Assignments')

@section('content')
    <section class="dashboard-panel">
        <div class="panel-heading">
            <div><p>Handover history</p><h2>Asset Assignments</h2></div>
            <a href="{{ route('asset-handovers.create') }}" class="btn btn-primary btn-lg">New Handover</a>
        </div>
        <div class="responsive-table">
            <table class="advanced-table">
                <thead><tr><th>Employee</th><th>Asset</th><th>Handover Date</th><th>Expected Return</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse($assignments as $assignment)
                        <tr>
                            <td>{{ $assignment->employee?->name_en }}<br><small>{{ $assignment->employee?->employee_code }}</small></td>
                            <td>{{ $assignment->asset?->asset_tag }}<br><small>{{ $assignment->asset?->name }}</small></td>
                            <td>{{ $assignment->handover_date?->format('M d, Y') }}</td>
                            <td>{{ $assignment->expected_return_date?->format('M d, Y') ?: '-' }}</td>
                            <td><span class="status-badge status-{{ $assignment->status->value }}">{{ $assignment->status->label() }}</span></td>
                            <td><a class="btn btn-sm btn-outline" href="{{ route('asset-handovers.show', $assignment) }}">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No handovers recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="table-footer">{{ $assignments->links() }}</div>
    </section>
@endsection
