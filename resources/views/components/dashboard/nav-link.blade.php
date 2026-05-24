@props(['href', 'icon' => 'dashboard', 'active' => false])

<a href="{{ $href }}" class="sidebar-link {{ $active ? 'is-active' : '' }}" data-sidebar-leaf>
    <x-dashboard.icon :name="$icon" class="sidebar-icon" />
    <span class="sidebar-label">{{ $slot }}</span>
</a>
