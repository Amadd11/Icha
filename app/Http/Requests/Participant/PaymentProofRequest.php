<?php

namespace App\Http\Requests\Participant;

use Illuminate\Foundation\Http\FormRequest;

class PaymentProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'registration_id' => ['required', 'exists:registrations,id'],
            'payment_method'  => ['required', 'string', 'max:100'],
            'proof_file'      => ['required', 'file', 'mimes:jpeg,jpg,png,pdf', 'max:5120'], // Max 5MB
        ];
    }
}
