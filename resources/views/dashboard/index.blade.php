@extends('layouts.dashboard')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('eyebrow', 'Asset overview')

@php
    $metrics = [
        ['label' => 'Total Assets', 'value' => $assetStats['total'], 'note' => 'All registered company assets', 'tone' => 'good', 'icon' => 'pages'],
        ['label' => 'Assigned Assets', 'value' => $assetStats['assigned'], 'note' => 'Currently with employees', 'tone' => 'warn', 'icon' => 'users'],
        ['label' => 'Available Assets', 'value' => $assetStats['available'], 'note' => 'Ready for handover', 'tone' => 'good', 'icon' => 'dashboard'],
        ['label' => 'Returned Assets', 'value' => $assetStats['returned'], 'note' => 'Returned and recorded', 'tone' => 'info', 'icon' => 'settings'],
    ];
    $maxHandover = max(1, $handoverTrend->max('value') ?: 1);
    $maxReturn = max(1, $returnTrend->max('value') ?: 1);
@endphp

@section('content')
    <section class="analytics-grid">
        @foreach ($metrics as $metric)
            <article class="analytics-card">
                <div class="analytics-card-top">
                    <span class="metric-icon"><x-dashboard.icon :name="$metric['icon']" /></span>
                    <em class="trend-pill trend-{{ $metric['tone'] }}">{{ $metric['value'] }}</em>
                </div>
                <div class="analytics-card-main">
                    <span>{{ $metric['label'] }}</span>
                    <strong>{{ $metric['value'] }}</strong>
                </div>
                <div class="analytics-card-footer">
                    <small>{{ $metric['note'] }}</small>
                </div>
            </article>
        @endforeach
    </section>

    <section class="dashboard-analytics-layout">
        <article class="dashboard-panel chart-panel">
            <div class="panel-heading analytics-heading">
                <div>
                    <p>Employees</p>
                    <h2>Employee Statistics</h2>
                </div>
            </div>
            <div class="queue-list">
                <div class="queue-item queue-good"><span>Active Employees</span><strong>{{ $employeeStats['active'] }}</strong></div>
                <div class="queue-item queue-info"><span>Leave Employees</span><strong>{{ $employeeStats['leave'] }}</strong></div>
                <div class="queue-item queue-warn"><span>Resigned Employees</span><strong>{{ $employeeStats['resigned'] }}</strong></div>
            </div>
        </article>

        <article class="dashboard-panel chart-panel">
            <div class="panel-heading analytics-heading">
                <div>
                    <p>Exports</p>
                    <h2>Reports</h2>
                </div>
                <a href="{{ route('reports.index') }}" class="btn btn-primary">Open Reports</a>
            </div>
            <div class="queue-list">
                <a class="queue-item report-link" href="{{ route('reports.export', 'assets') }}"><span>Asset Report CSV</span><strong>CSV</strong></a>
                <a class="queue-item report-link" href="{{ route('reports.export', 'employees') }}"><span>Employee Report CSV</span><strong>CSV</strong></a>
                <a class="queue-item report-link" href="{{ route('reports.export', 'handovers') }}"><span>Handover Report CSV</span><strong>CSV</strong></a>
            </div>
        </article>
    </section>

    <section class="dashboard-intel-grid">
        <article class="dashboard-panel insight-panel">
            <div class="panel-heading analytics-heading">
                <div>
                    <p>Categories</p>
                    <h2>Asset Category Wise</h2>
                </div>
            </div>
            <div class="channel-list">
                @forelse ($categoryStats as $row)
                    <div class="channel-row">
                        <div><span>{{ $row->label }}</span><strong>{{ $row->value }}</strong></div>
                        <i><b style="width: {{ min(100, $assetStats['total'] ? ($row->value / $assetStats['total']) * 100 : 0) }}%"></b></i>
                    </div>
                @empty
                    <div class="empty-state">No category data yet.</div>
                @endforelse
            </div>
        </article>

        <article class="dashboard-panel insight-panel">
            <div class="panel-heading analytics-heading">
                <div>
                    <p>Brands</p>
                    <h2>Asset Brand Wise</h2>
                </div>
            </div>
            <div class="channel-list">
                @forelse ($brandStats as $row)
                    <div class="channel-row">
                        <div><span>{{ $row->label }}</span><strong>{{ $row->value }}</strong></div>
                        <i><b style="width: {{ min(100, $assetStats['total'] ? ($row->value / $assetStats['total']) * 100 : 0) }}%"></b></i>
                    </div>
                @empty
                    <div class="empty-state">No brand data yet.</div>
                @endforelse
            </div>
        </article>

        <article class="dashboard-panel insight-panel">
            <div class="panel-heading analytics-heading">
                <div>
                    <p>Recent</p>
                    <h2>Latest Handovers</h2>
                </div>
            </div>
            <div class="activity-list">
                @forelse ($recentHandovers as $assignment)
                    <div class="activity-item">
                        <span></span>
                        <div>
                            <strong>{{ $assignment->asset?->asset_tag }} to {{ $assignment->employee?->name_en }}</strong>
                            <small>{{ $assignment->handover_date?->format('M d, Y') }} - {{ $assignment->status?->label() }}</small>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">No handovers yet.</div>
                @endforelse
            </div>
        </article>
    </section>

    <section class="dashboard-analytics-layout">
        <article class="dashboard-panel chart-panel">
            <div class="panel-heading analytics-heading"><div><p>Trend</p><h2>Monthly Handover Chart</h2></div></div>
            <div class="bar-chart">
                @foreach ($handoverTrend as $row)
                    <div><i style="height: {{ max(8, ($row['value'] / $maxHandover) * 140) }}px"></i><span>{{ $row['label'] }}</span><strong>{{ $row['value'] }}</strong></div>
                @endforeach
            </div>
        </article>

        <article class="dashboard-panel chart-panel">
            <div class="panel-heading analytics-heading"><div><p>Trend</p><h2>Monthly Return Chart</h2></div></div>
            <div class="bar-chart">
                @foreach ($returnTrend as $row)
                    <div><i style="height: {{ max(8, ($row['value'] / $maxReturn) * 140) }}px"></i><span>{{ $row['label'] }}</span><strong>{{ $row['value'] }}</strong></div>
                @endforeach
            </div>
        </article>
    </section>
@endsection
