@extends('layouts.auth')
@section('title', 'Reset password')
@section('content')
    <div class="login-form-wrap">
        <form class="login-form" action="{{ route('password.update') }}" method="post">
            @csrf
            <img class="logo" src="{{ asset('assets/logo.png') }}" alt="Teamiy" style="filter:none">
            <h2>Create new password</h2>
            <p class="sub">Choose a new password for your employee portal.</p>

            @if ($errors->any())
                <div class="auth-alert danger">{{ $errors->first() }}</div>
            @endif

            <input type="hidden" name="token" value="{{ $token }}">

            <label class="label" for="email">Email</label>
            <input class="input @error('email') is-invalid @enderror" id="email" name="email" type="email"
                value="{{ old('email', $email) }}" autocomplete="email" readonly>

            <div style="height:18px"></div>
            <label class="label" for="password">New password</label>
            <div class="password-wrapper">
                <input class="input @error('password') is-invalid @enderror" type="password" name="password" id="password"
                    autocomplete="new-password">
                <i class="fa-solid fa-eye toggle-password" id="togglePassword"></i>
            </div>

            <div style="height:18px"></div>
            <label class="label" for="password_confirmation">Confirm password</label>
            <input class="input" type="password" name="password_confirmation" id="password_confirmation"
                autocomplete="new-password">

            <div style="height:22px"></div>
            <button type="submit" class="btn btn-primary btn-block"
                style="padding:14px;font-size:15px;box-shadow:0 8px 20px -8px rgba(5,125,176,.6)">Update password</button>
        </form>
    </div>
@endsection
