@extends('layouts.dashboard')

@section('title', 'Declaration Forms')
@section('page-title', 'Declaration Forms')
@section('eyebrow', 'Documents')

@section('content')
    <section class="split-panel">
        <article class="dashboard-panel">
            <div class="panel-heading"><div><p>Generate declaration</p><h2>Assigned Assets</h2></div></div>
            <div class="return-list">
                @forelse($assignments as $assignment)
                    <form class="return-card" method="POST" action="{{ route('declarations.store', $assignment) }}">
                        @csrf
                        <strong>{{ $assignment->employee?->name_en }}</strong>
                        <span>{{ $assignment->asset?->asset_tag }} - {{ $assignment->asset?->name }}</span>
                        <button class="btn btn-primary action-icon-btn action-icon-edit" type="submit" aria-label="Generate Declaration" data-tooltip="Generate Declaration"><x-dashboard.icon name="file-plus" /></button>
                    </form>
                @empty
                    <div class="empty-state">No assigned assets available for declaration.</div>
                @endforelse
            </div>
        </article>
        <article class="dashboard-panel">
            <div class="panel-heading"><div><p>History</p><h2>Declaration History</h2></div></div>
            <div class="responsive-table"><table class="advanced-table"><thead><tr><th>No</th><th>Employee</th><th>Asset</th><th>Issued</th><th>Action</th></tr></thead><tbody>
                @forelse($declarations as $declaration)<tr><td>{{ $declaration->declaration_number }}</td><td>{{ $declaration->assignment?->employee?->name_en }}</td><td>{{ $declaration->assignment?->asset?->asset_tag }}</td><td>{{ $declaration->issued_at?->format('M d, Y') }}</td><td><div class="table-action-row"><a class="btn btn-sm btn-outline table-action-btn action-icon-btn action-icon-view" href="{{ route('declarations.show', $declaration) }}" aria-label="View declaration {{ $declaration->declaration_number }}" data-tooltip="View"><x-dashboard.icon name="eye" /></a></div></td></tr>@empty<tr><td class="table-empty" colspan="5">No declarations found.</td></tr>@endforelse
            </tbody></table></div><div class="table-footer">{{ $declarations->links() }}</div>
        </article>
    </section>
@endsection
