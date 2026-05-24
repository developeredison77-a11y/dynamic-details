@props(['items' => []])

@php
    $items = collect($items)
        ->filter(fn ($item) => filled($item['label'] ?? null))
        ->values();
@endphp

@if ($items->isNotEmpty())
    <nav class="dashboard-breadcrumbs" aria-label="Breadcrumb">
        <ol>
            @foreach ($items as $index => $item)
                @php
                    $isLast = $loop->last;
                    $url = $item['url'] ?? null;
                @endphp

                <li>
                    @if (! $isLast && $url)
                        <a href="{{ $url }}">{{ $item['label'] }}</a>
                    @else
                        <span @if ($isLast) aria-current="page" @endif>{{ $item['label'] }}</span>
                    @endif

                    @if (! $isLast)
                        <x-dashboard.icon name="chevron-right" />
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
