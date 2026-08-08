# Implementation Plan — Shared Hosting Friendly

## Phase 0
- Laravel 13 confirmed
- PHP compatibility checked
- Auth inspected
- Routes inspected
- Migrations inspected
- Tailwind/Vite inspected
- Existing layouts/components inspected

## Phase 1 — Conference CMS
- conferences
- categories
- speakers
- committees
- timelines
- sponsors
- CRUD + admin authorization

## Phase 2 — Public Website
- navbar
- hero
- countdown
- about
- scope
- speakers
- committee
- timeline
- registration
- publication
- sponsors
- contact
- responsive

## Phase 3 — Auth & Participant
- authentication
- roles
- participant profile
- participant dashboard

## Phase 4 — Registration & Payment
- registration_types
- registrations
- payments
- invoice
- proof upload
- admin verification

All synchronous.

## Phase 5 — Abstract
- submissions
- authors
- abstract upload
- status flow
- admin checking
- revision
- participant tracking

All synchronous.

## Phase 6 — Full Paper & Presentation
- paper_versions
- full paper upload
- presentation
- schedule

All synchronous.

## Phase 7 — Certificate & Publication
- certificates
- certificate files
- publications

Certificate generation may initially be synchronous.

## Phase 8 — Optional Jobs
Only if hosting supports it:
- bulk email
- bulk certificate generation
- large reports
- image processing

These must never be required for core functionality.

## Deployment QA
- production env
- storage writable
- uploads tested
- SMTP tested
- authorization tested
- no Redis dependency
- no queue worker dependency
