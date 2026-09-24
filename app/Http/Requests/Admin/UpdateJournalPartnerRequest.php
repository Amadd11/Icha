<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJournalPartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'conference_id'           => ['sometimes', 'required', 'exists:conferences,id'],
            'journal_name'            => ['sometimes', 'required', 'string', 'max:255'],
            'university'              => ['sometimes', 'required', 'string', 'max:255'],
            'website'                 => ['nullable', 'url', 'max:500'],
            'submission_requirements' => ['nullable', 'string'],
            'loa_information'         => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'conference_id.required' => 'Konferensi wajib dipilih.',
            'journal_name.required'  => 'Nama jurnal wajib diisi.',
            'university.required'    => 'Nama universitas / institusi wajib diisi.',
            'website.url'            => 'Format URL website tidak valid.',
        ];
    }
}
