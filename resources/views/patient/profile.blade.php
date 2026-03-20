@extends('layouts.app', ['title' => 'Patient Profile'])

@section('content')
<section class="card patient-hero">
    <div>
        <p class="hero-kicker">Patient Profile</p>
        <h2>Health Details and Contact Info</h2>
        <p>Keep your records current for safer, faster, and more personalized care.</p>
    </div>
</section>

<section class="card">
    <h2>Profile and Medical History</h2>
    <form method="POST" action="{{ route('patient.profile.update') }}" class="grid-form">
        @csrf
        <label>Name
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
        </label>
        <label>Phone
            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}">
        </label>
        <label>Address
            <input type="text" name="address" value="{{ old('address', $user->address) }}">
        </label>
        <label>Date of Birth
            <input type="date" name="date_of_birth" value="{{ old('date_of_birth', optional($user->patientProfile?->date_of_birth)->format('Y-m-d')) }}">
        </label>
        <label>Gender
            <input type="text" name="gender" value="{{ old('gender', $user->patientProfile?->gender) }}">
        </label>
        <label>Emergency Contact
            <input type="text" name="emergency_contact" value="{{ old('emergency_contact', $user->patientProfile?->emergency_contact) }}">
        </label>
        <label>Medical History
            <textarea name="medical_history">{{ old('medical_history', $user->patientProfile?->medical_history) }}</textarea>
        </label>
        <label>Allergies
            <textarea name="allergies">{{ old('allergies', $user->patientProfile?->allergies) }}</textarea>
        </label>
        <button class="btn" type="submit">Save Profile</button>
    </form>
    <a href="{{ route('patient.dashboard') }}" class="btn btn-ghost">Back to Dashboard</a>
</section>
@endsection
