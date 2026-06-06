@extends('layouts.dashboard')

@section('title', 'Imports')
@section('page-title', 'Import Existing Data')
@section('eyebrow', 'Imports')

@section('content')
    <section class="split-panel">
        <article class="dashboard-panel">
            <div class="panel-heading"><div><p>CSV import</p><h2>Upload Employees</h2></div></div>
            <form class="settings-form" method="POST" action="{{ route('imports.employees') }}" enctype="multipart/form-data">
                @csrf
                <label class="form-field file-field"><span>Employee Excel / CSV</span><input type="file" name="file" accept=".xlsx,.csv,text/csv">@error('file')<small>{{ $message }}</small>@enderror</label>
                <div class="form-actions"><button class="btn btn-primary" type="submit">Import Employees</button></div>
            </form>
        </article>
        <article class="dashboard-panel">
            <div class="panel-heading"><div><p>CSV import</p><h2>Upload Assets</h2></div></div>
            <form class="settings-form" method="POST" action="{{ route('imports.assets') }}" enctype="multipart/form-data">
                @csrf
                <label class="form-field file-field"><span>Asset Excel / CSV</span><input type="file" name="file" accept=".xlsx,.csv,text/csv">@error('file')<small>{{ $message }}</small>@enderror</label>
                <div class="form-actions"><button class="btn btn-primary" type="submit">Import Assets</button></div>
            </form>
        </article>
    </section>
    <section class="dashboard-panel">
        <div class="panel-heading"><div><p>Validation and duplicate checking</p><h2>Import History</h2></div></div>
        <div class="responsive-table"><table class="advanced-table"><thead><tr><th>File</th><th>Type</th><th>Total</th><th>Success</th><th>Failed</th><th>Errors</th></tr></thead><tbody>
            @foreach($batches as $batch)<tr><td>{{ $batch->file_name }}</td><td>{{ $batch->type?->label() }}</td><td>{{ $batch->total_rows }}</td><td>{{ $batch->successful_rows }}</td><td>{{ $batch->failed_rows }}</td><td><small>{{ collect($batch->errors)->pluck('messages')->flatten()->take(2)->implode(' | ') ?: '-' }}</small></td></tr>@endforeach
        </tbody></table></div><div class="table-footer">{{ $batches->links() }}</div>
    </section>
@endsection
