@extends('layouts.dashboard')

@section('title', 'Declaration')
@section('page-title', 'Declaration Details')
@section('eyebrow', 'Documents')

@section('content')
    <section class="dashboard-panel">
        <div class="panel-heading">
            <div><p>Printable document</p><h2>{{ $declaration->declaration_number }}</h2></div>
            <a class="btn btn-primary action-icon-btn action-icon-neutral" href="{{ route('declarations.print', $declaration) }}" target="_blank" aria-label="Print PDF" data-tooltip="Print PDF"><x-dashboard.icon name="printer" /></a>
        </div>
        <div class="detail-grid">
            <div><span>Employee</span><strong>{{ $declaration->assignment?->employee?->name_en }}</strong><small dir="rtl">{{ $declaration->assignment?->employee?->name_ar }}</small></div>
            <div><span>Asset</span><strong>{{ $declaration->assignment?->asset?->name }}</strong><small>{{ $declaration->assignment?->asset?->asset_tag }}</small></div>
            <div><span>Serial</span><strong>{{ $declaration->assignment?->asset?->serial_number ?: '-' }}</strong></div>
            <div><span>Issued At</span><strong>{{ $declaration->issued_at?->format('M d, Y') }}</strong></div>
        </div>
        <div class="document-preview">{{ $declaration->terms }}</div>
    </section>
@endsection
