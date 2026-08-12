# IMPLEMENTATION PLAN — ICHA (Reviewer Revised)

## Phase 1 — Foundation
- roles
- authentication
- reviewer profile
- reviewer middleware/policies
- reviewer layout

## Phase 2 — Submission
- submissions
- authors
- admin checking
- submission statuses

## Phase 3 — Review Domain
Create:
- review_rounds
- review_assignments
- reviews

Create enums:
- ReviewType
- ReviewStatus
- ReviewRoundStatus
- RecommendationType

## Phase 4 — Reviewer Onboarding
- login
- first-login profile completion
- profile validation/photo
- block review until complete

## Phase 5 — Assignment
Admin:
- reviewer list
- assign three reviewers
- prevent duplicate assignment
- monitor 0/3 to 3/3

Reviewer:
- topics
- assigned abstracts
- pending/completed

## Phase 6 — Abstract Review
- blinded abstract
- two criteria
- 1–5 scores
- total
- ORAL/POSTER
- confirmation
- synchronous submit
- summary

## Phase 7 — Locking
At 3/3:
- lock round
- prevent edits
- persist result
- notify participant

Use a transaction and concurrency-safe locking.

## Phase 8 — Revision
Revision creates a new review round. Never overwrite historical reviews.

## Phase 9 — Full Paper
Reuse:
```text
review_round.type = full_paper
```
Do not implement detailed paper scoring until criteria are confirmed.

## Phase 10 — Testing
Test reviewer profile, assignment ownership, blinding, scoring, duplicate submission, locking, history and conference isolation.

## Phase 11 — Shared Hosting
Verify no mandatory worker/Redis/WebSocket is required and review submission works synchronously.
