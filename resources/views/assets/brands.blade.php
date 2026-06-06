@extends('layouts.dashboard')

@section('title', 'Asset Brands')
@section('page-title', 'Brand Master')
@section('eyebrow', 'Assets')

@section('content')
    <section class="split-panel">
        <article class="dashboard-panel">
            <div class="panel-heading"><div><p>Brand master</p><h2>Add Brand</h2></div></div>
            <form class="settings-form" method="POST" action="{{ route('asset-brands.store') }}">
                @csrf
                <div class="form-grid single">
                    <label class="form-field"><span>Name</span><input name="name" value="{{ old('name') }}">@error('name')<small>{{ $message }}</small>@enderror</label>
                    <label class="check-field"><input type="checkbox" name="is_active" value="1" checked><span>Active</span></label>
                </div>
                <div class="form-actions"><button class="btn btn-primary" type="submit">Save Brand</button></div>
            </form>
        </article>
        <article class="dashboard-panel">
            <div class="panel-heading"><div><p>Brands</p><h2>Existing Brands</h2></div></div>
            <div class="responsive-table"><table class="advanced-table"><thead><tr><th>Name</th><th>Assets</th><th>Status</th></tr></thead><tbody>
                @foreach($brands as $brand)<tr><td>{{ $brand->name }}</td><td>{{ $brand->assets_count }}</td><td><span class="status-badge status-{{ $brand->is_active ? 'active' : 'inactive' }}">{{ $brand->is_active ? 'Active' : 'Inactive' }}</span></td></tr>@endforeach
            </tbody></table></div>
        </article>
    </section>
@endsection
