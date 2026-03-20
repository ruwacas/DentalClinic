<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
        $scheduled = Carbon::parse($request->string('scheduled_for')->toString());

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
            'reason' => $request->input('reason'),
            'status' => 'pending',
        ]);

        return response()->json($appointment->load(['patient', 'dentist']), 201);
    }

    public function show(Appointment $appointment): JsonResponse
    {
        return response()->json($appointment->load(['patient', 'dentist', 'notes']));
    }

    public function update(Request $request, Appointment $appointment): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:pending,confirmed,completed,canceled'],
            'scheduled_for' => ['nullable', 'date'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        if (isset($validated['scheduled_for'])) {
            $newTime = Carbon::parse($validated['scheduled_for']);

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
        $appointment->update([
            'status' => 'canceled',
            'canceled_by' => auth()->id(),
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
}
