<?php

namespace Database\Factories;

use App\Models\AbstractSubmission;
use App\Models\Category;
use App\Models\Conference;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AbstractSubmission>
 */
class AbstractSubmissionFactory extends Factory
{
    public function definition(): array
    {
        $conference = Conference::where('is_active', true)->first();
        $category = Category::inRandomOrder()->first();

        return [
            'abstract_code'     => 'ABS-' . str_pad(fake()->unique()->numberBetween(10, 999), 3, '0', STR_PAD_LEFT),
            'user_id'           => User::factory()->participant(),
            'conference_id'     => $conference?->id ?? 1,
            'category_id'       => $category?->id ?? 1,
            'title'             => fake()->sentence(8),
            'abstract_text'     => fake()->paragraphs(3, true),
            'keywords'          => implode(', ', fake()->words(4)),
            'presentation_type' => fake()->randomElement(['oral', 'poster']),
            'file_path'         => 'abstracts/sample_abstract.pdf',
            'status'            => 'pending',
            'review_notes'      => null,
            'reviewed_by'       => null,
            'reviewed_at'       => null,
        ];
    }

    public function accepted(string $presentationType = 'oral'): static
    {
        return $this->state(fn () => [
            'status'            => 'accepted',
            'presentation_type' => $presentationType,
            'reviewed_at'       => now(),
        ]);
    }

    public function underReview(): static
    {
        return $this->state(fn () => ['status' => 'under_review']);
    }

    public function revisionRequired(): static
    {
        return $this->state(fn () => [
            'status'       => 'revision_required',
            'review_notes' => 'Please elaborate more on the methodology section and include quantitative outcome data.',
            'reviewed_at'  => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status'       => 'rejected',
            'review_notes' => 'The topic does not align with the scope of this conference edition.',
            'reviewed_at'  => now(),
        ]);
    }
}
