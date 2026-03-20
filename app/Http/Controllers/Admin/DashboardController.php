<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\DentistProfile;
use App\Models\User;
use App\Models\WalkInQueue;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_patients' => User::where('role', 'patient')->count(),
            'total_dentists' => User::where('role', 'dentist')->count(),
            'appointments_today' => Appointment::whereDate('scheduled_for', now()->toDateString())->count(),
            'pending_appointments' => Appointment::where('status', 'pending')->count(),
        ];

        $appointments = Appointment::with(['patient', 'dentist'])->latest('scheduled_for')->limit(20)->get();
        $patients = User::where('role', 'patient')->latest()->limit(20)->get();
        $dentists = User::where('role', 'dentist')->with('dentistProfile')->get();
        $walkIns = WalkInQueue::latest('queued_at')->limit(20)->get();

        $reports = [
            'daily' => Appointment::whereDate('scheduled_for', now()->toDateString())->count(),
            'weekly' => Appointment::whereBetween('scheduled_for', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'monthly' => Appointment::whereMonth('scheduled_for', now()->month)->whereYear('scheduled_for', now()->year)->count(),
        ];

        return view('admin.dashboard', compact('stats', 'appointments', 'patients', 'dentists', 'reports', 'walkIns'));
    }

    public function upsertDentist(Request $request, ?User $user = null): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email,'.($user?->id ?? 'NULL')],
            'phone' => ['nullable', 'string', 'max:30'],
            'specialty' => ['nullable', 'string', 'max:120'],
            'years_of_experience' => ['nullable', 'integer', 'min:0'],
            'bio' => ['nullable', 'string'],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'],
        ]);

        if (! $user) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => $validated['password'],
                'role' => 'dentist',
            ]);
        } else {
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'role' => 'dentist',
                'password' => $validated['password'] ?? $user->password,
            ]);
        }

        DentistProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'specialty' => $validated['specialty'] ?? null,
                'years_of_experience' => $validated['years_of_experience'] ?? 0,
                'bio' => $validated['bio'] ?? null,
            ]
        );

        return back()->with('success', 'Dentist profile saved.');
    }

    public function updateAppointment(Request $request, Appointment $appointment): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,confirmed,completed,canceled'],
            'scheduled_for' => ['nullable', 'date'],
        ]);

        $payload = ['status' => $validated['status']];
        if (! empty($validated['scheduled_for'])) {
            $time = Carbon::parse($validated['scheduled_for']);
            $payload['scheduled_for'] = $time;
            $payload['ends_at'] = $time->copy()->addMinutes(30);
        }

        $appointment->update($payload);

        return back()->with('success', 'Appointment updated.');
    }

    public function queueWalkIn(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'guest_name' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        WalkInQueue::create($validated);

        return back()->with('success', 'Walk-in patient added to queue.');
    }

    public function updateQueue(Request $request, WalkInQueue $walkInQueue): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:waiting,in_service,completed,canceled'],
        ]);

        $walkInQueue->update($validated);

        return back()->with('success', 'Queue status updated.');
    }
}
