<?php

use App\Models\AbstractSubmission;
use App\Models\Category;
use App\Models\Conference;
use App\Models\FullPaper;
use App\Models\ReviewAssignment;
use App\Models\ReviewRound;
use App\Models\User;
use App\Services\Admin\PaperReviewService;
use App\Services\Admin\ReviewAssignmentService;
use App\Services\Reviewer\ReviewSubmissionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\ValidationException;

uses(DatabaseTransactions::class);

test('assignPaperReviewers enforces exactly 2 reviewers', function () {
    $service = app(ReviewAssignmentService::class);

    $conference = Conference::first() ?? Conference::factory()->create();
    $user = User::factory()->create(['role' => 'participant']);
    $reviewers = User::factory()->count(3)->create(['role' => 'reviewer']);

    $paper = FullPaper::create([
        'user_id' => $user->id,
        'conference_id' => $conference->id,
        'title' => 'Test Paper',
        'paper_code' => 'FP-TEST-1',
        'status' => 'pending',
    ]);

    // Passing 1 reviewer ID must fail
    expect(function () use ($service, $paper, $reviewers) {
        $service->assignPaperReviewers($paper, [$reviewers[0]->id]);
    })->toThrow(ValidationException::class);

    // Passing 3 reviewer IDs must fail
    expect(function () use ($service, $paper, $reviewers) {
        $service->assignPaperReviewers($paper, $reviewers->pluck('id')->toArray());
    })->toThrow(ValidationException::class);

    // Passing exactly 2 reviewer IDs succeeds
    $service->assignPaperReviewers($paper, [$reviewers[0]->id, $reviewers[1]->id]);

    $round = ReviewRound::where('submission_type', 'full_paper')
        ->where('submission_id', $paper->id)
        ->first();

    expect($round)->not->toBeNull();
    expect($round->assignments()->count())->toBe(2);
    expect($paper->fresh()->status)->toBe('under_review');
});

test('assignPaperReviewers prevents conflict of interest with paper author', function () {
    $service = app(ReviewAssignmentService::class);

    $conference = Conference::first() ?? Conference::factory()->create();
    $author = User::factory()->create(['role' => 'reviewer']); // Author is also a reviewer
    $otherReviewer = User::factory()->create(['role' => 'reviewer']);

    $paper = FullPaper::create([
        'user_id' => $author->id,
        'conference_id' => $conference->id,
        'title' => 'Conflict Paper',
        'paper_code' => 'FP-COI-1',
        'status' => 'pending',
    ]);

    expect(function () use ($service, $paper, $author, $otherReviewer) {
        $service->assignPaperReviewers($paper, [$author->id, $otherReviewer->id]);
    })->toThrow(ValidationException::class);
});

test('assignPaperReviewers validates track category matching', function () {
    $service = app(ReviewAssignmentService::class);

    $conference = Conference::first() ?? Conference::factory()->create();
    $categoryA = Category::create(['name' => 'Track A', 'code' => 'TA']);
    $categoryB = Category::create(['name' => 'Track B', 'code' => 'TB']);

    $user = User::factory()->create(['role' => 'participant']);
    $rev1 = User::factory()->create(['role' => 'reviewer']);
    $rev2 = User::factory()->create(['role' => 'reviewer']);

    $rev1->categories()->attach($categoryA->id);
    $rev2->categories()->attach($categoryB->id); // Different track

    $abstract = AbstractSubmission::create([
        'user_id' => $user->id,
        'conference_id' => $conference->id,
        'category_id' => $categoryA->id,
        'title' => 'Abstract for Paper',
        'abstract_code' => 'ABS-CAT-1',
        'presentation_type' => 'oral',
        'status' => 'accepted',
    ]);

    $paper = FullPaper::create([
        'user_id' => $user->id,
        'conference_id' => $conference->id,
        'abstract_id' => $abstract->id,
        'title' => 'Track A Paper',
        'paper_code' => 'FP-CAT-1',
        'status' => 'pending',
    ]);

    // Assigned rev2 which doesn't match Track A must fail
    expect(function () use ($service, $paper, $rev1, $rev2) {
        $service->assignPaperReviewers($paper, [$rev1->id, $rev2->id]);
    })->toThrow(ValidationException::class);
});

test('round locks when both 2/2 reviewers complete reviews and admin can then record decision', function () {
    $assignService = app(ReviewAssignmentService::class);
    $submitService = app(ReviewSubmissionService::class);
    $adminReviewService = app(PaperReviewService::class);

    $conference = Conference::first() ?? Conference::factory()->create();
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'participant']);
    $reviewers = User::factory()->count(2)->create(['role' => 'reviewer']);

    $paper = FullPaper::create([
        'user_id' => $user->id,
        'conference_id' => $conference->id,
        'title' => 'Test Paper 2 Reviews',
        'paper_code' => 'FP-LOCK-1',
        'status' => 'pending',
    ]);

    $assignService->assignPaperReviewers($paper, [$reviewers[0]->id, $reviewers[1]->id]);

    $round = ReviewRound::where('submission_type', 'full_paper')
        ->where('submission_id', $paper->id)
        ->first();

    $assignments = $round->assignments()->get();

    // Reviewer 1 submits review
    $submitService->submitReview($assignments[0], [
        'score_criteria_1' => 4,
        'score_criteria_2' => 4,
        'recommendation' => 'ORAL',
        'summary' => 'Great paper from reviewer 1',
    ]);

    // Round should still be open (only 1/2 complete)
    expect($round->fresh()->status)->toBe('open');

    // Admin decision should be rejected before 2/2 is complete
    expect(function () use ($adminReviewService, $paper, $admin) {
        $adminReviewService->reviewPaper($paper->fresh(), $admin, ['status' => 'accepted']);
    })->toThrow(ValidationException::class);

    // Reviewer 2 submits review
    $submitService->submitReview($assignments[1], [
        'score_criteria_1' => 5,
        'score_criteria_2' => 5,
        'recommendation' => 'ORAL',
        'summary' => 'Excellent paper from reviewer 2',
    ]);

    // Round should now be locked (2/2 complete)
    expect($round->fresh()->status)->toBe('locked');
    expect($round->finalRecommendation())->toBe('ORAL');

    // Admin can now record decision
    $result = $adminReviewService->reviewPaper($paper->fresh(), $admin, [
        'status' => 'accepted',
        'review_notes' => 'Both reviewers recommended accept.',
    ]);

    expect($result)->toBeTrue();
    expect($paper->fresh()->status)->toBe('accepted');
    expect($round->fresh()->status)->toBe('completed');
});
