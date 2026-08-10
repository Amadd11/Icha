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
            'registration_id' => ['required', 'exists:registrations,id'],
            'payment_method'  => ['required', 'string', 'max:100'],
            'proof_file'      => ['required', 'file', 'mimes:jpeg,jpg,png,pdf', 'max:5120'], // Max 5MB
        ];
    }

    public function messages(): array
    {
        return [
            'registration_id.required' => 'Pendaftaran konferensi tidak ditemukan.',
            'payment_method.required'  => 'Metode pembayaran wajib diisi.',
            'proof_file.required'      => 'Berkas bukti pembayaran wajib diunggah.',
            'proof_file.file'          => 'Berkas bukti pembayaran tidak valid.',
            'proof_file.mimes'         => 'Format berkas bukti pembayaran harus berupa .jpg, .jpeg, .png, atau .pdf.',
            'proof_file.max'           => 'Ukuran berkas bukti pembayaran maksimal 5MB.',
        ];
    }
}
