# ARCHITECTURE — ICHA

## Stack
- Laravel 13
- PHP compatible with Laravel 13
- MySQL
- Blade
- Tailwind CSS
- Vite
- Laravel Storage
- Laravel Authentication
- Policies / Form Requests

## Architecture Pattern
Use a pragmatic Service Layer.

Browser
→ Route
→ Middleware/Auth
→ Controller
→ Form Request
→ Service
→ Eloquent Model
→ MySQL

Controllers stay thin. Business logic belongs in Services. Validation belongs in Form Requests. Authorization belongs in Policies.

## Suggested Structure
app/
├── Http/
│   ├── Controllers/
│   └── Requests/
├── Models/
├── Services/
├── Policies/
├── Notifications/
└── Jobs/                 # optional only

resources/views/
├── components/
├── layouts/
├── conferences/
├── admin/
└── participant/

## Core Models
Conference
User
Participant
Speaker
CommitteeMember
Category
Timeline
RegistrationType
Registration
Payment
Submission
Author
PaperVersion
Presentation
Certificate
Publication
Sponsor
Faq

## Conference Root
Conference has relationships to conference-specific records.

Example:
`$conference->speakers()`
`$conference->categories()`
`$conference->timelines()`
`$conference->sponsors()`
`$conference->registrations()`
`$conference->submissions()`

## Conference Fields
Recommended:
- id
- title
- slug unique
- year
- tagline nullable
- description nullable
- theme nullable
- start_date nullable
- end_date nullable
- venue nullable
- city nullable
- country default Indonesia
- email nullable
- phone nullable
- website nullable
- logo nullable
- hero_image nullable
- status: draft/published/archived
- is_active boolean
- timestamps

## Routing
Use route model binding:
`Route::get('/conferences/{conference:slug}', ...)`

Never use year-specific hardcoded routes.

## Data Isolation
Every conference-specific query must be scoped to the selected conference.
Use relationships and explicit `conference_id` checks.
Policies must prevent participants from accessing another participant's data.

## Transactions
Use `DB::transaction()` for multi-record business operations such as payment verification and submission state changes.

## Jobs
No core workflow may require a queue worker.
Jobs may be introduced later for:
- bulk email
- bulk certificate generation
- large reports
- image processing
- large imports/exports

Do not require Redis, Supervisor, Docker or Reverb.

## File Storage
Use Laravel Storage. Validate MIME type and size. Generate safe filenames. Private files must be authorized before download.

## Security
- CSRF
- authentication
- authorization/policies
- Form Request validation
- mass-assignment protection
- safe file uploads
- protected private downloads
- APP_DEBUG=false in production
