# PRD — ICHA Conference Management System

## Product
A multi-year conference website and management system. One Laravel application manages multiple conference editions such as ICHA 2026, ICHA 2027, and future editions.

Modules:
- Public conference website
- Dynamic conference landing pages
- Conference CMS
- Participant registration
- Payment verification
- Abstract submission
- Author management
- Full paper submission
- Presentation management
- Certificate management
- Publication management
- Sponsor management

There is NO external reviewer role.

## Multi-Conference Rule
`Conference` is the root entity. Conference-specific records must belong to a `conference_id`.

Examples:
- speakers
- topics/categories
- committee
- timelines
- registration types
- sponsors
- registrations
- payments
- submissions
- presentations
- certificates
- publications

Data from different conference editions must never be mixed.

## URLs
- `/` = conference portal
- `/conferences` = conference archive/list
- `/conferences/{conference:slug}` = dynamic conference landing page
- `/conferences/{conference:slug}/speakers`
- `/conferences/{conference:slug}/registration`
- `/conferences/{conference:slug}/submissions`

Never hardcode a year in reusable views.

## Roles
### super_admin
Full access to users, conferences, CMS, participants and system settings.

### admin
Operational conference management: content, participants, registrations, payments, submissions, papers, presentations, certificates and publications.

### participant
Profile, conference registration, payment proof, abstract, authors, full paper, presentation, certificate and publication tracking.

## Participant Flow
Register → Login → Select Conference → Profile → Conference Registration → Payment → Payment Verification → Abstract Submission → Admin Checking → Accepted/Rejected/Revision Required → Full Paper → Presentation → Conference → Certificate → Publication.

## Status
Abstract:
- draft
- submitted
- under_review
- revision_required
- resubmitted
- accepted
- rejected

`under_review` means internal admin checking, NOT peer review.

Payment:
- pending
- waiting_verification
- paid
- rejected
- expired

Full paper:
- submitted
- revision_required
- approved

## Deployment Constraint
Primary target is shared hosting.

Core features MUST work without:
- Redis
- Supervisor
- Docker
- persistent queue workers
- Reverb/WebSockets

Core transactions are synchronous.

Jobs are optional only for non-critical heavy tasks such as bulk email, bulk certificate generation, large reports and image processing.

## Out of Scope
- reviewer accounts
- reviewer assignment
- peer review
- reviewer scoring
- reviewer dashboard
- queue-dependent core transactions

## MVP
1. Authentication and roles
2. Conference management
3. Dynamic conference landing page
4. Conference CMS
5. Participant registration
6. Payment verification
7. Abstract + authors
8. Revision flow
9. Full paper
10. Presentation
11. Certificate
12. Publication
13. Sponsor management
