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
            'reviewer_ids' => 'required|array|min:1|max:3',
            'reviewer_ids.*' => 'exists:users,id',
        ];
    }
}
