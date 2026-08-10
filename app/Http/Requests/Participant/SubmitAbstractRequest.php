<?php

namespace App\Http\Requests\Participant;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAbstractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'file' => ['required', 'file', 'mimes:doc,docx,pdf', 'max:10240'], // 10MB max
            'presentation_type' => ['nullable', 'in:oral,poster'],
            'keywords' => ['nullable', 'string', 'max:255'],
            'abstract_text' => ['nullable', 'string'],
        ];
    }
}
