@extends('layouts.dashboard')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('eyebrow', 'Overview')

@php
    $metrics = [
        ['label' => 'Total Revenue', 'value' => '$84.7k', 'trend' => '+12.4%', 'note' => 'vs last month', 'tone' => 'good', 'icon' => 'dashboard'],
        ['label' => 'Active Users', 'value' => '12,486', 'trend' => '+8.2%', 'note' => '1,284 online now', 'tone' => 'good', 'icon' => 'users'],
        ['label' => 'Pending Orders', 'value' => '342', 'trend' => '-3.1%', 'note' => '24 high priority', 'tone' => 'warn', 'icon' => 'pages'],
        ['label' => 'Conversion Rate', 'value' => '7.8%', 'trend' => '+1.6%', 'note' => 'checkout funnel', 'tone' => 'good', 'icon' => 'palette'],
    ];

    $channels = [
        ['label' => 'Organic', 'value' => 78],
        ['label' => 'Referral', 'value' => 64],
        ['label' => 'Social', 'value' => 52],
        ['label' => 'Direct', 'value' => 46],
        ['label' => 'Email', 'value' => 38],
    ];

    $orders = [
        ['id' => '#DD-1024', 'customer' => 'Aarav Mehta', 'product' => 'Enterprise Plan', 'amount' => '$1,280', 'status' => 'Paid', 'time' => '10:42 AM'],
        ['id' => '#DD-1023', 'customer' => 'Priya Sharma', 'product' => 'Brand Kit', 'amount' => '$640', 'status' => 'Pending', 'time' => '09:18 AM'],
        ['id' => '#DD-1022', 'customer' => 'Neha Kapoor', 'product' => 'Analytics Add-on', 'amount' => '$420', 'status' => 'Paid', 'time' => 'Yesterday'],
        ['id' => '#DD-1021', 'customer' => 'Rohan Iyer', 'product' => 'Custom Setup', 'amount' => '$2,100', 'status' => 'Review', 'time' => 'Yesterday'],
    ];

    $businessTargets = [
        ['label' => 'Appliances', 'value' => 189, 'color' => '#4f8df7'],
        ['label' => 'Automotive', 'value' => 136, 'color' => '#5fc069'],
        ['label' => 'Computers', 'value' => 401, 'color' => '#f6a226'],
        ['label' => 'Electronics', 'value' => 515, 'color' => '#f04b54'],
    ];

    $activities = [
        ['title' => 'New enterprise subscription approved', 'meta' => 'Finance team - 12 min ago'],
        ['title' => 'Theme settings updated successfully', 'meta' => 'Admin user - 38 min ago'],
        ['title' => 'Four pending orders moved to review', 'meta' => 'Operations - 1 hr ago'],
        ['title' => 'Weekly acquisition report generated', 'meta' => 'System - 2 hrs ago'],
    ];

    $queue = [
        ['label' => 'High Priority Tickets', 'value' => 18, 'tone' => 'warn'],
        ['label' => 'Awaiting Approval', 'value' => 9, 'tone' => 'info'],
        ['label' => 'Ready to Ship', 'value' => 42, 'tone' => 'good'],
    ];
@endphp

