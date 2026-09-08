<?php

use App\Models\AbstractSubmission;
use App\Models\Category;
use App\Models\Conference;
use App\Models\Review;
use App\Models\ReviewAssignment;
use App\Models\ReviewRound;
use App\Models\User;
use App\Services\Admin\ReviewAssignmentService;
use Illuminate\Validation\ValidationException;

test('assignReviewers enforces exactly 3 reviewers', function () {
    $service = app(ReviewAssignmentService::class);

    $conference = Conference::first() ?? Conference::factory()->create();
    $user = User::factory()->create(['role' => 'participant']);
    $reviewers = User::factory()->count(4)->create(['role' => 'reviewer']);

    $abstract = AbstractSubmission::create([
        'user_id' => $user->id,
        'conference_id' => $conference->id,
        'title' => 'Test Abstract',
        'abstract_code' => 'ABS-TEST-1',
        'presentation_type' => 'oral',
        'status' => 'under_review',
    ]);

    // Passing 2 reviewer IDs must fail
    expect(function () use ($service, $abstract, $reviewers) {
        $service->assignReviewers($abstract, [$reviewers[0]->id, $reviewers[1]->id]);
    })->toThrow(ValidationException::class);

    // Passing 4 reviewer IDs must fail
    expect(function () use ($service, $abstract, $reviewers) {
        $service->assignReviewers($abstract, $reviewers->pluck('id')->toArray());
    })->toThrow(ValidationException::class);

    // Passing exactly 3 reviewer IDs succeeds
    $service->assignReviewers($abstract, [$reviewers[0]->id, $reviewers[1]->id, $reviewers[2]->id]);

    $round = ReviewRound::where('submission_type', 'abstract')
        ->where('submission_id', $abstract->id)
        ->first();

    expect($round)->not->toBeNull();
    expect($round->assignments()->count())->toBe(3);
});

test('assignReviewers soft-deletes unassigned reviewers and restores re-assigned reviewers', function () {
    $service = app(ReviewAssignmentService::class);

    $conference = Conference::first() ?? Conference::factory()->create();
    $user = User::factory()->create(['role' => 'participant']);
    $reviewers = User::factory()->count(4)->create(['role' => 'reviewer']);

    $abstract = AbstractSubmission::create([
        'user_id' => $user->id,
        'conference_id' => $conference->id,
        'title' => 'Test Abstract 2',
        'abstract_code' => 'ABS-TEST-2',
        'presentation_type' => 'oral',
        'status' => 'under_review',
    ]);

    // Initial assignment: reviewer 0, 1, 2
    $service->assignReviewers($abstract, [$reviewers[0]->id, $reviewers[1]->id, $reviewers[2]->id]);

    $round = ReviewRound::where('submission_type', 'abstract')
        ->where('submission_id', $abstract->id)
        ->first();

    expect($round->assignments()->count())->toBe(3);

    // Replace reviewer 2 with reviewer 3 (keeps exactly 3 reviewers)
    $service->assignReviewers($abstract, [$reviewers[0]->id, $reviewers[1]->id, $reviewers[3]->id]);

    expect($round->assignments()->count())->toBe(3);

    // Reviewer 2 should be soft deleted, not permanently deleted
    $softDeletedAssignment = ReviewAssignment::withTrashed()
        ->where('review_round_id', $round->id)
        ->where('reviewer_id', $reviewers[2]->id)
        ->first();

    expect($softDeletedAssignment)->not->toBeNull();
    expect($softDeletedAssignment->trashed())->toBeTrue();

    // Re-assign reviewer 2 replacing reviewer 3: it should restore without throwing duplicate entry error
    $service->assignReviewers($abstract, [$reviewers[0]->id, $reviewers[1]->id, $reviewers[2]->id]);

    $restoredAssignment = ReviewAssignment::where('review_round_id', $round->id)
        ->where('reviewer_id', $reviewers[2]->id)
        ->first();

    expect($restoredAssignment)->not->toBeNull();
    expect($restoredAssignment->trashed())->toBeFalse();
});

test('assignReviewers prevents removal of reviewers who have already submitted review', function () {
    $service = app(ReviewAssignmentService::class);

    $conference = Conference::first() ?? Conference::factory()->create();
    $user = User::factory()->create(['role' => 'participant']);
    $reviewers = User::factory()->count(4)->create(['role' => 'reviewer']);

    $abstract = AbstractSubmission::create([
        'user_id' => $user->id,
        'conference_id' => $conference->id,
        'title' => 'Test Abstract 3',
        'abstract_code' => 'ABS-TEST-3',
        'presentation_type' => 'oral',
        'status' => 'under_review',
    ]);

    $service->assignReviewers($abstract, [$reviewers[0]->id, $reviewers[1]->id, $reviewers[2]->id]);

    $round = ReviewRound::where('submission_type', 'abstract')
        ->where('submission_id', $abstract->id)
        ->first();

    $assignment0 = ReviewAssignment::where('review_round_id', $round->id)
        ->where('reviewer_id', $reviewers[0]->id)
        ->first();

    // Reviewer 0 completes review
    Review::create([
        'review_assignment_id' => $assignment0->id,
        'recommendation' => 'ORAL',
        'score_criteria_1' => 4,
        'score_criteria_2' => 5,
        'total_score' => 9,
    ]);
    $assignment0->update(['status' => 'completed']);

    // Admin tries to unassign reviewer 0 by passing reviewers 1, 2, 3
    expect(function () use ($service, $abstract, $reviewers) {
        $service->assignReviewers($abstract, [$reviewers[1]->id, $reviewers[2]->id, $reviewers[3]->id]);
    })->toThrow(ValidationException::class);
});

