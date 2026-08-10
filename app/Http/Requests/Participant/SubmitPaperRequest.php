<?php

namespace App\Http\Requests\Participant;

use Illuminate\Foundation\Http\FormRequest;

class SubmitPaperRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'abstract_id' => ['nullable', 'exists:abstracts,id'],
            'file' => ['required', 'file', 'mimes:doc,docx,pdf', 'max:20480'], // 20MB max
        ];
    }
}
