@extends('layouts.dashboard')

@section('title', 'Assets')
@section('page-title', 'Asset Master')
@section('eyebrow', 'Assets')
@section('page-actions')
    <a href="{{ route('assets.create') }}" class="btn btn-primary listing-create-btn">
        <x-dashboard.icon name="plus" />
        <span>Create</span>
    </a>
@endsection

@section('content')
    @php($hasFilters = request()->filled('search') || request()->filled('status') || request()->filled('category') || request()->filled('brand'))
    <section class="dashboard-panel client-listing-panel {{ $hasFilters ? 'is-open' : '' }}" data-listing-filter>
        <div class="panel-heading">
            <label class="client-search listing-global-search"><x-dashboard.icon name="search" /><input form="asset-filter-form" value="{{ request('search') }}" placeholder="Search all columns..." data-auto-filter-control data-filter-proxy="search"></label>
            <div class="button-row">
                <button class="btn btn-secondary action-icon-btn action-icon-neutral" type="button" aria-label="Filters" data-tooltip="Filters" data-filter-toggle aria-expanded="{{ $hasFilters ? 'true' : 'false' }}"><x-dashboard.icon name="funnel" /></button>
                @if ($hasFilters)
                    <a class="btn btn-secondary action-icon-btn action-icon-neutral" href="{{ route('assets.index') }}" aria-label="Reset Filter" data-tooltip="Reset Filter"><x-dashboard.icon name="x" /></a>
                @endif
                <a href="{{ route('asset-brands.index') }}" class="btn btn-secondary action-icon-btn action-icon-neutral" aria-label="Brands" data-tooltip="Brands"><x-dashboard.icon name="tag" /></a>
                <a href="{{ route('asset-categories.index') }}" class="btn btn-secondary action-icon-btn action-icon-neutral" aria-label="Categories" data-tooltip="Categories"><x-dashboard.icon name="folder" /></a>
                <a href="{{ route('imports.assets.index') }}" class="btn btn-secondary action-icon-btn action-icon-neutral" aria-label="Import Assets" data-tooltip="Import Assets"><x-dashboard.icon name="upload" /></a>
            </div>
        </div>
        <form id="asset-filter-form" class="client-toolbar listing-filter-fields" method="GET" data-filter-panel data-auto-filter-form @unless($hasFilters) hidden @endunless>
                <span class="filter-label">Filter by:</span>
                <label class="client-search"><input name="search" value="{{ request('search') }}" placeholder="Asset name"></label>
                <select name="status" aria-label="Filter by status"><option value="">All Status</option>@foreach($statuses as $status)<option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>@endforeach</select>
                <select name="category" aria-label="Filter by category"><option value="">All Categories</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>@endforeach</select>
                <select name="brand" aria-label="Filter by brand"><option value="">All Brands</option>@foreach($brands as $brand)<option value="{{ $brand->id }}" @selected((string) request('brand') === (string) $brand->id)>{{ $brand->name }}</option>@endforeach</select>
        </form>
        <div class="responsive-table">
            <table class="advanced-table">
                <thead><tr><th>Asset</th><th>Category</th><th>Brand</th><th>Serial</th><th>Status</th><th>Assigned To</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse($assets as $asset)
                        <tr>
                            <td><div class="client-person"><span>{{ strtoupper(substr($asset->name, 0, 2)) }}</span><div><strong>{{ $asset->name }}</strong><small>{{ $asset->asset_tag }} {{ $asset->model ? '- '.$asset->model : '' }}</small></div></div></td>
                            <td>{{ $asset->category?->name }}</td>
                            <td>{{ $asset->brand?->name ?? '-' }}</td>
                            <td>{{ $asset->serial_number ?: '-' }}</td>
                            <td><span class="status-badge status-{{ $asset->status->value }}">{{ $asset->status->label() }}</span></td>
                            <td>{{ $asset->activeAssignment?->employee?->name_en ?? '-' }}</td>
                            <td>
                                <div class="table-action-row">
                                    <a class="btn btn-sm btn-outline table-action-btn action-icon-btn action-icon-edit" href="{{ route('assets.edit', $asset) }}" aria-label="Edit {{ $asset->name }}" data-tooltip="Edit">
                                        <x-dashboard.icon name="edit" />
                                    </a>
                                    <form method="POST" action="{{ route('assets.destroy', $asset) }}" data-confirm-delete>
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger table-action-btn action-icon-btn action-icon-delete" type="submit" aria-label="Delete {{ $asset->name }}" data-tooltip="Delete">
                                            <x-dashboard.icon name="trash" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="table-empty" colspan="7">No assets found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="table-footer">
            <span>Total {{ $assets->total() }} item(s)</span>
            <div class="table-pagination">
                <label class="pagination-size">
                    <span>Items per page</span>
                    <select name="per_page" form="asset-filter-form" aria-label="Items per page" data-auto-filter-control data-native-select>
                        @foreach ([10, 20, 30, 40, 50] as $size)
                            <option value="{{ $size }}" @selected($assets->perPage() === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </label>
                <span class="pagination-page">Page {{ $assets->currentPage() }} of {{ $assets->lastPage() }}</span>
                <div class="pagination-controls">
                    <a class="action-icon-btn action-icon-neutral {{ $assets->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $assets->url(1) }}" aria-label="First page"><x-dashboard.icon name="chevrons-left" /></a>
                    <a class="action-icon-btn action-icon-neutral {{ $assets->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $assets->previousPageUrl() ?? '#' }}" aria-label="Previous page"><x-dashboard.icon name="chevron-left" /></a>
                    <a class="action-icon-btn action-icon-neutral {{ $assets->hasMorePages() ? '' : 'is-disabled' }}" href="{{ $assets->nextPageUrl() ?? '#' }}" aria-label="Next page"><x-dashboard.icon name="chevron-right" /></a>
                    <a class="action-icon-btn action-icon-neutral {{ $assets->hasMorePages() ? '' : 'is-disabled' }}" href="{{ $assets->url($assets->lastPage()) }}" aria-label="Last page"><x-dashboard.icon name="chevrons-right" /></a>
                </div>
            </div>
        </div>
    </section>

@endsection
