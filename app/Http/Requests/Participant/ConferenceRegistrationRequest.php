<?php

namespace App\Http\Requests\Participant;

use Illuminate\Foundation\Http\FormRequest;

class ConferenceRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'registration_fee_id' => ['required', 'exists:registration_fees,id'],
            'notes'               => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'registration_fee_id.required' => 'Paket pendaftaran wajib dipilih.',
            'registration_fee_id.exists'   => 'Paket pendaftaran tidak valid.',
        ];
    }
}
