# IMPLEMENTATION PLAN — ICHA

## Phase 0 — Project Setup
- Laravel 13
- database
- authentication
- Tailwind/Vite
- base layout
- role middleware/policies

## Phase 1 — Conference Foundation
- conferences migration/model
- conference CRUD
- slug routing
- active conference
- conference portal
- dynamic landing page
- logo/hero upload

## Phase 2 — CMS
- categories/topics
- speakers
- committee
- timeline
- registration types
- sponsors
- FAQ
- publication information

## Phase 3 — Participant
- participant profile
- conference selection
- registration
- registration status
- payment
- proof upload
- payment verification

## Phase 4 — Submission
- abstract
- authors
- status transitions
- revision flow
- submission history

## Phase 5 — Paper & Presentation
- full paper
- paper versions
- presentation file
- schedule
- presentation status

## Phase 6 — Certificate & Publication
- certificate management
- certificate downloads
- publication records
- DOI/URL

## Phase 7 — Hardening
- authorization review
- conference isolation tests
- upload security
- validation
- responsive UI
- shared hosting deployment test
- production configuration

## Rule
Do not proceed to the next phase while the current phase has broken migrations, broken relationships, or unresolved authorization/data-isolation problems.
