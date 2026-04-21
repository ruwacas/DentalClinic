<?php

namespace App\Http\Requests;

use App\Models\Service;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $serviceOptions = Service::query()->pluck('name')->all();

        return [
            'dentist_id' => ['required', 'integer', 'exists:users,id'],
            'scheduled_for' => ['required', 'date', 'after:now'],
            'services' => ['required', 'array', 'min:1'],
            'services.*' => ['string', Rule::in($serviceOptions)],
        ];
    }
}