test('assignReviewers transitions abstract status from pending to under_review', function () {
    $service = app(ReviewAssignmentService::class);

    $conference = Conference::first() ?? Conference::factory()->create();
    $user = User::factory()->create(['role' => 'participant']);
    $reviewers = User::factory()->count(3)->create(['role' => 'reviewer']);

    $abstract = AbstractSubmission::create([
        'user_id' => $user->id,
        'conference_id' => $conference->id,
        'title' => 'Pending Abstract Test',
        'abstract_code' => 'ABS-TEST-PENDING',
        'presentation_type' => 'oral',
        'status' => 'pending',
    ]);

    expect($abstract->status)->toBe('pending');

    $service->assignReviewers($abstract, [$reviewers[0]->id, $reviewers[1]->id, $reviewers[2]->id]);

    expect($abstract->fresh()->status)->toBe('under_review');
});

test('assignReviewers prevents author from reviewing their own abstract', function () {
    $service = app(ReviewAssignmentService::class);

    $conference = Conference::first() ?? Conference::factory()->create();
    $author = User::factory()->create(['role' => 'reviewer']);
    $otherReviewers = User::factory()->count(2)->create(['role' => 'reviewer']);

    $abstract = AbstractSubmission::create([
        'user_id' => $author->id,
        'conference_id' => $conference->id,
        'title' => 'Author Conflict Test',
        'abstract_code' => 'ABS-TEST-CONFLICT',
        'presentation_type' => 'oral',
        'status' => 'pending',
    ]);

    expect(function () use ($service, $abstract, $author, $otherReviewers) {
        $service->assignReviewers($abstract, [$author->id, $otherReviewers[0]->id, $otherReviewers[1]->id]);
    })->toThrow(ValidationException::class);
});

test('assignReviewers enforces category matching between abstract and reviewers', function () {
    $service = app(ReviewAssignmentService::class);

    $conference = Conference::first() ?? Conference::factory()->create();
    $catA = Category::create([
        'conference_id' => $conference->id,
        'name' => 'Computer Science ' . uniqid(),
    ]);
    $catB = Category::create([
        'conference_id' => $conference->id,
        'name' => 'Economics ' . uniqid(),
    ]);

    $user = User::factory()->create(['role' => 'participant']);

    // Reviewers with Category A
    $reviewer1 = User::factory()->create(['role' => 'reviewer']);
    $reviewer1->categories()->attach($catA->id);

    $reviewer2 = User::factory()->create(['role' => 'reviewer']);
    $reviewer2->categories()->attach($catA->id);

    // Reviewer with only Category B
    $reviewer3 = User::factory()->create(['role' => 'reviewer']);
    $reviewer3->categories()->attach($catB->id);

    $abstract = AbstractSubmission::create([
        'user_id' => $user->id,
        'conference_id' => $conference->id,
        'category_id' => $catA->id,
        'title' => 'AI in Medicine',
        'abstract_code' => 'ABS-TEST-CAT',
        'presentation_type' => 'oral',
        'status' => 'pending',
    ]);

    // Assigning reviewer3 (who does not have Category A) should fail
    expect(function () use ($service, $abstract, $reviewer1, $reviewer2, $reviewer3) {
        $service->assignReviewers($abstract, [$reviewer1->id, $reviewer2->id, $reviewer3->id]);
    })->toThrow(ValidationException::class);

    // After attaching Category A to reviewer3, assignment succeeds
    $reviewer3->categories()->attach($catA->id);
    $service->assignReviewers($abstract, [$reviewer1->id, $reviewer2->id, $reviewer3->id]);

    $round = ReviewRound::where('submission_type', 'abstract')
        ->where('submission_id', $abstract->id)
        ->first();

    expect($round)->not->toBeNull();
    expect($round->assignments()->count())->toBe(3);
});

test('assignReviewers prevents manual assignment on revision rounds', function () {
    $service = app(ReviewAssignmentService::class);

    $conference = Conference::first() ?? Conference::factory()->create();
    $user = User::factory()->create(['role' => 'participant']);
    $reviewers = User::factory()->count(3)->create(['role' => 'reviewer']);

    $abstract = AbstractSubmission::create([
        'user_id' => $user->id,
        'conference_id' => $conference->id,
        'title' => 'Revision Round Test',
        'abstract_code' => 'ABS-TEST-REV',
        'presentation_type' => 'oral',
        'status' => 'under_review',
    ]);

    // Round 1 completed
    ReviewRound::create([
        'submission_type' => 'abstract',
        'submission_id'   => $abstract->id,
        'round_number'    => 1,
        'status'          => 'completed',
    ]);

    // Round 2 (Revision)
    ReviewRound::create([
        'submission_type' => 'abstract',
        'submission_id'   => $abstract->id,
        'round_number'    => 2,
        'status'          => 'open',
    ]);

    // Attempting manual assignment on revision round must throw ValidationException
    expect(function () use ($service, $abstract, $reviewers) {
        $service->assignReviewers($abstract, [$reviewers[0]->id, $reviewers[1]->id, $reviewers[2]->id]);
    })->toThrow(ValidationException::class);
});

