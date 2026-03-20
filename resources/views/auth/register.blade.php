@extends('layouts.app', ['title' => 'Register'])

@section('content')
<section class="card auth-card">
    <p class="hero-kicker">New Patient Registration</p>
    <h2>Create Patient Account</h2>
    <p>Book visits, manage records, and receive clear appointment reminders.</p>
    <form method="POST" action="{{ route('register') }}" class="grid-form">
        @csrf
        <label>Full Name
            <input type="text" name="name" value="{{ old('name') }}" required>
        </label>
        <label>Email
            <input type="email" name="email" value="{{ old('email') }}" required>
        </label>
        <label>Phone
            <input type="text" name="phone" value="{{ old('phone') }}">
        </label>
        <label>Address
            <input type="text" name="address" value="{{ old('address') }}">
        </label>
        <label>Password
            <input type="password" name="password" required>
        </label>
        <label>Confirm Password
            <input type="password" name="password_confirmation" required>
        </label>
        <button class="btn" type="submit">Register</button>
    </form>
    <p>Already registered? <a href="{{ route('login.form') }}">Login</a></p>
</section>
@endsection
