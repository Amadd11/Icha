# CODE ARCHITECTURE — ICHA
## Laravel 13 + Inertia.js + Vue

## 1. Goal

Project ICHA menggunakan arsitektur yang mudah dirawat dan dikembangkan untuk banyak conference edition:

- ICHA 2026
- ICHA 2027
- ICHA 2028
- dan conference berikutnya

Public website, Participant Dashboard, dan Admin Dashboard dipisahkan secara jelas.

Prinsip utama:

```text
Public
  ↓
Conference Website

Participant
  ↓
Registration → Payment → Abstract → Full Paper → Presentation → Certificate

Admin
  ↓
Manage Conference → Participants → Payments → Submissions → Papers → Certificates
```

---

# 2. Technology Stack

Backend:

- Laravel 13
- PHP
- MySQL
- Eloquent ORM
- Form Request
- Policies
- Services
- Laravel Authentication

Frontend:

- Vue 3
- Inertia.js
- Vite
- Tailwind CSS

Deployment:

- Shared hosting

Core business transaction MUST work synchronously.

Jobs/queues are optional and must not be required for core transactions.

---

# 3. High-Level Architecture

```text
┌─────────────────────────────────────────────────────────────┐
│                         Browser                             │
│                         Vue 3                              │
└────────────────────────────┬────────────────────────────────┘
                             │
                             │ Inertia
                             ▼
┌─────────────────────────────────────────────────────────────┐
│                     Laravel Routes                          │
│                 web.php / admin.php / participant.php       │
└────────────────────────────┬────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────┐
│                       Middleware                            │
│                  Auth / Role / Permission                   │
└────────────────────────────┬────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────┐
│                      Controller                             │
│            Thin Controller / HTTP Handling Only             │
└────────────────────────────┬────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────┐
│                    Form Request                             │
│                  Validation / Authorization                 │
└────────────────────────────┬────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────┐
│                       Service                               │
│                  Business Logic                             │
└────────────────────────────┬────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────┐
│                     Eloquent Model                          │
│                  Relationships / Query                      │
└────────────────────────────┬────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────┐
│                         MySQL                               │
└─────────────────────────────────────────────────────────────┘
```

---

# 4. Recommended Project Structure

