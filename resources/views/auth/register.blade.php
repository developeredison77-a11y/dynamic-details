@extends('layouts.auth')

@section('title', 'Register')
@section('form-eyebrow', 'New account')
@section('form-title', 'Create admin account')
@section('form-description', 'Fill in the details below to get started.')

@section('content')
    <form class="auth-form" method="POST" action="{{ route('register.store') }}" novalidate>
        @csrf

        <label class="form-field">
            <span>Full Name</span>
            <input type="text" name="name" value="{{ old('name') }}" autocomplete="name" autofocus placeholder="Admin User" required>
            @error('name') <small>{{ $message }}</small> @enderror
        </label>

        <label class="form-field">
            <span>Email Address</span>
            <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" placeholder="admin@example.com" required>
            @error('email') <small>{{ $message }}</small> @enderror
        </label>

        <label class="form-field">
            <span>Password</span>
            <input type="password" name="password" autocomplete="new-password" placeholder="Create a secure password" required>
            @error('password') <small>{{ $message }}</small> @enderror
        </label>

        <label class="form-field">
            <span>Confirm Password</span>
            <input type="password" name="password_confirmation" autocomplete="new-password" placeholder="Repeat password" required>
        </label>

        <div class="auth-row">
            <span>Already have access?</span>
            <a href="{{ route('login') }}">Sign in</a>
        </div>

        <button type="submit" class="btn btn-primary btn-lg auth-submit">Create Account</button>
    </form>
@endsection
