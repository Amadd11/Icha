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
            'registration_type_id' => ['required', 'exists:registration_types,id'],
            'currency'             => ['required', 'in:IDR,USD'],
            'notes'                => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'registration_type_id.required' => 'Kategori pendaftaran wajib dipilih.',
            'registration_type_id.exists'   => 'Kategori pendaftaran tidak valid.',
            'currency.required'             => 'Mata uang wajib dipilih (IDR/USD).',
            'currency.in'                   => 'Pilihan mata uang harus IDR atau USD.',
        ];
    }
}
