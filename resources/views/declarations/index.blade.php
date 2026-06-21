@extends('layouts.dashboard')

@section('title', 'Declaration Forms')
@section('page-title', 'Declaration Forms')
@section('eyebrow', 'Documents')

@section('content')
    @php($hasHandoverFilters = request()->filled('handover_search'))
    @php($hasReturnFilters = request()->filled('return_search'))
    @php($activeTab = (request('tab') === 'return' || ($hasReturnFilters && request('tab') !== 'handover')) ? 'return' : 'handover')
    @php($canUploadHandoverSigned = auth()->user()?->canAccess('declarations.create'))
    @php($canUploadReturnSigned = auth()->user()?->canAccess('asset-returns.create'))

    <nav class="return-tabs segmented-control" aria-label="Declaration sections">
        <a class="{{ $activeTab === 'handover' ? 'is-active' : '' }}" href="{{ route('declarations.index', ['tab' => 'handover']) }}">
            <span>Handover Declarations</span>
            <strong>{{ $declarations->total() }}</strong>
        </a>
        <a class="{{ $activeTab === 'return' ? 'is-active' : '' }}" href="{{ route('declarations.index', ['tab' => 'return']) }}">
            <span>Return Declarations</span>
            <strong>{{ $returnDeclarations->total() }}</strong>
        </a>
    </nav>

    <section class="dashboard-panel client-listing-panel return-tab-panel {{ $activeTab === 'handover' ? 'is-active' : '' }} {{ $hasHandoverFilters ? 'is-open' : '' }}" data-listing-filter @if($activeTab !== 'handover') hidden @endif>
        <div class="panel-heading">
            <label class="client-search listing-global-search"><x-dashboard.icon name="search" /><input form="handover-declaration-filter-form" value="{{ request('handover_search') }}" placeholder="Search handover declarations..." data-auto-filter-control data-filter-proxy="handover_search"></label>
            <div class="button-row">
                <button class="btn btn-secondary action-icon-btn action-icon-neutral" type="button" aria-label="Filters" data-tooltip="Filters" data-filter-toggle aria-expanded="{{ $hasHandoverFilters ? 'true' : 'false' }}"><x-dashboard.icon name="funnel" /></button>
                @if ($hasHandoverFilters)
                    <a class="btn btn-secondary action-icon-btn action-icon-neutral" href="{{ route('declarations.index') }}" aria-label="Reset Filter" data-tooltip="Reset Filter"><x-dashboard.icon name="x" /></a>
                @endif
            </div>
        </div>
        <form id="handover-declaration-filter-form" class="client-toolbar listing-filter-fields" method="GET" data-filter-panel data-auto-filter-form @unless($hasHandoverFilters) hidden @endunless>
            <span class="filter-label">Filter by:</span>
            <input type="hidden" name="tab" value="handover">
            <input type="hidden" name="return_search" value="{{ request('return_search') }}">
            <input type="hidden" name="return_per_page" value="{{ request('return_per_page', $returnDeclarations->perPage()) }}">
            <label class="client-search"><input name="handover_search" value="{{ request('handover_search') }}" placeholder="Declaration, employee or asset"></label>
        </form>
        <div class="responsive-table">
            <table class="advanced-table">
                <thead><tr><th>Declaration</th><th>Employee</th><th>Asset</th><th>Issued</th><th>Signed Form</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse($declarations as $declaration)
                        <tr>
                            <td><strong>{{ $declaration->declaration_number }}</strong></td>
                            <td>{{ $declaration->assignment?->employee?->name_en }}<br><small>{{ $declaration->assignment?->employee?->employee_code }}</small></td>
                            <td>{{ $declaration->assignment?->asset?->asset_tag }}<br><small>{{ $declaration->assignment?->asset?->name }}</small></td>
                            <td>{{ $declaration->issued_at?->format('M d, Y') }}</td>
                            <td>{{ $declaration->signed_file_path ? 'Uploaded' : 'Pending' }}</td>
                            <td>
                                <div class="table-action-row">
                                    @if ($declaration->signed_file_path)
                                        <button type="button" class="btn btn-sm btn-outline table-action-btn action-icon-btn action-icon-view" data-import-open="signed-handover-declaration-{{ $declaration->id }}" aria-label="View signed declaration {{ $declaration->declaration_number }}" data-tooltip="View Signed Form"><x-dashboard.icon name="eye" /></button>
                                    @endif
                                    @if ($canUploadHandoverSigned)
                                        <form method="POST" action="{{ route('declarations.signed', $declaration) }}" enctype="multipart/form-data" class="signed-return-upload-form">
                                            @csrf
                                            <label class="btn btn-sm btn-outline table-action-btn action-icon-btn action-icon-edit" aria-label="{{ $declaration->signed_file_path ? 'Replace signed handover form' : 'Upload signed handover form' }}" data-tooltip="{{ $declaration->signed_file_path ? 'Replace Signed Form' : 'Upload Signed Form' }}">
                                                <input type="file" name="signed_file" accept=".pdf,.jpg,.jpeg,.png,.webp" required onchange="this.form.submit()">
                                                <x-dashboard.icon name="upload" />
                                            </label>
                                        </form>
                                    @endif
                                    <a class="btn btn-sm btn-outline table-action-btn action-icon-btn action-icon-neutral" href="{{ route('declarations.print', $declaration) }}" target="_blank" aria-label="Download handover declaration form {{ $declaration->declaration_number }}" data-tooltip="Download Form PDF"><x-dashboard.icon name="download" /></a>
                                </div>
                                @error('signed_file')<small class="field-error">{{ $message }}</small>@enderror
                            </td>
                        </tr>
                    @empty
                        <tr><td class="table-empty" colspan="6">No handover declarations found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="table-footer">
            <span>Total {{ $declarations->total() }} item(s)</span>
            <div class="table-pagination">
                <label class="pagination-size">
                    <span>Items per page</span>
                    <select name="handover_per_page" form="handover-declaration-filter-form" aria-label="Handover declarations per page" data-auto-filter-control data-native-select>
                        @foreach ([10, 20, 30, 40, 50] as $size)
                            <option value="{{ $size }}" @selected($declarations->perPage() === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </label>
                <span class="pagination-page">Page {{ $declarations->currentPage() }} of {{ $declarations->lastPage() }}</span>
                <div class="pagination-controls">
                    <a class="action-icon-btn action-icon-neutral {{ $declarations->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $declarations->url(1) }}" aria-label="First page"><x-dashboard.icon name="chevrons-left" /></a>
                    <a class="action-icon-btn action-icon-neutral {{ $declarations->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $declarations->previousPageUrl() ?? '#' }}" aria-label="Previous page"><x-dashboard.icon name="chevron-left" /></a>
                    <a class="action-icon-btn action-icon-neutral {{ $declarations->hasMorePages() ? '' : 'is-disabled' }}" href="{{ $declarations->nextPageUrl() ?? '#' }}" aria-label="Next page"><x-dashboard.icon name="chevron-right" /></a>
                    <a class="action-icon-btn action-icon-neutral {{ $declarations->hasMorePages() ? '' : 'is-disabled' }}" href="{{ $declarations->url($declarations->lastPage()) }}" aria-label="Last page"><x-dashboard.icon name="chevrons-right" /></a>
                </div>
            </div>
        </div>
    </section>

    <section class="dashboard-panel client-listing-panel return-tab-panel {{ $activeTab === 'return' ? 'is-active' : '' }} {{ $hasReturnFilters ? 'is-open' : '' }}" data-listing-filter @if($activeTab !== 'return') hidden @endif>
        <div class="panel-heading">
            <label class="client-search listing-global-search"><x-dashboard.icon name="search" /><input form="return-declaration-filter-form" value="{{ request('return_search') }}" placeholder="Search return declarations..." data-auto-filter-control data-filter-proxy="return_search"></label>
            <div class="button-row">
                <button class="btn btn-secondary action-icon-btn action-icon-neutral" type="button" aria-label="Filters" data-tooltip="Filters" data-filter-toggle aria-expanded="{{ $hasReturnFilters ? 'true' : 'false' }}"><x-dashboard.icon name="funnel" /></button>
                @if ($hasReturnFilters)
                    <a class="btn btn-secondary action-icon-btn action-icon-neutral" href="{{ route('declarations.index', [
                        'tab' => 'return',
                        'handover_search' => request('handover_search'),
                        'handover_per_page' => request('handover_per_page', $declarations->perPage()),
                    ]) }}" aria-label="Reset Filter" data-tooltip="Reset Filter"><x-dashboard.icon name="x" /></a>
                @endif
            </div>
        </div>
        <form id="return-declaration-filter-form" class="client-toolbar listing-filter-fields" method="GET" data-filter-panel data-auto-filter-form @unless($hasReturnFilters) hidden @endunless>
            <span class="filter-label">Filter by:</span>
            <input type="hidden" name="tab" value="return">
            <input type="hidden" name="handover_search" value="{{ request('handover_search') }}">
            <input type="hidden" name="handover_per_page" value="{{ request('handover_per_page', $declarations->perPage()) }}">
            <label class="client-search"><input name="return_search" value="{{ request('return_search') }}" placeholder="Employee or asset"></label>
        </form>
        <div class="responsive-table">
            <table class="advanced-table">
                <thead><tr><th>Return</th><th>Employee</th><th>Asset</th><th>Returned</th><th>Signed Form</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse($returnDeclarations as $return)
                        <tr>
                            <td><strong>#{{ $return->id }}</strong><br><small>{{ $return->condition?->label() }}</small></td>
                            <td>{{ $return->employee?->name_en }}<br><small>{{ $return->employee?->employee_code }}</small></td>
                            <td>{{ $return->asset?->asset_tag }}<br><small>{{ $return->asset?->name }}</small></td>
                            <td>{{ $return->returned_at?->format('M d, Y') }}</td>
                            <td>{{ $return->signed_file_path ? 'Uploaded' : 'Pending' }}</td>
                            <td>
                                <div class="table-action-row">
                                    @if ($return->signed_file_path)
                                        <button type="button" class="btn btn-sm btn-outline table-action-btn action-icon-btn action-icon-view" data-import-open="signed-return-declaration-{{ $return->id }}" aria-label="View signed return form #{{ $return->id }}" data-tooltip="View Signed Form"><x-dashboard.icon name="eye" /></button>
                                    @endif
                                    @if ($canUploadReturnSigned)
                                        <form method="POST" action="{{ route('asset-returns.signed', $return) }}" enctype="multipart/form-data" class="signed-return-upload-form">
                                            @csrf
                                            <label class="btn btn-sm btn-outline table-action-btn action-icon-btn action-icon-edit" aria-label="{{ $return->signed_file_path ? 'Replace signed return form' : 'Upload signed return form' }}" data-tooltip="{{ $return->signed_file_path ? 'Replace Signed Form' : 'Upload Signed Form' }}">
                                                <input type="file" name="signed_file" accept=".pdf,.jpg,.jpeg,.png,.webp" required onchange="this.form.submit()">
                                                <x-dashboard.icon name="upload" />
                                            </label>
                                        </form>
                                    @endif
                                    <a class="btn btn-sm btn-outline table-action-btn action-icon-btn action-icon-neutral" href="{{ route('asset-returns.print', $return) }}" target="_blank" aria-label="Download return declaration form #{{ $return->id }}" data-tooltip="Download Form PDF"><x-dashboard.icon name="download" /></a>
                                </div>
                                @error('signed_file')<small class="field-error">{{ $message }}</small>@enderror
                            </td>
                        </tr>
                    @empty
                        <tr><td class="table-empty" colspan="6">No return declarations found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="table-footer">
            <span>Total {{ $returnDeclarations->total() }} item(s)</span>
            <div class="table-pagination">
                <label class="pagination-size">
                    <span>Items per page</span>
                    <select name="return_per_page" form="return-declaration-filter-form" aria-label="Return declarations per page" data-auto-filter-control data-native-select>
                        @foreach ([10, 20, 30, 40, 50] as $size)
                            <option value="{{ $size }}" @selected($returnDeclarations->perPage() === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </label>
                <span class="pagination-page">Page {{ $returnDeclarations->currentPage() }} of {{ $returnDeclarations->lastPage() }}</span>
                <div class="pagination-controls">
                    <a class="action-icon-btn action-icon-neutral {{ $returnDeclarations->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $returnDeclarations->url(1) }}" aria-label="First page"><x-dashboard.icon name="chevrons-left" /></a>
                    <a class="action-icon-btn action-icon-neutral {{ $returnDeclarations->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $returnDeclarations->previousPageUrl() ?? '#' }}" aria-label="Previous page"><x-dashboard.icon name="chevron-left" /></a>
                    <a class="action-icon-btn action-icon-neutral {{ $returnDeclarations->hasMorePages() ? '' : 'is-disabled' }}" href="{{ $returnDeclarations->nextPageUrl() ?? '#' }}" aria-label="Next page"><x-dashboard.icon name="chevron-right" /></a>
                    <a class="action-icon-btn action-icon-neutral {{ $returnDeclarations->hasMorePages() ? '' : 'is-disabled' }}" href="{{ $returnDeclarations->url($returnDeclarations->lastPage()) }}" aria-label="Last page"><x-dashboard.icon name="chevrons-right" /></a>
                </div>
            </div>
        </div>
    </section>

    @foreach ($declarations as $declaration)
        @if ($declaration->signed_file_path)
            @php($signedDeclarationUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($declaration->signed_file_path))
            <div class="modal-backdrop" data-import-modal="signed-handover-declaration-{{ $declaration->id }}" hidden>
                <div class="modal-card signed-return-viewer-modal" role="dialog" aria-modal="true" aria-labelledby="signed-handover-declaration-title-{{ $declaration->id }}">
                    <div class="modal-heading">
                        <div>
                            <p>Signed Handover Declaration</p>
                            <h2 id="signed-handover-declaration-title-{{ $declaration->id }}">{{ $declaration->declaration_number }}</h2>
                        </div>
                        <button type="button" class="action-icon-btn action-icon-neutral" data-modal-close aria-label="Close signed form viewer" data-tooltip="Close"><x-dashboard.icon name="x" /></button>
                    </div>
                    <div class="signed-return-viewer">
                        <iframe src="{{ $signedDeclarationUrl }}" title="Signed handover declaration {{ $declaration->declaration_number }}"></iframe>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" data-modal-close>Close</button>
                        <a class="btn btn-primary" href="{{ $signedDeclarationUrl }}" target="_blank">Open File</a>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    @foreach ($returnDeclarations as $return)
        @if ($return->signed_file_path)
            @php($signedReturnUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($return->signed_file_path))
            <div class="modal-backdrop" data-import-modal="signed-return-declaration-{{ $return->id }}" hidden>
                <div class="modal-card signed-return-viewer-modal" role="dialog" aria-modal="true" aria-labelledby="signed-return-declaration-title-{{ $return->id }}">
                    <div class="modal-heading">
                        <div>
                            <p>Signed Return Declaration</p>
                            <h2 id="signed-return-declaration-title-{{ $return->id }}">{{ $return->asset?->asset_tag }} return form</h2>
                        </div>
                        <button type="button" class="action-icon-btn action-icon-neutral" data-modal-close aria-label="Close signed form viewer" data-tooltip="Close"><x-dashboard.icon name="x" /></button>
                    </div>
                    <div class="signed-return-viewer">
                        <iframe src="{{ $signedReturnUrl }}" title="Signed return declaration #{{ $return->id }}"></iframe>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" data-modal-close>Close</button>
                        <a class="btn btn-primary" href="{{ $signedReturnUrl }}" target="_blank">Open File</a>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@endsection
