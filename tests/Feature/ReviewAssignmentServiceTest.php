<?php

use App\Models\AbstractSubmission;
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

