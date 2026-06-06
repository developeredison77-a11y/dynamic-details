@extends('layouts.dashboard')

@section('title', 'Handover Details')
@section('page-title', 'Handover Details')
@section('eyebrow', 'Asset Handover')

@section('content')
    <section class="dashboard-panel">
        <div class="panel-heading">
            <div><p>Saved handover</p><h2>{{ $assignment->asset?->asset_tag }} to {{ $assignment->employee?->name_en }}</h2></div>
            <div class="button-row">
                <form method="POST" action="{{ route('declarations.store', $assignment) }}">@csrf<button class="btn btn-secondary" type="submit">Generate Declaration</button></form>
                <a class="btn btn-primary" href="{{ route('asset-handovers.print', $assignment) }}" target="_blank">Print PDF</a>
            </div>
        </div>
        <div class="detail-grid">
            <div><span>Employee</span><strong>{{ $assignment->employee?->name_en }}</strong><small>{{ $assignment->employee?->employee_code }}</small></div>
            <div><span>Asset</span><strong>{{ $assignment->asset?->name }}</strong><small>{{ $assignment->asset?->asset_tag }}</small></div>
            <div><span>Brand</span><strong>{{ $assignment->asset?->brand?->name ?? '-' }}</strong></div>
            <div><span>Serial</span><strong>{{ $assignment->asset?->serial_number ?: '-' }}</strong></div>
            <div><span>Handover Date</span><strong>{{ $assignment->handover_date?->format('M d, Y') }}</strong></div>
            <div><span>Status</span><strong>{{ $assignment->status?->label() }}</strong></div>
        </div>
    </section>
@endsection
