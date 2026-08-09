# VIBE CODING RULES — ICHA

## General
Build incrementally. Do not generate the entire application blindly in one pass.

Before changing code:
1. Inspect existing files.
2. Inspect migrations/models/routes.
3. Reuse existing components.
4. Confirm the current architecture.
5. Make the smallest coherent change.

## Laravel
Use Laravel 13 conventions.
Prefer:
- Eloquent relationships
- Form Requests
- Policies
- Services for business logic
- route model binding
- Blade components
- Laravel Storage
- DB transactions

Avoid unnecessary repository abstractions unless they solve a real problem.

## Controllers
Controllers should:
- receive validated input
- call services
- return views/redirects
- avoid large business logic

## Models
Models should contain:
- relationships
- casts
- scopes where useful
- guarded/fillable rules

Do not put large workflows in models.

## Services
Use services for workflows such as:
- conference activation
- registration creation
- payment verification
- submission state transitions
- certificate issuance

## Authorization
Use Policies and middleware.
Never trust hidden form fields for authorization.

## Multi-Conference
Always preserve conference context.

Before implementing a feature, answer:
1. Does this belong to a conference?
2. Where is `conference_id` stored?
3. How is current conference resolved?
4. Can a participant/admin access data from another conference?

## UI
Use existing design system.
Do not introduce new colors without reason.
Do not create year-specific Blade files.

## Jobs
Do not introduce a Job simply because it is available.
Core operations must work synchronously on shared hosting.

## File Uploads
Validate:
- MIME/type
- file size
- extension
- authorization

Use Storage instead of manually constructing filesystem paths.

## Testing
For each module add tests for:
- happy path
- validation failure
- unauthorized access
- wrong conference context
- status transition rules

## Completion Rule
A feature is not complete until:
- migration works
- model relationships work
- validation works
- authorization works
- UI works
- conference isolation is verified
- relevant tests exist
