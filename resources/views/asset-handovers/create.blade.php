@extends('layouts.dashboard')

@section('title', $assignment->exists ? 'Edit Handover' : 'New Handover')
@section('page-title', $assignment->exists ? 'Edit Handover' : 'New Handover')
@section('eyebrow', 'Asset Handover')

@section('content')
    <section class="dashboard-panel asset-handover-form-panel">
        <div class="panel-heading"><div><p>{{ $assignment->exists ? 'Update handover' : 'Assign asset' }}</p><h2>{{ $assignment->exists ? ($assignment->asset?->asset_tag ?? 'Handover #'.$assignment->id) : 'Select Employee and Asset' }}</h2></div></div>
        <form class="settings-form" method="POST" action="{{ $assignment->exists ? route('asset-handovers.update', $assignment) : route('asset-handovers.store') }}">
            @csrf
            @if($assignment->exists) @method('PUT') @endif
            <div class="form-grid">
                <label class="form-field"><span>Employee</span><select name="employee_id"><option value="">Select employee</option>@foreach($employees as $employee)<option value="{{ $employee->id }}" @selected((string) old('employee_id', $assignment->employee_id) === (string) $employee->id)>{{ $employee->employee_code }} - {{ $employee->name_en }}</option>@endforeach</select>@error('employee_id')<small>{{ $message }}</small>@enderror</label>
                <label class="form-field"><span>Available Asset</span><select name="asset_id"><option value="">Select asset</option>@foreach($assets as $asset)<option value="{{ $asset->id }}" @selected((string) old('asset_id', $assignment->asset_id) === (string) $asset->id)>{{ $asset->asset_tag }} - {{ $asset->name }} ({{ $asset->category?->name }})</option>@endforeach</select>@error('asset_id')<small>{{ $message }}</small>@enderror</label>
                <label class="form-field"><span>Handover Date</span><input type="date" name="handover_date" value="{{ old('handover_date', optional($assignment->handover_date)->format('Y-m-d') ?? now(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d')) }}">@error('handover_date')<small>{{ $message }}</small>@enderror</label>
                <label class="form-field"><span>Expected Return Date</span><input type="date" name="expected_return_date" value="{{ old('expected_return_date', optional($assignment->expected_return_date)->format('Y-m-d')) }}">@error('expected_return_date')<small>{{ $message }}</small>@enderror</label>
                <label class="form-field form-field-wide"><span>Handover Notes</span><textarea name="handover_notes">{{ old('handover_notes', $assignment->handover_notes) }}</textarea>@error('handover_notes')<small>{{ $message }}</small>@enderror</label>
            </div>
            <div class="form-actions"><a class="btn btn-outline" href="{{ $assignment->exists ? route('asset-handovers.show', $assignment) : route('asset-handovers.index') }}">Cancel</a><button class="btn btn-primary btn-lg" type="submit">{{ $assignment->exists ? 'Update' : 'Save' }}</button></div>
        </form>
    </section>
@endsection
