@extends('layouts.auth')

@section('title', 'Login')
@section('form-eyebrow', 'Welcome back')
@section('form-title', 'Sign in to your account')
@section('form-description', 'Enter your admin credentials to continue.')

@section('content')
    <form class="auth-form" method="POST" action="{{ route('login.store') }}" novalidate>
        @csrf

        <label class="form-field">
            <span>Email Address</span>
            <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" autofocus placeholder="admin@example.com" required>
            @error('email') <small>{{ $message }}</small> @enderror
        </label>

        <label class="form-field">
            <span>Password</span>
            <input type="password" name="password" autocomplete="current-password" placeholder="Enter your password" required>
            @error('password') <small>{{ $message }}</small> @enderror
        </label>

        <div class="auth-row">
            <label class="check-field">
                <input type="checkbox" name="remember" value="1">
                <span>Remember me</span>
            </label>
            <a href="{{ route('register') }}">Create account</a>
        </div>

        <button type="submit" class="primary-button auth-submit">Sign in</button>
    </form>
@endsection
