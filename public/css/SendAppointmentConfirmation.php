<?php

namespace App\Jobs;

use App\Mail\AppointmentConfirmed;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendAppointmentConfirmation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Appointment $appointment
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // The recipient is the patient associated with the appointment
        $recipient = $this->appointment->patient;

        // Ensure the patient has an email to send to
        if ($recipient && $recipient->email) {
            Mail::to($recipient)->send(new AppointmentConfirmed($this->appointment));
        }
    }
}