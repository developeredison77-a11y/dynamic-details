@extends('layouts.auth')

@section('title', 'Register')
@section('eyebrow', 'Create access')
@section('headline', 'Start with a polished admin foundation.')
@section('description', 'Create a secure user account and land directly inside the responsive backend dashboard experience.')
@section('form-eyebrow', 'New Account')
@section('form-title', 'Create admin account')

@section('content')
    <form class="auth-form" method="POST" action="{{ route('register.store') }}">
        @csrf

        <label class="form-field">
            <span>Full Name</span>
            <input type="text" name="name" value="{{ old('name') }}" autocomplete="name" required autofocus placeholder="Admin User">
            @error('name') <small>{{ $message }}</small> @enderror
        </label>

        <label class="form-field">
            <span>Email Address</span>
            <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required placeholder="admin@example.com">
            @error('email') <small>{{ $message }}</small> @enderror
        </label>

        <label class="form-field">
            <span>Password</span>
            <input type="password" name="password" autocomplete="new-password" required placeholder="Create a secure password">
            @error('password') <small>{{ $message }}</small> @enderror
        </label>

        <label class="form-field">
            <span>Confirm Password</span>
            <input type="password" name="password_confirmation" autocomplete="new-password" required placeholder="Repeat password">
        </label>

        <div class="auth-row">
            <span>Already have access?</span>
            <a href="{{ route('login') }}">Sign in</a>
        </div>

        <button type="submit" class="primary-button auth-submit">Create Account</button>
    </form>
@endsection
