@extends('layouts.app', ['title' => 'Dentist Dashboard'])

@section('content')
<section class="card patient-hero">
    <div>
        <p class="hero-kicker">Dentist Portal</p>
        <h2>Dentist Dashboard</h2>
        <p>Today appointments</p>
        <p class="kpi-value">{{ $todayCount }}</p>
    </div>
</section>

<section class="grid two-col">
    <article class="card feature-card">
        <h3>Add Availability</h3>
        <p>Choose one or more working days, then set your clinic hours.</p>
        @php
            $selectedDays = collect(old('day_of_week', []))
                ->map(fn ($day) => (string) $day)
                ->all();
        @endphp
        <form method="POST" action="{{ route('dentist.availability.store') }}" class="grid-form" id="availability-form">
            @csrf
            <div id="selected-days">
                @foreach ($selectedDays as $day)
                    <input type="hidden" name="day_of_week[]" value="{{ $day }}">
                @endforeach
            </div>

            <fieldset class="weekday-picker" aria-label="Pick day of week">
                <legend>Day of Week</legend>
                <div class="weekday-grid">
                    <button type="button" class="day-btn {{ in_array('0', $selectedDays, true) ? 'active' : '' }}" data-day="0" aria-pressed="{{ in_array('0', $selectedDays, true) ? 'true' : 'false' }}">Sun</button>
                    <button type="button" class="day-btn {{ in_array('1', $selectedDays, true) ? 'active' : '' }}" data-day="1" aria-pressed="{{ in_array('1', $selectedDays, true) ? 'true' : 'false' }}">Mon</button>
                    <button type="button" class="day-btn {{ in_array('2', $selectedDays, true) ? 'active' : '' }}" data-day="2" aria-pressed="{{ in_array('2', $selectedDays, true) ? 'true' : 'false' }}">Tue</button>
                    <button type="button" class="day-btn {{ in_array('3', $selectedDays, true) ? 'active' : '' }}" data-day="3" aria-pressed="{{ in_array('3', $selectedDays, true) ? 'true' : 'false' }}">Wed</button>
                    <button type="button" class="day-btn {{ in_array('4', $selectedDays, true) ? 'active' : '' }}" data-day="4" aria-pressed="{{ in_array('4', $selectedDays, true) ? 'true' : 'false' }}">Thu</button>
                    <button type="button" class="day-btn {{ in_array('5', $selectedDays, true) ? 'active' : '' }}" data-day="5" aria-pressed="{{ in_array('5', $selectedDays, true) ? 'true' : 'false' }}">Fri</button>
                    <button type="button" class="day-btn {{ in_array('6', $selectedDays, true) ? 'active' : '' }}" data-day="6" aria-pressed="{{ in_array('6', $selectedDays, true) ? 'true' : 'false' }}">Sat</button>
                </div>
            </fieldset>
            @error('day_of_week')
                <small class="form-error">{{ $message }}</small>
            @enderror
            @error('day_of_week.*')
                <small class="form-error">{{ $message }}</small>
            @enderror

            <label>Start Time
                <input type="time" name="start_time" value="{{ old('start_time') }}" required>
            </label>
            <label>End Time
                <input type="time" name="end_time" value="{{ old('end_time') }}" required>
            </label>
            <label>Daily Client Limit
                <input type="number" name="max_clients_per_day" min="1" max="200" value="{{ old('max_clients_per_day', 10) }}" required>
            </label>
            @error('max_clients_per_day')
                <small class="form-error">{{ $message }}</small>
            @enderror
            <button class="btn" type="submit">Save Availability</button>
        </form>

        <hr>

        <h4>Saved Availability</h4>
        <div class="availability-stack">
            @php
                $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            @endphp
            @forelse ($availabilities as $availability)
                <article class="availability-item">
                <form method="POST" action="{{ route('dentist.availability.update', $availability) }}" class="availability-form" id="availability-update-{{ $availability->id }}">
                    @csrf
                    @method('PUT')
                    <div class="availability-fields">
                        <label>Day
                            <select name="day_of_week" required>
                                @foreach ($dayNames as $index => $label)
                                    <option value="{{ $index }}" @selected((int) $availability->day_of_week === $index)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <label>Start
                            <input type="time" name="start_time" value="{{ $availability->start_time }}" required>
                        </label>
                        <label>End
                            <input type="time" name="end_time" value="{{ $availability->end_time }}" required>
                        </label>
                        <label>Daily Limit
                            <input type="number" name="max_clients_per_day" min="1" max="200" value="{{ $availability->max_clients_per_day ?? 10 }}" required>
                        </label>
                        <label class="availability-active">
                            <input type="checkbox" name="is_active" value="1" @checked($availability->is_active)>
                            Active
                        </label>
                    </div>
                </form>
                <div class="availability-actions">
                    <button class="btn btn-ghost" type="submit" form="availability-update-{{ $availability->id }}">Update</button>
                    <form method="POST" action="{{ route('dentist.availability.delete', $availability) }}" onsubmit="return confirm('Delete this availability?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger" type="submit">Delete</button>
                    </form>
                </div>
                </article>
            @empty
                <div class="empty-state">
                    <h4>No saved availability yet</h4>
                    <p>Add your first schedule above so patients can see your open days.</p>
                </div>
            @endforelse
        </div>
    </article>

    <article class="card feature-card">
        <h3>Appointments</h3>
        <div class="list">
            @forelse ($appointments as $appointment)
                <div class="list-item block-item">
                    <strong>{{ $appointment->scheduled_for->format('M d, Y h:i A') }}</strong>
                    <p>Patient: {{ $appointment->patient->name }} | Status: {{ ucfirst($appointment->status) }}</p>
                    <form method="POST" action="{{ route('dentist.appointments.status', $appointment) }}" class="inline-form">
                        @csrf
                        @method('PUT')
                        <select name="status" required>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="completed">Completed</option>
                            <option value="canceled">Canceled</option>
                        </select>
                        <input type="text" name="treatment_details" placeholder="Treatment details">
                        <button class="btn" type="submit">Update</button>
                    </form>
                    <form method="POST" action="{{ route('dentist.appointments.notes', $appointment) }}" class="inline-form">
                        @csrf
                        <input type="text" name="note" placeholder="Add quick note" required>
                        <button class="btn btn-ghost" type="submit">Add Note</button>
                    </form>
                </div>
            @empty
                <div class="empty-state">
                    <h4>No appointments assigned</h4>
                    <p>Your schedule is clear for now. Add availability to receive bookings.</p>
                </div>
            @endforelse
        </div>
    </article>
