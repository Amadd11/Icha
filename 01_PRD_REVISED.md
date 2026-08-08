# PRD — ICHA 2026

## Product
Conference website + management system:
- Public website
- Admin CMS/dashboard
- Participant registration
- Abstract submission
- Full paper
- Presentation
- Payment verification
- Certificate
- Publication
- Sponsors

There is NO external reviewer role.

## Deployment Constraint
Primary deployment target: shared hosting.

Core features MUST NOT depend on queues/workers. Core transactions run synchronously. Jobs are optional only for non-critical background tasks.

## Roles
- super_admin: full access
- admin: operational management
- participant: registration, submission, uploads, tracking

## Public Pages
Home, About, Conference, Scope/Topics, Speakers, Committee, Timeline, Registration, Publication, Sponsors, FAQ, Contact.

## Participant Flow
Register → Profile → Conference Registration → Payment → Abstract Submission → Admin Checking → Accepted/Rejected/Revision Required → Full Paper → Presentation → Conference → Certificate → Publication.

## Status
Abstract: draft, submitted, under_review, revision_required, resubmitted, accepted, rejected.
Payment: pending, waiting_verification, paid, rejected, expired.
Full paper: submitted, revision_required, approved.

`under_review` means internal admin checking, NOT peer review.

## Out of Scope
- Reviewer accounts
- Reviewer assignment
- Peer review
- Reviewer scoring
- Reviewer dashboard
- Queue-dependent core transactions
