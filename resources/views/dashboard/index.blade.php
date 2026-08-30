@extends('layouts.dashboard')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('eyebrow', 'Asset overview')

@php
    $metrics = [
        ['label' => 'Total Assets', 'value' => $assetStats['total'], 'note' => 'All registered company assets', 'tone' => 'good', 'color' => 'assets', 'icon' => 'pages', 'href' => route('assets.index'), 'permission' => 'assets.view'],
        ['label' => 'Total Employees', 'value' => $employeeTotal, 'note' => 'All employee records', 'tone' => 'info', 'color' => 'employees', 'icon' => 'users', 'href' => route('employees.index'), 'permission' => 'employees.view'],
        ['label' => 'Total Brands', 'value' => $brandTotal, 'note' => 'Asset brand master records', 'tone' => 'warn', 'color' => 'brands', 'icon' => 'tag', 'href' => route('asset-brands.index'), 'permission' => 'asset-brands.manage'],
        ['label' => 'Total Categories', 'value' => $categoryTotal, 'note' => 'Asset category master records', 'tone' => 'good', 'color' => 'categories', 'icon' => 'folder', 'href' => route('asset-categories.index'), 'permission' => 'asset-categories.manage'],
    ];
    $hardwareDistributionMaxRaw = max(1, (int) ($hardwareDistribution->max('value') ?: 1));
    $hardwareDistributionStep = max(1, (int) ceil($hardwareDistributionMaxRaw / 5));
    $hardwareDistributionAxisMax = $hardwareDistributionStep * 5;
    $hardwareDistributionTicks = collect(range(0, 5))->map(fn (int $tick): int => $hardwareDistributionStep * $tick);
    $statusTotal = $assetStats['total'];
    $assetStatusChartRows = $assetStatusStats
        ->filter(fn (array $row): bool => $row['count'] > 0)
        ->sortByDesc('count')
        ->values();
    $formatChartNumber = static fn (float $value): string => rtrim(rtrim(number_format($value, 3, '.', ''), '0'), '.');
    $formatPercentage = static fn (float $value): string => number_format($value, $value == floor($value) ? 0 : 1);
    $fitChartLabel = static fn (string $value, int $limit = 18): string => \Illuminate\Support\Str::limit($value, $limit, '...');
    $chartCenterX = 280.0;
    $chartCenterY = 142.0;
    $chartOuterRx = 136.0;
    $chartOuterRy = 86.0;
    $chartInnerRx = 66.0;
    $chartInnerRy = 42.0;
    $chartDepth = 26.0;
    $chartPoint = static function (float $angle, float $rx, float $ry, float $cyOffset = 0) use ($chartCenterX, $chartCenterY, $formatChartNumber): array {
        $radians = deg2rad($angle);

        return [
            $formatChartNumber($chartCenterX + ($rx * cos($radians))),
            $formatChartNumber($chartCenterY + $cyOffset + ($ry * sin($radians))),
        ];
    };
    $chartSectorPath = static function (float $start, float $end, float $cyOffset = 0) use ($chartOuterRx, $chartOuterRy, $chartInnerRx, $chartInnerRy, $chartPoint, $formatChartNumber): string {
        [$outerStartX, $outerStartY] = $chartPoint($start, $chartOuterRx, $chartOuterRy, $cyOffset);
        [$outerEndX, $outerEndY] = $chartPoint($end, $chartOuterRx, $chartOuterRy, $cyOffset);
        [$innerEndX, $innerEndY] = $chartPoint($end, $chartInnerRx, $chartInnerRy, $cyOffset);
        [$innerStartX, $innerStartY] = $chartPoint($start, $chartInnerRx, $chartInnerRy, $cyOffset);
        $largeArc = abs($end - $start) > 180 ? 1 : 0;

        return 'M ' . $outerStartX . ' ' . $outerStartY
            . ' A ' . $formatChartNumber($chartOuterRx) . ' ' . $formatChartNumber($chartOuterRy) . ' 0 ' . $largeArc . ' 1 ' . $outerEndX . ' ' . $outerEndY
            . ' L ' . $innerEndX . ' ' . $innerEndY
            . ' A ' . $formatChartNumber($chartInnerRx) . ' ' . $formatChartNumber($chartInnerRy) . ' 0 ' . $largeArc . ' 0 ' . $innerStartX . ' ' . $innerStartY
            . ' Z';
    };
    $chartCursor = -90.0;
    $assetStatusSvgSegments = $statusTotal > 0
        ? $assetStatusChartRows
            ->map(function (array $row) use (&$chartCursor, $statusTotal, $chartSectorPath, $chartDepth, $chartPoint, $chartOuterRx, $chartOuterRy, $chartInnerRx, $chartInnerRy, $formatChartNumber, $formatPercentage, $fitChartLabel): array {
                $sliceDegrees = ($row['count'] / $statusTotal) * 360;
                $start = $chartCursor;
                $end = $chartCursor + $sliceDegrees;
                $gap = $sliceDegrees >= 16 ? 1.4 : ($sliceDegrees >= 6 ? 0.7 : 0);
                $visualStart = $start + ($gap / 2);
                $visualEnd = $end - ($gap / 2);
                $chartCursor = $end;

                if ($visualEnd <= $visualStart) {
                    $visualStart = $start;
                    $visualEnd = $end;
                }

                $midpoint = ($visualStart + $visualEnd) / 2;
                $isRightSide = cos(deg2rad($midpoint)) >= 0;
                [$calloutStartX, $calloutStartY] = $chartPoint($midpoint, $chartOuterRx + 3, $chartOuterRy + 3);
                [$calloutBendX, $calloutBendY] = $chartPoint($midpoint, $chartOuterRx + 24, $chartOuterRy + 18);
                $calloutEndX = (float) $calloutBendX + ($isRightSide ? 44 : -44);
                $calloutEndX = $isRightSide ? min($calloutEndX, 416.0) : max($calloutEndX, 144.0);
                $calloutEndY = (float) $calloutBendY;

                return [
                    ...$row,
                    'topPath' => $chartSectorPath($visualStart, $visualEnd),
                    'depthPath' => $chartSectorPath($visualStart, $visualEnd, $chartDepth),
                    'calloutPath' => 'M ' . $formatChartNumber($calloutEndX) . ' ' . $formatChartNumber($calloutEndY) . ' L ' . $calloutBendX . ' ' . $calloutBendY . ' L ' . $calloutStartX . ' ' . $calloutStartY,
                    'labelX' => $formatChartNumber($calloutEndX + ($isRightSide ? 6 : -6)),
                    'labelY' => $formatChartNumber($calloutEndY),
                    'labelAnchor' => $isRightSide ? 'start' : 'end',
                    'calloutLabel' => $fitChartLabel($row['name']),
                    'percentageLabel' => $formatPercentage($row['percentage']),
                ];
            })
        : collect();
