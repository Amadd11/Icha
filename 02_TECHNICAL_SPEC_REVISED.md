# Technical Specification — ICHA 2026

## Stack
- Laravel 13
- PHP compatible with Laravel 13
- MySQL
- Blade
- Tailwind CSS
- Vite
- Laravel Storage
- Laravel Authentication
- SMTP/Mail

## Shared Hosting First
The production system must work without:
- Redis
- Supervisor
- Docker
- persistent `php artisan queue:work`
- Reverb/WebSockets

## Architecture
Use:
- Controllers for HTTP orchestration
- Form Requests for validation
- Services for business logic
- Eloquent Models/relationships
- Policies/Middleware for authorization
- Notifications/Mail for communication
- Jobs only when truly useful

Do not create a repository for every model automatically.

## Critical Queue Rule
Core operations MUST be synchronous:
- registration
- abstract submission
- author creation
- payment verification
- status updates
- full paper upload
- presentation upload
- certificate record creation

The application must remain functional if no queue worker is running.

## Optional Jobs
Jobs may be used only for:
- bulk email
- large certificate generation
- large reports
- image processing
- large imports/exports

If a Job is added, explain its fallback/hosting requirement. It must never be required for core data integrity.

## Transaction Pattern
Request → Form Request → Service → DB transaction → save DB/files → commit → optional email/notification.

Email failure must not roll back a successful core transaction.

## Storage
Use Laravel Storage with safe generated filenames. Validate MIME/type and file size.

Suggested directories:
- `storage/app/public/conferences/`
- `storage/app/public/speakers/`
- `storage/app/public/submissions/abstracts/`
- `storage/app/public/submissions/papers/`
- `storage/app/public/presentations/`
- `storage/app/public/payments/`
- `storage/app/public/certificates/`
- `storage/app/public/sponsors/`

## Roles
Only:
- super_admin
- admin
- participant

## UI
Purple primary, yellow/gold accent, dark navy/indigo hero overlay, professional conference style, responsive and accessible.

## Explicitly Forbidden
Do not add reviewer modules, Redis requirement, Supervisor requirement, persistent worker requirement, or Reverb/WebSocket requirement.
