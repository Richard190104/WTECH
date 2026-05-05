@extends('layouts.app')

@section('title', 'Admin Login')

@section('content')
    <div class="auth-page">
        <div class="login-card">
            <div class="login-card-header">
                <div class="login-logo">
                    <i class="fas fa-shield-halved"></i>
                </div>
                <h1 class="login-title">Administrator Login</h1>
                <p class="login-subtitle">Sign in with an admin account to manage the catalog</p>
            </div>

            <form class="login-form" method="POST" action="{{ route('admin.login.store') }}" novalidate>
                @csrf

                <div class="login-field">
                    <label for="admin-login-email">Email address</label>
                    <div class="login-input-wrap">
                        <i class="fas fa-envelope login-input-icon"></i>
                        <input
                            type="email"
                            id="admin-login-email"
                            name="email"
                            class="login-input"
                            placeholder="admin@example.com"
                            autocomplete="email"
                            value="{{ old('email') }}"
                            required
                        >
                    </div>
                </div>

                <div class="login-field">
                    <label for="admin-login-password">Password</label>
                    <div class="login-input-wrap">
                        <i class="fas fa-lock login-input-icon"></i>
                        <input
                            type="password"
                            id="admin-login-password"
                            name="password"
                            class="login-input"
                            placeholder="Enter admin password"
                            autocomplete="current-password"
                            required
                        >
                    </div>
                </div>

                <label class="login-remember">
                    <input type="checkbox" id="admin-remember-me" name="remember" value="1">
                    <span>Remember me</span>
                </label>

                @error('email')
                    <p class="login-feedback error">{{ $message }}</p>
                @enderror

                <button type="submit" class="login-submit">
                    <i class="fas fa-right-to-bracket"></i> Sign In as Admin
                </button>

                <div class="login-divider"><span>or</span></div>

                <a href="{{ route('login') }}" class="login-register-btn">
                    <i class="fas fa-user"></i> User Login
                </a>
            </form>
        </div>
    </div>
@endsection
