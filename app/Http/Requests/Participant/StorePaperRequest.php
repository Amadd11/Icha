<?php

namespace App\Http\Requests\Participant;

use Illuminate\Foundation\Http\FormRequest;

class StorePaperRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'abstract_id' => ['nullable', 'exists:abstracts,id'],
            'file'        => ['required', 'file', 'mimes:doc,docx,pdf', 'max:20480'], // 20MB max
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'     => 'Judul naskah artikel (Full Paper) wajib diisi.',
            'title.max'          => 'Judul artikel maksimal 255 karakter.',
            'abstract_id.exists' => 'Abstrak terkait tidak ditemukan.',
            'file.required'      => 'Berkas naskah artikel (Full Paper) wajib diunggah saat pengajuan baru.',
            'file.file'          => 'Berkas naskah tidak valid.',
            'file.mimes'         => 'Format berkas naskah artikel harus berupa .doc, .docx, atau .pdf.',
            'file.max'           => 'Ukuran berkas naskah artikel maksimal 20MB.',
        ];
    }
}
