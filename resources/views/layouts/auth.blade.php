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
        <section class="auth-visual" aria-label="{{ $siteName }}">
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
                    <span>Secure Admin Workspace</span>
                </div>
            </div>

            <div class="auth-copy">
                <span>@yield('eyebrow')</span>
                <h1>@yield('headline')</h1>
                <p>@yield('description')</p>
            </div>

            <div class="auth-proof">
                <div>
                    <strong>Responsive</strong>
                    <span>Desktop, tablet, and mobile ready</span>
                </div>
                <div>
                    <strong>Persistent Theme</strong>
                    <span>Uses the same light and dark mode preference</span>
                </div>
            </div>
        </section>

        <section class="auth-panel">
            <div class="auth-card">
                <div class="auth-card-heading">
                    <div>
                        <p>@yield('form-eyebrow')</p>
                        <h2>@yield('form-title')</h2>
                    </div>
                </div>

                @yield('content')
            </div>
        </section>
    </main>

    <x-dashboard.toasts />
</body>
</html>
