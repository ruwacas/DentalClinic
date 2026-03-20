@extends('layouts.app', ['title' => 'Login'])

@section('content')
<section class="card auth-card">
    <p class="hero-kicker">Mountain Pearl Access</p>
    <h2>Welcome Back</h2>
    <p>Sign in to manage appointments, notifications, and your care journey.</p>
    <form method="POST" action="{{ route('login') }}" class="grid-form">
        @csrf
        <label>Email
            <input type="email" name="email" value="{{ old('email') }}" required>
        </label>
        <label>Password
            <input type="password" name="password" required>
        </label>
        <button class="btn" type="submit">Login</button>
    </form>
    <p>No account yet? <a href="{{ route('register.form') }}">Create one</a></p>
</section>
@endsection
