<?php

namespace App\Jobs;

use App\Mail\AppointmentRescheduled;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class NotifyDentistOfReschedule implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Appointment $appointment
    ) {}

    public function handle(): void
    {
        // Load the dentist relationship if not already loaded
        if (!$this->appointment->relationLoaded('dentist')) {
            $this->appointment->load('dentist');
        }

        $dentist = $this->appointment->dentist;

        if ($dentist && $dentist->email) {
            Mail::to($dentist)->send(new AppointmentRescheduled($this->appointment));
        }
    }
}