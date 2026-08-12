# EDGE CASES — ICHA Conference Management System

## Purpose

Dokumen ini menjadi checklist wajib sebelum dan selama implementasi ICHA. Fokusnya adalah keamanan akses, integritas data, concurrency, status workflow, blinded review, multi-conference isolation, file, dan kegagalan layanan non-kritis.

Core transaction harus tetap synchronous dan tidak bergantung pada queue/worker karena target deployment adalah shared hosting.

---

# 1. Reviewer Access & Authorization

## 1.1 Reviewer membuka assignment milik reviewer lain

**Scenario:** Reviewer A mencoba membuka assignment Reviewer B.

**Expected:** `403 Forbidden`, tanpa membocorkan data assignment.

Reviewer hanya boleh mengakses assignment dengan:

```text
review_assignments.reviewer_id = authenticated_user.id
```

Gunakan Policy, bukan hanya pengecekan Vue.

## 1.2 Reviewer submit assignment orang lain

Request harus ditolak dan review tidak boleh dibuat.

## 1.3 Reviewer submit setelah assignment selesai

Jika status sudah `submitted`, request kedua harus ditolak.

---

# 2. Reviewer Profile

## 2.1 Profile belum lengkap

Reviewer yang belum melengkapi profile diarahkan ke:

```text
/reviewer/profile
```

dan tidak dapat melakukan review.

## 2.2 Manipulasi status profile

Backend tetap memeriksa kelengkapan profile. Jangan percaya flag dari frontend.

---

# 3. Blinded Review

Reviewer boleh melihat:

```text
Submission ID
Title
Abstract
Keywords
Topic
```

Reviewer tidak boleh melihat:

```text
Author name
Author email
Institution
Phone
Corresponding author
Participant account information
```

Blinding wajib diterapkan di backend. Jangan mengirim author melalui Inertia lalu hanya menyembunyikannya dengan Vue.

Jika reviewer mencoba endpoint/resource lain untuk mengambil author, backend tetap harus menolak atau menghilangkan data tersebut.

---

# 4. Reviewer Assignment

## 4.1 Duplicate assignment

Reviewer yang sama tidak boleh ditambahkan dua kali pada review round.

Database:

```text
unique(review_round_id, reviewer_id)
```

## 4.2 Belum ada tiga reviewer

Jika baru ada 1/3 atau 2/3 reviewer, round belum dianggap selesai.

## 4.3 Reviewer keempat

Jika sudah:

```text
Reviewer A → submitted
Reviewer B → submitted
Reviewer C → submitted
```

Reviewer D harus ditolak karena round sudah locked.

---

# 5. Three-Reviewer Locking

Ketika menjadi `3/3`, system harus dalam satu transaction:

1. menyimpan review ketiga
2. mengubah assignment menjadi submitted
3. mengunci review round
4. mengisi `locked_at`
5. menghitung/finalisasi hasil
6. mencegah review tambahan

## Concurrent submission

Jika dua reviewer submit hampir bersamaan, sistem tetap harus menghasilkan kondisi valid:

```text
max 3 submitted reviews
locked_at terisi ketika mencapai 3
```

Gunakan `DB::transaction()` dan row locking/concurrency protection yang sesuai. Jangan mengandalkan frontend.

---

# 6. Review Scoring

Score harus:

```text
integer
minimum 1
maximum 5
required
```

Tolak:

```text
0
6
10
3.5
null
```

Frontend tidak boleh menjadi sumber kebenaran.

Contoh request:

```json
{
    "criteria_1_score": 3,
    "criteria_2_score": 4,
    "total_score": 10
}
```

Backend harus menghitung:

```text
3 + 4 = 7
```

`total_score` dari client tidak dipercaya.

Recommendation juga dihitung backend:

```text
total >= 5 → oral
total < 5  → poster
```

---

# 7. Review Submission

## 7.1 Double click

Reviewer menekan submit dua kali.

**Expected:** hanya satu review dibuat.

Gunakan:
- frontend loading state
- backend validation
- transaction
- database constraint jika diperlukan

## 7.2 Refresh setelah submit

Refresh tidak boleh membuat review kedua.

## 7.3 Browser back

Reviewer tidak boleh mengirim review kembali setelah assignment submitted.

---

# 8. Review Confirmation

Reviewer harus melakukan confirmation sebelum review menjadi submitted.

Jika cancel:
- review belum tersimpan sebagai submitted
- reviewer kembali ke form

---

# 9. Review History & Revision

Jika `revision_required`:

```text
Review Round 1
    ↓
Revision Required
    ↓
Participant Resubmit
    ↓
Review Round 2
```

Jangan overwrite review Round 1.

Historical reviews harus tetap tersimpan.

Assignment untuk round baru harus dibuat secara eksplisit. Reviewer lama tidak otomatis mendapat assignment baru kecuali business rule menetapkannya.

---

# 10. Conference Isolation

ICHA adalah multi-conference system.

Reviewer tidak boleh melihat assignment conference lain tanpa assignment yang valid pada conference tersebut.

