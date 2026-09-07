<?php

namespace App\Http\Requests\Participant;

use App\Models\Conference;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAbstractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'             => ['required', 'string', 'max:255'],
            'category_id'       => [
                'required',
                Rule::exists('categories', 'id')->where(function ($query) {
                    $activeConfId = Conference::where('is_active', true)->value('id');
                    if ($activeConfId) {
                        $query->where('conference_id', $activeConfId);
                    }
                }),
            ],
            'file'              => ['nullable', 'file', 'mimes:doc,docx,pdf', 'max:10240'], // Opsional saat revisi
            'presentation_type' => ['nullable', 'in:oral,poster'],
            'keywords'          => ['nullable', 'string', 'max:255'],
            'abstract_text'     => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'       => 'Judul abstrak wajib diisi.',
            'title.max'            => 'Judul abstrak maksimal 255 karakter.',
            'category_id.required' => 'Topik/kategori ilmiah wajib dipilih.',
            'category_id.exists'   => 'Topik ilmiah yang dipilih tidak valid untuk konferensi yang sedang aktif.',
            'file.file'            => 'Berkas yang diunggah tidak valid.',
            'file.mimes'           => 'Format berkas abstrak harus berupa .doc, .docx, atau .pdf.',
            'file.max'             => 'Ukuran berkas abstrak maksimal 10MB.',
        ];
    }
}
