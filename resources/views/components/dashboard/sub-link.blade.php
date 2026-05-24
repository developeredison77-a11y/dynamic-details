@props(['href', 'active' => false])

@php
    $isActive = $active || ($href !== '#' && url()->current() === $href);
@endphp

<a href="{{ $href }}" class="sidebar-sub-link {{ $isActive ? 'is-active' : '' }}" data-sidebar-sub-link>
    <span>{{ $slot }}</span>
</a>
