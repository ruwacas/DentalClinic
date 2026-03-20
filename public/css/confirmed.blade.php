<x-mail::message>
# Appointment Confirmation

Hello {{ $appointment->patient->name }},

This is a confirmation for your upcoming dental appointment.

**Dentist:** Dr. {{ $appointment->dentist->name }}
**Date & Time:** {{ $appointment->scheduled_for->format('F j, Y, g:i a') }}
**Reason:** {{ $appointment->reason ?? 'Not specified' }}

If you need to reschedule or cancel, please visit your dashboard.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>