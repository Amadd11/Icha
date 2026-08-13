<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'year'        => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'tagline'     => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'logo'        => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:2048'],
            'hero_image'  => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
            'start_date'  => ['nullable', 'date'],
            'end_date'    => ['nullable', 'date', 'after_or_equal:start_date'],
            'venue'       => ['nullable', 'string', 'max:255'],
            'city'        => ['nullable', 'string', 'max:100'],
            'country'     => ['nullable', 'string', 'max:100'],
            'theme'       => ['nullable', 'string', 'max:255'],
            'email'       => ['nullable', 'email', 'max:255'],
            'status'      => ['required', 'in:draft,active,archived'],
            'is_active'   => ['boolean'],
            'remove_logo' => ['nullable', 'boolean'],
            'remove_hero_image' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul konferensi wajib diisi.',
            'title.max' => 'Judul konferensi maksimal 255 karakter.',
            'year.integer' => 'Tahun harus berupa angka.',
            'logo.image' => 'Logo harus berupa berkas gambar.',
            'logo.max' => 'Ukuran logo maksimal 2MB.',
            'hero_image.image' => 'Gambar utama (hero) harus berupa berkas gambar.',
            'hero_image.max' => 'Ukuran gambar utama maksimal 10MB.',
            'end_date.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'email.email' => 'Format email tidak valid.',
            'status.required' => 'Status konferensi wajib dipilih.',
            'status.in' => 'Status konferensi tidak valid.',
        ];
    }
}
