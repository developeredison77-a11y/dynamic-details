@extends('layouts.dashboard')

@section('title', 'Asset Handovers')
@section('page-title', 'Asset Handover')
@section('eyebrow', 'Assignments')
@section('page-actions')
    <a href="{{ route('asset-handovers.create') }}" class="btn btn-primary listing-create-btn">
        <x-dashboard.icon name="plus" />
        <span>Create</span>
    </a>
@endsection

@section('content')
    @php($hasFilters = request()->filled('search') || request()->filled('status') || request()->filled('category'))
    @php($canManageHandovers = auth()->user()?->canAccess('asset-handovers.update') || auth()->user()?->canAccess('asset-handovers.create'))
    <section class="dashboard-panel client-listing-panel {{ $hasFilters ? 'is-open' : '' }}" data-listing-filter>
        <div class="panel-heading">
            <label class="client-search listing-global-search"><x-dashboard.icon name="search" /><input form="handover-filter-form" value="{{ request('search') }}" placeholder="Search all columns..." data-auto-filter-control data-filter-proxy="search"></label>
            <div class="button-row">
                <button class="btn btn-secondary action-icon-btn action-icon-neutral" type="button" aria-label="Filters" data-tooltip="Filters" data-filter-toggle aria-expanded="{{ $hasFilters ? 'true' : 'false' }}"><x-dashboard.icon name="funnel" /></button>
                @if ($hasFilters)
                    <a class="btn btn-secondary action-icon-btn action-icon-neutral" href="{{ route('asset-handovers.index') }}" aria-label="Reset Filter" data-tooltip="Reset Filter"><x-dashboard.icon name="x" /></a>
                @endif
            </div>
        </div>
        <form id="handover-filter-form" class="client-toolbar listing-filter-fields" method="GET" data-filter-panel data-auto-filter-form @unless($hasFilters) hidden @endunless>
            <span class="filter-label">Filter by:</span>
            <label class="client-search"><input name="search" value="{{ request('search') }}" placeholder="Employee or asset"></label>
            <select name="status" aria-label="Filter by status">
                <option value="">All Status</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <select name="category" aria-label="Filter by category">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </form>
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
                            <td>
                                <div class="table-action-row">
                                    @if ($canManageHandovers)
                                        @if ($assignment->canBeEdited())
                                            <a class="btn btn-sm btn-outline table-action-btn action-icon-btn action-icon-edit" href="{{ route('asset-handovers.edit', $assignment) }}" aria-label="Edit handover {{ $assignment->id }}" data-tooltip="Edit">
                                                <x-dashboard.icon name="edit" />
                                            </a>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline table-action-btn action-icon-btn action-icon-neutral" aria-label="Why edit is locked for handover {{ $assignment->id }}" data-tooltip="Why locked?" data-handover-edit-locked data-toast-message="This handover can be edited only before the handover date starts.">
                                                <x-dashboard.icon name="edit" />
                                            </button>
                                        @endif
                                    @endif
                                    <a class="btn btn-sm btn-outline table-action-btn action-icon-btn action-icon-view" href="{{ route('asset-handovers.show', $assignment) }}" aria-label="View handover {{ $assignment->id }}" data-tooltip="View">
                                        <x-dashboard.icon name="eye" />
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="table-empty" colspan="6">No handovers recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="table-footer">
            <span>Total {{ $assignments->total() }} item(s)</span>
            <div class="table-pagination">
                <label class="pagination-size">
                    <span>Items per page</span>
                    <select name="per_page" form="handover-filter-form" aria-label="Items per page" data-auto-filter-control data-native-select>
                        @foreach ([10, 20, 30, 40, 50] as $size)
                            <option value="{{ $size }}" @selected($assignments->perPage() === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </label>
                <span class="pagination-page">Page {{ $assignments->currentPage() }} of {{ $assignments->lastPage() }}</span>
                <div class="pagination-controls">
                    <a class="action-icon-btn action-icon-neutral {{ $assignments->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $assignments->url(1) }}" aria-label="First page"><x-dashboard.icon name="chevrons-left" /></a>
                    <a class="action-icon-btn action-icon-neutral {{ $assignments->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $assignments->previousPageUrl() ?? '#' }}" aria-label="Previous page"><x-dashboard.icon name="chevron-left" /></a>
                    <a class="action-icon-btn action-icon-neutral {{ $assignments->hasMorePages() ? '' : 'is-disabled' }}" href="{{ $assignments->nextPageUrl() ?? '#' }}" aria-label="Next page"><x-dashboard.icon name="chevron-right" /></a>
                    <a class="action-icon-btn action-icon-neutral {{ $assignments->hasMorePages() ? '' : 'is-disabled' }}" href="{{ $assignments->url($assignments->lastPage()) }}" aria-label="Last page"><x-dashboard.icon name="chevrons-right" /></a>
                </div>
            </div>
        </div>
    </section>
@endsection
