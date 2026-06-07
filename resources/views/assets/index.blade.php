@extends('layouts.dashboard')

@section('title', 'Assets')
@section('page-title', 'Asset Master')
@section('eyebrow', 'Assets')

@section('content')
    @php($assetImportErrors = $errors->getBag('assetImport'))
    <section class="dashboard-panel client-listing-panel {{ request()->hasAny(['search', 'status', 'category']) ? 'is-open' : '' }}" data-listing-filter>
        <div class="panel-heading">
            <div><p>Asset management</p><h2>All Assets</h2></div>
            <div class="button-row">
                <a href="{{ route('asset-brands.index') }}" class="btn btn-secondary action-icon-btn action-icon-neutral" aria-label="Brands" data-tooltip="Brands"><x-dashboard.icon name="tag" /></a>
                <a href="{{ route('asset-categories.index') }}" class="btn btn-secondary action-icon-btn action-icon-neutral" aria-label="Categories" data-tooltip="Categories"><x-dashboard.icon name="folder" /></a>
                <button class="btn btn-secondary action-icon-btn action-icon-neutral" type="button" aria-label="Show filters" data-tooltip="Filters" data-filter-toggle aria-expanded="{{ request()->hasAny(['search', 'status', 'category']) ? 'true' : 'false' }}"><x-dashboard.icon name="funnel" /></button>
                <button type="button" class="btn btn-secondary action-icon-btn action-icon-neutral" aria-label="Import Assets" data-tooltip="Import Assets" data-import-open="assets"><x-dashboard.icon name="upload" /></button>
                <a href="{{ route('assets.create') }}" class="btn btn-primary btn-lg action-icon-btn action-icon-edit" aria-label="Add Asset" data-tooltip="Add Asset"><x-dashboard.icon name="plus" /></a>
            </div>
        </div>
        <form class="client-toolbar listing-filter-fields" method="GET" data-filter-panel {{ request()->hasAny(['search', 'status', 'category']) ? '' : 'hidden' }}>
                <label class="client-search"><x-dashboard.icon name="search" /><input name="search" value="{{ request('search') }}" placeholder="Search assets, tag, serial"></label>
                <select name="status" aria-label="Filter by status"><option value="">All Status</option>@foreach($statuses as $status)<option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>@endforeach</select>
                <select name="category" aria-label="Filter by category"><option value="">All Categories</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>@endforeach</select>
                <div class="listing-filter-actions">
                    <button class="btn btn-primary" type="submit">Apply</button>
                    <a class="btn btn-secondary" href="{{ route('assets.index') }}">Reset</a>
                </div>
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
        <div class="table-footer">{{ $assets->links() }}</div>
    </section>

    <div class="modal-backdrop {{ $assetImportErrors->isNotEmpty() ? 'is-open' : '' }}" data-import-modal="assets" {{ $assetImportErrors->isNotEmpty() ? '' : 'hidden' }}>
        <div class="modal-card import-modal" role="dialog" aria-modal="true" aria-labelledby="asset-import-title">
            <div class="modal-heading">
                <div>
                    <p>Asset import</p>
                    <h2 id="asset-import-title">Upload Assets</h2>
                </div>
                <button type="button" class="action-icon-btn action-icon-neutral" aria-label="Close import popup" data-modal-close><x-dashboard.icon name="x" /></button>
            </div>
            <form class="settings-form" method="POST" action="{{ route('imports.assets') }}" enctype="multipart/form-data">
                @csrf
                <label class="form-field file-field {{ $assetImportErrors->has('file') ? 'has-error' : '' }}"><span>Asset Excel / CSV</span><input type="file" name="file" accept=".xlsx,.csv,text/csv">@if ($assetImportErrors->has('file'))<small>{{ $assetImportErrors->first('file') }}</small>@endif</label>
                @if ($assetImportErrors->has('rows'))
                    <div class="import-validation" role="alert">
                        <strong>Asset import validation failed</strong>
                        @foreach ($assetImportErrors->get('rows') as $message)
                            <p>{{ $message }}</p>
                        @endforeach
                    </div>
                @endif
                <div class="import-help">
                    <span>Required: asset_tag, name, category. CSV/XLSX only, up to 5 MB. Tags and serial numbers must be unique.</span>
                    <a class="btn btn-outline btn-sm" href="{{ route('imports.template', 'assets') }}"><x-dashboard.icon name="download" /> Download Template</a>
                </div>
                <div class="form-actions">
                    <button class="btn btn-outline" type="button" data-modal-close>Cancel</button>
                    <button class="btn btn-primary" type="submit">Import Assets</button>
                </div>
            </form>
        </div>
    </div>
@endsection
