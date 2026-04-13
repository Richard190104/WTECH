@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1>Register test</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-3">
            <label>First name</label>
            <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}">
        </div>

        <div class="mb-3">
            <label>Last name</label>
            <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}">
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}">
        </div>

        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control">
        </div>

        <div class="mb-3">
            <label>Confirm password</label>
            <input type="password" name="password_confirmation" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Register</button>
    </form>
</div>
@endsection