@section('content')
    <section class="analytics-grid">
        @foreach ($metrics as $metric)
            <article class="analytics-card">
                <div class="analytics-card-top">
                    <span class="metric-icon"><x-dashboard.icon :name="$metric['icon']" /></span>
                    <em class="trend-pill trend-{{ $metric['tone'] }}">{{ $metric['trend'] }}</em>
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
        <article class="dashboard-panel chart-panel revenue-panel">
            <div class="panel-heading analytics-heading">
                <div>
                    <p>Performance</p>
                    <h2>Revenue Overview</h2>
                </div>
                <div class="segmented-control" aria-label="Chart range">
                    <span class="is-active">12M</span>
                    <span>6M</span>
                    <span>30D</span>
                </div>
            </div>
            <div class="revenue-chart" aria-label="Monthly revenue trend">
                <svg viewBox="0 0 720 260" role="img" aria-hidden="true">
                    <defs>
                        <linearGradient id="revenueFill" x1="0" x2="0" y1="0" y2="1">
                            <stop offset="0%" stop-color="var(--primary)" stop-opacity="0.32" />
                            <stop offset="100%" stop-color="var(--primary)" stop-opacity="0.02" />
                        </linearGradient>
                    </defs>
                    <path class="chart-grid-line" d="M20 50H700M20 110H700M20 170H700M20 230H700" />
                    <path class="chart-area" d="M20 210 C80 180, 110 182, 150 150 S230 110, 275 132 S350 190, 405 126 S495 68, 555 92 S640 142, 700 58 V240 H20 Z" />
                    <path class="chart-line" d="M20 210 C80 180, 110 182, 150 150 S230 110, 275 132 S350 190, 405 126 S495 68, 555 92 S640 142, 700 58" />
                    <circle cx="405" cy="126" r="6" />
                    <circle cx="555" cy="92" r="6" />
                    <circle cx="700" cy="58" r="6" />
                </svg>
                <div class="chart-labels">
                    <span>Jan</span><span>Mar</span><span>May</span><span>Jul</span><span>Sep</span><span>Nov</span>
                </div>
            </div>
        </article>

        <article class="dashboard-panel chart-panel">
            <div class="panel-heading analytics-heading">
                <div>
                    <p>Acquisition</p>
                    <h2>Traffic Channels</h2>
                </div>
            </div>
            <div class="channel-list">
                @foreach ($channels as $channel)
                    <div class="channel-row">
                        <div>
                            <span>{{ $channel['label'] }}</span>
                            <strong>{{ $channel['value'] }}%</strong>
                        </div>
                        <i><b style="width: {{ $channel['value'] }}%"></b></i>
                    </div>
                @endforeach
            </div>
        </article>
    </section>

    <section class="dashboard-intel-grid">
        <article class="dashboard-panel insight-panel">
            <div class="panel-heading analytics-heading">
                <div>
                    <p>Goals</p>
                    <h2>Business Targets</h2>
                </div>
            </div>
            <div class="business-targets-chart" aria-label="Business targets by category">
                <svg class="business-pie-svg" viewBox="0 0 252 166" role="img" aria-label="Exploded 3D business targets pie chart">
                    <defs>
                        <linearGradient id="pieBlueTop" x1="20%" x2="80%" y1="10%" y2="100%">
                            <stop offset="0%" stop-color="#8fc4ff" />
                            <stop offset="100%" stop-color="#3b76d4" />
                        </linearGradient>
                        <linearGradient id="piePurpleTop" x1="20%" x2="80%" y1="10%" y2="100%">
                            <stop offset="0%" stop-color="#e18ccc" />
                            <stop offset="100%" stop-color="#a93f9d" />
                        </linearGradient>
                        <linearGradient id="pieGreenTop" x1="18%" x2="82%" y1="8%" y2="100%">
                            <stop offset="0%" stop-color="#c7f3a6" />
                            <stop offset="100%" stop-color="#73bf52" />
                        </linearGradient>
                        <linearGradient id="pieOrangeTop" x1="18%" x2="82%" y1="8%" y2="100%">
                            <stop offset="0%" stop-color="#ffe48a" />
                            <stop offset="100%" stop-color="#f59d12" />
                        </linearGradient>
                        <linearGradient id="pieRedTop" x1="18%" x2="82%" y1="8%" y2="100%">
                            <stop offset="0%" stop-color="#f69372" />
                            <stop offset="100%" stop-color="#ef3f22" />
                        </linearGradient>
                        <filter id="pieShadow" x="-20%" y="-20%" width="140%" height="150%">
                            <feDropShadow dx="0" dy="12" stdDeviation="7" flood-color="#0f172a" flood-opacity="0.22" />
                        </filter>
                    </defs>
                    <g filter="url(#pieShadow)">
                        <ellipse class="pie-core-shadow" cx="122" cy="96" rx="20" ry="10" />
                        <g class="pie-depth pie-depth-purple">
                            <path class="pie-side pie-side-purple" d="M 117.4 94.8 L 42.0 70.1 A 92 43 0 0 1 117.4 51.8 Z" />
                            <path class="pie-side pie-side-purple" d="M 42.0 45.1 A 92 43 0 0 1 117.4 26.8 L 117.4 51.8 A 92 43 0 0 0 42.0 70.1 Z" />
                            <path class="pie-side pie-side-purple" d="M 117.4 69.8 L 42.0 45.1 L 42.0 70.1 L 117.4 94.8 Z" />
                            <path class="pie-side pie-side-purple" d="M 117.4 69.8 L 117.4 26.8 L 117.4 51.8 L 117.4 94.8 Z" />
                        </g>
                        <g class="pie-depth pie-depth-yellow">
                            <path class="pie-side pie-side-yellow" d="M 126.2 94.7 L 126.2 51.7 A 92 43 0 0 1 196.7 67.0 Z" />
                            <path class="pie-side pie-side-yellow" d="M 126.2 26.7 A 92 43 0 0 1 196.7 42.0 L 196.7 67.0 A 92 43 0 0 0 126.2 51.7 Z" />
                            <path class="pie-side pie-side-yellow" d="M 126.2 69.7 L 126.2 26.7 L 126.2 51.7 L 126.2 94.7 Z" />
                            <path class="pie-side pie-side-yellow" d="M 126.2 69.7 L 196.7 42.0 L 196.7 67.0 L 126.2 94.7 Z" />
                        </g>
                        <g class="pie-depth pie-depth-green">
                            <path class="pie-side pie-side-green" d="M 131.5 98.7 L 201.9 71.1 A 92 43 0 0 1 223.4 100.2 Z" />
                            <path class="pie-side pie-side-green" d="M 201.9 46.1 A 92 43 0 0 1 223.4 75.2 L 223.4 100.2 A 92 43 0 0 0 201.9 71.1 Z" />
                            <path class="pie-side pie-side-green" d="M 131.5 73.7 L 201.9 46.1 L 201.9 71.1 L 131.5 98.7 Z" />
                            <path class="pie-side pie-side-green" d="M 131.5 73.7 L 223.4 75.2 L 223.4 100.2 L 131.5 98.7 Z" />
                        </g>
                        <g class="pie-depth pie-depth-red">
                            <path class="pie-side pie-side-red" d="M 130.3 104.9 L 222.3 106.4 A 92 43 0 0 1 169.2 143.8 Z" />
                            <path class="pie-side pie-side-red" d="M 222.3 81.4 A 92 43 0 0 1 169.2 118.8 L 169.2 143.8 A 92 43 0 0 0 222.3 106.4 Z" />
                            <path class="pie-side pie-side-red" d="M 130.3 79.9 L 222.3 81.4 L 222.3 106.4 L 130.3 104.9 Z" />
                            <path class="pie-side pie-side-red" d="M 130.3 79.9 L 169.2 118.8 L 169.2 143.8 L 130.3 104.9 Z" />
                        </g>
                        <g class="pie-depth pie-depth-blue">
                            <path class="pie-side pie-side-blue" d="M 112.1 102.0 L 55.5 135.9 A 92 43 0 0 1 36.7 77.4 Z" />
                            <path class="pie-side pie-side-blue" d="M 36.7 52.4 L 36.7 77.4 L 55.5 135.9 L 55.5 110.9 Z" />
                            <path class="pie-side pie-side-blue" d="M 112.1 77.0 L 55.5 110.9 L 55.5 135.9 L 112.1 102.0 Z" />
                            <path class="pie-side pie-side-blue" d="M 112.1 77.0 L 36.7 52.4 L 36.7 77.4 L 112.1 102.0 Z" />
                        </g>
                        <g class="pie-depth pie-depth-orange">
                            <path class="pie-side pie-side-orange" d="M 120.9 108.0 L 159.7 146.9 A 92 43 0 0 1 64.2 141.8 Z" />
                            <path class="pie-side pie-side-orange" d="M 159.7 121.9 A 92 43 0 0 1 64.2 116.8 L 64.2 141.8 A 92 43 0 0 0 159.7 146.9 Z" />
                            <path class="pie-side pie-side-orange" d="M 120.9 83.0 L 159.7 121.9 L 159.7 146.9 L 120.9 108.0 Z" />
                            <path class="pie-side pie-side-orange" d="M 120.9 83.0 L 64.2 116.8 L 64.2 141.8 L 120.9 108.0 Z" />
                        </g>
                        <g class="pie-top">
                            <path class="pie-slice pie-slice-purple" d="M 117.4 69.8 L 42.0 45.1 A 92 43 0 0 1 117.4 26.8 Z" />
                            <path class="pie-slice pie-slice-yellow" d="M 126.2 69.7 L 126.2 26.7 A 92 43 0 0 1 196.7 42.0 Z" />
                            <path class="pie-slice pie-slice-green" d="M 131.5 73.7 L 201.9 46.1 A 92 43 0 0 1 223.4 75.2 Z" />
                            <path class="pie-slice pie-slice-red" d="M 130.3 79.9 L 222.3 81.4 A 92 43 0 0 1 169.2 118.8 Z" />
                            <path class="pie-slice pie-slice-blue" d="M 112.1 77.0 L 55.5 110.9 A 92 43 0 0 1 36.7 52.4 Z" />
                            <path class="pie-slice pie-slice-orange" d="M 120.9 83.0 L 159.7 121.9 A 92 43 0 0 1 64.2 116.8 Z" />
                        </g>
                    </g>
                </svg>
                <ul class="business-targets-legend">
                    @foreach ($businessTargets as $target)
                        <li>
                            <span style="--target-color: {{ $target['color'] }}"></span>
                            <strong>{{ $target['label'] }} ({{ $target['value'] }})</strong>
                        </li>
                    @endforeach
                </ul>
            </div>
        </article>

        <article class="dashboard-panel insight-panel">
            <div class="panel-heading analytics-heading">
                <div>
                    <p>Activity</p>
                    <h2>Live Timeline</h2>
                </div>
            </div>
            <div class="activity-list">
                @foreach ($activities as $activity)
                    <div class="activity-item">
                        <span></span>
                        <div>
                            <strong>{{ $activity['title'] }}</strong>
                            <small>{{ $activity['meta'] }}</small>
                        </div>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="dashboard-panel insight-panel">
            <div class="panel-heading analytics-heading">
                <div>
                    <p>Queue</p>
                    <h2>Action Center</h2>
                </div>
            </div>
            <div class="queue-list">
                @foreach ($queue as $item)
                    <div class="queue-item queue-{{ $item['tone'] }}">
                        <span>{{ $item['label'] }}</span>
                        <strong>{{ $item['value'] }}</strong>
                    </div>
                @endforeach
            </div>
        </article>
    </section>

    <section class="dashboard-panel orders-panel">
        <div class="panel-heading analytics-heading">
            <div>
                <p>Operations</p>
                <h2>Recent Transactions</h2>
            </div>
            <a href="{{ route('settings.edit') }}" class="ghost-button">Settings</a>
        </div>

        <div class="responsive-table">
            <table class="advanced-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td><strong>{{ $order['id'] }}</strong></td>
                            <td>{{ $order['customer'] }}</td>
                            <td>{{ $order['product'] }}</td>
                            <td>{{ $order['amount'] }}</td>
                            <td><span class="status-badge status-{{ strtolower($order['status']) }}">{{ $order['status'] }}</span></td>
                            <td>{{ $order['time'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
