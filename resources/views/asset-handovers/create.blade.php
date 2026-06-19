@extends('layouts.dashboard')

@section('title', 'New Handover')
@section('page-title', 'New Handover')
@section('eyebrow', 'Asset Handover')

@section('content')
    <section class="dashboard-panel">
        <div class="panel-heading"><div><p>Assign asset</p><h2>Select Employee and Asset</h2></div></div>
        <form class="settings-form" method="POST" action="{{ route('asset-handovers.store') }}">
            @csrf
            <div class="form-grid">
                <label class="form-field"><span>Employee</span><select name="employee_id"><option value="">Select employee</option>@foreach($employees as $employee)<option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>{{ $employee->employee_code }} - {{ $employee->name_en }}</option>@endforeach</select>@error('employee_id')<small>{{ $message }}</small>@enderror</label>
                <label class="form-field"><span>Available Asset</span><select name="asset_id"><option value="">Select asset</option>@foreach($assets as $asset)<option value="{{ $asset->id }}" @selected(old('asset_id') == $asset->id)>{{ $asset->asset_tag }} - {{ $asset->name }} ({{ $asset->category?->name }})</option>@endforeach</select>@error('asset_id')<small>{{ $message }}</small>@enderror</label>
                <label class="form-field"><span>Handover Date</span><input type="date" name="handover_date" value="{{ old('handover_date', now()->format('Y-m-d')) }}">@error('handover_date')<small>{{ $message }}</small>@enderror</label>
                <label class="form-field"><span>Expected Return Date</span><input type="date" name="expected_return_date" value="{{ old('expected_return_date') }}">@error('expected_return_date')<small>{{ $message }}</small>@enderror</label>
                <label class="form-field form-field-wide"><span>Handover Notes</span><textarea name="handover_notes">{{ old('handover_notes') }}</textarea>@error('handover_notes')<small>{{ $message }}</small>@enderror</label>
            </div>
            <div class="form-actions"><a class="btn btn-outline" href="{{ route('asset-handovers.index') }}">Cancel</a><button class="btn btn-primary btn-lg" type="submit">Save</button></div>
        </form>
    </section>
@endsection
