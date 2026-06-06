@extends('layouts.dashboard')

@section('title', 'Assets')
@section('page-title', 'Asset Master')
@section('eyebrow', 'Assets')

@section('content')
    <section class="dashboard-panel client-listing-panel">
        <div class="panel-heading">
            <div><p>Asset management</p><h2>All Assets</h2></div>
            <div class="button-row">
                <a href="{{ route('asset-brands.index') }}" class="btn btn-secondary">Brands</a>
                <a href="{{ route('asset-categories.index') }}" class="btn btn-secondary">Categories</a>
                <a href="{{ route('imports.index') }}" class="btn btn-secondary">Import Assets</a>
                <a href="{{ route('assets.create') }}" class="btn btn-primary btn-lg">Add Asset</a>
            </div>
        </div>
        <form class="client-toolbar" method="GET">
            <label class="client-search"><x-dashboard.icon name="search" /><input name="search" value="{{ request('search') }}" placeholder="Search assets, tag, serial"></label>
            <select name="status"><option value="">All Status</option>@foreach($statuses as $status)<option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>@endforeach</select>
            <select name="category"><option value="">All Categories</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>@endforeach</select>
            <button class="btn btn-secondary" type="submit">Filter</button>
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
                                <div class="button-row">
                                    <a class="btn btn-sm btn-outline" href="{{ route('assets.edit', $asset) }}">Edit</a>
                                    <form method="POST" action="{{ route('assets.destroy', $asset) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" type="submit">Delete</button></form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No assets found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="table-footer">{{ $assets->links() }}</div>
    </section>
@endsection