test('DashboardService consolidates multi-round manuscript to a single row with previous_history and accurate stats', function () {
    $reviewer = User::factory()->create(['role' => 'reviewer']);
    $user = User::factory()->create(['role' => 'participant']);
    $conference = Conference::first() ?? Conference::factory()->create();

    $abstract = AbstractSubmission::create([
        'user_id'           => $user->id,
        'conference_id'     => $conference->id,
        'title'             => 'Consolidated Dashboard Test',
        'abstract_code'     => 'ABS-CONS-01',
        'presentation_type' => 'oral',
        'status'            => 'under_review',
    ]);

    // Round 1
    $round1 = ReviewRound::create([
        'submission_type' => 'abstract',
        'submission_id'   => $abstract->id,
        'round_number'    => 1,
        'status'          => 'completed',
    ]);
    $assign1 = ReviewAssignment::create([
        'review_round_id' => $round1->id,
        'reviewer_id'     => $reviewer->id,
        'status'          => 'completed',
    ]);
    Review::create([
        'review_assignment_id' => $assign1->id,
        'score_criteria_1'     => 3,
        'score_criteria_2'     => 3,
        'total_score'          => 6,
        'recommendation'       => 'REVISION',
        'summary'              => 'Notes from round 1',
    ]);

    // Round 2 (Active Revision Round)
    $round2 = ReviewRound::create([
        'submission_type' => 'abstract',
        'submission_id'   => $abstract->id,
        'round_number'    => 2,
        'status'          => 'open',
    ]);
    $assign2 = ReviewAssignment::create([
        'review_round_id' => $round2->id,
        'reviewer_id'     => $reviewer->id,
        'status'          => 'assigned',
    ]);

    // Log in as reviewer
    $this->actingAs($reviewer);

    $dashboardService = app(\App\Services\Reviewer\DashboardService::class);
    $assignments = $dashboardService->getAssignments();
    $stats = $dashboardService->getStats($assignments);

    // Should only have 1 consolidated row for this manuscript
    expect($assignments->count())->toBe(1);
    $activeItem = $assignments->first();
    expect($activeItem->id)->toBe($assign2->id);
    expect($activeItem->round->round_number)->toBe(2);

    // Previous history must contain round 1
    expect($activeItem->previous_history)->toHaveCount(1);
    expect($activeItem->previous_history[0]['round_number'])->toBe(1);
    expect($activeItem->previous_history[0]['review']['recommendation'])->toBe('REVISION');
    expect($activeItem->previous_history[0]['review']['summary'])->toBe('Notes from round 1');

    // Stats should reflect 1 distinct submission, 1 pending, 0 completed
    expect($stats['total_assigned'])->toBe(1);
    expect($stats['pending_reviews'])->toBe(1);
    expect($stats['completed_reviews'])->toBe(0);
});

test('AbstractReviewService allows revision_required on round 2 or higher and Dashboard consolidates all rounds', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'participant']);
    $reviewer = User::factory()->create(['role' => 'reviewer']);
    $conference = Conference::first() ?? Conference::factory()->create();

    $abstract = AbstractSubmission::create([
        'user_id'           => $user->id,
        'conference_id'     => $conference->id,
        'title'             => 'Unlimited Revision Test',
        'abstract_code'     => 'ABS-UNLIM-REV',
        'presentation_type' => 'oral',
        'status'            => 'under_review',
    ]);

    // Round 2 (Locked, ready for admin decision)
    $round2 = ReviewRound::create([
        'submission_type' => 'abstract',
        'submission_id'   => $abstract->id,
        'round_number'    => 2,
        'status'          => 'locked',
    ]);
    $assign2 = ReviewAssignment::create([
        'review_round_id' => $round2->id,
        'reviewer_id'     => $reviewer->id,
        'status'          => 'completed',
    ]);
    Review::create([
        'review_assignment_id' => $assign2->id,
        'score_criteria_1'     => 4,
        'score_criteria_2'     => 4,
        'total_score'          => 8,
        'recommendation'       => 'REVISION',
    ]);

    $reviewService = app(\App\Services\Admin\AbstractReviewService::class);

    // Setting revision_required on round 2 is allowed without limitation
    $result = $reviewService->reviewAbstract($abstract, $admin, [
        'status' => 'revision_required',
        'review_notes' => 'Please revise section 3 again.',
    ]);

    expect($result)->toBeTrue();
    expect($abstract->fresh()->status)->toBe('revision_required');
});

