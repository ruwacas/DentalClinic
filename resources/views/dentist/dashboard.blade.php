@extends('layouts.app', ['title' => 'Dentist Dashboard'])

@section('content')
<section class="card patient-hero dentist-hero">
    <div class="dentist-hero-today">
        <p class="hero-metric-label">Today Appointments</p>
        <p class="kpi-value hero-metric-count">{{ $todayCount }}</p>
        @if ($todayAppointments->isEmpty())
            <p class="upcoming-empty-note">No appointments scheduled today.</p>
        @else
            <div class="today-mini-wrap">
                <table class="today-mini-table">
                    <colgroup>
                        <col class="mini-col-patient">
                        <col class="mini-col-date">
                        <col class="mini-col-time">
                        <col class="mini-col-services">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Patient Name</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Services</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($todayAppointments as $todayAppointment)
                            <tr>
                                <td>{{ $todayAppointment->patient->name }}</td>
                                <td>{{ $todayAppointment->scheduled_for->format('M d, Y') }}</td>
                                <td>{{ $todayAppointment->scheduled_for->format('h:i A') }}</td>
                                <td>{{ ! empty($todayAppointment->services) ? implode(', ', $todayAppointment->services) : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    <div class="dentist-hero-upcoming">
        <p class="hero-metric-label">Upcoming Appointments</p>
        <p class="kpi-value hero-metric-count">{{ $upcomingCount }}</p>
        @if ($upcomingAppointments->isEmpty())
            <p class="upcoming-empty-note">No upcoming appointments.</p>
        @else
            <div class="upcoming-mini-wrap">
                <table class="upcoming-mini-table">
                    <colgroup>
                        <col class="mini-col-patient">
                        <col class="mini-col-date">
                        <col class="mini-col-time">
                        <col class="mini-col-services">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Patient Name</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Services</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($upcomingAppointments as $upcomingAppointment)
                            <tr>
                                <td>{{ $upcomingAppointment->patient->name }}</td>
                                <td>{{ $upcomingAppointment->scheduled_for->format('M d, Y') }}</td>
                                <td>{{ $upcomingAppointment->scheduled_for->format('h:i A') }}</td>
                                <td>{{ ! empty($upcomingAppointment->services) ? implode(', ', $upcomingAppointment->services) : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</section>

<section class="grid two-col">
    @php
        $showAvailabilityPanel = request()->query('view') === 'availability'
            || $errors->hasAny(['day_of_week', 'day_of_week.*', 'start_time', 'end_time', 'max_clients_per_day', 'is_active']);
        $showAppointmentsPanel = request()->query('view') === 'appointments'
            || $errors->hasAny(['status', 'treatment_details', 'cancellation_reason']);
    @endphp
    @if ($showAvailabilityPanel)
    <article class="card feature-card">
        <h3>Set Availability</h3>
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
    @endif

    @if ($showAppointmentsPanel)
        <article class="card feature-card" id="appointments-panel">
            <h3>Update Appointments</h3>
            @if ($appointments->isEmpty())
                <div class="empty-state">
                    <h4>No appointments assigned</h4>
                    <p>Your schedule is clear for now. Add availability to receive bookings.</p>
                </div>
            @else
                <div class="dentist-table-wrap">
                    <table class="dentist-table">
                        <thead>
                            <tr>
                                <th>Patient Name</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th>Update Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($appointments as $appointment)
                                <tr>
                                    <td>{{ $appointment->patient->name }}</td>
                                    <td>{{ $appointment->scheduled_for->format('M d, Y') }}</td>
                                    <td>{{ $appointment->scheduled_for->format('h:i A') }}</td>
                                    <td>{{ ucfirst($appointment->status) }}</td>
                                    <td class="dentist-actions-cell">
                                        <form method="POST" action="{{ route('dentist.appointments.status', $appointment) }}" class="inline-form">
                                            @csrf
                                            @method('PUT')
                                            <select name="status" required>
                                                <option value="pending" @selected($appointment->status === 'pending')>Pending</option>
                                                <option value="confirmed" @selected($appointment->status === 'confirmed')>Confirmed</option>
                                                <option value="completed" @selected($appointment->status === 'completed')>Completed</option>
                                                <option value="canceled" @selected($appointment->status === 'canceled')>Canceled</option>
                                            </select>
                                            <input type="text" name="treatment_details" placeholder="Treatment details" value="{{ $appointment->treatment_details }}">
                                            <button class="btn" type="submit">Update</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </article>
    @endif
</section>

<script>
    (function () {
        const form = document.getElementById('availability-form');
        const dayButtons = document.querySelectorAll('.day-btn');
        const selectedDaysContainer = document.getElementById('selected-days');

        if (!form || !selectedDaysContainer) {
            return;
        }

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
