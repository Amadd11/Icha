# VIBE CODING RULES — ICHA (Reviewer Revised)

## General
Build incrementally. Inspect existing files, migrations, models, routes and components before changing code.

## Current Role Model
The authoritative roles are:
- super_admin
- admin
- reviewer
- participant

Do not revert to the old assumption that there is no reviewer role.

## Reviewer Security
Reviewer can access only:
- own profile
- assigned review rounds
- assigned submissions
- own reviews

Reviewer cannot browse all submissions, access admin pages, see author identity in blinded review, review unassigned submissions or edit another reviewer's review.

## First Login
Incomplete reviewer profile must redirect to profile completion and block review actions until completed.

## Abstract Review
Use:
```text
review_rounds
review_assignments
reviews
```

Do not use `submissions.reviewer_id`.

One abstract review round requires three distinct reviewers.

## Review Submission
Validate assignment ownership, profile completion, two criteria, scores 1–5, then calculate total and ORAL/POSTER on the server. Save review, update assignment and lock the round after 3 submissions inside one transaction.

## Blinding
Do not send author identity to reviewer Vue pages. Backend must enforce this.

## Status
Use `admin_checking` for admin checking and `under_review` for academic review.

## Full Paper
Reuse the generic review architecture, but do not invent criteria until confirmed.

## Laravel
Prefer Form Requests, Policies, Services, Eloquent relationships, DB transactions and route model binding. Do not create repositories for every model.

## Vue/Inertia
Separate:
```text
Pages/Participant
Pages/Reviewer
Pages/Admin
```
and:
```text
ParticipantLayout
ReviewerLayout
AdminLayout
```

## Shared Hosting
Core review submission must work synchronously. No Redis, worker or WebSocket dependency.

## Tests
Every reviewer feature needs tests for happy path, validation, authorization, wrong conference, wrong assignment, blinded data, duplicate review, score calculation, status transitions and 3-review locking.
