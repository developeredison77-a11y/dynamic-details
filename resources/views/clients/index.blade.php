@extends('layouts.dashboard')

@section('title', 'All Clients')
@section('page-title', 'All Clients')
@section('eyebrow', 'Clients')
@section('page-actions')
    <button type="button" class="btn btn-primary listing-create-btn">
        <x-dashboard.icon name="plus" />
        <span>Create</span>
    </button>
@endsection

@php
    $clients = [
        ['name' => 'Aarav Mehta', 'company' => 'Northstar Retail', 'email' => 'aarav@northstar.test', 'phone' => '+91 98765 12001', 'status' => 'Active', 'plan' => 'Enterprise', 'value' => '$12,800', 'joined' => '2026-05-12'],
        ['name' => 'Priya Sharma', 'company' => 'Brightline Studio', 'email' => 'priya@brightline.test', 'phone' => '+91 98765 12002', 'status' => 'Pending', 'plan' => 'Pro', 'value' => '$4,600', 'joined' => '2026-05-10'],
        ['name' => 'Rohan Iyer', 'company' => 'Vector Labs', 'email' => 'rohan@vector.test', 'phone' => '+91 98765 12003', 'status' => 'Active', 'plan' => 'Enterprise', 'value' => '$18,400', 'joined' => '2026-05-08'],
        ['name' => 'Neha Kapoor', 'company' => 'Urban Leaf', 'email' => 'neha@urbanleaf.test', 'phone' => '+91 98765 12004', 'status' => 'Inactive', 'plan' => 'Starter', 'value' => '$1,200', 'joined' => '2026-05-05'],
        ['name' => 'Vikram Rao', 'company' => 'Atlas Fintech', 'email' => 'vikram@atlas.test', 'phone' => '+91 98765 12005', 'status' => 'Active', 'plan' => 'Pro', 'value' => '$7,950', 'joined' => '2026-05-02'],
        ['name' => 'Isha Nair', 'company' => 'Blue Peak', 'email' => 'isha@bluepeak.test', 'phone' => '+91 98765 12006', 'status' => 'Pending', 'plan' => 'Starter', 'value' => '$980', 'joined' => '2026-04-29'],
        ['name' => 'Karan Malhotra', 'company' => 'Summit Cloud', 'email' => 'karan@summit.test', 'phone' => '+91 98765 12007', 'status' => 'Active', 'plan' => 'Enterprise', 'value' => '$22,300', 'joined' => '2026-04-26'],
        ['name' => 'Meera Joshi', 'company' => 'Craftworks', 'email' => 'meera@craftworks.test', 'phone' => '+91 98765 12008', 'status' => 'Inactive', 'plan' => 'Pro', 'value' => '$3,450', 'joined' => '2026-04-22'],
        ['name' => 'Dev Patel', 'company' => 'Signal Nine', 'email' => 'dev@signalnine.test', 'phone' => '+91 98765 12009', 'status' => 'Active', 'plan' => 'Starter', 'value' => '$1,840', 'joined' => '2026-04-19'],
        ['name' => 'Ananya Das', 'company' => 'Nova Health', 'email' => 'ananya@novahealth.test', 'phone' => '+91 98765 12010', 'status' => 'Active', 'plan' => 'Pro', 'value' => '$6,720', 'joined' => '2026-04-15'],
        ['name' => 'Kabir Khan', 'company' => 'Monarch Media', 'email' => 'kabir@monarch.test', 'phone' => '+91 98765 12011', 'status' => 'Pending', 'plan' => 'Enterprise', 'value' => '$15,600', 'joined' => '2026-04-11'],
        ['name' => 'Tara Singh', 'company' => 'Greenframe', 'email' => 'tara@greenframe.test', 'phone' => '+91 98765 12012', 'status' => 'Active', 'plan' => 'Pro', 'value' => '$5,200', 'joined' => '2026-04-08'],
    ];
@endphp