```text
app/
├── Enums/
│   ├── UserRole.php
│   ├── ConferenceStatus.php
│   ├── AbstractStatus.php
│   ├── PaymentStatus.php
│   ├── PaperStatus.php
│   └── RegistrationStatus.php
│
├── Http/
│   ├── Controllers/
│   │   ├── Public/
│   │   │   ├── HomeController.php
│   │   │   └── ConferenceController.php
│   │   │
│   │   ├── Participant/
│   │   │   ├── DashboardController.php
│   │   │   ├── ProfileController.php
│   │   │   ├── RegistrationController.php
│   │   │   ├── PaymentController.php
│   │   │   ├── SubmissionController.php
│   │   │   ├── PaperController.php
│   │   │   ├── PresentationController.php
│   │   │   └── CertificateController.php
│   │   │
│   │   └── Admin/
│   │       ├── DashboardController.php
│   │       ├── ConferenceController.php
│   │       ├── ParticipantController.php
│   │       ├── RegistrationController.php
│   │       ├── PaymentController.php
│   │       ├── SubmissionController.php
│   │       ├── PaperController.php
│   │       ├── PresentationController.php
│   │       ├── CertificateController.php
│   │       ├── PublicationController.php
│   │       ├── SpeakerController.php
│   │       ├── TimelineController.php
│   │       └── SponsorController.php
│   │
│   ├── Requests/
│   │   ├── Admin/
│   │   ├── Participant/
│   │   └── Public/
│   │
│   └── Resources/
│       ├── ConferenceResource.php
│       ├── SpeakerResource.php
│       ├── SubmissionResource.php
│       ├── RegistrationResource.php
│       └── PaymentResource.php
│
├── Models/
│   ├── User.php
│   ├── Conference.php
│   ├── Participant.php
│   ├── Speaker.php
│   ├── CommitteeMember.php
│   ├── Topic.php
│   ├── Timeline.php
│   ├── RegistrationType.php
│   ├── Registration.php
│   ├── Payment.php
│   ├── Submission.php
│   ├── Author.php
│   ├── Paper.php
│   ├── Presentation.php
│   ├── Certificate.php
│   ├── Publication.php
│   ├── Sponsor.php
│   └── Faq.php
│
├── Policies/
│   ├── ConferencePolicy.php
│   ├── RegistrationPolicy.php
│   ├── PaymentPolicy.php
│   ├── SubmissionPolicy.php
│   ├── PaperPolicy.php
│   └── CertificatePolicy.php
│
├── Services/
│   ├── ConferenceService.php
│   ├── RegistrationService.php
│   ├── PaymentService.php
│   ├── SubmissionService.php
│   ├── PaperService.php
│   ├── PresentationService.php
│   └── CertificateService.php
│
└── Support/
    └── ConferenceContext.php


resources/
├── js/
│   ├── app.js
│   │
│   ├── Components/
│   │   ├── UI/
│   │   │   ├── Button.vue
│   │   │   ├── Card.vue
│   │   │   ├── Badge.vue
│   │   │   ├── Modal.vue
│   │   │   ├── Input.vue
│   │   │   ├── Select.vue
│   │   │   ├── Textarea.vue
│   │   │   └── Pagination.vue
│   │   │
│   │   ├── Navigation/
│   │   │   ├── Navbar.vue
│   │   │   ├── MobileMenu.vue
│   │   │   ├── ConferenceDropdown.vue
│   │   │   └── UserMenu.vue
│   │   │
│   │   ├── Conference/
│   │   │   ├── Hero.vue
│   │   │   ├── Countdown.vue
│   │   │   ├── About.vue
│   │   │   ├── Theme.vue
│   │   │   ├── Topics.vue
│   │   │   ├── Timeline.vue
│   │   │   ├── Speakers.vue
│   │   │   ├── Registration.vue
│   │   │   ├── Publication.vue
│   │   │   ├── Sponsors.vue
│   │   │   ├── Faq.vue
│   │   │   └── Contact.vue
│   │   │
│   │   ├── Registration/
│   │   ├── Payment/
│   │   ├── Submission/
│   │   ├── Paper/
│   │   └── Certificate/
│   │
│   ├── Layouts/
│   │   ├── PublicLayout.vue
│   │   ├── ParticipantLayout.vue
│   │   └── AdminLayout.vue
│   │
│   ├── Pages/
│   │   ├── Public/
│   │   │   ├── Home.vue
│   │   │   ├── Conferences/
│   │   │   │   └── Index.vue
│   │   │   └── Conference/
│   │   │       ├── Show.vue
│   │   │       ├── Speakers.vue
│   │   │       ├── Timeline.vue
│   │   │       └── Registration.vue
│   │   │
│   │   ├── Participant/
│   │   │   ├── Dashboard.vue
│   │   │   ├── Profile.vue
│   │   │   ├── Conferences/
│   │   │   ├── Registration/
│   │   │   ├── Payments/
│   │   │   ├── Submissions/
│   │   │   ├── Papers/
│   │   │   ├── Presentations/
│   │   │   └── Certificates/
│   │   │
│   │   └── Admin/
│   │       ├── Dashboard.vue
│   │       ├── Conferences/
│   │       ├── Participants/
│   │       ├── Registrations/
│   │       ├── Payments/
│   │       ├── Submissions/
│   │       ├── Papers/
│   │       ├── Presentations/
│   │       ├── Certificates/
│   │       ├── Publications/
│   │       ├── Speakers/
│   │       ├── Timelines/
│   │       └── Sponsors/
│   │
│   ├── Composables/
│   │   ├── useConference.js
│   │   ├── useModal.js
│   │   ├── usePagination.js
│   │   └── useFileUpload.js
│   │
│   └── Utils/
│       ├── formatDate.js
│       ├── formatCurrency.js
│       └── status.js
│
├── css/
│   └── app.css
│
routes/
├── web.php
├── admin.php
└── participant.php
```

---

# 5. Three Application Areas

## Public

Purpose:

