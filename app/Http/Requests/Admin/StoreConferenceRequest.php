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
            'logo'          => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:2048'],
            'hero_images'   => ['nullable', 'array', 'max:4'],
            'hero_images.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
            'poster'        => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
            'start_date'          => ['nullable', 'date'],
            'end_date'            => ['nullable', 'date', 'after_or_equal:start_date'],
            'venue'               => ['nullable', 'string', 'max:255'],
            'city'                => ['nullable', 'string', 'max:100'],
            'country'             => ['nullable', 'string', 'max:100'],
            'theme'               => ['nullable', 'string', 'max:255'],
            'email'               => ['nullable', 'email', 'max:255'],
            'bank_name'           => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:100'],
            'bank_account_holder' => ['nullable', 'string', 'max:150'],
            'bank_instructions'   => ['nullable', 'string'],
            'abstract_template'   => ['nullable', 'file', 'mimes:doc,docx,pdf', 'max:20480'],
            'paper_template'      => ['nullable', 'file', 'mimes:doc,docx,pdf', 'max:20480'],
            'status'              => ['required', 'in:draft,active,archived'],
            'is_active'           => ['boolean'],
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
            'hero_images.max' => 'Maksimal 4 foto hero banner yang dapat diunggah.',
            'hero_images.*.image' => 'Berkas hero banner harus berupa gambar.',
            'hero_images.*.max' => 'Ukuran berkas hero banner maksimal 10MB per foto.',
            'end_date.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'email.email' => 'Format email tidak valid.',
            'status.required' => 'Status konferensi wajib dipilih.',
            'status.in' => 'Status konferensi tidak valid.',
        ];
    }
}
