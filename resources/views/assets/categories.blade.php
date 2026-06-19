@extends('layouts.dashboard')

@section('title', 'Asset Categories')
@section('page-title', 'Category Master')
@section('eyebrow', 'Assets')

@section('content')
    @php($isEditing = $editCategory->exists)
    @php($hasFilters = request()->filled('search') || request()->filled('status'))

    <section class="dashboard-panel brand-create-panel">
        <div class="panel-heading"><div><p>Category master</p><h2>{{ $isEditing ? 'Edit Category' : 'Add Category' }}</h2></div></div>
        <form class="settings-form brand-create-form" method="POST" action="{{ $isEditing ? route('asset-categories.update', $editCategory) : route('asset-categories.store') }}">
            @csrf
            @if($isEditing)
                @method('PUT')
            @endif
            <label class="form-field brand-name-field"><span>Category Name</span><input name="name" value="{{ $isEditing ? ($errors->any() ? old('name', $editCategory->name) : $editCategory->name) : old('name') }}" placeholder="Enter category name">@error('name')<small>{{ $message }}</small>@enderror</label>
            <div class="form-actions brand-create-actions">
                @if($isEditing)
                    <a class="btn btn-secondary" href="{{ route('asset-categories.index') }}">Cancel</a>
                @endif
                <button class="btn btn-primary" type="submit">{{ $isEditing ? 'Update' : 'Save' }}</button>
            </div>
        </form>
    </section>

    <section class="dashboard-panel client-listing-panel {{ $hasFilters ? 'is-open' : '' }}" data-listing-filter>
        <div class="panel-heading">
            <label class="client-search listing-global-search"><x-dashboard.icon name="search" /><input form="category-filter-form" value="{{ request('search') }}" placeholder="Search all columns..." data-auto-filter-control data-filter-proxy="search"></label>
            <div class="button-row">
                <button class="btn btn-secondary action-icon-btn action-icon-neutral" type="button" aria-label="Filters" data-tooltip="Filters" data-filter-toggle aria-expanded="{{ $hasFilters ? 'true' : 'false' }}"><x-dashboard.icon name="funnel" /></button>
                @if ($hasFilters)
                    <a class="btn btn-secondary action-icon-btn action-icon-neutral" href="{{ route('asset-categories.index') }}" aria-label="Reset Filter" data-tooltip="Reset Filter"><x-dashboard.icon name="x" /></a>
                @endif
            </div>
        </div>
        <form id="category-filter-form" class="client-toolbar listing-filter-fields" method="GET" data-filter-panel data-auto-filter-form @unless($hasFilters) hidden @endunless>
            <span class="filter-label">Filter by:</span>
            <label class="client-search"><input name="search" value="{{ request('search') }}" placeholder="Category Name"></label>
            <select name="status" aria-label="Filter by status">
                <option value="">All Status</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>
        </form>
        <div class="responsive-table">
            <table class="advanced-table">
                <thead><tr><th>Category</th><th>Assets</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td><div class="client-person"><span>{{ strtoupper(substr($category->name, 0, 2)) }}</span><div><strong>{{ $category->name }}</strong><small>Category master</small></div></div></td>
                            <td>{{ $category->assets_count }}</td>
                            <td><span class="status-badge status-{{ $category->is_active ? 'active' : 'inactive' }}">{{ $category->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td>
                                <div class="table-action-row">
                                    <form method="POST" action="{{ route('asset-categories.status', $category) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="status-toggle {{ $category->is_active ? 'is-active' : '' }}" type="submit" aria-label="{{ $category->is_active ? 'Deactivate' : 'Activate' }} {{ $category->name }}" data-tooltip="{{ $category->is_active ? 'Deactivate' : 'Activate' }}">
                                            <span></span>
                                        </button>
                                    </form>
                                    <a class="btn btn-sm btn-outline table-action-btn action-icon-btn action-icon-edit" href="{{ route('asset-categories.edit', $category) }}" aria-label="Edit {{ $category->name }}" data-tooltip="Edit">
                                        <x-dashboard.icon name="edit" />
                                    </a>
                                    <form method="POST" action="{{ route('asset-categories.destroy', $category) }}" data-confirm-delete>
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger table-action-btn action-icon-btn action-icon-delete" type="submit" aria-label="Delete {{ $category->name }}" data-tooltip="Delete">
                                            <x-dashboard.icon name="trash" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="table-empty" colspan="4">No categories found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="table-footer">
            <span>Total {{ $categories->total() }} item(s)</span>
            <div class="table-pagination">
                <label class="pagination-size">
                    <span>Items per page</span>
                    <select name="per_page" form="category-filter-form" aria-label="Items per page" data-auto-filter-control data-native-select>
                        @foreach ([10, 20, 30, 40, 50] as $size)
                            <option value="{{ $size }}" @selected($categories->perPage() === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </label>
                <span class="pagination-page">Page {{ $categories->currentPage() }} of {{ $categories->lastPage() }}</span>
                <div class="pagination-controls">
                    <a class="action-icon-btn action-icon-neutral {{ $categories->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $categories->url(1) }}" aria-label="First page"><x-dashboard.icon name="chevrons-left" /></a>
                    <a class="action-icon-btn action-icon-neutral {{ $categories->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $categories->previousPageUrl() ?? '#' }}" aria-label="Previous page"><x-dashboard.icon name="chevron-left" /></a>
                    <a class="action-icon-btn action-icon-neutral {{ $categories->hasMorePages() ? '' : 'is-disabled' }}" href="{{ $categories->nextPageUrl() ?? '#' }}" aria-label="Next page"><x-dashboard.icon name="chevron-right" /></a>
                    <a class="action-icon-btn action-icon-neutral {{ $categories->hasMorePages() ? '' : 'is-disabled' }}" href="{{ $categories->url($categories->lastPage()) }}" aria-label="Last page"><x-dashboard.icon name="chevrons-right" /></a>
                </div>
            </div>
        </div>
    </section>
@endsection