- conference information
- conference archive
- speakers
- topics
- timeline
- registration information
- publication
- sponsors
- FAQ
- contact

Layout:

```text
PublicLayout.vue
```

Routes:

```text
/
 /conferences/{conference:slug}
```

---

## Participant

Purpose:

Participant manages their own conference activity.

Layout:

```text
ParticipantLayout.vue
```

Routes:

```text
/participant/dashboard
/participant/profile
/participant/conferences
/participant/registrations
/participant/payments
/participant/submissions
/participant/papers
/participant/presentations
/participant/certificates
```

Participant MUST NOT access admin pages.

---

## Admin

Purpose:

Manage the conference system.

Layout:

```text
AdminLayout.vue
```

Routes:

```text
/admin/dashboard
/admin/conferences
/admin/participants
/admin/registrations
/admin/payments
/admin/submissions
/admin/papers
/admin/presentations
/admin/certificates
/admin/publications
/admin/speakers
/admin/timelines
/admin/sponsors
```

---

# 6. Role-Based Access

Roles:

```text
super_admin
admin
participant
```

Authentication flow:

```text
Login
  │
  ▼
Check Role
  │
  ├── participant ──→ /participant/dashboard
  │
  ├── admin ────────→ /admin/dashboard
  │
  └── super_admin ──→ /admin/dashboard
```

Do not rely only on frontend menu visibility.

Backend MUST enforce access using:

- middleware
- policies
- authorization checks

---

# 7. Middleware

Example:

```php
Route::middleware(['auth', 'role:participant'])
    ->prefix('participant')
    ->name('participant.')
    ->group(function () {
        // participant routes
    });
```

Admin:

```php
Route::middleware(['auth', 'role:admin,super_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // admin routes
    });
```

---

# 8. Dashboard Separation

Participant:

```text
ParticipantLayout
        │
        ├── Participant Navigation
        │
        └── Participant Dashboard
```

Admin:

```text
AdminLayout
        │
        ├── Admin Sidebar
        │
        └── Admin Dashboard
```

Do NOT create one giant dashboard that contains:

```text
if admin
if participant
if super_admin
```

Keep pages and layouts separate.

---

# 9. Participant Dashboard

Show:

```text
Current Conference
Registration Status
Payment Status
Abstract Status
Full Paper Status
Presentation Status
Certificate Status
Next Action
Important Deadline
```

Example:

```text
ICHA 2026

Registration       ✓ Paid
Abstract            ✓ Accepted
Full Paper          ● Pending
Presentation        ○ Pending
Certificate         ○ Not Available
```

Participant should immediately know what action is required next.

---

# 10. Admin Dashboard

Show conference-specific metrics:

```text
Current Conference
        │
        ▼

Participants
Registrations
Paid Registrations
Pending Payments
Submitted Abstracts
Accepted Abstracts
Full Papers
Presentations
Certificates
```

Example:

```text
ICHA 2026

320 Participants
250 Paid
45 Abstracts
30 Full Papers
20 Presentations
```

---

# 11. Current Conference Context

Because ICHA is multi-conference, Admin must have a Current Conference selector.

Example:

```text
Current Conference
[ ICHA 2026 ▼ ]
```

When selected:

```text
Admin Dashboard
Participants
Payments
Submissions
Papers
Certificates
```

must use the selected conference.

Never mix records from different conferences.

---

# 12. Conference Model Principle

Conference-specific entities MUST have:

```text
conference_id
```

Examples:

```text
speakers
topics
timelines
registration_types
sponsors
faqs
publications
```

Relationship:

```text
Conference
 ├── Speakers
 ├── Topics
 ├── Timelines
 ├── Registration Types
 ├── Sponsors
 ├── FAQs
 └── Publications
```

Participant-related data should also reference the relevant conference.

---

# 13. Public Conference Page

Route:

```text
/conferences/{conference:slug}
```

Controller:

```php
public function show(Conference $conference)
{
    $conference->load([
        'speakers',
        'topics',
        'timelines',
        'registrationTypes',
        'sponsors',
        'faqs',
        'publications',
    ]);

    return Inertia::render('Public/Conference/Show', [
        'conference' => new ConferenceResource($conference),
    ]);
}
```

