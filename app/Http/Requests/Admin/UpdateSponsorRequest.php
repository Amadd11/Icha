<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSponsorRequest extends FormRequest
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
            'website'       => ['nullable', 'url', 'max:255'],
            'tier'          => ['required', 'in:title,platinum,gold,silver,bronze,exhibitor'],
            'description'   => ['nullable', 'string'],
            'is_active'     => ['boolean'],
            'order'         => ['nullable', 'integer', 'min:0'],
            'logo'          => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'conference_id.required' => 'Konferensi wajib dipilih.',
            'name.required'          => 'Nama sponsor wajib diisi.',
            'tier.required'          => 'Tingkat sponsorship wajib dipilih.',
            'logo.image'             => 'Logo harus berupa gambar.',
            'logo.max'               => 'Ukuran logo maksimal 2MB.',
        ];
    }
}
