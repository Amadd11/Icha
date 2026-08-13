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
            'phone'                => ['nullable', 'string', 'max:50'],
            'institution'          => ['nullable', 'string', 'max:255'],
            'country'              => ['required', 'string', 'max:100'],
            'city'                 => ['nullable', 'string', 'max:100'],
            'participant_category' => ['nullable', 'in:student,non_student'],
            'gender'               => ['nullable', 'in:male,female'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                 => 'Nama lengkap wajib diisi.',
            'country.required'              => 'Negara asal wajib diisi.',
            'participant_category.in'       => 'Kategori peserta (student/non_student) tidak valid.',
            'gender.in'                     => 'Jenis kelamin tidak valid.',
        ];
    }
}
