@extends('layouts.app')

@section('title', 'Register')

@section('content')
<main class="login-page-main">
    <div class="login-card">
        <div class="login-card-header">
            <h1 class="login-title">Create Account</h1>
            <p class="login-subtitle">Join ElectroHub and start shopping</p>
        </div>

        @if ($errors->any())
            <div class="login-feedback error" style="display: block; margin-bottom: 16px;">
                <ul style="margin: 0; padding-left: 18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="login-form" id="register-form" method="POST" action="{{ route('register') }}">
            @csrf

            <div class="login-field">
                <label for="first-name" class="visually-hidden">First Name</label>
                <div class="login-input-wrap">
                    <span class="login-input-icon"><i class="fas fa-user"></i></span>
                    <input
                        type="text"
                        id="first-name"
                        name="first_name"
                        class="login-input"
                        placeholder="First Name"
                        value="{{ old('first_name') }}"
                        required
                    >
                </div>
            </div>

            <div class="login-field">
                <label for="last-name" class="visually-hidden">Last Name</label>
                <div class="login-input-wrap">
                    <span class="login-input-icon"><i class="fas fa-user"></i></span>
                    <input
                        type="text"
                        id="last-name"
                        name="last_name"
                        class="login-input"
                        placeholder="Last Name"
                        value="{{ old('last_name') }}"
                        required
                    >
                </div>
            </div>

            <div class="login-field">
                <label for="register-email" class="visually-hidden">Email Address</label>
                <div class="login-input-wrap">
                    <span class="login-input-icon"><i class="fas fa-envelope"></i></span>
                    <input
                        type="email"
                        id="register-email"
                        name="email"
                        class="login-input"
                        placeholder="Email Address"
                        value="{{ old('email') }}"
                        required
                    >
                </div>
            </div>

            <div class="login-field">
                <label for="register-password" class="visually-hidden">Password</label>
                <div class="login-input-wrap">
                    <span class="login-input-icon"><i class="fas fa-lock"></i></span>
                    <input
                        type="password"
                        id="register-password"
                        name="password"
                        class="login-input"
                        placeholder="Password (min. 8 characters)"
                        required
                        minlength="8"
                    >
                    <button type="button" class="login-toggle-pw" id="toggle-password-1" aria-label="Show password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="login-field">
                <label for="confirm-password" class="visually-hidden">Confirm Password</label>
                <div class="login-input-wrap">
                    <span class="login-input-icon"><i class="fas fa-lock"></i></span>
                    <input
                        type="password"
                        id="confirm-password"
                        name="password_confirmation"
                        class="login-input"
                        placeholder="Confirm Password"
                        required
                        minlength="8"
                    >
                    <button type="button" class="login-toggle-pw" id="toggle-password-2" aria-label="Show password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="login-remember" style="margin: var(--spacing-md) 0;">
                <input type="checkbox" id="terms-agree" required>
                <label for="terms-agree">
                    I agree to the
                    <a href="#" style="color: var(--primary); text-decoration: none;">Terms &amp; Conditions</a>
                    and
                    <a href="#" style="color: var(--primary); text-decoration: none;">Privacy Policy</a>
                </label>
            </div>

            @error('register')
                <div class="login-feedback error" style="display: block;">{{ $message }}</div>
            @enderror

            <button type="submit" class="login-submit" id="register-btn">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </form>

        <div class="login-divider">
            <span>Already have an account?</span>
        </div>

        <button type="button" class="login-register-btn" onclick="window.location.href='{{ route('login') }}'">
            <i class="fas fa-sign-in-alt"></i> Sign In
        </button>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const togglePassword = (buttonId, inputId) => {
        const button = document.getElementById(buttonId);
        const input = document.getElementById(inputId);

        button?.addEventListener('click', function () {
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';

            const icon = this.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-eye', !isPassword);
                icon.classList.toggle('fa-eye-slash', isPassword);
            }
        });
    };

    togglePassword('toggle-password-1', 'register-password');
    togglePassword('toggle-password-2', 'confirm-password');
});
</script>
@endsection