<?php

namespace App\Http\Requests\Participant;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
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
}
