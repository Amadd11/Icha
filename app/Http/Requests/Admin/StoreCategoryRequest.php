<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
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
            'badge'         => ['nullable', 'string', 'max:50'],
            'description'   => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'conference_id.required' => 'Konferensi wajib dipilih.',
            'name.required'          => 'Nama topik/kategori ilmiah wajib diisi.',
            'name.max'               => 'Nama topik ilmiah maksimal 255 karakter.',
        ];
    }
}
