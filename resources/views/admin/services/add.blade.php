@extends('layouts.app', ['title' => 'Add Service'])

@section('content')
<section class="card">
    <h2>Add Service</h2>
    <p>Create a new service available for patient bookings.</p>

    <form method="POST" action="{{ route('admin.services.store') }}" class="grid-form auth-form">
        @csrf

        <label>Category
            <input type="text" name="category" value="{{ old('category') }}" placeholder="E.g., A. CONSULTATION" required>
        </label>

        <label>Service Name
            <input type="text" name="name" value="{{ old('name') }}" required>
        </label>

        <div class="inline-form">
            <a class="btn btn-ghost" href="{{ route('admin.dashboard') }}">Back</a>
            <button class="btn" type="submit">Add Service</button>
        </div>
    </form>
</section>
@endsection
