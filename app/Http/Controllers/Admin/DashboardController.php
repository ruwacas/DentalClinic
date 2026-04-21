<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\DentistProfile;
use App\Models\Service;
use App\Models\User;
use App\Models\WalkInQueue;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $today = now()->toDateString();

        $todayAppointments = Appointment::with(['patient', 'dentist'])
            ->whereDate('scheduled_for', $today)
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('scheduled_for')
            ->get();

        $upcomingAppointments = Appointment::with(['patient', 'dentist'])
            ->whereDate('scheduled_for', '>', $today)
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('scheduled_for')
            ->get();

        $stats = [
            'total_patients' => User::where('role', 'patient')->count(),
            'total_dentists' => User::where('role', 'dentist')->count(),
            'today_appointments' => $todayAppointments->count(),
            'upcoming_appointments' => $upcomingAppointments->count(),
        ];

        $dentists = User::where('role', 'dentist')->with('dentistProfile')->get();

        $servicePeriodOptions = [
            '7d' => 'Last 7 Days',
            '30d' => 'Last 30 Days',
            'ytd' => 'Year to Date',
            'all' => 'All Time',
        ];

        $selectedServicePeriod = (string) $request->query('service_period', '30d');
        if (! array_key_exists($selectedServicePeriod, $servicePeriodOptions)) {
            $selectedServicePeriod = '30d';
        }

        $selectedTrendPeriod = (string) $request->query('trend_period', '30d');
        if (! array_key_exists($selectedTrendPeriod, $servicePeriodOptions)) {
            $selectedTrendPeriod = '30d';
        }

        $periodStart = match ($selectedServicePeriod) {
            '7d' => now()->subDays(7)->startOfDay(),
            '30d' => now()->subDays(30)->startOfDay(),
            'ytd' => now()->startOfYear(),
            default => null,
        };

        $serviceUsageCounts = [];
        $appointmentsForUsage = Appointment::query()
            ->when($periodStart, fn ($query) => $query->where('scheduled_for', '>=', $periodStart))
            ->get(['services']);

        foreach ($appointmentsForUsage as $appointmentForUsage) {
            foreach ((array) ($appointmentForUsage->services ?? []) as $serviceName) {
                $serviceUsageCounts[$serviceName] = ($serviceUsageCounts[$serviceName] ?? 0) + 1;
            }
        }

        $categoryPalette = [
            '#157F7B',
            '#2F66A8',
            '#C97A2A',
            '#6A4FB0',
            '#2C8C6B',
            '#B04F7B',
            '#5C7B8A',
        ];

        $serviceRecords = Service::query()
            ->orderBy('category')
            ->orderBy('name')
            ->get(['category', 'name']);

        $categoryColors = [];
        $serviceChartData = [];

        foreach ($serviceRecords as $serviceRecord) {
            if (! isset($categoryColors[$serviceRecord->category])) {
                $colorIndex = count($categoryColors) % count($categoryPalette);
                $categoryColors[$serviceRecord->category] = $categoryPalette[$colorIndex];
            }

            $serviceChartData[] = [
                'service' => $serviceRecord->name,
                'category' => $serviceRecord->category,
                'count' => $serviceUsageCounts[$serviceRecord->name] ?? 0,
                'color' => $categoryColors[$serviceRecord->category],
            ];
        }

        $now = now();
        $trendGranularity = 'day';
        $onlineReservationXAxisLabel = 'Day';

        if ($selectedTrendPeriod === 'ytd') {
            $trendGranularity = 'week';
            $onlineReservationXAxisLabel = 'Week';
        }

        if ($selectedTrendPeriod === 'all') {
            $trendGranularity = 'month';
            $onlineReservationXAxisLabel = 'Month';
        }

        $firstAppointmentCreatedAt = Appointment::query()->min('created_at');

        $trendStart = match ($trendGranularity) {
            'day' => $selectedTrendPeriod === '7d'
                ? $now->copy()->subDays(6)->startOfDay()
                : $now->copy()->subDays(29)->startOfDay(),
            'week' => $now->copy()->startOfYear()->startOfWeek(),
            default => $firstAppointmentCreatedAt
                ? Carbon::parse((string) $firstAppointmentCreatedAt)->startOfMonth()
                : $now->copy()->startOfMonth(),
        };

        $trendEnd = match ($trendGranularity) {
            'day' => $now->copy()->startOfDay(),
            'week' => $now->copy()->startOfWeek(),
            default => $now->copy()->startOfMonth(),
        };

        $rawReservationRows = Appointment::query()
            ->whereBetween('created_at', [$trendStart, $now])
            ->get(['created_at', 'status']);

        $reservationCountsByBucket = [];
        $completedCountsByBucket = [];
        foreach ($rawReservationRows as $reservationRow) {
            $timestampCarbon = Carbon::parse((string) $reservationRow->created_at);

            $bucketKey = match ($trendGranularity) {
                'day' => $timestampCarbon->toDateString(),
                'week' => $timestampCarbon->copy()->startOfWeek()->toDateString(),
                default => $timestampCarbon->format('Y-m'),
            };

            $reservationCountsByBucket[$bucketKey] = ($reservationCountsByBucket[$bucketKey] ?? 0) + 1;

            if ($reservationRow->status === 'completed') {
                $completedCountsByBucket[$bucketKey] = ($completedCountsByBucket[$bucketKey] ?? 0) + 1;
            }
        }

        $onlineReservationTrendData = [];
        $periodIterator = CarbonPeriod::create(
            $trendStart,
            $trendGranularity === 'day' ? '1 day' : ($trendGranularity === 'week' ? '1 week' : '1 month'),
            $trendEnd
        );

        foreach ($periodIterator as $point) {
            $pointKey = match ($trendGranularity) {
                'day' => $point->toDateString(),
                'week' => $point->copy()->startOfWeek()->toDateString(),
                default => $point->format('Y-m'),
            };

            $pointLabel = match ($trendGranularity) {
                'day' => $point->format('M d'),
                'week' => $point->format('M d'),
                default => $point->format('M Y'),
            };

            $onlineReservationTrendData[] = [
                'label' => $pointLabel,
                'count' => $reservationCountsByBucket[$pointKey] ?? 0,
                'completed_count' => $completedCountsByBucket[$pointKey] ?? 0,
            ];
        }

        $servicePeriodLabel = $servicePeriodOptions[$selectedServicePeriod];
        $trendPeriodLabel = $servicePeriodOptions[$selectedTrendPeriod];

        return view('admin.dashboard', compact(
            'stats',
            'todayAppointments',
            'upcomingAppointments',
            'dentists',
            'serviceChartData',
            'categoryColors',
            'servicePeriodOptions',
            'selectedServicePeriod',
            'selectedTrendPeriod',
            'servicePeriodLabel',
            'trendPeriodLabel',
            'onlineReservationTrendData',
            'onlineReservationXAxisLabel'
        ));
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
