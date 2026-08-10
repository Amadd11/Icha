<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreConferenceRequest extends FormRequest
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
            'hero_image'  => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'start_date'  => ['nullable', 'date'],
            'end_date'    => ['nullable', 'date', 'after_or_equal:start_date'],
            'venue'       => ['nullable', 'string', 'max:255'],
            'city'        => ['nullable', 'string', 'max:100'],
            'country'     => ['nullable', 'string', 'max:100'],
            'theme'       => ['nullable', 'string', 'max:255'],
            'website'     => ['nullable', 'url', 'max:255'],
            'email'       => ['nullable', 'email', 'max:255'],
            'status'      => ['required', 'in:draft,active,archived'],
            'is_active'   => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul konferensi wajib diisi saat membuat konferensi baru.',
            'title.max' => 'Judul konferensi maksimal 255 karakter.',
            'year.integer' => 'Tahun harus berupa angka.',
            'logo.image' => 'Logo harus berupa berkas gambar.',
            'logo.max' => 'Ukuran logo maksimal 2MB.',
            'hero_image.image' => 'Gambar utama (hero) harus berupa berkas gambar.',
            'hero_image.max' => 'Ukuran gambar utama maksimal 5MB.',
            'end_date.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'website.url' => 'Format URL situs web tidak valid.',
            'email.email' => 'Format email tidak valid.',
            'status.required' => 'Status konferensi wajib dipilih.',
            'status.in' => 'Status konferensi tidak valid.',
        ];
    }
}
