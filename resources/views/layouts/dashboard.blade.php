@php
    $settings = $appSettings ?? \App\Models\Setting::DEFAULTS;
    $siteName = $settings['site_name'] ?? config('app.name');
    $primaryColor = $settings['theme_color'] ?? '#2563eb';
    $logo = ! empty($settings['site_logo']) ? asset('storage/'.$settings['site_logo']) : null;
    $favicon = ! empty($settings['site_favicon']) ? asset('storage/'.$settings['site_favicon']) : asset('favicon.ico');
    $user = auth()->user();
    $avatarPath = $user ? data_get($user, 'profile_photo_path') ?? data_get($user, 'profile_image') ?? data_get($user, 'avatar') : null;
    $avatar = $avatarPath ? (str_starts_with($avatarPath, 'http') ? $avatarPath : asset('storage/'.$avatarPath)) : null;
    $breadcrumbs ??= match (true) {
        request()->routeIs('dashboard') => [
            ['label' => 'Dashboard'],
        ],
        request()->routeIs('clients.*') => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Clients'],
            ['label' => 'All Clients'],
        ],
        request()->routeIs('employees.*') => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Employee Master'],
        ],
        request()->routeIs('roles.*') => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Access Control'],
            ['label' => 'Roles & Permissions'],
        ],
        request()->routeIs('assets.*', 'asset-brands.*', 'asset-categories.*') => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Asset Master'],
        ],
        request()->routeIs('asset-handovers.*') => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Asset Handover'],
        ],
        request()->routeIs('asset-returns.*') => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Asset Return'],
        ],
        request()->routeIs('declarations.*') => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Declaration Forms'],
        ],
        request()->routeIs('imports.employees.index') => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Employee Master', 'url' => route('employees.index')],
            ['label' => 'Import Employees'],
        ],
        request()->routeIs('imports.assets.index') => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Asset Master', 'url' => route('assets.index')],
            ['label' => 'Import Assets'],
        ],
        request()->routeIs('imports.*') => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Imports'],
        ],
        request()->routeIs('reports.*') => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Reports'],
        ],
        request()->routeIs('profile.*') => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Account'],
            ['label' => 'Profile'],
        ],
        request()->routeIs('settings.*') => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Settings'],
        ],
        default => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => trim($__env->yieldContent('page-title', 'Page'))],
        ],
    };
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ $siteName }}</title>
    <link rel="icon" href="{{ $favicon }}">
    <script>
        (() => {
            const theme = localStorage.getItem('dashboard-theme') || 'light';
            document.documentElement.dataset.theme = theme;
        })();
    </script>
    <style>
        :root { --primary: {{ $primaryColor }}; }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="dashboard-body">
    <div class="dashboard-shell" data-dashboard-shell>
        <aside class="dashboard-sidebar" data-sidebar>
            <div class="sidebar-header">
                <div class="sidebar-brand">
                    <a href="{{ route('dashboard') }}" class="brand-mark">
                        @if ($logo)
                            <img src="{{ $logo }}" alt="{{ $siteName }}">
                        @else
                            <span>{{ strtoupper(substr($siteName, 0, 1)) }}</span>
                        @endif
                    </a>
                    <a href="{{ route('dashboard') }}" class="brand-copy">
                        <strong>{{ $siteName }}</strong>
                        <span>Control Center</span>
                    </a>
                </div>
            </div>

            <nav class="sidebar-nav" aria-label="Dashboard navigation">
                @if ($user?->canAccess('dashboard.view'))
                    <x-dashboard.nav-link :href="route('dashboard')" icon="dashboard" :active="request()->routeIs('dashboard')">
                        Dashboard
                    </x-dashboard.nav-link>
                @endif

                @if ($user?->canAccess('employees.view') || $user?->canAccess('employees.create') || $user?->canAccess('employees.import'))
                    <x-dashboard.nav-group icon="users" label="Employee Master" :active="request()->routeIs('employees.*', 'imports.employees.index')">
                        @if ($user?->canAccess('employees.view'))
                            <x-dashboard.sub-link :href="route('employees.index')" :active="request()->routeIs('employees.index')">All Employees</x-dashboard.sub-link>
                        @endif
                        @if ($user?->canAccess('employees.create'))
                            <x-dashboard.sub-link :href="route('employees.create')" :active="request()->routeIs('employees.create')">Add Employee</x-dashboard.sub-link>
                        @endif
                        @if ($user?->canAccess('employees.import'))
                            <x-dashboard.sub-link :href="route('imports.employees.index')" :active="request()->routeIs('imports.employees.index')">Import Employees</x-dashboard.sub-link>
                        @endif
                    </x-dashboard.nav-group>
                @endif

                @if ($user?->canAccess('assets.view') || $user?->canAccess('assets.create') || $user?->canAccess('assets.import') || $user?->canAccess('asset-brands.manage') || $user?->canAccess('asset-categories.manage'))
                    <x-dashboard.nav-group icon="pages" label="Asset Master" :active="request()->routeIs('assets.*', 'asset-brands.*', 'asset-categories.*', 'imports.assets.index')">
                        @if ($user?->canAccess('assets.view'))
                            <x-dashboard.sub-link :href="route('assets.index')" :active="request()->routeIs('assets.index')">All Assets</x-dashboard.sub-link>
                        @endif
                        @if ($user?->canAccess('assets.create'))
                            <x-dashboard.sub-link :href="route('assets.create')" :active="request()->routeIs('assets.create')">Add Asset</x-dashboard.sub-link>
                        @endif
                        @if ($user?->canAccess('assets.import'))
                            <x-dashboard.sub-link :href="route('imports.assets.index')" :active="request()->routeIs('imports.assets.index')">Import Assets</x-dashboard.sub-link>
                        @endif
                        @if ($user?->canAccess('asset-brands.manage'))
                            <x-dashboard.sub-link :href="route('asset-brands.index')" :active="request()->routeIs('asset-brands.*')">Brands</x-dashboard.sub-link>
                        @endif
                        @if ($user?->canAccess('asset-categories.manage'))
                            <x-dashboard.sub-link :href="route('asset-categories.index')" :active="request()->routeIs('asset-categories.*')">Categories</x-dashboard.sub-link>
                        @endif
                    </x-dashboard.nav-group>
                @endif

                @if ($user?->canAccess('asset-handovers.view') || $user?->canAccess('asset-returns.view') || $user?->canAccess('declarations.view'))
                    <x-dashboard.nav-group icon="dashboard" label="Asset Operations" :active="request()->routeIs('asset-handovers.*', 'asset-returns.*', 'declarations.*')">
                        @if ($user?->canAccess('asset-handovers.view'))
                            <x-dashboard.sub-link :href="route('asset-handovers.index')" :active="request()->routeIs('asset-handovers.*')">Handovers</x-dashboard.sub-link>
                        @endif
                        @if ($user?->canAccess('asset-returns.view'))
                            <x-dashboard.sub-link :href="route('asset-returns.index')" :active="request()->routeIs('asset-returns.*')">Returns</x-dashboard.sub-link>
                        @endif
                        @if ($user?->canAccess('declarations.view'))
                            <x-dashboard.sub-link :href="route('declarations.index')" :active="request()->routeIs('declarations.*')">Declarations</x-dashboard.sub-link>
                        @endif
                    </x-dashboard.nav-group>
                @endif

                @if ($user?->canAccess('imports.view') || $user?->canAccess('reports.view'))
                    <x-dashboard.nav-group icon="pages" label="Data & Reports" :active="request()->routeIs('imports.*', 'reports.*')">
                        @if ($user?->canAccess('imports.view'))
                            <x-dashboard.sub-link :href="route('imports.index')" :active="request()->routeIs('imports.index')">Imports</x-dashboard.sub-link>
                        @endif
                        @if ($user?->canAccess('reports.view'))
                            <x-dashboard.sub-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">Reports</x-dashboard.sub-link>
                        @endif
                    </x-dashboard.nav-group>
                @endif

                @if ($user?->canAccess('roles.view'))
                    <x-dashboard.nav-group icon="settings" label="Access Control" :active="request()->routeIs('roles.*')">
                        <x-dashboard.sub-link :href="route('roles.index')" :active="request()->routeIs('roles.*')">Roles & Permissions</x-dashboard.sub-link>
                    </x-dashboard.nav-group>
                @endif

                @if ($user?->canAccess('settings.view'))
                    <x-dashboard.nav-link :href="route('settings.edit')" icon="settings" :active="request()->routeIs('settings.*')">
                        Settings
                    </x-dashboard.nav-link>
                @endif
            </nav>
        </aside>

        <div class="mobile-backdrop" data-sidebar-backdrop></div>

        <div class="dashboard-main">
            <header class="dashboard-topbar">
                <div class="topbar-left">
                    <button type="button" class="btn btn-icon icon-button action-icon-btn action-icon-neutral" data-sidebar-toggle aria-label="Toggle sidebar" data-tooltip="Toggle sidebar">
                        <x-dashboard.icon name="menu" />
                    </button>
                    <span class="topbar-divider" aria-hidden="true"></span>
                    <label class="topbar-search">
                        <x-dashboard.icon name="search" />
                        <input type="search" placeholder="Search..." aria-label="Search">
                        <kbd>Ctrl K</kbd>
                    </label>
                </div>

                <div class="topbar-actions">
                    <div class="topbar-user-cluster">
                        <button type="button" class="btn btn-icon theme-switch action-icon-btn action-icon-neutral" data-theme-toggle aria-label="Toggle color theme" data-tooltip="Toggle theme">
                            <x-dashboard.icon name="sun" class="theme-sun" />
                            <x-dashboard.icon name="moon" class="theme-moon" />
                        </button>
                        @if ($user?->canAccess('settings.view'))
                            <a href="{{ route('settings.edit') }}" class="btn btn-icon icon-button action-icon-btn action-icon-neutral" aria-label="Settings" data-tooltip="Settings">
                                <x-dashboard.icon name="settings" />
                            </a>
                        @endif
                        <span class="topbar-divider" aria-hidden="true"></span>

                        <div class="user-menu" data-dropdown>
                            <button type="button" class="btn user-button" data-dropdown-toggle aria-expanded="false">
                                <span class="user-meta">
                                    <strong>{{ $user?->name ?? 'Admin' }}</strong>
                                    <small>{{ $user?->email ?? 'Super Admin' }}</small>
                                </span>
                                @if ($avatar)
                                    <img class="user-avatar" src="{{ $avatar }}" alt="{{ $user?->name ?? 'User' }}">
                                @else
                                    <span class="user-avatar user-avatar-fallback">{{ strtoupper(substr($user?->name ?? 'Admin', 0, 1)) }}</span>
                                @endif
                            </button>

                            <div class="dropdown-panel" data-dropdown-panel>
                                <div class="dropdown-user">
                                    @if ($avatar)
                                        <img src="{{ $avatar }}" alt="{{ $user?->name ?? 'User' }}">
                                    @else
                                        <span><x-dashboard.icon name="user" /></span>
                                    @endif
                                    <div>
                                        <strong>{{ $user?->name ?? 'Admin' }}</strong>
                                        <small>{{ $user?->email ?? 'Administrator' }}</small>
                                    </div>
                                </div>
                                <a href="{{ route('profile.show') }}">
                                    <x-dashboard.icon name="user" />
                                    Profile
                                    <x-dashboard.icon name="chevron-right" class="dropdown-arrow" />
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit">
                                        <x-dashboard.icon name="logout" />
                                        Logout
                                        <x-dashboard.icon name="chevron-right" class="dropdown-arrow" />
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <div class="dashboard-breadcrumb-bar">
                <x-dashboard.breadcrumbs :items="$breadcrumbs" />
            </div>

            <div class="dashboard-page-header">
                <div class="dashboard-page-title">
                    <p>@yield('eyebrow', 'Backend')</p>
                    <h1>@yield('page-title', 'Dashboard')</h1>
                </div>
                @hasSection('page-actions')
                    <div class="dashboard-page-actions">
                        @yield('page-actions')
                    </div>
                @endif
            </div>

            <main class="dashboard-content">
                @yield('content')
            </main>
        </div>
    </div>

    <div class="modal-backdrop" data-confirm-modal hidden>
        <div class="modal-card confirm-modal" role="dialog" aria-modal="true" aria-labelledby="delete-confirm-title">
            <div class="modal-heading">
                <h2 id="delete-confirm-title">Confirm Deletion</h2>
                <button type="button" class="action-icon-btn action-icon-neutral" aria-label="Close confirmation popup" data-confirm-cancel><x-dashboard.icon name="x" /></button>
            </div>
            <div class="confirm-message">
                <p data-confirm-message>Are you sure you want to delete this item?</p>
                <strong>This action cannot be undone.</strong>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" data-confirm-cancel>Cancel</button>
                <button type="button" class="btn btn-danger" data-confirm-accept>Delete</button>
            </div>
        </div>
    </div>

    <x-dashboard.toasts />
</body>
</html>
