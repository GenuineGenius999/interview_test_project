@extends('layouts.app')

@section('title', 'Log in')

@section('content')
    <section class="auth-card" aria-labelledby="login-heading">
        <div>
            <p class="eyebrow">Ticket Desk</p>
            <h1 id="login-heading">Log in</h1>
            <p class="muted">Use your organization account to continue.</p>
        </div>

        <form method="POST" action="{{ route('login.store') }}" class="form-stack">
            @csrf

            <div class="field">
                <label for="email">Email</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    autocomplete="email"
                    required
                    autofocus
                    aria-describedby="email-error"
                >
                @error('email')
                    <p class="field-error" id="email-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    required
                    aria-describedby="password-error"
                >
                @error('password')
                    <p class="field-error" id="password-error">{{ $message }}</p>
                @enderror
            </div>

            <button class="button" type="submit">Log in</button>
        </form>
    </section>
@endsection
