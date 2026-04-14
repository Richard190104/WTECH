@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <div class="auth-page">
        <div class="login-card">
            <div class="login-card-header">
                <div class="login-logo">
                    <i class="fas fa-bolt"></i>
                </div>
                <h1 class="login-title">Welcome back</h1>
                <p class="login-subtitle">Sign in to your ElectroHub account</p>
            </div>

            <form class="login-form" method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                <div class="login-field">
                    <label for="login-email">Email address</label>
                    <div class="login-input-wrap">
                        <i class="fas fa-envelope login-input-icon"></i>
                        <input
                            type="email"
                            id="login-email"
                            name="email"
                            class="login-input"
                            placeholder="you@example.com"
                            autocomplete="email"
                            value="{{ old('email') }}"
                            required
                        >
                    </div>
                </div>

                <div class="login-field">
                    <div class="login-label-row">
                        <label for="login-password">Password</label>
                        <a href="#" class="login-forgot">Forgot password?</a>
                    </div>
                    <div class="login-input-wrap">
                        <i class="fas fa-lock login-input-icon"></i>
                        <input
                            type="password"
                            id="login-password"
                            name="password"
                            class="login-input"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required
                        >
                        <button type="button" class="login-toggle-pw" id="toggle-password" aria-label="Toggle password visibility">
                            <i class="fas fa-eye" id="toggle-pw-icon"></i>
                        </button>
                    </div>
                </div>

                <label class="login-remember">
                    <input type="checkbox" id="remember-me" name="remember" value="1">
                    <span>Remember me</span>
                </label>

                @error('email')
                    <p class="login-feedback error">{{ $message }}</p>
                @enderror

                <button type="submit" class="login-submit">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>

                <div class="login-divider"><span>or</span></div>

                <a href="{{ route('register') }}" class="login-register-btn">
                    <i class="fas fa-user-plus"></i> Create an account
                </a>
            </form>
        </div>
    </div>
@endsection