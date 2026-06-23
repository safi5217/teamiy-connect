@extends('layouts.auth')
@section('title', 'Sign in')
@section('content')
    <div class="login-form-wrap">
        <form class="login-form" action="{{ route('auth.login') }}" method="post">
            @csrf
            <img class="logo" src="{{ asset('assets/logo.png') }}" alt="Teamiy" style="filter:none">
            <h2>Welcome back</h2>
            <p class="sub">Sign in to your employee portal.</p>

            @if (session('status'))
                <div class="auth-alert success">{{ session('status') }}</div>
            @endif

            <label class="label" for="login">Email, work email, or username</label>
            <input class="input @error('login') is-invalid @enderror" id="login" name="login" type="text"
                value="{{ old('login') }}" autocomplete="username" autofocus>
            @error('login')
                <small class="field-error">{{ $message }}</small>
            @enderror

            <div style="height:18px"></div>
            <label class="label" for="password">Password</label>
            <div class="password-wrapper">
                <input class="input @error('password') is-invalid @enderror" type="password" name="password" id="password"
                    autocomplete="current-password">
                <i class="fa-solid fa-eye toggle-password" id="togglePassword" data-target="password"></i>
            </div>
            @error('password')
                <small class="field-error">{{ $message }}</small>
            @enderror

            <div class="login-row">
                <label class="checkrow"><input type="checkbox" name="remember" value="1" @checked(old('remember'))
                        style="width:16px;height:16px;accent-color:var(--primary)">Remember me</label>
                <a class="link" href="{{ route('password.request') }}">Forgot password?</a>
            </div>
            <button type="submit" class="btn btn-primary btn-block"
                style="padding:14px;font-size:15px;box-shadow:0 8px 20px -8px rgba(5,125,176,.6)">Sign in</button>
        </form>
    </div>
@endsection
