@extends('layouts.app', ['title' => 'Edit My Account'])

@section('content')
<section class="card">
    <h2>Edit My Account</h2>
    <p>Update your admin profile details.</p>

    <form method="POST" action="{{ route('admin.accounts.update') }}" class="grid-form auth-form">
        @csrf
        @method('PUT')

        <label>Name
            <input type="text" name="name" value="{{ old('name', $admin->name) }}" required>
        </label>

        <label>Enter New Password
            <input type="password" name="password" placeholder="Leave blank to keep your current password">
        </label>

        <label>Reenter New Password
            <input type="password" name="password_confirmation" placeholder="Leave blank to keep your current password">
        </label>

        <div class="inline-form">
            <a class="btn btn-ghost" href="{{ route('admin.dashboard') }}">Back</a>
            <button class="btn" type="submit">Save Changes</button>
        </div>
    </form>
</section>
@endsection
