<?php

namespace App\Http\Controllers\Patient;

use App\Jobs\SendAppointmentConfirmation;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppointmentRequest;
use App\Jobs\NotifyDentistOfReschedule;
use App\Models\Appointment;
use App\Models\ClinicNotification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function dashboard(Request $request): View
    {
        $user = $request->user();

        $upcoming = Appointment::with('dentist')
            ->where('patient_id', $user->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('scheduled_for', '>=', now())
            ->orderBy('scheduled_for')
            ->get();

        $history = Appointment::with('dentist')
            ->where('patient_id', $user->id)
            ->where(function ($query) {
                $query->where('scheduled_for', '<', now())
                    ->orWhereIn('status', ['completed', 'canceled']);
            })
            ->latest('scheduled_for')
            ->limit(20)
            ->get();

        $dentists = User::where('role', 'dentist')->with('dentistProfile')->orderBy('name')->get();

        $notifications = $user->notificationsFeed()->latest()->limit(10)->get();

        return view('patient.dashboard', compact('user', 'upcoming', 'history', 'dentists', 'notifications'));
    }

    public function profile(Request $request): View
    {
        $user = $request->user()->load('patientProfile');

        return view('patient.profile', compact('user'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:30'],
            'emergency_contact' => ['nullable', 'string', 'max:255'],
            'medical_history' => ['nullable', 'string'],
            'allergies' => ['nullable', 'string'],
        ]);

        $request->user()->update([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
        ]);

        $request->user()->patientProfile()->updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'gender' => $data['gender'] ?? null,
                'emergency_contact' => $data['emergency_contact'] ?? null,
                'medical_history' => $data['medical_history'] ?? null,
                'allergies' => $data['allergies'] ?? null,
            ]
        );

        return back()->with('success', 'Profile updated successfully.');
    }

    public function availableSlots(Request $request): JsonResponse
    {
        $request->validate([
            'dentist_id' => ['required', 'integer', 'exists:users,id'],
            'date' => ['required', 'date'],
        ]);

        $date = Carbon::parse($request->string('date')->toString());
        $dayOfWeek = (int) $date->dayOfWeek;

        $availabilities = User::findOrFail($request->integer('dentist_id'))
            ->availabilities()
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get();

        $dayCapacity = (int) $availabilities->sum('max_clients_per_day');

        if ($availabilities->isEmpty() || $dayCapacity < 1) {
            return response()->json([
                'slots' => [],
                'message' => 'Dentist is not available on this day.',
            ]);
        }

        $bookedQuery = Appointment::where('dentist_id', $request->integer('dentist_id'))
            ->whereDate('scheduled_for', $date->toDateString())
            ->whereIn('status', ['pending', 'confirmed']);

        $dailyBookedCount = (clone $bookedQuery)->count();

        if ($dailyBookedCount >= $dayCapacity) {
            return response()->json([
                'slots' => [],
                'message' => 'This dentist has reached the daily client limit for the selected date.',
            ]);
        }

        $booked = $bookedQuery
            ->pluck('scheduled_for')
            ->map(fn ($value) => Carbon::parse($value)->format('H:i'))
            ->toArray();

        $slots = [];
        foreach ($availabilities as $availability) {
            $cursor = Carbon::parse($date->toDateString().' '.$availability->start_time);
            $end = Carbon::parse($date->toDateString().' '.$availability->end_time);

            while ($cursor < $end) {
                $slotLabel = $cursor->format('H:i');
                if (! in_array($slotLabel, $booked, true) && $cursor->greaterThan(now())) {
                    $slots[] = [
                        'value' => $cursor->format('Y-m-d H:i:s'),
                        'label' => $cursor->format('h:i A'),
                    ];
                }
                $cursor->addMinutes(30);
            }
        }

        return response()->json(['slots' => $slots]);
    }

    public function store(StoreAppointmentRequest $request): RedirectResponse
    {
        $scheduled = Carbon::parse($request->string('scheduled_for')->toString());
        $endsAt = $scheduled->copy()->addMinutes(30);

        $availabilityError = $this->validateDentistCapacityAndSchedule(
            $request->integer('dentist_id'),
            $scheduled
        );

        if ($availabilityError !== null) {
            return back()->withInput()->withErrors(['scheduled_for' => $availabilityError]);
        }

        $alreadyBooked = Appointment::where('dentist_id', $request->integer('dentist_id'))
            ->where('scheduled_for', $scheduled)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($alreadyBooked) {
            return back()->withErrors(['scheduled_for' => 'That slot is no longer available.']);
        }

        $appointment = Appointment::create([
            'patient_id' => $request->user()->id,
            'dentist_id' => $request->integer('dentist_id'),
            'scheduled_for' => $scheduled,
            'ends_at' => $endsAt,
            'reason' => $request->input('reason'),
            'status' => 'pending',
        ]);

        $this->notifyPatient($appointment, 'Appointment Booked', 'Your appointment is pending confirmation.', 'system');
        $this->notifyDentist($appointment, 'New Appointment Request', "{$appointment->patient->name} has requested an appointment.");
        SendAppointmentConfirmation::dispatch($appointment);

        return back()->with('success', 'Appointment booked successfully. A confirmation email will be sent shortly.');
    }

    public function reschedule(StoreAppointmentRequest $request, Appointment $appointment): RedirectResponse
    {
        abort_unless($appointment->patient_id === $request->user()->id, 403);

        if (! in_array($appointment->status, ['pending', 'confirmed'], true)) {
            return back()->withErrors(['appointment' => 'This appointment cannot be rescheduled.']);
        }

        $newSlot = Carbon::parse($request->string('scheduled_for')->toString());

        $availabilityError = $this->validateDentistCapacityAndSchedule(
            $request->integer('dentist_id'),
            $newSlot,
            $appointment->id
        );

        if ($availabilityError !== null) {
            return back()->withInput()->withErrors(['scheduled_for' => $availabilityError]);
        }

        $exists = Appointment::where('dentist_id', $request->integer('dentist_id'))
            ->where('scheduled_for', $newSlot)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('id', '!=', $appointment->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['scheduled_for' => 'The selected new slot is not available.']);
        }

        $appointment->update([
            'dentist_id' => $request->integer('dentist_id'),
            'scheduled_for' => $newSlot,
            'ends_at' => $newSlot->copy()->addMinutes(30),
            'reason' => $request->input('reason'),
            'status' => 'pending',
        ]);

        $this->notifyPatient($appointment, 'Appointment Rescheduled', 'Your appointment has been changed and is now pending confirmation.', 'sms');
        $this->notifyDentist($appointment, 'Appointment Rescheduled', "{$appointment->patient->name} has rescheduled their appointment.");
        NotifyDentistOfReschedule::dispatch($appointment);

        return back()->with('success', 'Appointment rescheduled.');
    }

    public function cancel(Request $request, Appointment $appointment): RedirectResponse
    {
        abort_unless($appointment->patient_id === $request->user()->id, 403);

        $appointment->update([
            'status' => 'canceled',
            'canceled_by' => $request->user()->id,
            'cancellation_reason' => $request->input('cancellation_reason', 'Canceled by patient'),
        ]);

        $this->notifyPatient($appointment, 'Appointment Canceled', 'Your appointment has been canceled.', 'system');
        $this->notifyDentist($appointment, 'Appointment Canceled', "An appointment with {$appointment->patient->name} has been canceled.");

        return back()->with('success', 'Appointment canceled.');
    }

    private function notifyPatient(Appointment $appointment, string $title, string $message, string $channel): void
    {
        ClinicNotification::create([
            'user_id' => $appointment->patient_id,
            'channel' => $channel,
            'title' => $title,
            'message' => $message,
            'meta' => ['appointment_id' => $appointment->id]
        ]);
    }

    private function notifyDentist(Appointment $appointment, string $title, string $message, string $channel = 'system'): void
    {
        ClinicNotification::create([
            'user_id' => $appointment->dentist_id,
            'channel' => $channel,
            'title' => $title,
            'message' => $message,
            'meta' => ['appointment_id' => $appointment->id]
        ]);
    }

    private function validateDentistCapacityAndSchedule(int $dentistId, Carbon $scheduled, ?int $ignoreAppointmentId = null): ?string
    {
        $dayOfWeek = (int) $scheduled->dayOfWeek;

        $availabilities = User::findOrFail($dentistId)
            ->availabilities()
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->get();

        if ($availabilities->isEmpty()) {
            return 'Dentist is not available on the selected day.';
        }

        $slotIsWithinAvailability = $availabilities->contains(function ($availability) use ($scheduled) {
            $start = Carbon::parse($scheduled->toDateString().' '.$availability->start_time);
            $end = Carbon::parse($scheduled->toDateString().' '.$availability->end_time);

            return $scheduled->greaterThanOrEqualTo($start) && $scheduled->lessThan($end);
        });

        if (! $slotIsWithinAvailability) {
            return 'Selected time is outside the dentist availability window.';
        }

        $dailyCapacity = (int) $availabilities->sum('max_clients_per_day');

        $dailyBookedQuery = Appointment::where('dentist_id', $dentistId)
            ->whereDate('scheduled_for', $scheduled->toDateString())
            ->whereIn('status', ['pending', 'confirmed']);

        if ($ignoreAppointmentId !== null) {
            $dailyBookedQuery->where('id', '!=', $ignoreAppointmentId);
        }

        if ($dailyBookedQuery->count() >= $dailyCapacity) {
            return 'This dentist has reached the daily client limit for the selected date.';
        }

        return null;
    }
}
