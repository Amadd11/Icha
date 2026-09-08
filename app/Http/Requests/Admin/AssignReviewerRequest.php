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
        $paper = $this->route('paper');

        $submissionType = $paper ? 'full_paper' : 'abstract';
        $submissionId = $paper ? ($paper->id ?? $paper) : ($abstract ? ($abstract->id ?? $abstract) : null);
        $requiredCount = $paper ? 2 : 3;

        $round = null;
        if ($submissionId) {
            $round = ReviewRound::where('submission_type', $submissionType)
                ->where('submission_id', $submissionId)
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
            'reviewer_ids' => ['required', 'array', "size:{$requiredCount}"],
            'reviewer_ids.*' => ['distinct', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        $requiredCount = $this->route('paper') ? 2 : 3;

        return [
            'reviewer_ids.prohibited' => 'Penugasan reviewer pada tahap revisi terkunci otomatis dan tidak dapat diubah secara manual.',
            'reviewer_ids.required' => 'Pilih reviewer terlebih dahulu.',
            'reviewer_ids.size' => "Tepat {$requiredCount} reviewer harus dipilih untuk telaah awal.",
            'reviewer_ids.*.exists' => 'Reviewer yang dipilih tidak valid.',
            'reviewer_ids.*.distinct' => 'Reviewer tidak boleh dipilih lebih dari satu kali.',
        ];
    }
}
