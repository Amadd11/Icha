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
            'currency'            => ['required', 'in:IDR,USD'],
            'notes'               => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'registration_fee_id.required' => 'Paket pendaftaran wajib dipilih.',
            'registration_fee_id.exists'   => 'Paket pendaftaran tidak valid.',
            'currency.required'            => 'Mata uang wajib dipilih (IDR/USD).',
            'currency.in'                  => 'Pilihan mata uang harus IDR atau USD.',
        ];
    }
}
