@php
    $settings = $appSettings ?? \App\Models\Setting::DEFAULTS;
    $siteName = $settings['site_name'] ?? config('app.name');
    $primaryColor = $settings['theme_color'] ?? '#2563eb';
    $logo = ! empty($settings['site_logo']) ? asset('storage/'.$settings['site_logo']) : null;
    $favicon = ! empty($settings['site_favicon']) ? asset('storage/'.$settings['site_favicon']) : asset('favicon.ico');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - {{ $siteName }}</title>
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
<body class="auth-body">
    <main class="auth-shell">
        <section class="auth-panel" aria-label="@yield('title')">
            <div class="auth-card">
                <div class="auth-brand">
                    <a href="{{ url('/') }}" class="brand-mark">
                        @if ($logo)
                            <img src="{{ $logo }}" alt="{{ $siteName }}">
                        @else
                            <span>{{ strtoupper(substr($siteName, 0, 1)) }}</span>
                        @endif
                    </a>
                    <div>
                        <strong>{{ $siteName }}</strong>
                    </div>
                </div>

                <div class="auth-card-heading">
                    <div>
                        @hasSection('form-eyebrow')
                            <p>@yield('form-eyebrow')</p>
                        @endif
                        <h2>@yield('form-title')</h2>
                        @hasSection('form-description')
                            <span>@yield('form-description')</span>
                        @endif
                    </div>
                </div>

                @yield('content')
            </div>
        </section>
    </main>

    <x-dashboard.toasts />
</body>
</html>
