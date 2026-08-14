<?php

namespace App\Http\Requests\Reviewer;

use Illuminate\Foundation\Http\FormRequest;

class SubmitReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'score_criteria_1' => ['required', 'integer', 'min:1', 'max:5'],
            'score_criteria_2' => ['required', 'integer', 'min:1', 'max:5'],
            'recommendation'   => ['required', 'string', 'in:ORAL,POSTER,REVISION,REJECT,oral,poster,revision,reject,accepted,rejected,revision_required'],
            'summary'          => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'score_criteria_1.required' => 'Skor kriteria 1 wajib dipilih (1-5).',
            'score_criteria_2.required' => 'Skor kriteria 2 wajib dipilih (1-5).',
            'recommendation.required'   => 'Rekomendasi hasil penilaian wajib dipilih.',
        ];
    }
}
