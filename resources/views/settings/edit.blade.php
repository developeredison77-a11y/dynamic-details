@extends('layouts.dashboard')

@section('title', 'Settings')
@section('page-title', 'Settings')
@section('eyebrow', 'Branding')

@section('content')
    <section class="dashboard-panel">
        <div class="panel-heading">
            <div>
                <p>Application settings</p>
                <h2>Theme and Site Identity</h2>
            </div>
        </div>

        <form class="settings-form" method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" novalidate>
            @csrf
            @method('PUT')

            <div class="form-grid">
                <label class="form-field">
                    <span>Site Name</span>
                    <input type="text" name="site_name" value="{{ old('site_name', $settings['site_name'] ?? '') }}">
                    @error('site_name') <small>{{ $message }}</small> @enderror
                </label>

                <label class="form-field color-field">
                    <span>Primary Theme Color</span>
                    <input type="color" name="theme_color" value="{{ old('theme_color', $settings['theme_color'] ?? '#2563eb') }}" data-theme-color-input>
                    @error('theme_color') <small>{{ $message }}</small> @enderror
                </label>

                <label class="form-field file-field">
                    <span>Site Logo</span>
                    <input type="file" name="site_logo" accept="image/png,image/jpeg,image/webp,image/svg+xml">
                    @error('site_logo') <small>{{ $message }}</small> @enderror
                </label>

                <label class="form-field file-field">
                    <span>Site Favicon</span>
                    <input type="file" name="site_favicon" accept=".ico,image/png,image/webp,image/svg+xml">
                    @error('site_favicon') <small>{{ $message }}</small> @enderror
                </label>
            </div>

            <div class="settings-preview">
                <div>
                    <span>Current logo</span>
                    @if (! empty($settings['site_logo']))
                        <img src="{{ asset('storage/'.$settings['site_logo']) }}" alt="{{ $settings['site_name'] ?? 'Site logo' }}">
                    @else
                        <strong>{{ strtoupper(substr($settings['site_name'] ?? 'D', 0, 1)) }}</strong>
                    @endif
                </div>
                <div>
                    <span>Current favicon</span>
                    @if (! empty($settings['site_favicon']))
                        <img src="{{ asset('storage/'.$settings['site_favicon']) }}" alt="Site favicon">
                    @else
                        <strong>ICO</strong>
                    @endif
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">Save Settings</button>
            </div>
        </form>
    </section>
@endsection
