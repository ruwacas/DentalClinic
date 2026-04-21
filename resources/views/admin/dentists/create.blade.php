@extends('layouts.app', ['title' => 'Add Dentist Account'])

@section('content')
<section class="card">
    <h2>Add Dentist Account</h2>
    <p>Create a new dentist account.</p>

    <form method="POST" action="{{ route('admin.dentists.store') }}" class="grid-form auth-form">
        @csrf

        <label>Name
            <input type="text" name="name" value="{{ old('name') }}" required>
        </label>

        <label>Email
            <input type="email" name="email" value="{{ old('email') }}" required>
        </label>

        <label>Phone
            <input type="text" name="phone" value="{{ old('phone') }}">
        </label>

        <label>Specialty
            <input type="text" name="specialty" value="{{ old('specialty') }}">
        </label>

        <label>Years of Experience
            <input type="number" name="years_of_experience" min="0" value="{{ old('years_of_experience') }}">
        </label>

        <label>Bio
            <textarea name="bio">{{ old('bio') }}</textarea>
        </label>

        <label>Password
            <input type="password" name="password" required>
        </label>

        <label>Confirm Password
            <input type="password" name="password_confirmation" required>
        </label>

        <div class="inline-form">
            <a class="btn btn-ghost" href="{{ route('admin.dentists.index') }}">Back</a>
            <button class="btn" type="submit">Add Dentist</button>
        </div>
    </form>
</section>
@endsection
