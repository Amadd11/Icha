<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommitteeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'conference_id' => ['required', 'exists:conferences,id'],
            'name'          => ['required', 'string', 'max:255'],
            'role'          => ['required', 'string', 'max:255'],
            'institution'   => ['nullable', 'string', 'max:255'],
            'group'         => ['required', 'in:steering,organizing,scientific'],
            'order'         => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'conference_id.required' => 'Konferensi wajib dipilih.',
            'name.required'          => 'Nama komite wajib diisi.',
            'role.required'          => 'Jabatan/peran komite wajib diisi.',
            'group.required'         => 'Kelompok komite wajib dipilih.',
        ];
    }
}
