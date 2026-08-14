# ICHA — Reviewer, Presenter & Registration Workflow

## Tujuan

Implementasikan fitur workflow **Presenter → Abstract → 3 Reviewer → Admin Decision → Oral/Poster → Full Paper → Presentation** pada aplikasi ICHA yang sudah berjalan.

Project:
- Laravel 13
- Inertia.js
- Vue 3
- JavaScript
- Shared hosting

Jangan mengubah fitur existing yang tidak berkaitan.

## 1. Role

- `super_admin`: akses penuh.
- `admin`: conference, registration, payment, submission, reviewer assignment, review monitoring, final decision, full paper, presentation, certificate, publication.
- `reviewer`: hanya melakukan review terhadap abstract/paper yang ditugaskan.
- `participant`: registration, payment, submission, revision, full paper, dan tracking.

Tidak ada role `committee`.

## 2. Registration Fee

Gunakan tabel `registration_fees`, bukan `registration_types`.

Field minimal:

```text
id
conference_id
name
mode
type
category
price
is_active
timestamps
```

`mode`:
```text
online
offline
```

`type` HANYA:
```text
presenter
non_presenter
```

`category` opsional untuk membedakan tarif:
```text
national
student
international
```

`category` dapat NULL untuk non-presenter.

`registrations` harus menggunakan `registration_fee_id` dan menyimpan snapshot `amount` agar perubahan harga tidak mengubah histori transaksi.

Business rule:
- `registration_fee.type = presenter` → peserta dapat mengikuti workflow abstract.
- `registration_fee.type = non_presenter` → tidak memiliki workflow abstract/reviewer/full paper/presentation.

## 3. Flow Non-Presenter

```text
Register
→ Pilih Registration Fee
→ Payment
→ Admin Verify Payment
→ Registration Paid
→ Attend Conference
→ Certificate
```

## 4. Flow Presenter

```text
Register
→ Pilih Registration Fee dengan type = presenter
→ Payment
→ Admin Verify Payment
→ Submit Abstract
→ Admin Checking
→ Assign 3 Reviewers
→ Reviewer 1 Review
→ Reviewer 2 Review
→ Reviewer 3 Review
→ 3/3 Completed
→ Review Round Completed
→ Review Round Locked
→ Admin Final Decision
→ Accepted / Revision Required / Rejected
```

Jika Accepted:

```text
Accepted
→ Admin menentukan Presentation Type
→ Oral / Poster
→ Full Paper
→ Full Paper Approved
→ Presentation
→ Conference
→ Certificate
→ Publication
```

## 5. Abstract Status

```text
draft
submitted
admin_checking
under_review
revision_required
resubmitted
accepted
rejected
```

Jangan menjadikan `oral` atau `poster` sebagai abstract status.

## 6. Reviewer Assignment

Reviewer tidak boleh memilih abstract sendiri.

Hanya Admin yang melakukan assignment.

Satu review round maksimal 3 reviewer.

Reviewer yang sama tidak boleh ditugaskan dua kali pada round yang sama.

Reviewer harus aktif.

## 7. Review Round

Tabel `review_rounds`:

```text
id
submission_id
round_number
status
started_at
completed_at
locked_at
timestamps
```

Status:

```text
pending
in_progress
completed
locked
```

Satu submission dapat memiliki banyak review round. Round lama tidak boleh dihapus.

## 8. Review Assignment

Tabel `review_assignments`:

```text
id
review_round_id
reviewer_id
status
assigned_at
completed_at
timestamps
```

Status:

```text
assigned
in_progress
completed
```

Tambahkan unique constraint:

```text
review_round_id + reviewer_id
```

## 9. Review

Tabel `reviews`:

```text
id
review_assignment_id
criteria_1_score
criteria_2_score
total_score
recommendation
comments
submitted_at
timestamps
```

Criteria:
- Criteria 1: 1–5
- Criteria 2: 1–5

Jangan mengarang nama kriteria.

`total_score` wajib dihitung backend:

```text
criteria_1_score + criteria_2_score
```

Reviewer dapat memberikan recommendation:
```text
oral
poster
```

Recommendation reviewer hanya menjadi pertimbangan Admin.

## 10. Blinded Review

