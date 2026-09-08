<?php

namespace App\Http\Requests\Reviewer;

use Illuminate\Foundation\Http\FormRequest;

class SubmitReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('recommendation')) {
            $this->merge([
                'recommendation' => strtoupper(trim((string) $this->recommendation)),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'score_criteria_1' => ['required', 'integer', 'min:1', 'max:5'],
            'score_criteria_2' => ['required', 'integer', 'min:1', 'max:5'],
            'recommendation'   => [
                'required',
                'string',
                'in:ACCEPTED,ACCEPT,REVISION,REVISION_REQUIRED,REJECT,REJECTED,ORAL,POSTER,accepted,accept,revision,revision_required,reject,rejected,oral,poster',
            ],
            'summary'          => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'score_criteria_1.required' => 'Skor kriteria 1 wajib dipilih (1-5).',
            'score_criteria_2.required' => 'Skor kriteria 2 wajib dipilih (1-5).',
            'recommendation.required'   => 'Rekomendasi (Accepted, Revision, atau Reject) wajib dipilih.',
            'recommendation.in'         => 'Rekomendasi yang dipilih tidak valid.',
        ];
    }
}
