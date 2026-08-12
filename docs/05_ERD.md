# ERD — ICHA (Reviewer Revised)

## Core
```text
Conference
 ├─ Topics
 ├─ Registrations
 └─ Submissions
      ├─ Authors
      ├─ Review Rounds
      │    └─ Review Assignments
      │         └─ Reviews
      ├─ Paper Versions
      ├─ Presentations
      ├─ Certificates
      └─ Publications
```

## users
```text
id
name
email
password
role
must_complete_reviewer_profile
timestamps
```

Roles: super_admin, admin, reviewer, participant.

## reviewer_profiles
```text
id
user_id
title
full_name
date_of_birth
university
affiliation
country
phone
photo
completed_at
timestamps
```

Fields follow the supplied reviewer guideline. fileciteturn2file0L32-L35

## submissions
```text
id
conference_id
participant_id
category_id
registration_id
title
abstract
keywords
status
submitted_at
review_result
presentation_type
review_locked_at
timestamps
```

## review_rounds
```text
id
submission_id
type
round_number
required_reviewers
status
opened_at
locked_at
timestamps
```

`type`: abstract, full_paper.

For abstract review, `required_reviewers = 3`.

## review_assignments
```text
id
review_round_id
reviewer_id
status
assigned_at
started_at
submitted_at
timestamps
```

Unique constraint:
```text
(review_round_id, reviewer_id)
```

## reviews
```text
id
review_assignment_id
criteria_1_score
criteria_2_score
total_score
recommendation
comments
submitted_at
timestamps
```

For abstract:
- each score 1–5
- total = criterion 1 + criterion 2
- total >= 5 → oral
- total < 5 → poster

fileciteturn2file0L103-L105

## History
Never overwrite old reviews. Revision creates a new review_round.

## Relationships
```text
User
 ├─ reviewerProfile
 └─ reviewerAssignments

Submission
 ├─ authors
 └─ reviewRounds

ReviewRound
 ├─ submission
 └─ reviewAssignments

ReviewAssignment
 ├─ reviewer
 └─ review
```

## Critical Constraints
- one abstract review round requires three distinct reviewers
- duplicate assignment prevented
- reviewer sees only assigned work
- blinded author data enforced by backend
- round locks after 3 submitted reviews
- historical reviews preserved
- all data remains conference-isolated
