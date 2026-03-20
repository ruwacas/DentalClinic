<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Models\ClinicNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendAppointmentConfirmation implements ShouldQueue
{
    use Queueable;

    public function __construct(public Appointment $appointment)
    {
    }

    public function handle(): void
    {
        ClinicNotification::create([
            'user_id' => $this->appointment->patient_id,
            'channel' => 'email',
            'title' => 'Appointment Confirmation',
            'message' => 'Your appointment booking was received and is awaiting clinic confirmation.',
            'meta' => ['appointment_id' => $this->appointment->id],
        ]);
    }
}
