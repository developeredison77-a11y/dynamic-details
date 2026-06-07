@extends('layouts.dashboard')

@section('title', 'Imports')
@section('page-title', 'Import Existing Data')
@section('eyebrow', 'Imports')

@section('content')
    @php($employeeImportErrors = $errors->getBag('employeeImport'))
    @php($assetImportErrors = $errors->getBag('assetImport'))
    <section class="split-panel">
        <article class="dashboard-panel">
            <div class="panel-heading"><div><p>CSV import</p><h2>Upload Employees</h2></div></div>
            <form class="settings-form" method="POST" action="{{ route('imports.employees') }}" enctype="multipart/form-data">
                @csrf
                <label class="form-field file-field {{ $employeeImportErrors->has('file') ? 'has-error' : '' }}"><span>Employee Excel / CSV</span><input type="file" name="file" accept=".xlsx,.csv,text/csv">@if ($employeeImportErrors->has('file'))<small>{{ $employeeImportErrors->first('file') }}</small>@endif</label>
                @if ($employeeImportErrors->has('rows'))
                    <div class="import-validation" role="alert">
                        <strong>Employee import validation failed</strong>
                        @foreach ($employeeImportErrors->get('rows') as $message)
                            <p>{{ $message }}</p>
                        @endforeach
                    </div>
                @endif
                <div class="import-help"><span>Required: name_en, email. Employee codes are generated automatically. CSV/XLSX only, up to 5 MB. Emails must be unique.</span><a class="btn btn-outline btn-sm" href="{{ route('imports.template', 'employees') }}"><x-dashboard.icon name="download" /> Download Template</a></div>
                <div class="form-actions"><button class="btn btn-primary" type="submit">Import Employees</button></div>
            </form>
        </article>
        <article class="dashboard-panel">
            <div class="panel-heading"><div><p>CSV import</p><h2>Upload Assets</h2></div></div>
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
                <div class="import-help"><span>Required: asset_tag, name, asset_category_id. Optional: asset_brand_id, serial_number, model, condition. Use IDs from the reference list below.</span><a class="btn btn-outline btn-sm" href="{{ route('imports.template', 'assets') }}"><x-dashboard.icon name="download" /> Download Template</a></div>
                <div class="form-actions"><button class="btn btn-primary" type="submit">Import Assets</button></div>
            </form>
            @include('imports.partials.asset-reference')
        </article>
    </section>
    <section class="dashboard-panel">
        <div class="panel-heading"><div><p>Validation and duplicate checking</p><h2>Import History</h2></div></div>
        <div class="responsive-table"><table class="advanced-table"><thead><tr><th>File</th><th>Type</th><th>Total</th><th>Success</th><th>Failed</th><th>Errors</th></tr></thead><tbody>
            @forelse($batches as $batch)<tr><td>{{ $batch->file_name }}</td><td>{{ $batch->type?->label() }}</td><td>{{ $batch->total_rows }}</td><td>{{ $batch->successful_rows }}</td><td>{{ $batch->failed_rows }}</td><td><small>{{ collect($batch->errors)->pluck('messages')->flatten()->take(2)->implode(' | ') ?: '-' }}</small></td></tr>@empty<tr><td class="table-empty" colspan="6">No import history found.</td></tr>@endforelse
        </tbody></table></div>
        <x-dashboard.pagination :paginator="$batches" form-id="import-history-pagination" label="item(s)" />
    </section>
@endsection