Reviewer tidak boleh menerima:
- nama author
- email author
- nomor telepon
- participant ID yang dapat mengidentifikasi peserta
- payment information
- reviewer lain
- score reviewer lain
- comment reviewer lain

Reviewer hanya menerima:
- submission reference
- title
- abstract
- keywords
- topic/track jika diperlukan
- file yang diperlukan untuk review

Jangan mengirim seluruh Eloquent Model ke Inertia.

Gunakan Resource khusus reviewer:
- `ReviewerSubmissionResource`
- `ReviewAssignmentResource`
- `ReviewResource`

## 11. Submit Review

Saat reviewer submit:
1. Authenticate reviewer.
2. Authorize dengan Policy.
3. Pastikan assignment milik reviewer tersebut.
4. Pastikan assignment masih dapat direview.
5. Validasi criteria 1–5.
6. Validasi recommendation.
7. Hitung total score di backend.
8. Simpan review.
9. Ubah assignment menjadi `completed`.
10. Hitung completed reviewer pada round.
11. Jika belum 3, round tetap berjalan.
12. Jika sudah 3, round menjadi `completed` dan `locked`.
13. Submission TIDAK otomatis accepted.
14. Gunakan `DB::transaction()`.

## 12. Aturan 3 Reviewer

Business rule utama:

```text
Reviewer 1 ✓
Reviewer 2 ✓
Reviewer 3 ✓
        ↓
3/3 Completed
        ↓
Review Round = completed
        ↓
Review Round = locked
        ↓
Waiting for Admin Decision
```

Jangan:
```text
3/3 reviewer → automatically accepted
```

## 13. Admin Final Decision

Setelah 3 reviewer selesai, Admin melihat:
- reviewer progress
- score
- total score
- average score jika diperlukan
- recommendation
- comments
- histori review round

Admin menentukan:
```text
accepted
revision_required
rejected
```

Simpan:
```text
decision
decided_by
decided_at
decision_note
```

Jika Accepted, Admin menentukan:
```text
presentation_type = oral | poster
```

Recommendation reviewer tidak otomatis menjadi presentation type final.

## 14. Revision

Jika `revision_required`:

```text
Abstract
→ Revision Required
→ Participant revisi
→ Resubmit
→ Review Round baru
→ Assign reviewer
→ Review
→ Admin Decision
```

Round sebelumnya tidak dihapus.

## 15. Accepted

Jika Admin memilih `accepted` dan menentukan `oral` atau `poster`:

```text
Accepted
→ Full Paper Submission
→ Full Paper Approved
→ Presentation
→ Conference
→ Certificate
→ Publication
```

## 16. Full Paper

Status minimal:
```text
draft
submitted
revision_required
approved
```

Jika full paper membutuhkan review, gunakan workflow review yang dapat dipakai ulang tanpa membuat sistem reviewer terpisah.

## 17. Architecture

Gunakan:

```text
Route
→ Controller
→ Form Request
→ Policy
→ Service
→ Model
→ Resource
→ Inertia
→ Vue
```

Controller harus tipis.

Service yang disarankan:
- `RegistrationService`
- `ReviewAssignmentService`
- `ReviewService`
- `ReviewDecisionService`

Tanggung jawab:
- ReviewAssignmentService: assign/replace/remove reviewer.
- ReviewService: submit review, calculate score, complete/lock round.
- ReviewDecisionService: accept/requestRevision/reject.

Jangan over-engineering.

## 18. Policies

Minimal:
- `SubmissionPolicy`
- `ReviewAssignmentPolicy`
- `ReviewPolicy`
- `ReviewRoundPolicy`

Authorization wajib backend. Cegah IDOR.

## 19. Resources

Minimal:
- `ReviewerSubmissionResource`
- `ReviewAssignmentResource`
- `ReviewResource`

Pastikan blinded data benar-benar tidak dikirim ke frontend.

## 20. Inertia + Vue

Gunakan:

```text
resources/js/Pages/

Admin/
└── Reviews/
    ├── Index.vue
    ├── Show.vue
    └── Assign.vue

Reviewer/
└── Reviews/
    ├── Index.vue
    └── Show.vue

Participant/
└── Submissions/
```

Gunakan Composition API dan reusable components.

## 21. Reviewer Dashboard

Reviewer hanya melihat assignment miliknya.

