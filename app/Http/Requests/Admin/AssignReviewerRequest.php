<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AssignReviewerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reviewer_ids' => 'required|array|size:3',
            'reviewer_ids.*' => 'distinct|exists:users,id',
        ];
    }
}
