<?php

namespace App\Http\Controllers\Dentist;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAppointmentStatusRequest;
use App\Models\Appointment;
use App\Models\AppointmentNote;
use App\Models\Availability;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const DAY_LABELS = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    ];

    public function index(Request $request): View
    {
        $dentist = $request->user();

        $appointments = Appointment::with('patient')
            ->where('dentist_id', $dentist->id)
            ->orderBy('scheduled_for')
            ->get();

        $todayCount = Appointment::where('dentist_id', $dentist->id)
            ->whereDate('scheduled_for', now()->toDateString())
            ->count();

        $availabilities = $dentist->availabilities()
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return view('dentist.dashboard', compact('appointments', 'todayCount', 'dentist', 'availabilities'));
    }

    public function saveAvailability(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'day_of_week' => ['required', 'array', 'min:1'],
            'day_of_week.*' => ['integer', 'between:0,6', 'distinct'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'max_clients_per_day' => ['required', 'integer', 'min:1', 'max:200'],
        ]);

        $dentist = $request->user();
        $days = collect($validated['day_of_week'])->unique()->values();

        $conflictingDays = $dentist->availabilities()
            ->whereIn('day_of_week', $days)
            ->pluck('day_of_week')
            ->unique()
            ->sort()
            ->values();

        if ($conflictingDays->isNotEmpty()) {
            $dayNames = $this->formatDayNames($conflictingDays);

            return back()
                ->withInput()
                ->withErrors([
                    'day_of_week' => 'Schedule conflict detected for: '.$dayNames.'. Please edit existing availability instead.',
                ]);
        }

        foreach ($days as $day) {
            $dentist->availabilities()->create([
                'day_of_week' => $day,
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'max_clients_per_day' => $validated['max_clients_per_day'],
                'is_active' => true,
            ]);
        }

        return back()->with('success', 'Availability saved for '.$days->count().' day(s).');
    }

    public function updateAvailability(Request $request, Availability $availability): RedirectResponse
    {
        abort_unless($availability->dentist_id === $request->user()->id, 403);

        $dentistId = $request->user()->id;

        $validated = $request->validate([
            'day_of_week' => [
                'required',
                'integer',
                'between:0,6',
                Rule::unique('availabilities', 'day_of_week')
                    ->where(fn ($query) => $query->where('dentist_id', $dentistId))
                    ->ignore($availability->id),
            ],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'max_clients_per_day' => ['required', 'integer', 'min:1', 'max:200'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'day_of_week.unique' => 'Schedule conflict detected: this day already has an availability entry.',
        ]);

        $availability->update([
            'day_of_week' => $validated['day_of_week'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'max_clients_per_day' => $validated['max_clients_per_day'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Availability updated.');
    }

    public function deleteAvailability(Request $request, Availability $availability): RedirectResponse
    {
        abort_unless($availability->dentist_id === $request->user()->id, 403);

        $availability->delete();

        return back()->with('success', 'Availability deleted.');
    }

    private function formatDayNames(Collection $days): string
    {
        return $days
            ->map(fn ($day) => self::DAY_LABELS[(int) $day] ?? 'Day '.$day)
            ->implode(', ');
    }

    public function updateStatus(UpdateAppointmentStatusRequest $request, Appointment $appointment): RedirectResponse
    {
        abort_unless($appointment->dentist_id === $request->user()->id, 403);

        $appointment->update([
            'status' => $request->string('status')->toString(),
            'treatment_details' => $request->input('treatment_details'),
            'cancellation_reason' => $request->input('cancellation_reason'),
            'canceled_by' => $request->string('status')->toString() === 'canceled' ? $request->user()->id : null,
        ]);

        return back()->with('success', 'Appointment status updated.');
    }

    public function addNote(Request $request, Appointment $appointment): RedirectResponse
    {
        abort_unless($appointment->dentist_id === $request->user()->id, 403);

        $validated = $request->validate([
            'note' => ['required', 'string'],
        ]);

        AppointmentNote::create([
            'appointment_id' => $appointment->id,
            'dentist_id' => $request->user()->id,
            'note' => $validated['note'],
        ]);

        return back()->with('success', 'Note added.');
    }
}
