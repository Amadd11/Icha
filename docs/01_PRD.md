# PRD — ICHA Conference Management System (Reviewer Revised)

## Product
ICHA is a multi-year conference website and management system using one Laravel application for ICHA 2026, 2027 and future editions.

Modules:
- Public conference website and CMS
- Participant registration and payment
- Abstract submission
- Abstract peer review
- Full paper submission/review
- Presentation
- Certificate
- Publication
- Speakers, topics, timeline, sponsors and FAQ

## Deployment
Primary target is shared hosting. Core transactions MUST work synchronously without Redis, Supervisor, Docker, persistent workers or WebSockets. Jobs are optional only for non-critical tasks.

## Roles
### super_admin
Full system access, including users, conferences, reviewer management and settings.

### admin
Operational management: conference content, participants, registrations, payments, submissions, reviewer assignment, papers, presentations, certificates and publications.

### reviewer
Academic reviewer. Reviewer receives credentials from the committee. First login requires profile completion: title/name, date of birth, university, affiliation, country, phone and photo. Reviewer can view assigned topics/abstracts, review blinded abstracts, score criteria and submit reviews.

### participant
Profile, registration, payment, abstract, authors, full paper, presentation, certificate and publication tracking.

The reviewer profile requirements follow the supplied guideline. fileciteturn2file0L32-L35

## Abstract Review Requirements
- One abstract is reviewed by THREE reviewers.
- Review is blinded.
- Abstract is locked after three submitted reviews.
- There are two review criteria.
- Each criterion has five scoring levels: very weak to very strong.
- Total score >= 5 => ORAL.
- Total score < 5 => POSTER.
- Author receives an abstract notification.

These rules come from the supplied reviewer guideline. fileciteturn2file0L44-L52 fileciteturn2file0L103-L105

## Abstract Status
Use:
- draft
- submitted
- admin_checking
- reviewer_assignment
- under_review
- revision_required
- resubmitted
- accepted
- rejected
- locked

`admin_checking` means committee/admin checking. `under_review` means academic reviewer review.

## Review Status
- assigned
- in_progress
- submitted
- cancelled

Submitted reviews should be immutable unless an explicit admin correction workflow is introduced.

## Review Rounds
A submission can have multiple review rounds. Revision must create a new round and preserve historical reviews.

```text
Submission
 ├─ Round 1
 │   ├─ Reviewer A
 │   ├─ Reviewer B
 │   └─ Reviewer C
 └─ Round 2
     ├─ Reviewer A
     ├─ Reviewer B
     └─ Reviewer C
```

## Full Paper
Reuse the review-round/assignment/review architecture for full papers, but DO NOT invent full-paper criteria until confirmed by the committee.

## Out of Scope
- reviewer bidding
- reviewer chat
- reviewer-to-reviewer communication
- automated reviewer matching
- invented full-paper scoring criteria
- queue-dependent core workflows

## Acceptance Criteria
Reviewer module is complete when:
- authentication works
- first-login profile completion is enforced
- reviewer sees only assigned work
- author identity is hidden in blinded review
- duplicate assignment is prevented
- abstract has three distinct reviewer assignments
- two criteria with 1–5 scores are validated
- total and ORAL/POSTER are calculated server-side
- confirmation occurs before submission
- review is locked after three submissions
- historical reviews are preserved
- notification can be triggered after the core transaction
- conference isolation and authorization are tested
