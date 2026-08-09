<?php

namespace App\Http\Requests\Participant;

use Illuminate\Foundation\Http\FormRequest;

class ConferenceRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'registration_type_id' => ['required', 'exists:registration_types,id'],
            'currency'             => ['required', 'in:IDR,USD'],
            'notes'                => ['nullable', 'string', 'max:500'],
        ];
    }
}
