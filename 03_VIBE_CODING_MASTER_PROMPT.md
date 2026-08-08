# MASTER VIBE CODING PROMPT — ICHA 2026

You are a senior Laravel 13 full-stack engineer working on an existing Laravel 13 application named ICHA 2026.

Read these project documents before implementation:
- 01_PRD_REVISED.md
- 02_ERD.md
- 02_TECHNICAL_SPEC_REVISED.md
- 04_IMPLEMENTATION_PLAN_REVISED.md
- 06_STATUS_FLOW.md

## Product
Build a professional international conference website and management system with:
- public website
- admin CMS/dashboard
- participant authentication
- conference registration
- payment verification
- abstract submission
- author management
- full paper upload
- presentation management
- certificate management
- publication management
- sponsor management

There is NO reviewer module.

## Critical Deployment Constraint
Primary production target is SHARED HOSTING.

Therefore:
- Core operations MUST work synchronously.
- Core operations MUST NOT require queue workers.
- Do NOT require Redis, Supervisor, Docker, persistent queue workers, or Reverb.
- Use MySQL and Laravel Storage.
- Keep deployment simple.

## Queue/Jobs Policy
Jobs are OPTIONAL.

Never dispatch a Job for a core transaction such as registration, abstract submission, payment verification, status updates, paper upload, or presentation upload.

Jobs may be used only for optional heavy tasks:
- bulk email
- bulk certificate generation
- large reports
- image processing
- large imports/exports

If no queue worker is running, the core application must still work.

## Important Transaction Pattern
For core operations:
Request
→ Form Request
→ Service
→ DB transaction
→ save database/files
→ commit
→ optional notification/email

If email fails after a successful database transaction, do NOT roll back the business operation. Log the mail failure.

## Architecture
- Thin Controllers
- Form Requests
- Services for business logic
- Eloquent relationships
- Policies/Middleware
- Notifications/Mail
- Jobs only when genuinely needed

Avoid over-engineering. Do not create unnecessary repositories.

## Development Workflow
Before coding:
1. Inspect existing Laravel structure.
2. Check existing authentication.
3. Check migrations/models.
4. Check routes.
5. Check Blade layouts/components.
6. Check Tailwind/Vite.
7. Reuse existing code.
8. Avoid unnecessary rewrites.

For each feature:
1. Briefly explain plan.
2. Migration.
3. Model/relationships.
4. Form Request.
5. Service if needed.
6. Policy/Middleware.
7. Controller.
8. Routes.
9. Blade UI.
10. Seeder/factory if useful.
11. Check syntax/migrations.
12. Summarize changed files.

## Roles
Only:
- super_admin
- admin
- participant

Never create reviewer role.

## Submission Flow
DRAFT
→ SUBMITTED
→ UNDER_REVIEW
→ ACCEPTED / REJECTED / REVISION_REQUIRED
→ RESUBMITTED
→ ACCEPTED

`UNDER_REVIEW` means internal admin checking only.

## UI Direction
Use the existing ICHA 2026 visual style:
- purple navbar
- yellow/gold accent
- dark navy/indigo image overlay
- professional conference look
- responsive
- accessible contrast
- clean spacing
- avoid excessive effects

## Security
- Validate all input.
- Authorize resource ownership.
- Validate uploaded file type/size.
- Generate safe filenames.
- Never execute uploaded files.
- Protect private downloads.
- Do not expose production stack traces.

## Performance
- Pagination for admin lists.
- Eager loading.
- Avoid N+1.
- Optimize images.
- Avoid unnecessary requests.

## First Task
Do NOT immediately create all features.

First inspect the existing project and report:
1. Architecture
2. Authentication
3. Existing tables/models
4. Routes
5. Blade components/layouts
6. Tailwind/Vite setup
7. Potential conflicts with ICHA requirements

Then propose the smallest Phase 1 implementation step.
