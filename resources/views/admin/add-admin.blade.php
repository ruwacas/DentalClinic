@extends('layouts.app', ['title' => 'Add Admin Account'])

@section('content')
<section class="card">
    <h2>Add Admin Account</h2>
    <p>Create a new administrator account.</p>

    <form method="POST" action="{{ route('admin.accounts.store') }}" class="grid-form auth-form">
        @csrf

        <label>Name
            <input type="text" name="name" value="{{ old('name') }}" required>
        </label>

        <label>Username (Email)
            <input type="email" name="email" value="{{ old('email') }}" required>
        </label>

        <label>Enter Password
            <input type="password" name="password" required>
        </label>

        <label>Reenter Password
            <input type="password" name="password_confirmation" required>
        </label>

        <div class="inline-form">
            <a class="btn btn-ghost" href="{{ route('admin.dashboard') }}">Back</a>
            <button class="btn" type="submit">Create Admin</button>
        </div>
    </form>
</section>
@endsection