@endphp

@section('content')
    <section class="analytics-grid">
        @foreach ($metrics as $metric)
            @php
                $canOpenMetric = auth()->user()?->canAccess($metric['permission']);
            @endphp
            @if ($canOpenMetric)
                <a class="analytics-card analytics-card-highlight analytics-card-{{ $metric['color'] }} analytics-card-link" href="{{ $metric['href'] }}">
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
            @else
                <div class="analytics-card analytics-card-highlight analytics-card-{{ $metric['color'] }}">
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
                </div>
            @endif
        @endforeach
    </section>

    <section class="dashboard-analytics-layout dashboard-top-insights-layout">
        <article class="dashboard-panel chart-panel hardware-distribution-panel">
            <div class="hardware-distribution-frame">
                <h2>Hardware Asset Distribution</h2>

                @if ($hardwareDistribution->isNotEmpty())
                    <div class="hardware-distribution-chart" style="--hardware-axis-max: {{ $hardwareDistributionAxisMax }}; --hardware-row-count: {{ max(1, $hardwareDistribution->count()) }};">
                        <div class="hardware-distribution-plot">
                            <div class="hardware-distribution-grid" aria-hidden="true">
                                @foreach ($hardwareDistributionTicks as $tick)
                                    <span style="--tick-position: {{ $loop->index * 20 }}%;"><i></i><em>{{ $tick }}</em></span>
                                @endforeach
                            </div>

                            <div class="hardware-distribution-rows">
                                @foreach ($hardwareDistribution as $row)
                                    @php
                                        $barWidth = $hardwareDistributionAxisMax > 0 ? min(100, ($row['value'] / $hardwareDistributionAxisMax) * 100) : 0;
                                    @endphp
                                    <div class="hardware-distribution-row" style="--hardware-bar-color: {{ $row['color'] }}; --hardware-bar-width: {{ $barWidth }}%;">
                                        <span class="hardware-distribution-label" title="{{ $row['label'] }}">{{ $row['label'] }}</span>
                                        <span class="hardware-distribution-bar">
                                            <i></i>
                                            <strong>{{ $row['value'] }}</strong>
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="hardware-distribution-axis-label">Unit Count <span aria-hidden="true">-&gt;</span></div>
                    </div>
                @else
                    <div class="hardware-distribution-empty">Hardware asset distribution will appear once asset categories are added.</div>
                @endif
            </div>
        </article>

        @if (auth()->user()?->canAccess('reports.view'))
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
                    @if (auth()->user()?->canAccess('reports.export'))
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
                    @endif
                </div>
            </article>
        @endif
    </section>

    <section class="dashboard-analytics-layout dashboard-asset-status-layout">
        <article class="asset-status-panel asset-status-panel-modern">
            <div class="asset-status-modern-header">
                <div>
                    <span>Inventory Health</span>
                    <h2>Asset Status Distribution <small>(Total: {{ $statusTotal }} Units)</small></h2>
                </div>
            </div>

            <div class="asset-status-summary-grid">
                <div class="asset-status-summary-card">
                    <span class="asset-status-summary-icon"><x-dashboard.icon name="pages" /></span>
                    <div>
                        <span>Total Assets</span>
                        <strong>{{ $statusTotal }}</strong>
                    </div>
                </div>
                <div class="asset-status-summary-card">
                    <span class="asset-status-summary-icon is-green"><x-dashboard.icon name="user-check" /></span>
                    <div>
                        <span>Categories</span>
                        <strong>{{ $assetStatusSummary['categoryCount'] }}</strong>
                    </div>
                </div>
                <div class="asset-status-summary-card">
                    <span class="asset-status-summary-icon is-red"><x-dashboard.icon name="settings" /></span>
                    <div>
                        <span>Top Category</span>
                        <strong>{{ $assetStatusSummary['topCategory'] }}</strong>
                    </div>
                </div>
                <div class="asset-status-summary-card">
                    <span class="asset-status-summary-icon is-green"><x-dashboard.icon name="rotate-ccw" /></span>
                    <div>
                        <span>Last Update</span>
                        <strong>{{ $assetStatusSummary['lastUpdate']?->format('M d, Y') ?? '-' }}</strong>
                    </div>
                </div>
            </div>

            <div class="asset-status-modern-body">
                <div class="asset-status-donut-card">
                    <div class="asset-status-3d-wrap" role="img" aria-label="Asset category distribution chart: {{ $statusTotal }} total assets">
                        <svg class="asset-status-3d-chart" viewBox="0 0 560 300" aria-hidden="true" focusable="false">
                            <defs>
                                <radialGradient id="assetStatusGloss" cx="36%" cy="18%" r="70%">
                                    <stop offset="0%" stop-color="#ffffff" stop-opacity="0.72" />
                                    <stop offset="42%" stop-color="#ffffff" stop-opacity="0.2" />
                                    <stop offset="100%" stop-color="#ffffff" stop-opacity="0" />
                                </radialGradient>
                            </defs>
                            <ellipse class="asset-status-3d-shadow" cx="280" cy="194" rx="150" ry="76" />

                            @forelse ($assetStatusSvgSegments as $segment)
                                <path class="asset-status-3d-depth" d="{{ $segment['depthPath'] }}" style="--slice-color: {{ $segment['color'] }};" />
                            @empty
                                <ellipse class="asset-status-3d-empty-depth" cx="280" cy="168" rx="136" ry="86" />
                            @endforelse

                            @forelse ($assetStatusSvgSegments as $segment)
                                <path class="asset-status-3d-slice" d="{{ $segment['topPath'] }}" style="--slice-color: {{ $segment['color'] }};" />
                            @empty
                                <ellipse class="asset-status-3d-empty-ring" cx="280" cy="142" rx="136" ry="86" />
                            @endforelse

                            <ellipse class="asset-status-3d-gloss" cx="280" cy="142" rx="136" ry="86" />

                            @foreach ($assetStatusSvgSegments as $segment)
                                <path class="asset-status-3d-callout-line" d="{{ $segment['calloutPath'] }}" />
                                <text class="asset-status-3d-callout" x="{{ $segment['labelX'] }}" y="{{ $segment['labelY'] }}" text-anchor="{{ $segment['labelAnchor'] }}">
                                    <title>{{ $segment['name'] }} - {{ $segment['percentageLabel'] }}%</title>
                                    <tspan x="{{ $segment['labelX'] }}" dy="-4">{{ $segment['calloutLabel'] }}</tspan>
                                    <tspan class="asset-status-3d-callout-meta" x="{{ $segment['labelX'] }}" dy="15">{{ $segment['percentageLabel'] }}%</tspan>
                                </text>
                            @endforeach

                            <ellipse class="asset-status-3d-center-depth" cx="280" cy="155" rx="69" ry="44" />
                            <ellipse class="asset-status-3d-center" cx="280" cy="142" rx="66" ry="42" />
                            <text class="asset-status-3d-value" x="280" y="133" text-anchor="middle" dominant-baseline="central">{{ $statusTotal }}</text>
                            <text class="asset-status-3d-label" x="280" y="162" text-anchor="middle" dominant-baseline="central">Total Assets</text>
                        </svg>
                    </div>
                </div>

                <div class="asset-status-breakdown asset-status-percentage-panel" aria-label="Asset category percentages">
                    <div class="asset-status-percentage-heading">
                        <span>Category Share</span>
                        <strong>{{ $statusTotal }} Assets</strong>
                    </div>
                    @foreach ($assetStatusStats as $row)
                        <div class="asset-status-progress-row" style="--status-color: {{ $row['color'] }}; --status-progress: {{ $row['percentage'] }}%;">
                            <div class="asset-status-progress-head">
                                <span title="{{ $row['name'] }}"><i></i>{{ $row['name'] }}</span>
                                <strong>{{ $row['count'] }}</strong>
                                <em>{{ number_format($row['percentage'], $row['percentage'] == floor($row['percentage']) ? 0 : 1) }}%</em>
                            </div>
                            <div class="asset-status-progress-track"><i></i></div>
                        </div>
                    @endforeach
                </div>
            </div>

            @if ($statusTotal === 0)
                <div class="asset-status-empty">Asset category distribution will appear once assets are added.</div>
            @endif
        </article>
    </section>
@endsection
