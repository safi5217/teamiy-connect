@extends('layouts.auth')
@section('title', 'Forgot password')
@section('content')
    <div class="login-form-wrap">
        <form class="login-form" action="{{ route('password.email') }}" method="post">
            @csrf
            <img class="logo" src="{{ asset('assets/logo.png') }}" alt="Teamiy" style="filter:none">
            <h2>Reset password</h2>
            <p class="sub">Enter your employee email, work email, or username.</p>

            @if (session('status'))
                <div class="auth-alert success">{{ session('status') }}</div>
            @endif

            <label class="label" for="login">Employee login</label>
            <input class="input @error('login') is-invalid @enderror" id="login" name="login" type="text"
                value="{{ old('login') }}" autocomplete="username" autofocus>
            @error('login')
                <small class="field-error">{{ $message }}</small>
            @enderror

            <div style="height:22px"></div>
            <button type="submit" class="btn btn-primary btn-block"
                style="padding:14px;font-size:15px;box-shadow:0 8px 20px -8px rgba(5,125,176,.6)">Send reset link</button>

            <div class="auth-actions">
                <a class="link" href="{{ route('login') }}">Back to sign in</a>
            </div>
        </form>
    </div>
@endsection
