<?php

namespace App\Http\Requests\Participant;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                 => ['required', 'string', 'max:255'],
            'phone'                => ['required', 'string', 'max:30'],
            'institution'          => ['required', 'string', 'max:255'],
            'country'              => ['required', 'string', 'max:100'],
            'city'                 => ['required', 'string', 'max:100'],
            'address'              => ['nullable', 'string'],
            'participant_category' => ['required', 'in:student,non_student'],
            'identity_number'      => ['required', 'string', 'max:100'],
            'gender'               => ['required', 'in:male,female,other'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                 => 'Nama lengkap wajib diisi.',
            'phone.required'                => 'Nomor telepon/WhatsApp wajib diisi.',
            'institution.required'          => 'Nama instansi/universitas wajib diisi.',
            'country.required'              => 'Negara asal wajib diisi.',
            'city.required'                 => 'Kota asal wajib diisi.',
            'participant_category.required' => 'Kategori peserta (mahasiswa/umum) wajib dipilih.',
            'identity_number.required'      => 'Nomor identitas (NIK/NIM/KTP/Paspor) wajib diisi.',
            'gender.required'               => 'Jenis kelamin wajib dipilih.',
        ];
    }
}