Do not hardcode:

```text
ICHA 2026
```

Use:

```vue
{{ conference.title }}
```

---

# 14. Vue Page Structure

Avoid giant Vue pages.

Bad:

```text
Show.vue
├── Navbar
├── Hero
├── About
├── Topics
├── Speakers
├── Timeline
├── Registration
├── Sponsors
├── FAQ
└── Footer
```

if all code exists inside one file.

Preferred:

```vue
<template>
    <PublicLayout>

        <ConferenceHero
            :conference="conference"
        />

        <ConferenceAbout
            :conference="conference"
        />

        <ConferenceTopics
            :topics="conference.topics"
        />

        <ConferenceTimeline
            :timelines="conference.timelines"
        />

        <ConferenceSpeakers
            :speakers="conference.speakers"
        />

        <ConferenceRegistration
            :registration-types="conference.registration_types"
        />

        <ConferenceSponsors
            :sponsors="conference.sponsors"
        />

    </PublicLayout>
</template>
```

---

# 15. Components Rule

Components should be presentation-focused.

Good:

```vue
<SpeakerCard
    :speaker="speaker"
/>
```

Avoid database queries inside Vue components.

Do not put API/business logic directly into reusable UI components.

---

# 16. Controller Rule

Controllers must remain thin.

Good:

```php
public function store(
    StoreSubmissionRequest $request,
    Conference $conference
) {
    $submission = $this->submissionService->create(
        $conference,
        $request->user(),
        $request->validated()
    );

    return to_route(
        'participant.submissions.show',
        $submission
    );
}
```

Business logic belongs in:

```text
SubmissionService.php
```

---

# 17. Service Layer

Use services for business processes.

Examples:

```text
ConferenceService
RegistrationService
PaymentService
SubmissionService
PaperService
PresentationService
CertificateService
```

A service may coordinate multiple models and database transactions.

Example:

```php
DB::transaction(function () {
    // create registration
    // create payment
    // update status
});
```

Core transactions must complete synchronously because deployment is shared hosting.

---

# 18. Repository Rule

Do NOT create repositories for every model by default.

Avoid:

```text
ConferenceRepository
SpeakerRepository
PaymentRepository
SubmissionRepository
```

unless there is a real architectural reason.

Prefer:

```text
Controller
    ↓
Service
    ↓
Eloquent
```

Use repositories only when query complexity or multiple data sources justify them.

---

# 19. Form Request Rule

Validation belongs in Form Requests.

Example:

```text
StoreSubmissionRequest
UpdateSubmissionRequest
StorePaymentRequest
StoreConferenceRequest
```

Do not place large validation arrays directly inside controllers.

---

# 20. Resource Rule

Use API/HTTP Resources when the data sent to Vue needs transformation or strict field selection.

Example:

```php
return Inertia::render('Public/Conference/Show', [
    'conference' => new ConferenceResource($conference),
]);
```

Avoid sending unnecessary database columns or sensitive fields.

---

# 21. Policies

Use policies for ownership and action authorization.

Examples:

```text
SubmissionPolicy
PaymentPolicy
PaperPolicy
CertificatePolicy
ConferencePolicy
```

Example logic:

```text
Participant can update own submission
Participant cannot update another participant's submission

Admin can manage submissions
Participant cannot access admin submissions
```

---

# 22. Inertia Shared Data

Only share global data that is actually needed.

Possible shared props:

```text
auth.user
auth.role
currentConference
flash
```

Do not share large datasets globally.

Bad:

```text
share all conferences
share all speakers
share all participants
```

Load them only on pages that need them.

---

# 23. Vue Composables

Use composables for reusable frontend logic.

Examples:

```text
useConference()
useModal()
usePagination()
useFileUpload()
```

Do not duplicate the same logic across multiple pages.

---

# 24. Utils

Use utility functions for simple transformations.

Examples:

```text
formatDate()
formatCurrency()
getStatusLabel()
getStatusClass()
```

Business rules should NOT be hidden inside generic utilities.

---

# 25. File Uploads

Files:

- abstract
- full paper
- payment proof
- presentation
- profile photo

