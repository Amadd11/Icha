<?php

namespace App\Http\Requests\Participant;

use App\Models\Conference;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaperRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'title'       => ['required', 'string', 'max:255'],
            'abstract_id' => [
                'nullable',
                Rule::exists('abstracts', 'id')->where(function ($query) use ($userId) {
                    if ($userId) {
                        $query->where('user_id', $userId);
                    }
                    $activeConfId = Conference::where('is_active', true)->value('id');
                    if ($activeConfId) {
                        $query->where('conference_id', $activeConfId);
                    }
                }),
            ],
            'file'        => ['required', 'file', 'mimes:doc,docx,pdf', 'max:20480'], // 20MB max
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'     => 'Judul naskah artikel (Full Paper) wajib diisi.',
            'title.max'          => 'Judul artikel maksimal 255 karakter.',
            'abstract_id.exists' => 'Abstrak tidak ditemukan, bukan milik Anda, atau tidak terdaftar pada konferensi aktif.',
            'file.required'      => 'Berkas naskah artikel (Full Paper) wajib diunggah saat pengajuan baru.',
            'file.file'          => 'Berkas naskah tidak valid.',
            'file.mimes'         => 'Format berkas naskah artikel harus berupa .doc, .docx, atau .pdf.',
            'file.max'           => 'Ukuran berkas naskah artikel maksimal 20MB.',
        ];
    }
}
