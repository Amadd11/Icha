# SYSTEM FLOW — ICHA (Reviewer Revised)

## Participant → Abstract
```text
Submit Abstract
 → Admin Checking
 → Reviewer Assignment
 → 3 Reviewers
 → Blinded Review
 → 3 Reviews Submitted
 → Abstract Locked
 → Calculate Result
 → ORAL / POSTER
 → Notify Author
```

## Reviewer Onboarding
The guideline requires committee-provided credentials and profile completion before review. fileciteturn2file0L32-L35

```text
Committee creates account
 → Reviewer Login
 → First Login?
    ├─ Yes → Complete Profile
    └─ No
 → Reviewer Dashboard
```

## Reviewer Abstract Review
The guideline describes: review page → choose topic → view abstract list → select abstract → give review → confirm → submit → summary. fileciteturn2file0L85-L103 fileciteturn2file0L113-L130

```text
Reviewer Dashboard
 → Choose Topic
 → Assigned Abstracts
 → Open Abstract
 → Read Blinded Abstract
 → Score Criterion 1
 → Score Criterion 2
 → Calculate Total
 → Calculate ORAL/POSTER
 → Confirmation
 → Submit Review
 → Review Summary
```

## Three-reviewer locking
```text
Abstract #001
 ├─ Reviewer A → submitted
 ├─ Reviewer B → submitted
 └─ Reviewer C → submitted
              ↓
             3/3
              ↓
       ABSTRACT LOCKED
```

The guideline explicitly requires three blinded reviewers and locking after three reviews. fileciteturn2file0L44-L47

## Scoring
```text
Criterion 1 = 1..5
Criterion 2 = 1..5
Total = C1 + C2

Total >= 5 → ORAL
Total < 5  → POSTER
```

fileciteturn2file0L103-L105

## Revision
```text
Revision Required
 → Participant Updates
 → Resubmitted
 → New Review Round
 → Historical round remains intact
```

## Full Paper
```text
Accepted Abstract
 → Upload Full Paper
 → Admin Checking
 → Assign Reviewer(s)
 → Full Paper Review
 → Approved / Revision Required / Rejected
 → Presentation
```

Number of reviewers and criteria for full paper remain configurable until confirmed.

## Admin
Admin selects current conference, checks submissions, assigns three reviewers, monitors 0/3–3/3, and manages the final result.

## Core Transaction
```text
Request → Form Request → Service → DB Transaction → Commit → Response
```
Email/background work must not determine whether the review submission succeeds.