should be stored using Laravel Storage.

Recommended:

```text
storage/app/public/
├── conferences/
├── submissions/
├── papers/
├── payments/
├── presentations/
├── certificates/
└── participants/
```

Do not store uploaded files directly inside:

```text
resources/
public/
```

unless they are intentionally public assets.

---

# 26. Jobs / Queues

Core transaction MUST NOT depend on jobs.

Do NOT require a worker for:

```text
Registration
Payment submission
Abstract submission
Paper submission
Status update
Certificate generation
```

Jobs may optionally be introduced for:

```text
Bulk email
Non-critical notifications
Large report generation
Image processing
```

If a job fails, the core transaction should remain valid.

---

# 27. Database Transaction Rule

Use database transactions for operations that must succeed together.

Example:

```text
Submit Abstract
    │
    ├── Create Submission
    ├── Create Authors
    ├── Save File
    └── Update Status
```

Where appropriate, wrap database changes in:

```php
DB::transaction(...)
```

---

# 28. Naming Convention

Backend:

```text
ConferenceController
ConferenceService
ConferencePolicy
ConferenceResource
ConferenceStatus
```

Vue:

```text
ConferenceHero.vue
ConferenceTimeline.vue
ConferenceCard.vue
```

Do not use inconsistent names such as:

```text
ConfHero.vue
ConferenceHeroSection.vue
HeroConference.vue
```

Choose one naming convention and keep it consistent.

---

# 29. Folder Naming Convention

Use:

```text
Pages/Admin/
Pages/Participant/
Pages/Public/
```

instead of:

```text
Pages/admin/
Pages/user/
Pages/front/
```

Use consistent domain names throughout the application.

---

# 30. Maintainability Rules

Every new feature should answer:

1. Is this a public feature?
2. Is this a participant feature?
3. Is this an admin feature?
4. Is the logic business logic?
5. Is the UI reusable?
6. Does the data belong to a conference?
7. Does this require authorization?
8. Does this require a service?
9. Does this need a Form Request?
10. Does this need a Policy?

This prevents random code placement.

---

# 31. Feature Development Example

For Abstract Submission:

```text
app/
├── Http/
│   ├── Controllers/Participant/SubmissionController.php
│   ├── Requests/Participant/StoreSubmissionRequest.php
│   └── Resources/SubmissionResource.php
│
├── Models/
│   ├── Submission.php
│   └── Author.php
│
├── Services/
│   └── SubmissionService.php
│
└── Policies/
    └── SubmissionPolicy.php
```

Frontend:

```text
resources/js/
├── Pages/Participant/Submissions/
│   ├── Index.vue
│   ├── Create.vue
│   └── Show.vue
│
└── Components/Submission/
    ├── SubmissionForm.vue
    ├── AuthorForm.vue
    ├── SubmissionStatus.vue
    └── FileUpload.vue
```

This makes the entire feature easy to locate.

---

# 32. Final Architecture Principle

The project should follow:

```text
                    ICHA
                     │
          ┌──────────┼──────────┐
          │          │          │
        Public   Participant   Admin
          │          │          │
        Pages      Pages       Pages
          │          │          │
       Components Components Components
          │          │          │
          └──────────┼──────────┘
                     │
                  Inertia
                     │
                Controllers
                     │
               Form Requests
                     │
                  Services
                     │
               Policies/Enums
                     │
                  Eloquent
                     │
                  MySQL
```

The most important rules are:

1. Public, Participant, and Admin are separate application areas.
2. Participant and Admin use different layouts.
3. Role access is enforced on the backend, not only hidden in Vue.
4. Controllers stay thin.
5. Business logic goes into Services.
6. Validation goes into Form Requests.
7. Authorization goes into Policies.
8. Eloquent is used directly unless a Repository is genuinely needed.
9. Conference-specific data always has a clear conference context.
10. Vue pages are composed from reusable components.
11. Core features do not depend on queues/workers.
12. Do not duplicate pages for each conference year.
13. Do not hardcode conference-specific content.
14. Use Inertia props/Resources to send only required data.
15. Keep the architecture simple enough to work reliably on shared hosting.
