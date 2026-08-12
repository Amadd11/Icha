# DATABASE RULES — ICHA (Reviewer Revised)

## Naming
Plural snake_case tables, snake_case columns, singular StudlyCase models, `{model}_id` foreign keys.

## Review Tables
Required:
```text
reviewer_profiles
review_rounds
review_assignments
reviews
```

`reviewer_topics` is optional if topic expertise/selection needs persistence.

## Constraints
```text
unique conferences.slug
unique registration_number
unique certificate_number
unique (review_round_id, reviewer_id)
```

## Scores
Use integer 1..5 for abstract criteria. Do not use floating point.

## Recommendation
Use:
```text
oral
poster
```
Calculated by the backend.

## Locking
`review_rounds.locked_at` is nullable. When submitted reviews reach `required_reviewers`:
- set locked_at
- set round completed/locked
- prevent further reviewer edits
- preserve review records

## Concurrency
Review submission and 3/3 locking must run inside a DB transaction with concurrency protection. Never trust a frontend review count.

## Blinding
Do not store or expose copied author identity in reviewer records. Reviewer-facing queries/resources must select only safe submission fields.

## Foreign Keys
```text
review_rounds.submission_id → submissions.id
review_assignments.review_round_id → review_rounds.id
review_assignments.reviewer_id → users.id
reviews.review_assignment_id → review_assignments.id
reviewer_profiles.user_id → users.id
```

## Indexes
Index:
- conference_id
- submission_id
- reviewer_id
- review_round_id
- status
- assigned_at
- submitted_at

## Files
Store file paths, not binaries. Private paper files require authorized download.
