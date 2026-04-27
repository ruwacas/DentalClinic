<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AppointmentApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $appointments = Appointment::with(['patient', 'dentist'])
            ->where(function ($query) use ($request) {
                if ($request->user()->isPatient()) {
                    $query->where('patient_id', $request->user()->id);
                }

                if ($request->user()->isDentist()) {
                    $query->where('dentist_id', $request->user()->id);
                }
            })
            ->latest('scheduled_for')
            ->paginate(15);

        return response()->json($appointments);
    }

    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        if (! $request->user()->isPatient()) {
            abort(403, 'Only patients can create appointments via this endpoint.');
        }

        $scheduled = Carbon::parse($request->string('scheduled_for')->toString());

        $availabilityError = $this->validateDentistCapacityAndSchedule(
            $request->integer('dentist_id'),
            $scheduled
        );

        if ($availabilityError !== null) {
            return response()->json(['message' => $availabilityError], 422);
        }

        $exists = Appointment::where('dentist_id', $request->integer('dentist_id'))
            ->where('scheduled_for', $scheduled)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Selected slot is unavailable.'], 422);
        }

        $appointment = Appointment::create([
            'patient_id' => $request->user()->id,
            'dentist_id' => $request->integer('dentist_id'),
            'scheduled_for' => $scheduled,
            'ends_at' => $scheduled->copy()->addMinutes(30),
            'services' => $request->input('services', []),
            'status' => 'pending',
        ]);

        return response()->json($appointment->load(['patient', 'dentist']), 201);
    }

    public function show(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentAccess(request(), $appointment);

        return response()->json($appointment->load(['patient', 'dentist', 'notes']));
    }

    public function update(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentAccess($request, $appointment);

        $validated = $request->validate([
            'status' => ['nullable', 'in:pending,confirmed,completed,canceled'],
            'scheduled_for' => ['nullable', 'date'],
            'services' => ['nullable', 'array'],
            'services.*' => ['string'],
        ]);

        if ($request->user()->isPatient() && array_key_exists('status', $validated)) {
            return response()->json(['message' => 'Patients cannot update appointment status.'], 403);
        }

        if (isset($validated['scheduled_for'])) {
            $newTime = Carbon::parse($validated['scheduled_for']);

            $availabilityError = $this->validateDentistCapacityAndSchedule(
                $appointment->dentist_id,
                $newTime,
                $appointment->id
            );

            if ($availabilityError !== null) {
                return response()->json(['message' => $availabilityError], 422);
            }

            $exists = Appointment::where('dentist_id', $appointment->dentist_id)
                ->where('scheduled_for', $newTime)
                ->whereIn('status', ['pending', 'confirmed'])
                ->where('id', '!=', $appointment->id)
                ->exists();

            if ($exists) {
                return response()->json(['message' => 'Selected slot is unavailable.'], 422);
            }

            $validated['scheduled_for'] = $newTime;
            $validated['ends_at'] = $newTime->copy()->addMinutes(30);
        }

        $appointment->update($validated);

        return response()->json($appointment->fresh()->load(['patient', 'dentist']));
    }

    public function destroy(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentAccess(request(), $appointment);

        $appointment->update([
            'status' => 'canceled',
            'canceled_by' => Auth::id(),
            'cancellation_reason' => 'Canceled via API',
        ]);

        return response()->json(['message' => 'Appointment canceled successfully.']);
    }

    public function slots(Request $request): JsonResponse
    {
        $request->validate([
            'dentist_id' => ['required', 'integer', 'exists:users,id'],
            'date' => ['required', 'date'],
        ]);

        $date = Carbon::parse($request->input('date'));
        $dentist = User::findOrFail($request->integer('dentist_id'));

        $availabilities = $dentist->availabilities()
            ->where('day_of_week', $date->dayOfWeek)
            ->where('is_active', true)
            ->get();

        $booked = Appointment::where('dentist_id', $dentist->id)
            ->whereDate('scheduled_for', $date->toDateString())
            ->whereIn('status', ['pending', 'confirmed'])
            ->pluck('scheduled_for')
            ->map(fn ($value) => Carbon::parse($value)->format('H:i'))
            ->toArray();

        $slots = [];
        foreach ($availabilities as $availability) {
            $cursor = Carbon::parse($date->toDateString().' '.$availability->start_time);
            $end = Carbon::parse($date->toDateString().' '.$availability->end_time);

            while ($cursor < $end) {
                $label = $cursor->format('H:i');
                if (! in_array($label, $booked, true) && $cursor->greaterThan(now())) {
                    $slots[] = $cursor->format('Y-m-d H:i:s');
                }
                $cursor->addMinutes(30);
            }
        }

        return response()->json(['slots' => $slots]);
    }

    private function authorizeAppointmentAccess(Request $request, Appointment $appointment): void
    {
        $user = $request->user();

        if (! $user) {
            throw new HttpException(401, 'Unauthenticated.');
        }

        if ($user->isAdmin()) {
            return;
        }

        $canAccess = ($user->isPatient() && $appointment->patient_id === $user->id)
            || ($user->isDentist() && $appointment->dentist_id === $user->id);

        if (! $canAccess) {
            abort(403, 'You are not authorized to access this appointment.');
        }
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
