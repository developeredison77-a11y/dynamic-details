@extends('layouts.dashboard')

@section('title', 'Handover Details')
@section('page-title', 'Handover Details')
@section('eyebrow', 'Asset Handover')

@section('content')
    @php
        $canManageHandovers = auth()->user()?->canAccess('asset-handovers.update') || auth()->user()?->canAccess('asset-handovers.create');
        $isReturned = $assignment->status?->value === 'returned';
        $returnDate = $assignment->returned_at ?? $assignment->returnRecord?->returned_at;
        $returnCondition = $assignment->return_condition ?? $assignment->returnRecord?->condition;
        $daysText = null;

        if ($assignment->handover_date && $assignment->expected_return_date) {
            $days = $assignment->handover_date->diffInDays($assignment->expected_return_date);
            $daysText = $days === 0 ? 'Same day' : $days.' day'.($days === 1 ? '' : 's');
        }
    @endphp

    <section class="dashboard-panel handover-detail-panel">
        <div class="handover-hero">
            <div class="handover-identity">
                <span class="handover-kicker">Handover #{{ $assignment->id }}</span>
                <h2>{{ $assignment->asset?->asset_tag }} to {{ $assignment->employee?->name_en }}</h2>
                <p>{{ $assignment->asset?->name }}{{ $assignment->asset?->model ? ' - '.$assignment->asset?->model : '' }}</p>
            </div>
            <div class="handover-actions button-row">
                <span class="status-badge status-{{ $assignment->status?->value }}">{{ $assignment->status?->label() }}</span>
                @if ($canManageHandovers)
                    @if ($assignment->canBeEdited())
                        <a class="btn btn-secondary action-icon-btn action-icon-edit" href="{{ route('asset-handovers.edit', $assignment) }}" aria-label="Edit Handover" data-tooltip="Edit"><x-dashboard.icon name="edit" /></a>
                    @else
                        <button type="button" class="btn btn-secondary action-icon-btn action-icon-neutral" aria-label="Why edit is locked" data-tooltip="Why locked?" data-handover-edit-locked data-toast-message="This handover can be edited only before the handover date starts."><x-dashboard.icon name="edit" /></button>
                    @endif
                @endif
                <form method="POST" action="{{ route('declarations.store', $assignment) }}">@csrf<button class="btn btn-secondary action-icon-btn action-icon-neutral" type="submit" aria-label="Generate Declaration" data-tooltip="Generate Declaration"><x-dashboard.icon name="file-plus" /></button></form>
                <a class="btn btn-primary action-icon-btn action-icon-neutral" href="{{ route('asset-handovers.print', $assignment) }}" target="_blank" aria-label="Print PDF" data-tooltip="Print PDF"><x-dashboard.icon name="printer" /></a>
            </div>
        </div>

        <div class="handover-status-strip">
            <div>
                <span>Handover Date</span>
                <strong>{{ $assignment->handover_date?->format('M d, Y') }}</strong>
            </div>
            <div>
                <span>Expected Return</span>
                <strong>{{ $assignment->expected_return_date?->format('M d, Y') ?? '-' }}</strong>
                @if ($daysText)<small>{{ $daysText }} planned</small>@endif
            </div>
            <div>
                <span>Actual Return</span>
                <strong>{{ $returnDate?->format('M d, Y') ?? 'Pending' }}</strong>
            </div>
            <div>
                <span>Asset State</span>
                <strong>{{ $assignment->asset?->status?->label() ?? '-' }}</strong>
                <small>{{ $assignment->asset?->condition?->label() ?? '-' }}</small>
            </div>
        </div>

        <div class="handover-detail-layout">
            <article class="handover-info-card">
                <div class="handover-card-heading">
                    <span>Employee</span>
                    <strong>{{ $assignment->employee?->name_en }}</strong>
                </div>
                <dl>
                    <div><dt>Code</dt><dd>{{ $assignment->employee?->employee_code }}</dd></div>
                    <div><dt>Arabic Name</dt><dd>{{ $assignment->employee?->name_ar ?: '-' }}</dd></div>
                    <div><dt>Created By</dt><dd>{{ $assignment->creator?->name ?? '-' }}</dd></div>
                </dl>
            </article>

            <article class="handover-info-card">
                <div class="handover-card-heading">
                    <span>Asset</span>
                    <strong>{{ $assignment->asset?->name }}</strong>
                </div>
                <dl>
                    <div><dt>Tag</dt><dd>{{ $assignment->asset?->asset_tag }}</dd></div>
                    <div><dt>Category</dt><dd>{{ $assignment->asset?->category?->name ?? '-' }}</dd></div>
                    <div><dt>Brand</dt><dd>{{ $assignment->asset?->brand?->name ?? '-' }}</dd></div>
                    <div><dt>Serial</dt><dd>{{ $assignment->asset?->serial_number ?: '-' }}</dd></div>
                </dl>
            </article>

            <article class="handover-info-card">
                <div class="handover-card-heading">
                    <span>Documents</span>
                    <strong>{{ $assignment->declaration ? 'Declaration ready' : 'Not generated' }}</strong>
                </div>
                <dl>
                    <div><dt>Declaration</dt><dd>{{ $assignment->declaration?->declaration_number ?? '-' }}</dd></div>
                    <div><dt>Return Status</dt><dd>{{ $isReturned ? 'Closed' : 'Open' }}</dd></div>
                    <div><dt>Return Condition</dt><dd>{{ $returnCondition?->label() ?? '-' }}</dd></div>
                </dl>
            </article>
        </div>

        <div class="handover-notes-grid">
            <article>
                <span>Handover Notes</span>
                <p>{{ $assignment->handover_notes ?: 'No handover notes recorded.' }}</p>
            </article>
            <article>
                <span>Return Notes</span>
                <p>{{ $assignment->return_notes ?: ($assignment->returnRecord?->notes ?: 'No return notes recorded.') }}</p>
            </article>
        </div>
    </section>
@endsection
