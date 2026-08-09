# MASTER PROMPT — VIBE CODING

You are building the ICHA Conference Management System using Laravel 13.

Read all project documentation before writing code:
- 01_PRD.md
- 02_FLOW.md
- 03_ARCHITECTURE.md
- 04_DESIGN.md
- 05_ERD.md
- 06_DATABASE_RULES.md
- 07_VIBE_CODING_RULES.md
- 08_IMPLEMENTATION_PLAN.md

## Non-Negotiable Requirements

1. This is a multi-conference system.
2. One Laravel application must support ICHA 2026, ICHA 2027, ICHA 2028 and future editions.
3. `Conference` is the root entity.
4. Conference-specific data must be isolated by `conference_id`.
5. Landing pages are dynamic and use one reusable template.
6. Never hardcode a year such as ICHA 2026 in reusable templates.
7. There is NO external reviewer role.
8. `under_review` means internal admin checking.
9. Core transactions must work synchronously.
10. The application must be deployable on shared hosting.
11. Do not make Redis, Supervisor, Docker, Reverb or persistent queue workers mandatory.
12. Jobs are optional only for non-critical background work.
13. Use Laravel 13 conventions.
14. Keep controllers thin.
15. Use Form Requests for validation.
16. Use Policies for authorization.
17. Use Services for multi-step business workflows.
18. Use Eloquent relationships and route model binding.
19. Reuse Blade/Tailwind components.
20. Do not create separate landing pages for each conference year.

## Coding Process

Before coding:
- inspect the existing project
- identify current Laravel version
- inspect migrations
- inspect models
- inspect routes
- inspect views/components
- do not overwrite existing working code unnecessarily

Then:
1. Explain the implementation plan briefly.
2. Implement the smallest complete step.
3. Run relevant checks/tests.
4. Fix errors before moving on.
5. Keep architecture consistent with the documentation.

## Multi-Conference Example

Correct:
`/conferences/icha-2026`
`/conferences/icha-2027`

Correct Blade:
`{{ $conference->title }}`

Incorrect:
`<h1>ICHA 2026</h1>`

Correct query:
`$conference->speakers()->get()`

Incorrect:
`Speaker::all()`

## Shared Hosting Rule

Never design a critical workflow that requires a queue worker.

Registration, payment verification, abstract submission, paper upload, presentation upload and certificate record creation must work synchronously.

## UI Rule

Use the existing ICHA visual language:
- deep purple/indigo
- dark navy
- yellow/gold
- clean academic design
- responsive
- accessible

Do not add random visual styles.

## Output Rule

When implementing a feature, report:
- files created/changed
- migration/model changes
- routes
- authorization
- tests/checks
- any deployment consideration

Do not claim a feature is complete if it has not been verified.
