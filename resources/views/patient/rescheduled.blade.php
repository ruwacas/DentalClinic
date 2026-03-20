<x-mail::message>
# Appointment Rescheduled

Hello Dr. {{ $appointment->dentist->name }},

Patient **{{ $appointment->patient->name }}** has rescheduled their appointment.

**New Date & Time:** {{ $appointment->scheduled_for->format('F j, Y, g:i a') }}
**Reason for Change:** {{ $appointment->reason ?? 'No reason provided' }}

Please login to your dashboard to review this appointment.

<x-mail::button :url="route('dentist.dashboard')">
View Dashboard
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>