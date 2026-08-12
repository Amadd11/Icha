# ARCHITECTURE — ICHA Laravel 13 + Inertia Vue (Reviewer Revised)

## Stack
- Laravel 13
- PHP
- MySQL
- Inertia.js
- Vue 3
- Vite
- Tailwind CSS
- Laravel Storage
- Form Requests
- Policies
- Services

## Architecture
```text
Browser → Vue → Inertia → Route → Middleware/Auth/Role
→ Controller → Form Request → Policy → Service → Eloquent → MySQL
```

Controllers stay thin. Business workflows belong in Services.

## Application Areas
### Public
PublicLayout and dynamic conference pages.

### Participant
ParticipantLayout, registration, payment, submissions, papers, presentations and certificates.

### Reviewer
ReviewerLayout, dashboard, topics, assigned abstracts, review form, completed reviews and profile.

### Admin
AdminLayout, conference management, participants, payments, submissions, reviewer assignment, papers, presentations, certificates and publications.

## Roles
```text
super_admin
admin
reviewer
participant
```

Redirect:
```text
participant → /participant/dashboard
reviewer    → /reviewer/dashboard
admin       → /admin/dashboard
super_admin → /admin/dashboard
```

## Backend Structure
```text
app/
├── Enums/
│   ├── UserRole.php
│   ├── AbstractStatus.php
│   ├── ReviewStatus.php
│   ├── ReviewType.php
│   └── RecommendationType.php
├── Http/
│   ├── Controllers/{Public,Participant,Reviewer,Admin}
│   ├── Requests/{Participant,Reviewer,Admin}
│   └── Resources/
├── Models/
│   ├── User.php
│   ├── ReviewerProfile.php
│   ├── Conference.php
│   ├── Topic.php
│   ├── Submission.php
│   ├── ReviewRound.php
│   ├── ReviewAssignment.php
│   └── Review.php
├── Policies/
└── Services/
    ├── ReviewerService.php
    ├── ReviewAssignmentService.php
    └── ReviewService.php
```

## Vue Structure
```text
resources/js/
├── Layouts/
│   ├── PublicLayout.vue
│   ├── ParticipantLayout.vue
│   ├── ReviewerLayout.vue
│   └── AdminLayout.vue
├── Components/Reviewer/
│   ├── ReviewerProfileForm.vue
│   ├── TopicCard.vue
│   ├── ReviewCriteria.vue
│   ├── ReviewSummary.vue
│   └── ReviewConfirmationModal.vue
└── Pages/Reviewer/
    ├── Dashboard.vue
    ├── Profile.vue
    ├── Topics/Index.vue
    ├── Abstracts/Index.vue
    ├── Abstracts/Show.vue
    └── Reviews/Completed.vue
```

## Review Domain
Do not store reviewer_id directly on submissions.

Use:
```text
Submission
 → ReviewRound
 → ReviewAssignment
 → Review
```

A new revision creates a new review round and preserves history.

## Blinded Review
Backend resources must not expose author name, email, institution or corresponding-author identity to reviewers when blinded review is enabled.

## ReviewService
Must:
1. verify assignment ownership
2. verify round is open
3. verify reviewer profile is complete
4. validate two criteria
5. validate score 1–5
6. calculate total
7. calculate recommendation
8. create review atomically
9. mark assignment submitted
10. count submitted reviews
11. lock after required count
12. trigger optional notification after commit

Use a transaction and concurrency-safe locking.

## Shared Hosting
Review submission is a synchronous HTTP request. No Redis, queue worker or WebSocket is required.
