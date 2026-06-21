@extends('layouts.dashboard')

@section('title', 'Declaration')
@section('page-title', 'Declaration Details')
@section('eyebrow', 'Documents')

@section('content')
    @php($assignment = $declaration->assignment)

    <section class="dashboard-panel handover-detail-panel declaration-detail-panel">
        <div class="handover-hero">
            <div class="handover-identity">
                <span class="handover-kicker">Declaration</span>
                <h2>{{ $declaration->declaration_number }}</h2>
                <p>{{ $assignment?->asset?->asset_tag }} to {{ $assignment?->employee?->name_en }}</p>
            </div>
            <div class="handover-actions button-row">
                <span class="status-badge status-{{ $assignment?->status?->value }}">{{ $assignment?->status?->label() }}</span>
                <a class="btn btn-secondary action-icon-btn action-icon-neutral" href="{{ route('asset-handovers.show', $assignment) }}" aria-label="View Handover" data-tooltip="View Handover"><x-dashboard.icon name="eye" /></a>
                <a class="btn btn-primary action-icon-btn action-icon-neutral" href="{{ route('declarations.print', $declaration) }}" target="_blank" aria-label="Print PDF" data-tooltip="Print PDF"><x-dashboard.icon name="printer" /></a>
            </div>
        </div>

        <div class="handover-status-strip">
            <div>
                <span>Issued Date</span>
                <strong>{{ $declaration->issued_at?->format('M d, Y') }}</strong>
            </div>
            <div>
                <span>Handover Date</span>
                <strong>{{ $assignment?->handover_date?->format('M d, Y') ?? '-' }}</strong>
            </div>
            <div>
                <span>Expected Return</span>
                <strong>{{ $assignment?->expected_return_date?->format('M d, Y') ?? '-' }}</strong>
            </div>
            <div>
                <span>Asset State</span>
                <strong>{{ $assignment?->asset?->status?->label() ?? '-' }}</strong>
                <small>{{ $assignment?->asset?->condition?->label() ?? '-' }}</small>
            </div>
        </div>

        <div class="handover-detail-layout">
            <article class="handover-info-card">
                <div class="handover-card-heading">
                    <span>Employee</span>
                    <strong>{{ $assignment?->employee?->name_en }}</strong>
                </div>
                <dl>
                    <div><dt>Code</dt><dd>{{ $assignment?->employee?->employee_code }}</dd></div>
                    <div><dt>Arabic Name</dt><dd>{{ $assignment?->employee?->name_ar ?: '-' }}</dd></div>
                    <div><dt>Handover</dt><dd>#{{ $assignment?->id }}</dd></div>
                </dl>
            </article>

            <article class="handover-info-card">
                <div class="handover-card-heading">
                    <span>Asset</span>
                    <strong>{{ $assignment?->asset?->name }}</strong>
                </div>
                <dl>
                    <div><dt>Tag</dt><dd>{{ $assignment?->asset?->asset_tag }}</dd></div>
                    <div><dt>Category</dt><dd>{{ $assignment?->asset?->category?->name ?? '-' }}</dd></div>
                    <div><dt>Brand</dt><dd>{{ $assignment?->asset?->brand?->name ?? '-' }}</dd></div>
                    <div><dt>Serial</dt><dd>{{ $assignment?->asset?->serial_number ?: '-' }}</dd></div>
                </dl>
            </article>

            <article class="handover-info-card">
                <div class="handover-card-heading">
                    <span>Document</span>
                    <strong>Responsibility acknowledgement</strong>
                </div>
                <dl>
                    <div><dt>Number</dt><dd>{{ $declaration->declaration_number }}</dd></div>
                    <div><dt>Status</dt><dd>{{ $assignment?->status?->label() ?? '-' }}</dd></div>
                    <div><dt>Signed Copy</dt><dd>{{ $declaration->signed_file_path ? 'Uploaded' : 'Pending' }}</dd></div>
                </dl>
            </article>
        </div>

        <div class="signed-declaration-panel">
            <div>
                <span>Signed Declaration</span>
                <strong>{{ $declaration->signed_file_path ? $declaration->signed_file_name : 'Upload after employee signature' }}</strong>
                <p>{{ $declaration->signed_uploaded_at ? 'Uploaded '.$declaration->signed_uploaded_at->format('M d, Y h:i A') : 'Print the declaration, collect the employee signature, then upload the scanned PDF or image here.' }}</p>
            </div>
            <div class="signed-declaration-actions">
                @if ($declaration->signed_file_path)
                    <a class="btn btn-secondary" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($declaration->signed_file_path) }}" target="_blank">View Signed Copy</a>
                @endif
                @if (auth()->user()?->canAccess('declarations.create'))
                    <form method="POST" action="{{ route('declarations.signed', $declaration) }}" enctype="multipart/form-data">
                        @csrf
                        <label class="btn btn-outline signed-upload-control">
                            <input type="file" name="signed_file" accept=".pdf,.jpg,.jpeg,.png,.webp" required onchange="this.form.submit()">
                            <span>{{ $declaration->signed_file_path ? 'Replace Signed Copy' : 'Upload Signed Copy' }}</span>
                        </label>
                        @error('signed_file')<small>{{ $message }}</small>@enderror
                    </form>
                @endif
            </div>
        </div>

        <div class="declaration-terms">
            <span>Declaration Terms</span>
            <p>{{ $declaration->terms }}</p>
        </div>
    </section>
@endsection
