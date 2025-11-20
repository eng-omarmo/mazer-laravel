@extends('layouts.auth')
@section('title','Register')
@section('content')
<div class="row h-100">
    <div class="col-lg-5 col-12">
        <div id="auth-left">
            <div class="auth-logo">
                <a href="{{ url('/') }}"><img src="{{ asset('assets/images/logo/logo.png') }}" alt="Logo"></a>
            </div>
            <h1 class="auth-title">Sign up.</h1>
            <p class="auth-subtitle mb-5">Create an account to continue.</p>

            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="form-group position-relative has-icon-left mb-4">
                    <input id="name" type="text" name="name" value="{{ old('name') }}" class="form-control form-control-xl" placeholder="Name" required autofocus autocomplete="name">
                    <div class="form-control-icon">
                        <i class="bi bi-person"></i>
                    </div>
                </div>
                @error('name')<div class="text-danger mb-2">{{ $message }}</div>@enderror

                <div class="form-group position-relative has-icon-left mb-4">
                    <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control form-control-xl" placeholder="Email" required autocomplete="username">
                    <div class="form-control-icon">
                        <i class="bi bi-envelope"></i>
                    </div>
                </div>
                @error('email')<div class="text-danger mb-2">{{ $message }}</div>@enderror

                <div class="form-group position-relative has-icon-left mb-4">
                    <input id="password" type="password" name="password" class="form-control form-control-xl" placeholder="Password" required autocomplete="new-password">
                    <div class="form-control-icon">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                </div>
                @error('password')<div class="text-danger mb-2">{{ $message }}</div>@enderror

                <div class="form-group position-relative has-icon-left mb-4">
                    <input id="password_confirmation" type="password" name="password_confirmation" class="form-control form-control-xl" placeholder="Confirm Password" required autocomplete="new-password">
                    <div class="form-control-icon">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                </div>
                @error('password_confirmation')<div class="text-danger mb-2">{{ $message }}</div>@enderror

                <button class="btn btn-primary btn-block btn-lg shadow-lg mt-5">Register</button>
            </form>
            <div class="text-center mt-5 text-lg fs-4">
                <p class="text-gray-600">Already registered? <a href="{{ route('login') }}" class="font-bold">Log in</a>.</p>
            </div>
        </div>
    </div>
    <div class="col-lg-7 d-none d-lg-block">
        <div id="auth-right"></div>
    </div>
</div>
@endsection
