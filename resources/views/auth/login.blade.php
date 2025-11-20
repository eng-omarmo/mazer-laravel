@extends('layouts.auth')
@section('title','Login')
@section('content')
<div class="row h-100">
    <div class="col-lg-5 col-12">
        <div id="auth-left">
            <div class="auth-logo">
                <a href="{{ url('/') }}"><img src="{{ asset('assets/images/logo/logo.png') }}" alt="Logo"></a>
            </div>
            <h1 class="auth-title">Log in.</h1>
            <p class="auth-subtitle mb-5">Log in with your credentials.</p>

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="form-group position-relative has-icon-left mb-4">
                    <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control form-control-xl" placeholder="Email" required autofocus autocomplete="username">
                    <div class="form-control-icon">
                        <i class="bi bi-envelope"></i>
                    </div>
                </div>
                @error('email')<div class="text-danger mb-2">{{ $message }}</div>@enderror

                <div class="form-group position-relative has-icon-left mb-4">
                    <input id="password" type="password" name="password" class="form-control form-control-xl" placeholder="Password" required autocomplete="current-password">
                    <div class="form-control-icon">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                </div>
                @error('password')<div class="text-danger mb-2">{{ $message }}</div>@enderror

                <div class="form-check form-check-lg d-flex align-items-end">
                    <input class="form-check-input me-2" type="checkbox" id="remember_me" name="remember">
                    <label class="form-check-label text-gray-600" for="remember_me">Keep me logged in</label>
                </div>
                <button class="btn btn-primary btn-block btn-lg shadow-lg mt-5">Log in</button>
            </form>
            <div class="text-center mt-5 text-lg fs-4">
                <p class="text-gray-600">Don't have an account? <a href="{{ route('register') }}" class="font-bold">Sign up</a>.</p>
                @if (Route::has('password.request'))
                <p><a class="font-bold" href="{{ route('password.request') }}">Forgot password?</a>.</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-7 d-none d-lg-block">
        <div id="auth-right"></div>
    </div>
</div>
@endsection