Form:

```text
Criteria 1: 1 2 3 4 5
Criteria 2: 1 2 3 4 5
Recommendation: Oral / Poster
Comments
[Submit Review]
```

Setelah submit status `Completed`. Jika locked, form disabled.

## 22. Admin Review Dashboard

Admin dapat melihat:
- Pending Assignment
- Assigned Reviews
- Review Progress
- Completed Reviews
- Final Decisions
- Review Rounds

Contoh:
```text
Abstract #001
✓ Reviewer A — Completed
✓ Reviewer B — Completed
○ Reviewer C — Pending
2 / 3
```

Setelah 3/3:
```text
3 / 3 Completed
Waiting for Admin Decision
```

## 23. Security

WAJIB:
- backend authorization
- Policy
- Form Request validation
- ownership validation
- blinded Resource
- backend score calculation
- backend status transition
- database transaction
- duplicate assignment prevention
- duplicate review prevention
- locked review protection
- IDOR protection
- reviewer tidak dapat melihat author
- reviewer tidak dapat melihat reviewer/review lain
- reviewer tidak dapat mengubah assignment

Jangan percaya dari frontend jika dapat ditentukan backend:
```text
reviewer_id
participant_id
total_score
status
conference_id
```

## 24. Shared Hosting

Core workflow harus synchronous.

Jangan bergantung pada:
- Queue
- Redis
- Supervisor
- WebSocket
- Docker runtime
- Worker

Jobs hanya opsional untuk non-critical task seperti email.

## 25. Database Relationships

```text
Conference
 ├── RegistrationFees
 ├── Registrations
 └── Submissions

RegistrationFee
 └── Registrations

Participant/User
 ├── Registration
 └── Submissions

Submission
 ├── ReviewRounds
 └── FullPaper

ReviewRound
 └── ReviewAssignments

ReviewAssignment
 ├── Reviewer/User
 └── Review

Submission
 └── Presentation
```

## 26. Tests Wajib

Buat feature tests untuk:
- presenter dapat submit abstract
- non-presenter tidak dapat submit abstract
- admin dapat assign reviewer
- reviewer tidak dapat self-assign
- reviewer hanya melihat assignment miliknya
- reviewer tidak dapat melihat identitas author
- reviewer tidak dapat melihat reviewer lain
- reviewer dapat submit review
- criteria hanya 1–5
- total score dihitung backend
- duplicate assignment ditolak
- duplicate review ditolak
- 1/3 dan 2/3 belum complete
- 3/3 menjadi completed dan locked
- 3/3 TIDAK otomatis accepted
- admin dapat Accept / Revision Required / Reject
- revision membuat round baru
- histori round lama tetap tersedia
- admin dapat menentukan Oral/Poster
- reviewer tidak dapat mengubah review setelah locked
- IDOR terhadap review ditolak

## 27. Implementation Order

```text
1. Inspect existing project
2. Inspect existing migrations/models
3. Registration Fee
4. Registration relationship
5. Submission
6. Review Round
7. Review Assignment
8. Review
9. Enums
10. Form Requests
11. Policies
12. Services
13. Resources
14. Controllers
15. Routes
16. Admin Review UI
17. Reviewer UI
18. Participant Submission UI
19. Final Decision UI
20. Oral/Poster
21. Tests
```

Jangan membuat ulang fitur yang sudah ada.

## Definition of Done

Fitur selesai jika:
- Presenter dan Non-Presenter memiliki workflow berbeda.
- Registration Fee terhubung ke conference.
- Presenter dapat submit abstract.
- Admin dapat assign maksimal 3 reviewer.
- Reviewer hanya melihat assignment miliknya.
- Blinded review diterapkan pada backend Resource.
- 3 reviewer dapat review independen.
- 3/3 menyelesaikan dan mengunci round.
- 3/3 tidak otomatis accepted.
- Admin melakukan final decision.
- Admin dapat Accepted / Revision Required / Rejected.
- Admin dapat menentukan Oral / Poster.
- Revision membuat round baru.
- Histori round lama tetap ada.
- Full Paper hanya terbuka setelah Accepted.
- Workflow berjalan tanpa Queue/Worker.
- Policy dan validation diterapkan.
- Feature tests tersedia.
- Fitur existing tidak rusak.
