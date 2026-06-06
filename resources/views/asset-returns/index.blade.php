@extends('layouts.dashboard')

@section('title', 'Asset Returns')
@section('page-title', 'Asset Return')
@section('eyebrow', 'Returns')

@section('content')
    <section class="split-panel">
        <article class="dashboard-panel">
            <div class="panel-heading"><div><p>Return process</p><h2>Return Assigned Asset</h2></div></div>
            <div class="return-list">
                @forelse($assignments as $assignment)
                    <form class="return-card" method="POST" action="{{ route('asset-returns.store', $assignment) }}">
                        @csrf
                        <strong>{{ $assignment->asset?->asset_tag }} - {{ $assignment->asset?->name }}</strong>
                        <span>{{ $assignment->employee?->employee_code }} - {{ $assignment->employee?->name_en }}</span>
                        <div class="form-grid single">
                            <label class="form-field"><span>Returned At</span><input type="date" name="returned_at" value="{{ now()->format('Y-m-d') }}"></label>
                            <label class="form-field"><span>Condition</span><select name="condition">@foreach($conditions as $condition)<option value="{{ $condition->value }}">{{ $condition->label() }}</option>@endforeach</select></label>
                            <label class="form-field"><span>Notes</span><textarea name="notes"></textarea></label>
                        </div>
                        <button class="btn btn-primary action-icon-btn action-icon-edit" type="submit" aria-label="Return Asset" data-tooltip="Return Asset"><x-dashboard.icon name="rotate-ccw" /></button>
                    </form>
                @empty
                    <div class="empty-state">No assigned assets are pending return.</div>
                @endforelse
            </div>
        </article>
        <article class="dashboard-panel">
            <div class="panel-heading"><div><p>Return history</p><h2>Returned Assets</h2></div></div>
            <div class="responsive-table"><table class="advanced-table"><thead><tr><th>Asset</th><th>Employee</th><th>Date</th><th>Condition</th><th>Print</th></tr></thead><tbody>
                @forelse($returns as $return)<tr><td>{{ $return->asset?->asset_tag }}</td><td>{{ $return->employee?->name_en }}</td><td>{{ $return->returned_at?->format('M d, Y') }}</td><td>{{ $return->condition?->label() }}</td><td><div class="table-action-row"><a class="btn btn-sm btn-outline table-action-btn action-icon-btn action-icon-neutral" href="{{ route('asset-returns.print', $return) }}" target="_blank" aria-label="Print return PDF for {{ $return->asset?->asset_tag }}" data-tooltip="Print"><x-dashboard.icon name="printer" /></a></div></td></tr>@empty<tr><td class="table-empty" colspan="5">No returned assets found.</td></tr>@endforelse
            </tbody></table></div><div class="table-footer">{{ $returns->links() }}</div>
        </article>
    </section>
@endsection