@section('content')
    <section class="dashboard-panel client-listing-panel" data-client-table data-listing-filter>
        <div class="panel-heading">
            <label class="client-search listing-global-search">
                <x-dashboard.icon name="search" />
                <input type="search" data-client-search placeholder="Search all columns...">
            </label>
            <div class="button-row">
                <button type="button" class="btn btn-secondary action-icon-btn action-icon-neutral" aria-label="Filters" data-tooltip="Filters" data-filter-toggle aria-expanded="false"><x-dashboard.icon name="funnel" /></button>
                <button type="button" class="btn btn-secondary action-icon-btn action-icon-neutral" aria-label="Reset Filter" data-tooltip="Reset Filter" data-client-reset hidden><x-dashboard.icon name="x" /></button>
            </div>
        </div>

        <div class="client-toolbar listing-filter-fields" data-filter-panel hidden>
                <span class="filter-label">Filter by:</span>
                <label class="client-search">
                    <input type="search" data-client-search placeholder="Client name">
                </label>
                <select data-client-status aria-label="Filter by status">
                    <option value="">All Status</option>
                    <option value="Active">Active</option>
                    <option value="Pending">Pending</option>
                    <option value="Inactive">Inactive</option>
                </select>

                <select data-client-plan aria-label="Filter by plan">
                    <option value="">All Plans</option>
                    <option value="Enterprise">Enterprise</option>
                    <option value="Pro">Pro</option>
                    <option value="Starter">Starter</option>
                </select>
        </div>

        <div class="responsive-table">
            <table class="advanced-table client-table">
                <thead>
                    <tr>
                        <th><button type="button" data-sort="name">Client</button></th>
                        <th><button type="button" data-sort="company">Company</button></th>
                        <th>Email</th>
                        <th><button type="button" data-sort="status">Status</button></th>
                        <th><button type="button" data-sort="plan">Plan</button></th>
                        <th><button type="button" data-sort="value">Value</button></th>
                        <th><button type="button" data-sort="joined">Joined</button></th>
                    </tr>
                </thead>
                <tbody data-client-body>
                    @foreach ($clients as $client)
                        <tr
                            data-name="{{ $client['name'] }}"
                            data-company="{{ $client['company'] }}"
                            data-email="{{ $client['email'] }}"
                            data-status="{{ $client['status'] }}"
                            data-plan="{{ $client['plan'] }}"
                            data-value="{{ preg_replace('/[^0-9]/', '', $client['value']) }}"
                            data-joined="{{ $client['joined'] }}"
                        >
                            <td>
                                <div class="client-person">
                                    <span>{{ collect(explode(' ', $client['name']))->map(fn ($part) => strtoupper(substr($part, 0, 1)))->take(2)->implode('') }}</span>
                                    <div>
                                        <strong>{{ $client['name'] }}</strong>
                                        <small>{{ $client['phone'] }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $client['company'] }}</td>
                            <td>{{ $client['email'] }}</td>
                            <td><span class="status-badge status-{{ strtolower($client['status']) }}">{{ $client['status'] }}</span></td>
                            <td>{{ $client['plan'] }}</td>
                            <td>{{ $client['value'] }}</td>
                            <td>{{ \Carbon\Carbon::parse($client['joined'])->format('M d, Y') }}</td>
                        </tr>
                    @endforeach
                    <tr data-client-empty hidden>
                        <td class="table-empty" colspan="7">No clients found.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <span data-client-summary>Showing clients</span>
            <div class="table-pagination">
                <label class="pagination-size">
                    <span>Items per page</span>
                    <select data-client-per-page aria-label="Items per page" data-native-select>
                        @foreach ([10, 20, 30, 40, 50] as $size)
                            <option value="{{ $size }}" @selected($size === 10)>{{ $size }}</option>
                        @endforeach
                    </select>
                </label>
                <span class="pagination-page" data-page-current>1</span>
                <div class="pagination-controls">
                    <button type="button" class="action-icon-btn action-icon-neutral" data-page-first aria-label="First page" data-tooltip="First page"><x-dashboard.icon name="chevrons-left" /></button>
                    <button type="button" class="action-icon-btn action-icon-neutral" data-page-prev aria-label="Previous page" data-tooltip="Previous page"><x-dashboard.icon name="chevron-left" /></button>
                    <button type="button" class="action-icon-btn action-icon-neutral" data-page-next aria-label="Next page" data-tooltip="Next page"><x-dashboard.icon name="chevron-right" /></button>
                    <button type="button" class="action-icon-btn action-icon-neutral" data-page-last aria-label="Last page" data-tooltip="Last page"><x-dashboard.icon name="chevrons-right" /></button>
                </div>
            </div>
        </div>
    </section>
@endsection
