@extends('layouts.dashboard')

@section('title', 'Asset Categories')
@section('page-title', 'Asset Categories')
@section('eyebrow', 'Assets')

@section('content')
    <section class="split-panel">
        <article class="dashboard-panel">
            <div class="panel-heading"><div><p>Category master</p><h2>Add Category</h2></div></div>
            <form class="settings-form" method="POST" action="{{ route('asset-categories.store') }}">
                @csrf
                <div class="form-grid single">
                    <label class="form-field"><span>Name</span><input name="name" value="{{ old('name') }}">@error('name')<small>{{ $message }}</small>@enderror</label>
                    <label class="form-field"><span>Code</span><input name="code" value="{{ old('code') }}">@error('code')<small>{{ $message }}</small>@enderror</label>
                    <label class="check-field"><input type="checkbox" name="requires_serial" value="1" checked><span>Requires serial number</span></label>
                    <label class="check-field"><input type="checkbox" name="is_active" value="1" checked><span>Active</span></label>
                </div>
                <div class="form-actions"><button class="btn btn-primary" type="submit">Save Category</button></div>
            </form>
        </article>
        <article class="dashboard-panel">
            <div class="panel-heading"><div><p>Categories</p><h2>Existing Categories</h2></div></div>
            <div class="responsive-table"><table class="advanced-table"><thead><tr><th>Name</th><th>Code</th><th>Assets</th><th>Serial</th></tr></thead><tbody>
                @foreach($categories as $category)<tr><td>{{ $category->name }}</td><td>{{ $category->code ?: '-' }}</td><td>{{ $category->assets_count }}</td><td>{{ $category->requires_serial ? 'Required' : 'Optional' }}</td></tr>@endforeach
            </tbody></table></div>
        </article>
    </section>
@endsection
