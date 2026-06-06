@extends('layouts.dashboard')

@section('title', 'Reports')
@section('page-title', 'Reports')
@section('eyebrow', 'Dashboard')

@section('content')
    <section class="analytics-grid">
        <a class="analytics-card report-link" href="{{ route('reports.export', 'assets') }}"><div class="analytics-card-main"><span>Export</span><strong>Assets</strong></div><div class="analytics-card-footer"><small>CSV / Excel compatible</small></div></a>
        <a class="analytics-card report-link" href="{{ route('reports.export', 'employees') }}"><div class="analytics-card-main"><span>Export</span><strong>Employees</strong></div><div class="analytics-card-footer"><small>CSV / Excel compatible</small></div></a>
        <a class="analytics-card report-link" href="{{ route('reports.export', 'handovers') }}"><div class="analytics-card-main"><span>Export</span><strong>Handovers</strong></div><div class="analytics-card-footer"><small>CSV / Excel compatible</small></div></a>
        <button class="analytics-card report-link print-card" onclick="window.print()"><div class="analytics-card-main"><span>Export</span><strong>PDF</strong></div><div class="analytics-card-footer"><small>Print current report</small></div></button>
    </section>
    <section class="dashboard-panel">
        <div class="panel-heading"><div><p>Asset report</p><h2>Current Allocation</h2></div></div>
        <div class="responsive-table"><table class="advanced-table"><thead><tr><th>Asset</th><th>Category</th><th>Brand</th><th>Status</th><th>Employee</th></tr></thead><tbody>
            @forelse($assets as $asset)<tr><td>{{ $asset->asset_tag }} - {{ $asset->name }}</td><td>{{ $asset->category?->name }}</td><td>{{ $asset->brand?->name ?? '-' }}</td><td>{{ $asset->status?->label() }}</td><td>{{ $asset->activeAssignment?->employee?->name_en ?? '-' }}</td></tr>@empty<tr><td class="table-empty" colspan="5">No data found.</td></tr>@endforelse
        </tbody></table></div>
    </section>
@endsection
