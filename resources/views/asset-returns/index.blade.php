@extends('layouts.dashboard')

@section('title', 'Asset Returns')
@section('page-title', 'Asset Return')
@section('eyebrow', 'Returns')

@section('content')
    @php($hasReturnFilters = request()->filled('return_search'))
    @php($hasHistoryFilters = request()->filled('history_search') || request()->filled('history_condition'))
    @php($activeTab = (request('tab') === 'returned' || ($hasHistoryFilters && request('tab') !== 'pending')) ? 'returned' : 'pending')
    @php($canUploadSignedReturns = auth()->user()?->canAccess('asset-returns.create'))

    <nav class="return-tabs segmented-control" aria-label="Asset return sections">
        <a class="{{ $activeTab === 'pending' ? 'is-active' : '' }}" href="{{ route('asset-returns.index', ['tab' => 'pending']) }}">
            <span>To Return</span>
            <strong>{{ $assignments->total() }}</strong>
        </a>
        <a class="{{ $activeTab === 'returned' ? 'is-active' : '' }}" href="{{ route('asset-returns.index', ['tab' => 'returned']) }}">
            <span>Returned Assets</span>
            <strong>{{ $returns->total() }}</strong>
        </a>
    </nav>

    <section class="dashboard-panel client-listing-panel return-tab-panel {{ $activeTab === 'pending' ? 'is-active' : '' }} {{ $hasReturnFilters ? 'is-open' : '' }}" data-listing-filter @if($activeTab !== 'pending') hidden @endif>
        <div class="panel-heading">
            <label class="client-search listing-global-search"><x-dashboard.icon name="search" /><input form="return-filter-form" value="{{ request('return_search') }}" placeholder="Search pending returns..." data-auto-filter-control data-filter-proxy="return_search"></label>
            <div class="button-row">
                <button class="btn btn-secondary action-icon-btn action-icon-neutral" type="button" aria-label="Filters" data-tooltip="Filters" data-filter-toggle aria-expanded="{{ $hasReturnFilters ? 'true' : 'false' }}"><x-dashboard.icon name="funnel" /></button>
                @if ($hasReturnFilters)
                    <a class="btn btn-secondary action-icon-btn action-icon-neutral" href="{{ route('asset-returns.index') }}" aria-label="Reset Filter" data-tooltip="Reset Filter"><x-dashboard.icon name="x" /></a>
                @endif
            </div>
        </div>
        <form id="return-filter-form" class="client-toolbar listing-filter-fields" method="GET" data-filter-panel data-auto-filter-form @unless($hasReturnFilters) hidden @endunless>
            <span class="filter-label">Filter by:</span>
            <input type="hidden" name="tab" value="pending">
            <input type="hidden" name="history_search" value="{{ request('history_search') }}">
            <input type="hidden" name="history_condition" value="{{ request('history_condition') }}">
            <input type="hidden" name="history_per_page" value="{{ request('history_per_page', $returns->perPage()) }}">
            <label class="client-search"><input name="return_search" value="{{ request('return_search') }}" placeholder="Employee or asset"></label>
        </form>
        <div class="responsive-table">
            <table class="advanced-table">
                <thead><tr><th>Asset</th><th>Employee</th><th>Handover Date</th><th>Expected Return</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse($assignments as $assignment)
                        <tr>
                            <td><div class="client-person"><span>{{ strtoupper(substr($assignment->asset?->asset_tag ?? 'RT', 0, 2)) }}</span><div><strong>{{ $assignment->asset?->asset_tag }}</strong><small>{{ $assignment->asset?->name ?? '-' }}</small></div></div></td>
                            <td>{{ $assignment->employee?->name_en }}<br><small>{{ $assignment->employee?->employee_code }}</small></td>
                            <td>{{ $assignment->handover_date?->format('M d, Y') ?? '-' }}</td>
                            <td>{{ $assignment->expected_return_date?->format('M d, Y') ?? '-' }}</td>
                            <td><span class="status-badge status-{{ $assignment->status?->value }}">{{ $assignment->status?->label() }}</span></td>
                            <td>
                                <div class="table-action-row">
                                    <button type="button" class="btn btn-sm btn-outline table-action-btn action-icon-btn action-icon-edit" data-import-open="asset-return-{{ $assignment->id }}" aria-label="Return asset {{ $assignment->asset?->asset_tag }}" data-tooltip="Return Asset">
                                        <x-dashboard.icon name="rotate-ccw" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="table-empty" colspan="6">No assigned assets are pending return.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="table-footer">
            <span>Total {{ $assignments->total() }} item(s)</span>
            <div class="table-pagination">
                <label class="pagination-size">
                    <span>Items per page</span>
                    <select name="return_per_page" form="return-filter-form" aria-label="Items per page" data-auto-filter-control data-native-select>
                        @foreach ([10, 20, 30, 40, 50] as $size)
                            <option value="{{ $size }}" @selected($assignments->perPage() === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </label>
                <span class="pagination-page">Page {{ $assignments->currentPage() }} of {{ $assignments->lastPage() }}</span>
                <div class="pagination-controls">
                    <a class="action-icon-btn action-icon-neutral {{ $assignments->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $assignments->url(1) }}" aria-label="First page"><x-dashboard.icon name="chevrons-left" /></a>
                    <a class="action-icon-btn action-icon-neutral {{ $assignments->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $assignments->previousPageUrl() ?? '#' }}" aria-label="Previous page"><x-dashboard.icon name="chevron-left" /></a>
                    <a class="action-icon-btn action-icon-neutral {{ $assignments->hasMorePages() ? '' : 'is-disabled' }}" href="{{ $assignments->nextPageUrl() ?? '#' }}" aria-label="Next page"><x-dashboard.icon name="chevron-right" /></a>
                    <a class="action-icon-btn action-icon-neutral {{ $assignments->hasMorePages() ? '' : 'is-disabled' }}" href="{{ $assignments->url($assignments->lastPage()) }}" aria-label="Last page"><x-dashboard.icon name="chevrons-right" /></a>
                </div>
            </div>
        </div>
    </section>

    <section class="dashboard-panel client-listing-panel return-history-panel return-tab-panel {{ $activeTab === 'returned' ? 'is-active' : '' }} {{ $hasHistoryFilters ? 'is-open' : '' }}" data-listing-filter @if($activeTab !== 'returned') hidden @endif>
        <div class="panel-heading">
            <label class="client-search listing-global-search"><x-dashboard.icon name="search" /><input form="history-filter-form" value="{{ request('history_search') }}" placeholder="Search return history..." data-auto-filter-control data-filter-proxy="history_search"></label>
            <div class="button-row">
                <button class="btn btn-secondary action-icon-btn action-icon-neutral" type="button" aria-label="History Filters" data-tooltip="Filters" data-filter-toggle aria-expanded="{{ $hasHistoryFilters ? 'true' : 'false' }}"><x-dashboard.icon name="funnel" /></button>
                @if ($hasHistoryFilters)
                    <a class="btn btn-secondary action-icon-btn action-icon-neutral" href="{{ route('asset-returns.index', [
                        'tab' => 'returned',
                        'return_search' => request('return_search'),
                        'return_per_page' => request('return_per_page', $assignments->perPage()),
                    ]) }}" aria-label="Reset History Filter" data-tooltip="Reset Filter"><x-dashboard.icon name="x" /></a>
                @endif
            </div>
        </div>
        <form id="history-filter-form" class="client-toolbar listing-filter-fields" method="GET" data-filter-panel data-auto-filter-form @unless($hasHistoryFilters) hidden @endunless>
            <span class="filter-label">History filter:</span>
            <input type="hidden" name="tab" value="returned">
            <input type="hidden" name="return_search" value="{{ request('return_search') }}">
            <input type="hidden" name="return_per_page" value="{{ request('return_per_page', $assignments->perPage()) }}">
            <label class="client-search"><input name="history_search" value="{{ request('history_search') }}" placeholder="Employee or asset"></label>
            <select name="history_condition" aria-label="Filter history by condition" data-native-select>
                <option value="">All Conditions</option>
                @foreach($conditions as $condition)
                    <option value="{{ $condition->value }}" @selected(request('history_condition') === $condition->value)>{{ $condition->label() }}</option>
                @endforeach
            </select>
        </form>
        <div class="responsive-table">
            <table class="advanced-table">
                <thead><tr><th>Asset</th><th>Employee</th><th>Returned At</th><th>Condition</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse($returns as $return)
                        <tr>
                            <td><div class="client-person"><span>{{ strtoupper(substr($return->asset?->asset_tag ?? 'RT', 0, 2)) }}</span><div><strong>{{ $return->asset?->asset_tag }}</strong><small>{{ $return->asset?->name ?? '-' }}</small></div></div></td>
                            <td>{{ $return->employee?->name_en }}<br><small>{{ $return->employee?->employee_code }}</small></td>
                            <td>{{ $return->returned_at?->format('M d, Y') }}</td>
                            <td><span class="status-badge status-{{ $return->condition?->value }}">{{ $return->condition?->label() }}</span></td>
                            <td>
                                <div class="table-action-row">
                                    @if ($return->signed_file_path)
                                        <button type="button" class="btn btn-sm btn-outline table-action-btn action-icon-btn action-icon-view" data-import-open="signed-return-{{ $return->id }}" aria-label="View signed return for {{ $return->asset?->asset_tag }}" data-tooltip="View Signed Return"><x-dashboard.icon name="eye" /></button>
                                    @endif
                                    @if ($canUploadSignedReturns)
                                        <form method="POST" action="{{ route('asset-returns.signed', $return) }}" enctype="multipart/form-data" class="signed-return-upload-form">
                                            @csrf
                                            <label class="btn btn-sm btn-outline table-action-btn action-icon-btn action-icon-edit" aria-label="{{ $return->signed_file_path ? 'Replace signed return document' : 'Upload signed return document' }}" data-tooltip="{{ $return->signed_file_path ? 'Replace Signed Return' : 'Upload Signed Return' }}">
                                                <input type="file" name="signed_file" accept=".pdf,.jpg,.jpeg,.png,.webp" required onchange="this.form.submit()">
                                                <x-dashboard.icon name="upload" />
                                            </label>
                                        </form>
                                    @endif
                                    <a class="btn btn-sm btn-outline table-action-btn action-icon-btn action-icon-neutral" href="{{ route('asset-returns.print', $return) }}" target="_blank" aria-label="Print return PDF for {{ $return->asset?->asset_tag }}" data-tooltip="Print"><x-dashboard.icon name="printer" /></a>
                                </div>
                                @error('signed_file')<small class="field-error">{{ $message }}</small>@enderror
                            </td>
                        </tr>
                    @empty
                        <tr><td class="table-empty" colspan="5">No returned assets found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="table-footer">
            <span>Total {{ $returns->total() }} item(s)</span>
            <div class="table-pagination">
                <label class="pagination-size">
                    <span>Items per page</span>
                    <select name="history_per_page" form="history-filter-form" aria-label="History items per page" data-auto-filter-control data-native-select>
                        @foreach ([10, 20, 30, 40, 50] as $size)
                            <option value="{{ $size }}" @selected($returns->perPage() === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </label>
                <span class="pagination-page">Page {{ $returns->currentPage() }} of {{ $returns->lastPage() }}</span>
                <div class="pagination-controls">
                    <a class="action-icon-btn action-icon-neutral {{ $returns->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $returns->url(1) }}" aria-label="First history page"><x-dashboard.icon name="chevrons-left" /></a>
                    <a class="action-icon-btn action-icon-neutral {{ $returns->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $returns->previousPageUrl() ?? '#' }}" aria-label="Previous history page"><x-dashboard.icon name="chevron-left" /></a>
                    <a class="action-icon-btn action-icon-neutral {{ $returns->hasMorePages() ? '' : 'is-disabled' }}" href="{{ $returns->nextPageUrl() ?? '#' }}" aria-label="Next history page"><x-dashboard.icon name="chevron-right" /></a>
                    <a class="action-icon-btn action-icon-neutral {{ $returns->hasMorePages() ? '' : 'is-disabled' }}" href="{{ $returns->url($returns->lastPage()) }}" aria-label="Last history page"><x-dashboard.icon name="chevrons-right" /></a>
                </div>
            </div>
        </div>
    </section>

    @foreach ($returns as $return)
        @if ($return->signed_file_path)
            @php($signedReturnUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($return->signed_file_path))
            <div class="modal-backdrop" data-import-modal="signed-return-{{ $return->id }}" hidden>
                <div class="modal-card signed-return-viewer-modal" role="dialog" aria-modal="true" aria-labelledby="signed-return-title-{{ $return->id }}">
                    <div class="modal-heading">
                        <div>
                            <p>Signed Asset Return</p>
                            <h2 id="signed-return-title-{{ $return->id }}">{{ $return->asset?->asset_tag }} signed copy</h2>
                        </div>
                        <button type="button" class="action-icon-btn action-icon-neutral" data-modal-close aria-label="Close signed return viewer" data-tooltip="Close"><x-dashboard.icon name="x" /></button>
                    </div>
                    <div class="signed-return-viewer">
                        <iframe src="{{ $signedReturnUrl }}" title="Signed return document for {{ $return->asset?->asset_tag }}"></iframe>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" data-modal-close>Close</button>
                        <a class="btn btn-primary" href="{{ $signedReturnUrl }}" target="_blank">Open File</a>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    @foreach ($assignments as $assignment)
        <div class="modal-backdrop" data-import-modal="asset-return-{{ $assignment->id }}" hidden>
            <div class="modal-card return-process-modal" role="dialog" aria-modal="true" aria-labelledby="return-form-title-{{ $assignment->id }}">
                <div class="modal-heading">
                    <div>
                        <p>Return process</p>
                        <h2 id="return-form-title-{{ $assignment->id }}">Return Assigned Asset</h2>
                    </div>
                    <button type="button" class="action-icon-btn action-icon-neutral" data-modal-close aria-label="Close return form" data-tooltip="Close"><x-dashboard.icon name="x" /></button>
                </div>
                <div class="return-list">
                    <form class="return-card" method="POST" action="{{ route('asset-returns.store', $assignment) }}">
                        @csrf
                        <strong>{{ $assignment->asset?->asset_tag }} - {{ $assignment->asset?->name }}</strong>
                        <span>{{ $assignment->employee?->employee_code }} - {{ $assignment->employee?->name_en }}</span>
                        <div class="form-grid single">
                            <label class="form-field"><span>Returned At</span><input type="date" name="returned_at" value="{{ now(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d') }}"></label>
                            <label class="form-field"><span>Condition</span><select name="condition">@foreach($conditions as $condition)<option value="{{ $condition->value }}">{{ $condition->label() }}</option>@endforeach</select></label>
                            <label class="form-field"><span>Notes</span><textarea name="notes"></textarea></label>
                        </div>
                        <div class="form-actions return-form-actions">
                            <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
                            <button class="btn btn-primary" type="submit"><x-dashboard.icon name="rotate-ccw" /><span>Return Asset</span></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endsection
