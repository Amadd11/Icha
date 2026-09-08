<?php

namespace Tests\Feature;

use App\Models\AbstractSubmission;
use App\Models\Category;
use App\Models\Conference;
use App\Models\Review;
use App\Models\ReviewAssignment;
use App\Models\ReviewRound;
use App\Models\User;
use Tests\TestCase;

class ReviewerWorkflowTest extends TestCase
{
    protected Conference $conference;
    protected Category $category;
    protected User $reviewer1;
    protected User $reviewer2;
    protected User $participant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->conference = Conference::where('is_active', true)->first() ?? Conference::first();
        $this->category = Category::where('conference_id', $this->conference->id)->first() ?? Category::first();

        $this->reviewer1 = User::where('email', 'reviewer@gmail.com')->first();
        $this->reviewer2 = User::where('email', 'reviewer@icha.com')->first() ?? User::where('role', 'reviewer')->latest()->first();
        $this->participant = User::where('role', 'participant')->first();
    }

    public function test_guest_cannot_access_reviewer_dashboard(): void
    {
        $response = $this->get('/reviewer/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_participant_is_forbidden_from_reviewer_dashboard(): void
    {
        $response = $this->actingAs($this->participant)->get('/reviewer/dashboard');
        $response->assertStatus(403);
    }

    public function test_reviewer_can_access_dashboard_and_load_stats(): void
    {
        $response = $this->actingAs($this->reviewer1)->get('/reviewer/dashboard');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Reviewer/Dashboard')
            ->has('stats')
            ->has('assignments')
        );
    }

    public function test_reviewer_can_submit_review_with_scores_and_recommendation(): void
    {
        // 1. Ensure an abstract and review round exist
        $abstract = AbstractSubmission::first();
        if (!$abstract) {
            $abstract = AbstractSubmission::create([
                'user_id'           => $this->participant->id,
                'conference_id'     => $this->conference->id,
                'category_id'       => $this->category->id,
                'title'             => 'Clinical Governance in Digital Healthcare Systems',
                'keywords'          => 'Clinical, Governance, Healthcare, AI',
                'abstract_text'     => 'This abstract evaluates modern clinical governance frameworks implemented in public hospitals.',
                'presentation_type' => 'oral',
                'status'            => 'under_review',
            ]);
        }

        $round = ReviewRound::firstOrCreate(
            [
                'submission_type' => 'abstract',
                'submission_id'   => $abstract->id,
            ],
            [
                'status' => 'pending',
            ]
        );

        $assignment = ReviewAssignment::firstOrCreate(
            [
                'review_round_id' => $round->id,
                'reviewer_id'     => $this->reviewer1->id,
            ],
            [
                'status' => 'assigned',
            ]
        );

        $round->update(['status' => 'pending']);
        $assignment->update(['status' => 'assigned']);

        // 2. Submit Review
        $payload = [
            'score_criteria_1' => 4,
            'score_criteria_2' => 5,
            'recommendation'   => 'ORAL',
            'summary'          => 'Comprehensive methodology and clear administrative implications for healthcare.',
        ];

        $response = $this->actingAs($this->reviewer1)
            ->post("/reviewer/assignments/{$assignment->id}/review", $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        // 3. Assert review saved
        $this->assertDatabaseHas('reviews', [
            'review_assignment_id' => $assignment->id,
            'score_criteria_1'     => 4,
            'score_criteria_2'     => 5,
            'total_score'          => 9,
            'recommendation'       => 'ORAL',
        ]);

        // 4. Assert assignment status changed to completed
        $this->assertDatabaseHas('review_assignments', [
            'id'     => $assignment->id,
            'status' => 'completed',
        ]);
    }

    public function test_reviewer_cannot_review_assignment_belonging_to_another_reviewer(): void
    {
        // Create an assignment for Reviewer 2
        $abstract = AbstractSubmission::first();
        $round = ReviewRound::firstOrCreate([
            'submission_type' => 'abstract',
            'submission_id'   => $abstract->id,
        ]);

        $assignmentOther = ReviewAssignment::firstOrCreate(
            [
                'review_round_id' => $round->id,
                'reviewer_id'     => $this->reviewer2->id,
            ],
            [
                'status' => 'assigned',
            ]
        );

        // Reviewer 1 tries to submit review for Reviewer 2's assignment
        $payload = [
            'score_criteria_1' => 3,
            'score_criteria_2' => 3,
            'recommendation'   => 'POSTER',
            'summary'          => 'Unauthorized attempt test',
        ];

        $response = $this->actingAs($this->reviewer1)
            ->post("/reviewer/assignments/{$assignmentOther->id}/review", $payload);

        $response->assertStatus(403);
    }

    public function test_review_submission_validates_score_boundaries(): void
    {
        $assignment = ReviewAssignment::where('reviewer_id', $this->reviewer1->id)->first();

        // Out of bounds score (score must be 1 to 5)
        $payload = [
            'score_criteria_1' => 10, // invalid > 5
            'score_criteria_2' => 0,  // invalid < 1
            'recommendation'   => 'INVALID_REC',
            'summary'          => 'Invalid test',
        ];

        $response = $this->actingAs($this->reviewer1)
            ->post("/reviewer/assignments/{$assignment->id}/review", $payload);

        $response->assertSessionHasErrors(['score_criteria_1', 'score_criteria_2', 'recommendation']);
    }

    public function test_reviewer_can_update_their_submitted_review_before_admin_decision(): void
    {
        $abstract = AbstractSubmission::first();
        $round = ReviewRound::firstOrCreate(
            [
                'submission_type' => 'abstract',
                'submission_id'   => $abstract->id,
            ],
            [
                'status' => 'open',
            ]
        );

        $assignment = ReviewAssignment::firstOrCreate(
            [
                'review_round_id' => $round->id,
                'reviewer_id'     => $this->reviewer1->id,
            ],
            [
                'status' => 'completed',
            ]
        );

        // Submit updated review
        $updatePayload = [
            'score_criteria_1' => 5,
            'score_criteria_2' => 5,
            'recommendation'   => 'ORAL',
            'summary'          => 'Updated review with perfect score.',
        ];

        $response = $this->actingAs($this->reviewer1)
            ->post("/reviewer/assignments/{$assignment->id}/review", $updatePayload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('reviews', [
            'review_assignment_id' => $assignment->id,
            'score_criteria_1'     => 5,
            'score_criteria_2'     => 5,
            'total_score'          => 10,
            'recommendation'       => 'ORAL',
            'summary'              => 'Updated review with perfect score.',
        ]);
    }

    public function test_reviewer_cannot_update_review_after_admin_final_decision(): void
    {
        $abstract = AbstractSubmission::first();
        $round = ReviewRound::firstOrCreate(
            [
                'submission_type' => 'abstract',
                'submission_id'   => $abstract->id,
            ],
            [
                'status' => 'completed',
            ]
        );
        $round->update(['status' => 'completed']);
        $abstract->update(['status' => 'accepted']);

        $assignment = ReviewAssignment::firstOrCreate(
            [
                'review_round_id' => $round->id,
                'reviewer_id'     => $this->reviewer1->id,
            ],
            [
                'status' => 'completed',
            ]
        );

        $updatePayload = [
            'score_criteria_1' => 2,
            'score_criteria_2' => 2,
            'recommendation'   => 'REJECT',
            'summary'          => 'Attempt to change score after decision finalized.',
        ];

        $response = $this->actingAs($this->reviewer1)
            ->post("/reviewer/assignments/{$assignment->id}/review", $updatePayload);

        $response->assertSessionHasErrors('error');
    }
}
