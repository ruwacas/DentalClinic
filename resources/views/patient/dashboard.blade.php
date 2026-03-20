@extends('layouts.app', ['title' => 'Patient Dashboard'])

@section('content')
<section class="patient-hero card">
    <div>
        <h2>Your Smile Plan, Simplified</h2>
        <p>Book, move, and track appointments with real-time slot visibility and clear confirmations.</p>
    </div>
    <a class="btn" href="{{ route('patient.profile') }}">Update Profile</a>
</section>

<section class="grid two-col">
    <article class="card feature-card">
        <h2>Book Appointment</h2>
        <form method="POST" action="{{ route('patient.appointments.store') }}" class="grid-form" id="book-form">
            @csrf
            <label>Dentist
                <select name="dentist_id" id="dentist-select" required>
                    <option value="">Choose dentist</option>
                    @foreach ($dentists as $dentist)
                        <option value="{{ $dentist->id }}">{{ $dentist->name }} @if($dentist->dentistProfile?->specialty)- {{ $dentist->dentistProfile->specialty }}@endif</option>
                    @endforeach
                </select>
            </label>
            <label>Date
                <input type="date" id="date-select" required>
            </label>
            <label>Available Slot
                <select name="scheduled_for" id="slot-select" required>
                    <option value="">Select date and dentist</option>
                </select>
            </label>
            <label>Reason
                <input type="text" name="reason" placeholder="Cleaning, pain, checkup...">
            </label>
            <button class="btn" type="submit">Book Now</button>
        </form>
    </article>

    <article class="card feature-card">
        <h2>Upcoming Appointments</h2>
        <div class="list">
            @forelse ($upcoming as $appointment)
                <div class="list-item">
                    <div>
                        <strong>{{ $appointment->scheduled_for->format('M d, Y h:i A') }}</strong>
                        <p>with Dr. {{ $appointment->dentist->name }} | {{ ucfirst($appointment->status) }}</p>
                    </div>
                    <div class="stack-actions">
                        <button type="button" 
                                class="btn btn-ghost"
                                data-dentist-id="{{ $appointment->dentist_id }}"
                                data-action-url="{{ route('patient.appointments.reschedule', $appointment) }}"
                                onclick="openRescheduleModal(this.dataset.dentistId, this.dataset.actionUrl)">
                            Reschedule
                        </button>
                        <form method="POST" action="{{ route('patient.appointments.cancel', $appointment) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="cancellation_reason" value="Canceled by patient">
                            <button class="btn btn-danger" type="submit">Cancel</button>
                        </form>
                    </div>
                </div>
            @empty
                <p>No upcoming appointments.</p>
            @endforelse
        </div>
    </article>
</section>

{{-- Reschedule Modal --}}
<dialog id="reschedule-modal" class="card" style="margin: auto; width: 90%; max-width: 500px; padding: 0; border: none;">
    <div style="padding: 2rem;">
        <h3>Reschedule Appointment</h3>
        <p>Select a new date and time slot.</p>
        <form method="POST" id="reschedule-form" class="grid-form">
            @csrf
            @method('PUT')
            <input type="hidden" name="dentist_id" id="reschedule-dentist-id">
            
            <label>New Date
                <input type="date" id="reschedule-date" required>
            </label>
            
            <label>Available Slot
                <select name="scheduled_for" id="reschedule-slot" required>
                    <option value="">Select date first</option>
                </select>
            </label>
            
            <label>Reason for change
                <input type="text" name="reason" placeholder="E.g., schedule conflict...">
            </label>

            <div class="inline-form" style="justify-content: flex-end;">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('reschedule-modal').close()">Cancel</button>
                <button class="btn" type="submit">Confirm Reschedule</button>
            </div>
        </form>
    </div>
</dialog>

<section class="grid two-col">
    <article class="card feature-card">
        <h2>Recent History</h2>
        <div class="list">
            @forelse ($history as $appointment)
                <div class="list-item">
                    <span>{{ $appointment->scheduled_for->format('M d, Y h:i A') }} with Dr. {{ $appointment->dentist->name }}</span>
                    <span class="chip chip-{{ $appointment->status }}">{{ ucfirst($appointment->status) }}</span>
                </div>
            @empty
                <p>No historical records yet.</p>
            @endforelse
        </div>
    </article>

    <article class="card feature-card">
        <h2>Notifications (Email/SMS Simulation)</h2>
        <div class="list">
            @forelse ($notifications as $notification)
                <div class="list-item">
                    <div>
                        <strong>{{ $notification->title }}</strong>
                        <p>{{ $notification->message }}</p>
                    </div>
                    <span class="chip">{{ strtoupper($notification->channel) }}</span>
                </div>
            @empty
                <p>No notifications yet.</p>
            @endforelse
        </div>
        <a class="btn btn-ghost" href="{{ route('patient.profile') }}">Edit Profile & Medical History</a>
    </article>
</section>

<script>
    const dentistSelect = document.getElementById('dentist-select');
    const dateSelect = document.getElementById('date-select');
    const slotSelect = document.getElementById('slot-select');

    // Reschedule elements
    const rescheduleModal = document.getElementById('reschedule-modal');
    const rescheduleForm = document.getElementById('reschedule-form');
    const rescheduleDate = document.getElementById('reschedule-date');
    const rescheduleSlot = document.getElementById('reschedule-slot');
    const rescheduleDentistId = document.getElementById('reschedule-dentist-id');

    async function loadSlots() {
        if (!dentistSelect.value || !dateSelect.value) {
            return;
        }

        const query = new URLSearchParams({
            dentist_id: dentistSelect.value,
            date: dateSelect.value,
        });

        const response = await fetch(`{{ route('patient.slots') }}?${query.toString()}`);
        const data = await response.json();

        slotSelect.innerHTML = '';
        if (!data.slots || !data.slots.length) {
            slotSelect.innerHTML = `<option value="">${data.message || 'No slots available'}</option>`;
            return;
        }

        slotSelect.innerHTML = '<option value="">Choose slot</option>';
        data.slots.forEach((slot) => {
            const option = document.createElement('option');
            option.value = slot.value;
            option.textContent = slot.label;
            slotSelect.appendChild(option);
        });
    }

    dentistSelect.addEventListener('change', loadSlots);
    dateSelect.addEventListener('change', loadSlots);

    // Reschedule Logic
    window.openRescheduleModal = function(dentistId, actionUrl) {
        rescheduleForm.action = actionUrl;
        rescheduleDentistId.value = dentistId;
        rescheduleDate.value = '';
        rescheduleSlot.innerHTML = '<option value="">Select date first</option>';
        rescheduleModal.showModal();
    };

    rescheduleDate.addEventListener('change', async () => {
        const dentistId = rescheduleDentistId.value;
        const date = rescheduleDate.value;

        if (!dentistId || !date) return;

        const query = new URLSearchParams({ dentist_id: dentistId, date: date });
        const response = await fetch(`{{ route('patient.slots') }}?${query.toString()}`);
        const data = await response.json();

        rescheduleSlot.innerHTML = '';
        if (!data.slots || !data.slots.length) {
            rescheduleSlot.innerHTML = `<option value="">${data.message || 'No slots available'}</option>`;
            return;
        }

        rescheduleSlot.innerHTML = '<option value="">Choose new slot</option>';
        data.slots.forEach((slot) => {
            const option = document.createElement('option');
            option.value = slot.value;
            option.textContent = slot.label;
            rescheduleSlot.appendChild(option);
        });
    });
</script>
@endsection
