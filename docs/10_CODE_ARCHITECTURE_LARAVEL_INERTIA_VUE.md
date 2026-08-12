# CODE ARCHITECTURE — ICHA Laravel 13 + Inertia Vue (Reviewer Revised)

## Areas
```text
Public
Participant
Reviewer
Admin
```

Each has its own layout and Pages directory.

## Structure
```text
app/
├── Enums/
├── Http/
│   ├── Controllers/{Public,Participant,Reviewer,Admin}
│   ├── Requests/{Participant,Reviewer,Admin}
│   └── Resources/
├── Models/
├── Policies/
├── Services/
└── Support/

resources/js/
├── Components/
│   ├── UI/
│   ├── Conference/
│   └── Reviewer/
├── Layouts/
│   ├── PublicLayout.vue
│   ├── ParticipantLayout.vue
│   ├── ReviewerLayout.vue
│   └── AdminLayout.vue
├── Pages/
│   ├── Public/
│   ├── Participant/
│   ├── Reviewer/
│   └── Admin/
├── Composables/
└── Utils/

routes/
├── web.php
├── participant.php
├── reviewer.php
└── admin.php
```

## Reviewer Routes
```text
GET  /reviewer/dashboard
GET  /reviewer/profile
PUT  /reviewer/profile
GET  /reviewer/topics
GET  /reviewer/topics/{topic}/abstracts
GET  /reviewer/abstracts/{assignment}
POST /reviewer/abstracts/{assignment}/review
GET  /reviewer/reviews/completed
```

Every route must authorize the assignment.

## Reviewer Controller
Keep it thin:
```php
public function show(ReviewAssignment $assignment)
{
    $this->authorize('view', $assignment);

    return Inertia::render('Reviewer/Abstracts/Show', [
        'assignment' => new ReviewAssignmentResource(
            $assignment->load('reviewRound')
        ),
    ]);
}
```

The resource must be blinded.

## Services
```text
ReviewerService
ReviewAssignmentService
ReviewService
```

`ReviewService` owns score calculation, recommendation, review creation, assignment status, round locking and final result.

## Vue
Review pages should be presentation-focused:
```vue
<ReviewCriteria v-model="form.criteria_1_score" />
<ReviewCriteria v-model="form.criteria_2_score" />
<ReviewSummary ... />
<ReviewConfirmationModal ... />
```

Business rules remain on the backend.

## Resource
Reviewer-facing resource may expose:
- assignment id/status
- topic
- submission id/title/abstract/keywords
- current review data if applicable

It must not expose author identity in blinded mode.

## Reviewer Dashboard
Show:
- current conference
- pending reviews
- completed reviews
- topics
- assigned abstracts

Keep it simpler than admin.

## Shared Hosting
Reviewer submission is a standard synchronous HTTP + database transaction. No worker, Redis or WebSocket is required.
