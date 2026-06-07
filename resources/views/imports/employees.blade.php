@extends('layouts.dashboard')

@section('title', 'Import Employees')
@section('page-title', 'Import Employees')
@section('eyebrow', 'Employee Imports')

@section('content')
    @php($employeeImportErrors = $errors->getBag('employeeImport'))
    <section class="dashboard-panel">
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
    </section>
    <section class="dashboard-panel">
        <div class="panel-heading"><div><p>Validation and duplicate checking</p><h2>Employee Import History</h2></div></div>
        <div class="responsive-table"><table class="advanced-table"><thead><tr><th>File</th><th>Total</th><th>Success</th><th>Failed</th><th>Errors</th></tr></thead><tbody>
            @forelse($batches as $batch)<tr><td>{{ $batch->file_name }}</td><td>{{ $batch->total_rows }}</td><td>{{ $batch->successful_rows }}</td><td>{{ $batch->failed_rows }}</td><td><small>{{ collect($batch->errors)->pluck('messages')->flatten()->take(2)->implode(' | ') ?: '-' }}</small></td></tr>@empty<tr><td class="table-empty" colspan="5">No employee import history found.</td></tr>@endforelse
        </tbody></table></div>
        <x-dashboard.pagination :paginator="$batches" form-id="employee-import-history-pagination" label="item(s)" />
    </section>
@endsection
