@extends('layouts.app', ['title' => 'Admin Dashboard'])

@section('content')
<section class="card patient-hero">
    <div>
        <p class="hero-kicker">Admin Console</p>
        <h2>Clinic Operations Overview</h2>
        <p>Monitor appointments, manage users, and keep daily operations flowing smoothly.</p>
    </div>
</section>

<section class="grid cards-4">
    <article class="card"><h3>Total Patients</h3><p class="kpi-value">{{ $stats['total_patients'] }}</p></article>
    <article class="card"><h3>Total Dentists</h3><p class="kpi-value">{{ $stats['total_dentists'] }}</p></article>
    <article class="card"><h3>Appointments Today</h3><p class="kpi-value">{{ $stats['appointments_today'] }}</p></article>
    <article class="card"><h3>Pending Reservations</h3><p class="kpi-value">{{ $stats['pending_appointments'] }}</p></article>
</section>

<section class="grid two-col">
    <article class="card">
        <h2>Create Dentist Account</h2>
        <form method="POST" action="{{ route('admin.dentists.store') }}" class="grid-form">
            @csrf
            <input type="text" name="name" placeholder="Dentist name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="phone" placeholder="Phone">
            <input type="text" name="specialty" placeholder="Specialty">
            <input type="number" name="years_of_experience" placeholder="Years of experience" min="0">
            <textarea name="bio" placeholder="Bio"></textarea>
            <input type="password" name="password" placeholder="Temporary password" required>
            <button class="btn" type="submit">Save Dentist</button>
        </form>
    </article>

    <article class="card">
        <h2>Walk-in Queue</h2>
        <form method="POST" action="{{ route('admin.queue.store') }}" class="inline-form">
            @csrf
            <input type="text" name="guest_name" placeholder="Guest name">
            <input type="text" name="phone" placeholder="Phone">
            <input type="text" name="reason" placeholder="Reason">
            <button class="btn" type="submit">Add to Queue</button>
        </form>
        <div class="list">
            @foreach ($walkIns as $item)
                <div class="list-item">
                    <span>{{ $item->guest_name ?? 'Patient #'.$item->patient_id }} - {{ $item->status }}</span>
                    <form method="POST" action="{{ route('admin.queue.update', $item) }}" class="inline-form">
                        @csrf
                        @method('PUT')
                        <select name="status">
                            <option value="waiting">Waiting</option>
                            <option value="in_service">In Service</option>
                            <option value="completed">Completed</option>
                            <option value="canceled">Canceled</option>
                        </select>
                        <button class="btn btn-ghost" type="submit">Update</button>
                    </form>
                </div>
            @endforeach
        </div>
    </article>
</section>

<section class="grid two-col">
    <article class="card">
        <h2>Appointments Monitoring</h2>
        <div class="list">
            @foreach ($appointments as $appointment)
                <div class="list-item block-item">
                    <p><strong>{{ $appointment->patient->name }}</strong> with Dr. {{ $appointment->dentist->name }}</p>
                    <p>{{ $appointment->scheduled_for->format('M d, Y h:i A') }}</p>
                    <form method="POST" action="{{ route('admin.appointments.update', $appointment) }}" class="inline-form">
                        @csrf
                        @method('PUT')
                        <select name="status">
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="completed">Completed</option>
                            <option value="canceled">Canceled</option>
                        </select>
                        <input type="datetime-local" name="scheduled_for">
                        <button class="btn" type="submit">Apply</button>
                    </form>
                </div>
            @endforeach
        </div>
    </article>

    <article class="card">
        <h2>Reports</h2>
        <p>Daily appointments: <strong>{{ $reports['daily'] }}</strong></p>
        <p>Weekly appointments: <strong>{{ $reports['weekly'] }}</strong></p>
        <p>Monthly appointments: <strong>{{ $reports['monthly'] }}</strong></p>

        <h3>Dentists</h3>
        <div class="list compact">
            @foreach ($dentists as $dentist)
                <div class="list-item">
                    <span>{{ $dentist->name }}</span>
                    <span>{{ $dentist->dentistProfile?->specialty }}</span>
                </div>
            @endforeach
        </div>

        <h3>Patients</h3>
        <div class="list compact">
            @foreach ($patients as $patient)
                <div class="list-item">
                    <span>{{ $patient->name }}</span>
                    <span>{{ $patient->email }}</span>
                </div>
            @endforeach
        </div>
    </article>
</section>
@endsection
