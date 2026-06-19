@extends('layouts.dashboard')

@section('title', $asset->exists ? 'Edit Asset' : 'Add Asset')
@section('page-title', $asset->exists ? 'Edit Asset' : 'Add Asset')
@section('eyebrow', 'Asset Master')

@section('content')
    <section class="dashboard-panel">
        <div class="panel-heading"><div><p>Asset details</p><h2>{{ $asset->exists ? $asset->asset_tag : 'New asset' }}</h2></div></div>
        <form class="settings-form" method="POST" action="{{ $asset->exists ? route('assets.update', $asset) : route('assets.store') }}">
            @csrf
            @if($asset->exists) @method('PUT') @endif
            <div class="form-grid">
                <label class="form-field"><span>Asset Tag</span><input name="asset_tag" value="{{ old('asset_tag', $asset->asset_tag) }}">@error('asset_tag')<small>{{ $message }}</small>@enderror</label>
                <label class="form-field"><span>Name</span><input name="name" value="{{ old('name', $asset->name) }}">@error('name')<small>{{ $message }}</small>@enderror</label>
                <label class="form-field"><span>Category</span><select name="asset_category_id"><option value="">Select category</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string) old('asset_category_id', $asset->asset_category_id) === (string) $category->id)>{{ $category->name }}</option>@endforeach</select>@error('asset_category_id')<small>{{ $message }}</small>@enderror</label>
                <label class="form-field"><span>Brand</span><select name="asset_brand_id"><option value="">Unbranded</option>@foreach($brands as $brand)<option value="{{ $brand->id }}" @selected((string) old('asset_brand_id', $asset->asset_brand_id) === (string) $brand->id)>{{ $brand->name }}</option>@endforeach</select>@error('asset_brand_id')<small>{{ $message }}</small>@enderror</label>
                <label class="form-field"><span>Serial Number</span><input name="serial_number" value="{{ old('serial_number', $asset->serial_number) }}">@error('serial_number')<small>{{ $message }}</small>@enderror</label>
                <label class="form-field"><span>Model</span><input name="model" value="{{ old('model', $asset->model) }}">@error('model')<small>{{ $message }}</small>@enderror</label>
                <label class="form-field"><span>Status</span><select name="status">@foreach($statuses as $status)<option value="{{ $status->value }}" @selected(old('status', $asset->status?->value ?? 'available') === $status->value)>{{ $status->label() }}</option>@endforeach</select>@error('status')<small>{{ $message }}</small>@enderror</label>
                <label class="form-field"><span>Condition</span><select name="condition">@foreach($conditions as $condition)<option value="{{ $condition->value }}" @selected(old('condition', $asset->condition?->value ?? 'good') === $condition->value)>{{ $condition->label() }}</option>@endforeach</select>@error('condition')<small>{{ $message }}</small>@enderror</label>
                <label class="form-field"><span>Purchased At</span><input type="date" name="purchased_at" value="{{ old('purchased_at', optional($asset->purchased_at)->format('Y-m-d')) }}">@error('purchased_at')<small>{{ $message }}</small>@enderror</label>
                <label class="form-field"><span>Purchase Value</span><input type="number" step="0.01" name="purchase_value" value="{{ old('purchase_value', $asset->purchase_value) }}">@error('purchase_value')<small>{{ $message }}</small>@enderror</label>
                <label class="form-field form-field-wide"><span>Notes</span><textarea name="notes">{{ old('notes', $asset->notes) }}</textarea>@error('notes')<small>{{ $message }}</small>@enderror</label>
            </div>
            <div class="form-actions"><a class="btn btn-outline" href="{{ route('assets.index') }}">Cancel</a><button class="btn btn-primary btn-lg" type="submit">{{ $asset->exists ? 'Update' : 'Save' }}</button></div>
        </form>
    </section>
@endsection
