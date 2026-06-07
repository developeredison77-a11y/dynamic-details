@props([
    'paginator',
    'formId' => 'pagination-form',
    'label' => 'item(s)',
])

<form id="{{ $formId }}" class="pagination-form" method="GET" data-auto-filter-form></form>

<div class="table-footer">
    <span>Total {{ $paginator->total() }} {{ $label }}</span>
    <div class="table-pagination">
        <label class="pagination-size">
            <span>Items per page</span>
            <select name="per_page" form="{{ $formId }}" aria-label="Items per page" data-auto-filter-control data-native-select>
                @foreach ([10, 20, 30, 40, 50] as $size)
                    <option value="{{ $size }}" @selected($paginator->perPage() === $size)>{{ $size }}</option>
                @endforeach
            </select>
        </label>
        <span class="pagination-page">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>
        <div class="pagination-controls">
            <a class="action-icon-btn action-icon-neutral {{ $paginator->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $paginator->url(1) }}" aria-label="First page"><x-dashboard.icon name="chevrons-left" /></a>
            <a class="action-icon-btn action-icon-neutral {{ $paginator->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $paginator->previousPageUrl() ?? '#' }}" aria-label="Previous page"><x-dashboard.icon name="chevron-left" /></a>
            <a class="action-icon-btn action-icon-neutral {{ $paginator->hasMorePages() ? '' : 'is-disabled' }}" href="{{ $paginator->nextPageUrl() ?? '#' }}" aria-label="Next page"><x-dashboard.icon name="chevron-right" /></a>
            <a class="action-icon-btn action-icon-neutral {{ $paginator->hasMorePages() ? '' : 'is-disabled' }}" href="{{ $paginator->url($paginator->lastPage()) }}" aria-label="Last page"><x-dashboard.icon name="chevrons-right" /></a>
        </div>
    </div>
</div>
