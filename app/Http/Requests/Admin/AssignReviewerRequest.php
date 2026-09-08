<?php

namespace App\Http\Requests\Admin;

use App\Models\ReviewRound;
use Illuminate\Foundation\Http\FormRequest;

class AssignReviewerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $abstract = $this->route('abstract');
        $round = null;
        if ($abstract) {
            $round = ReviewRound::where('submission_type', 'abstract')
                ->where('submission_id', $abstract->id ?? $abstract)
                ->latest('round_number')
                ->first();
        }

        $isRound1 = (!$round || $round->round_number === 1);

        if (!$isRound1) {
            return [
                'reviewer_ids' => ['prohibited'],
            ];
        }

        return [
            'reviewer_ids' => ['required', 'array', 'size:3'],
            'reviewer_ids.*' => ['distinct', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'reviewer_ids.prohibited' => 'Penugasan reviewer pada tahap revisi terkunci otomatis dan tidak dapat diubah secara manual.',
            'reviewer_ids.required' => 'Pilih reviewer terlebih dahulu.',
            'reviewer_ids.size' => 'Tepat 3 reviewer harus dipilih untuk telaah awal.',
            'reviewer_ids.*.exists' => 'Reviewer yang dipilih tidak valid.',
            'reviewer_ids.*.distinct' => 'Reviewer tidak boleh dipilih lebih dari satu kali.',
        ];
    }
}
