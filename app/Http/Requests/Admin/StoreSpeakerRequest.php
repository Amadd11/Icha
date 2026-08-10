<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSpeakerRequest extends FormRequest
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
            'title'         => ['nullable', 'string', 'max:100'],
            'institution'   => ['nullable', 'string', 'max:255'],
            'country'       => ['nullable', 'string', 'max:100'],
            'country_code'  => ['nullable', 'string', 'size:2'],
            'bio'           => ['nullable', 'string'],
            'email'         => ['nullable', 'email', 'max:255'],
            'type'          => ['required', 'in:keynote,invited,plenary'],
            'order'         => ['nullable', 'integer', 'min:0'],
            'photo'         => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'conference_id.required' => 'Konferensi wajib dipilih.',
            'conference_id.exists'   => 'Konferensi tidak ditemukan.',
            'name.required'          => 'Nama pembicara wajib diisi.',
            'name.max'               => 'Nama pembicara maksimal 255 karakter.',
            'email.email'            => 'Format email tidak valid.',
            'type.required'          => 'Tipe pembicara (keynote/invited/plenary) wajib dipilih.',
            'type.in'                => 'Tipe pembicara tidak valid.',
            'photo.image'            => 'Foto harus berupa berkas gambar.',
            'photo.mimes'            => 'Format foto harus jpeg, jpg, png, atau webp.',
            'photo.max'              => 'Ukuran foto maksimal 2MB.',
        ];
    }
}
