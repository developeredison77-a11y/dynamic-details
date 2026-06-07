@extends('layouts.dashboard')

@section('title', 'Import Assets')
@section('page-title', 'Import Assets')
@section('eyebrow', 'Asset Imports')

@section('content')
    @php($assetImportErrors = $errors->getBag('assetImport'))
    <section class="dashboard-panel">
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
            <div class="import-help"><span>Required: asset_tag, name, asset_category_id. Optional: asset_brand_id, serial_number, model, condition. Use IDs from the reference list below; condition accepts new, good, fair, or damaged.</span><a class="btn btn-outline btn-sm" href="{{ route('imports.template', 'assets') }}"><x-dashboard.icon name="download" /> Download Template</a></div>
            <div class="form-actions"><button class="btn btn-primary" type="submit">Import Assets</button></div>
        </form>
    </section>
    <section class="dashboard-panel">
        <div class="panel-heading"><div><p>Spreadsheet reference</p><h2>Asset Import Values</h2></div></div>
        @include('imports.partials.asset-reference')
    </section>
    <section class="dashboard-panel">
        <div class="panel-heading"><div><p>Validation and duplicate checking</p><h2>Asset Import History</h2></div></div>
        <div class="responsive-table"><table class="advanced-table"><thead><tr><th>File</th><th>Total</th><th>Success</th><th>Failed</th><th>Errors</th></tr></thead><tbody>
            @forelse($batches as $batch)<tr><td>{{ $batch->file_name }}</td><td>{{ $batch->total_rows }}</td><td>{{ $batch->successful_rows }}</td><td>{{ $batch->failed_rows }}</td><td><small>{{ collect($batch->errors)->pluck('messages')->flatten()->take(2)->implode(' | ') ?: '-' }}</small></td></tr>@empty<tr><td class="table-empty" colspan="5">No asset import history found.</td></tr>@endforelse
        </tbody></table></div>
        <x-dashboard.pagination :paginator="$batches" form-id="asset-import-history-pagination" label="item(s)" />
    </section>
@endsection