Participant juga tidak boleh mengakses submission conference lain.

Admin harus bekerja pada conference yang sedang dipilih.

Jangan menggunakan ID sebagai satu-satunya security boundary.

---

# 11. User Status

Jika reviewer dinonaktifkan:

- tidak dapat login/melakukan review baru
- historical reviews tetap tersimpan

Sebaiknya gunakan deactivation/soft delete daripada hard delete untuk user yang memiliki historical data.

---

# 12. Submission Status

Status transition harus dikontrol.

Contoh:

```text
draft
  ↓
submitted
  ↓
admin_checking
  ↓
reviewer_assignment
  ↓
under_review
  ↓
revision_required
  ↓
resubmitted
  ↓
accepted / rejected
```

Invalid transition seperti:

```text
draft → accepted
```

harus ditolak.

Status transition dikelola di Service/Domain logic, bukan bebas dari Controller.

---

# 13. File Upload

Tangani:

- file terlalu besar
- extension tidak valid
- MIME/type tidak valid
- file corrupt
- upload gagal
- file diganti
- file dihapus
- unauthorized download

File private harus melalui authorization sebelum download.

Jangan expose path private secara langsung.

Jika database record dan file storage membutuhkan dua langkah, pastikan tidak ada record file yatim ketika upload gagal.

---

# 14. Payment

Tangani:

- payment pending
- payment rejected
- payment expired
- duplicate payment submission
- admin memverifikasi payment dua kali

Status transition harus idempotent dan tidak menghasilkan side effect ganda.

Aturan urutan payment → abstract/paper harus mengikuti business rule final, bukan asumsi.

---

# 15. Certificate

Jika participant belum memenuhi syarat, certificate tidak boleh dibuat.

Generate certificate harus idempotent dan menggunakan unique certificate number.

Jangan membuat dua certificate untuk participant + conference yang seharusnya hanya memiliki satu certificate.

---

# 16. Publication

Submission yang belum approved tidak boleh dianggap published.

Menghapus publication tidak boleh menghapus historical submission.

---

# 17. Notification

Notification/email adalah non-critical.

Jika review berhasil disimpan tetapi email gagal:

```text
Review transaction
      ↓
COMMIT
      ↓
Optional notification
      ↓
Email gagal
      ↓
Review tetap tersimpan
```

Jangan rollback core review transaction hanya karena email gagal.

---

# 18. Session & Security

Tangani:

- session expired saat submit
- CSRF
- unauthorized request
- role escalation
- direct URL access

Jangan hanya mengandalkan:

```text
v-if="user.role === 'reviewer'"
```

Authorization wajib dilakukan backend menggunakan middleware/policy.

---

# 19. Shared Hosting

Core system tidak boleh bergantung pada:

```text
Redis
Supervisor
Queue Worker
WebSocket
Docker
```

Core flow:

```text
HTTP Request
 ↓
Controller
 ↓
Form Request / Policy
 ↓
Service
 ↓
DB Transaction
 ↓
Response
```

Jobs hanya boleh digunakan untuk pekerjaan non-critical jika hosting mendukungnya.

---

# 20. Automated Testing Checklist

## Authentication
- reviewer login
- participant login
- admin login
- inactive reviewer

## Authorization
- reviewer own assignment
- reviewer assignment orang lain
- participant submission orang lain
- conference isolation

## Reviewer Profile
- incomplete profile
- complete profile
- invalid profile

## Review
- valid score
- score < 1
- score > 5
- missing score
- duplicate submit
- review after locked
- wrong reviewer
- blinded data

## Calculation

```text
1 + 1 = 2 → POSTER
2 + 2 = 4 → POSTER
2 + 3 = 5 → ORAL
5 + 5 = 10 → ORAL
```

## Three Reviewer Rule

```text
1/3 → open
2/3 → open
3/3 → locked
4th → rejected
```

## Revision
- new review round
- old reviews preserved
- cross-round access prevented

## File
- invalid extension
- oversized file
- unauthorized download

## Payment
- duplicate verification
- invalid status transition

---

# Priority

## P0 — Wajib sebelum production

- reviewer authorization
- blinded review
- duplicate submission protection
- score validation
- server-side calculation
- 3-reviewer locking
- concurrent submission protection
- review history
- conference isolation
- file authorization
- role/policy protection

## P1 — Wajib sebelum MVP selesai

- reviewer profile completion
- revision rounds
- payment status protection
- certificate idempotency
- invalid status transitions
- session handling

## P2 — Setelah MVP

- retry notification
- advanced audit log
- advanced reporting
- reviewer workload optimization
- additional analytics

---

# Vibe Coding Rule

AI coding agent MUST check this document before implementing any workflow.

For every feature, ask:

1. What happens if the request is sent twice?
2. What happens if the user changes the request manually?
3. What happens if the user does not have permission?
4. What happens if two requests happen simultaneously?
5. What happens if the status is already completed?
6. What happens if the related record belongs to another conference?
7. What happens if a file is invalid or inaccessible?
8. What happens if an optional notification fails?

Never implement only the happy path.
