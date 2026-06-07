@extends('layouts.dashboard')

@section('title', 'Asset Brands')
@section('page-title', 'Brand Master')
@section('eyebrow', 'Assets')

@section('content')
    @php($isEditing = $editBrand->exists)

    <section class="dashboard-panel brand-create-panel">
        <div class="panel-heading"><div><p>Brand master</p><h2>{{ $isEditing ? 'Edit Brand' : 'Add Brand' }}</h2></div></div>
        <form class="settings-form brand-create-form" method="POST" action="{{ $isEditing ? route('asset-brands.update', $editBrand) : route('asset-brands.store') }}">
            @csrf
            @if($isEditing)
                @method('PUT')
            @endif
            <label class="form-field brand-name-field"><span>Brand Name</span><input name="name" value="{{ old('name', $editBrand->name) }}" placeholder="Enter brand name">@error('name')<small>{{ $message }}</small>@enderror</label>
            <div class="form-actions brand-create-actions">
                @if($isEditing)
                    <a class="btn btn-secondary" href="{{ route('asset-brands.index') }}">Cancel</a>
                @endif
                <button class="btn btn-primary" type="submit">{{ $isEditing ? 'Update Brand' : 'Save Brand' }}</button>
            </div>
        </form>
    </section>

    <section class="dashboard-panel">
        <div class="panel-heading"><div><p>Brands</p><h2>Existing Brands</h2></div></div>
        <div class="responsive-table">
            <table class="advanced-table">
                <thead><tr><th>Name</th><th>Assets</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($brands as $brand)
                        <tr>
                            <td><strong>{{ $brand->name }}</strong></td>
                            <td>{{ $brand->assets_count }}</td>
                            <td><span class="status-badge status-{{ $brand->is_active ? 'active' : 'inactive' }}">{{ $brand->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td>
                                <div class="table-action-row">
                                    <a class="btn btn-sm btn-outline table-action-btn action-icon-btn action-icon-edit" href="{{ route('asset-brands.edit', $brand) }}" aria-label="Edit {{ $brand->name }}" data-tooltip="Edit">
                                        <x-dashboard.icon name="edit" />
                                    </a>
                                    <form method="POST" action="{{ route('asset-brands.status', $brand) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="status-toggle {{ $brand->is_active ? 'is-active' : '' }}" type="submit" aria-label="{{ $brand->is_active ? 'Deactivate' : 'Activate' }} {{ $brand->name }}" data-tooltip="{{ $brand->is_active ? 'Deactivate' : 'Activate' }}">
                                            <span></span>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('asset-brands.destroy', $brand) }}" data-confirm-delete>
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger table-action-btn action-icon-btn action-icon-delete" type="submit" aria-label="Delete {{ $brand->name }}" data-tooltip="Delete">
                                            <x-dashboard.icon name="trash" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="table-empty" colspan="4">No brands found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
