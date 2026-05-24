@props(['icon' => 'pages', 'label', 'active' => false])

<div class="sidebar-group {{ $active ? 'is-active' : '' }}" data-sidebar-group>
    <button type="button" class="sidebar-link sidebar-group-toggle" data-submenu-toggle aria-expanded="false">
        <x-dashboard.icon :name="$icon" class="sidebar-icon" />
        <span class="sidebar-label">{{ $label }}</span>
        <x-dashboard.icon name="chevron" class="sidebar-chevron" />
    </button>
    <div class="sidebar-submenu" data-submenu data-menu-label="{{ $label }}">
        {{ $slot }}
    </div>
</div>
