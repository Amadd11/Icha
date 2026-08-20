<?php

namespace App\Http\Requests\Participant;

use Illuminate\Foundation\Http\FormRequest;

class PaymentProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'registration_id' => ['nullable', 'integer', 'exists:registrations,id'],
            'payment_method'  => ['required', 'string', 'max:100'],
            'proof_file'      => ['required', 'file', 'mimes:jpeg,jpg,png,pdf', 'max:5120'], // Max 5MB
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.required'  => 'Metode pembayaran wajib dipilih / diisi.',
            'proof_file.required'      => 'Berkas bukti transfer wajib diunggah.',
            'proof_file.file'          => 'Berkas bukti transfer tidak valid.',
            'proof_file.mimes'         => 'Format berkas harus berupa JPG, PNG, atau PDF.',
            'proof_file.max'           => 'Ukuran berkas maksimal 5MB.',
        ];
    }
}
