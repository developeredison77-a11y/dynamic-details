@extends('layouts.dashboard')

@section('title', 'Profile')
@section('page-title', 'Profile')
@section('eyebrow', 'Account')

@section('content')
    @php
        $avatar = $user?->profile_image ? asset('storage/'.$user->profile_image) : null;
    @endphp

    <section class="dashboard-panel profile-form-panel">
        <div class="panel-heading">
            <div>
                <p>Personal information</p>
                <h2>Edit Profile</h2>
            </div>
        </div>

        <form class="settings-form" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="profile-image-field">
                <div class="profile-image-preview" data-profile-image-preview>
                    @if ($avatar)
                        <img src="{{ $avatar }}" alt="{{ $user?->name ?? 'Profile image' }}">
                    @else
                        <x-dashboard.icon name="user" />
                    @endif
                </div>
                <label class="form-field file-field">
                    <span>Profile Image</span>
                    <input type="file" name="profile_image" accept="image/*" data-profile-image-input>
                    @error('profile_image') <small>{{ $message }}</small> @enderror
                </label>
            </div>

            <div class="form-grid">
                <label class="form-field">
                    <span>Full Name</span>
                    <input type="text" name="name" value="{{ old('name', $user?->name) }}" required autocomplete="name">
                    @error('name') <small>{{ $message }}</small> @enderror
                </label>

                <label class="form-field">
                    <span>Email Address</span>
                    <input type="email" name="email" value="{{ old('email', $user?->email) }}" required autocomplete="email">
                    @error('email') <small>{{ $message }}</small> @enderror
                </label>

                <label class="form-field">
                    <span>New Password</span>
                    <input type="password" name="password" autocomplete="new-password" placeholder="Leave blank to keep current password">
                    @error('password') <small>{{ $message }}</small> @enderror
                </label>

                <label class="form-field">
                    <span>Confirm New Password</span>
                    <input type="password" name="password_confirmation" autocomplete="new-password" placeholder="Repeat new password">
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="primary-button">Update Profile</button>
            </div>
        </form>
    </section>
@endsection
