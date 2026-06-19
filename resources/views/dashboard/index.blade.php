@extends('layouts.dashboard')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('eyebrow', 'Asset overview')

@php
    $metrics = [
        ['label' => 'Total Assets', 'value' => $assetStats['total'], 'note' => 'All registered company assets', 'tone' => 'good', 'icon' => 'pages', 'href' => route('assets.index')],
        ['label' => 'Total Employees', 'value' => $employeeTotal, 'note' => 'All employee records', 'tone' => 'info', 'icon' => 'users', 'href' => route('employees.index')],
        ['label' => 'Total Brands', 'value' => $brandTotal, 'note' => 'Asset brand master records', 'tone' => 'warn', 'icon' => 'tag', 'href' => route('asset-brands.index')],
        ['label' => 'Total Categories', 'value' => $categoryTotal, 'note' => 'Asset category master records', 'tone' => 'good', 'icon' => 'folder', 'href' => route('asset-categories.index')],
    ];
    $maxHandover = max(1, $handoverTrend->max('value') ?: 1);
    $maxReturn = max(1, $returnTrend->max('value') ?: 1);
    $statusTotal = $assetStats['total'];
    $runningStatusTotal = 0;
    $assetStatusChartStops = $assetStatusStats
        ->filter(fn (array $row): bool => $row['count'] > 0)
        ->map(function (array $row) use (&$runningStatusTotal, $statusTotal): string {
            $start = $statusTotal > 0 ? ($runningStatusTotal / $statusTotal) * 100 : 0;
            $runningStatusTotal += $row['count'];
            $end = $statusTotal > 0 ? ($runningStatusTotal / $statusTotal) * 100 : 0;

            return "{$row['color']} " . number_format($start, 4, '.', '') . "% " . number_format($end, 4, '.', '') . '%';
        })
        ->implode(', ');
    $assetStatusChartBackground = $statusTotal > 0 ? "conic-gradient({$assetStatusChartStops})" : 'conic-gradient(var(--surface-soft) 0 100%)';
@endphp

@section('content')
    <section class="analytics-grid">
        @foreach ($metrics as $metric)
            <a class="analytics-card analytics-card-link" href="{{ $metric['href'] }}">
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
            </a>
        @endforeach
    </section>

    <section class="dashboard-analytics-layout dashboard-overview-layout">
        <article class="asset-status-panel">
            <div class="asset-status-header">
                <div class="asset-status-title">
                    <span>Assets</span>
                    <h2>Asset Status Distribution</h2>
                    <p>Live asset counts grouped by current inventory status</p>
                </div>
                <div class="asset-status-total" aria-label="Total assets">
                    <span>Total Assets</span>
                    <strong>{{ $statusTotal }}</strong>
                </div>
            </div>

            <div class="asset-status-content">
                <div class="asset-status-chart-wrap">
                    <div class="asset-status-chart" style="--asset-status-chart: {{ $assetStatusChartBackground }};" role="img" aria-label="Asset status distribution chart">
                        <div>
                            <strong>{{ $statusTotal }}</strong>
                            <span>Assets</span>
                        </div>
                    </div>
                </div>

                <div class="asset-status-legend" aria-label="Asset status legend">
                    @foreach ($assetStatusStats as $row)
                        <div class="asset-status-row">
                            <span class="asset-status-name">
                                <i style="--status-color: {{ $row['color'] }}"></i>
                                {{ $row['name'] }}
                            </span>
                            <strong>{{ $row['count'] }}</strong>
                            <em>{{ number_format($row['percentage'], $row['percentage'] == floor($row['percentage']) ? 0 : 1) }}%</em>
                        </div>
                    @endforeach
                </div>
            </div>

            @if ($statusTotal === 0)
                <div class="asset-status-empty">Asset status distribution will appear once assets are added.</div>
            @endif
        </article>

        <article class="dashboard-panel chart-panel reports-panel">
            <div class="reports-panel-content">
                <div class="panel-heading analytics-heading reports-heading">
                    <div class="reports-title">
                        <span class="reports-kicker">
                            <x-dashboard.icon name="pages" />
                            Exports
                        </span>
                        <h2>Reports</h2>
                        <p>Download and manage export reports</p>
                    </div>
                    <a href="{{ route('reports.index') }}" class="btn btn-primary reports-open-button action-icon-btn action-icon-neutral" aria-label="Open Reports" data-tooltip="Open Reports">
                        <x-dashboard.icon name="download" />
                    </a>
                </div>
                <div class="reports-export-list">
                    <a class="report-export-card" href="{{ route('reports.export', 'assets') }}">
                        <span class="report-export-icon"><x-dashboard.icon name="file-csv" /></span>
                        <span class="report-export-copy">
                            <span>Asset Report CSV</span>
                            <small>Asset inventory export</small>
                        </span>
                        <strong>CSV</strong>
                        <span class="report-export-arrow"><x-dashboard.icon name="chevron-right" /></span>
                    </a>
                    <a class="report-export-card" href="{{ route('reports.export', 'employees') }}">
                        <span class="report-export-icon"><x-dashboard.icon name="file-csv" /></span>
                        <span class="report-export-copy">
                            <span>Employee Report CSV</span>
                            <small>Employee records export</small>
                        </span>
                        <strong>CSV</strong>
                        <span class="report-export-arrow"><x-dashboard.icon name="chevron-right" /></span>
                    </a>
                    <a class="report-export-card" href="{{ route('reports.export', 'handovers') }}">
                        <span class="report-export-icon"><x-dashboard.icon name="file-csv" /></span>
                        <span class="report-export-copy">
                            <span>Handover Report CSV</span>
                            <small>Asset handover export</small>
                        </span>
                        <strong>CSV</strong>
                        <span class="report-export-arrow"><x-dashboard.icon name="chevron-right" /></span>
                    </a>
                </div>
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
