<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Models\ClinicNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class NotifyDentistOfReschedule implements ShouldQueue
{
    use Queueable;

    public function __construct(public Appointment $appointment)
    {
    }

    public function handle(): void
    {
        ClinicNotification::create([
            'user_id' => $this->appointment->dentist_id,
            'channel' => 'system',
            'title' => 'Reschedule Alert',
            'message' => 'A patient has submitted a rescheduled appointment request.',
            'meta' => ['appointment_id' => $this->appointment->id],
        ]);
    }
}
