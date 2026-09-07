<?php

namespace App\Http\Requests\Participant;

use App\Models\Conference;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConferenceRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'registration_fee_id' => [
                'required',
                Rule::exists('registration_fees', 'id')->where(function ($query) {
                    $activeConfId = Conference::where('is_active', true)->value('id');
                    $query->where('conference_id', $activeConfId ?? -1)
                        ->where('is_active', true);
                }),
            ],
            'notes'               => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'registration_fee_id.required' => 'Paket pendaftaran wajib dipilih.',
            'registration_fee_id.exists'   => 'Paket pendaftaran tidak valid untuk konferensi yang sedang aktif.',
        ];
    }
}
