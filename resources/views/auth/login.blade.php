@extends('layouts.auth')

@section('title', 'Login')
@section('eyebrow', 'Welcome back')
@section('headline', 'Control your backend with clarity.')
@section('description', 'Sign in to manage dashboard settings, branding, content, and the rest of your admin workflow from a refined responsive interface.')

@section('content')
    <form class="auth-form" method="POST" action="{{ route('login.store') }}">
        @csrf

        <label class="form-field">
            <span>Email Address</span>
            <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus placeholder="admin@example.com">
            @error('email') <small>{{ $message }}</small> @enderror
        </label>

        <label class="form-field">
            <span>Password</span>
            <input type="password" name="password" autocomplete="current-password" required placeholder="Enter your password">
            @error('password') <small>{{ $message }}</small> @enderror
        </label>

        <div class="auth-row">
            <label class="check-field">
                <input type="checkbox" name="remember" value="1">
                <span>Remember me</span>
            </label>
            <a href="{{ route('register') }}">Create account</a>
        </div>

        <button type="submit" class="primary-button auth-submit">Sign In</button>
    </form>
@endsection
