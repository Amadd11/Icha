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
            'score_criteria_1' => 'required|integer|min:1|max:5',
            'score_criteria_2' => 'required|integer|min:1|max:5',
            'recommendation' => 'required|string|in:ORAL,POSTER,REJECT',
            'summary' => 'nullable|string',
        ];
    }
}