</section>

<script>
    (function () {
        const form = document.getElementById('availability-form');
        const dayButtons = document.querySelectorAll('.day-btn');
        const selectedDaysContainer = document.getElementById('selected-days');
        const selectedDays = new Set(
            Array.from(selectedDaysContainer.querySelectorAll('input[name="day_of_week[]"]')).map((input) => input.value)
        );

        const renderHiddenInputs = () => {
            selectedDaysContainer.innerHTML = '';

            Array.from(selectedDays)
                .sort()
                .forEach((day) => {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'day_of_week[]';
                    hiddenInput.value = day;
                    selectedDaysContainer.appendChild(hiddenInput);
                });
        };

        dayButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const dayValue = button.dataset.day;

                if (selectedDays.has(dayValue)) {
                    selectedDays.delete(dayValue);
                    button.classList.remove('active');
                    button.setAttribute('aria-pressed', 'false');
                } else {
                    selectedDays.add(dayValue);
                    button.classList.add('active');
                    button.setAttribute('aria-pressed', 'true');
                }

                renderHiddenInputs();
            });
        });

        form.addEventListener('submit', (event) => {
            if (!selectedDays.size) {
                event.preventDefault();
                alert('Please select at least one day of the week.');
            }
        });
    })();
</script>
@endsection
