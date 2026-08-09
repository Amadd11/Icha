# SYSTEM FLOW — ICHA

## A. Conference Portal
Visitor → `/` → Featured/Active Conference → Select Conference → Conference Landing Page.

## B. Conference Context
`/conferences/icha-2026` loads conference by slug.
All conference-specific child data must be queried through the current conference context.

## C. Participant
Register → Login → Select Conference → Complete Profile → Select Registration Type → Create Registration → Payment → Upload Proof → Admin Verification → Paid → Submit Abstract → Admin Checking → Accepted/Revision/Rejected → Full Paper → Presentation → Certificate → Publication.

## D. Abstract Revision
Submitted → Under Review → Revision Required → Participant Updates → Resubmitted → Admin Checking → Accepted/Rejected/Revision Required.

## E. Payment
Registration Created → Pending → Waiting Verification → Admin checks proof → Paid OR Rejected.
Expired may be used when payment deadline passes.

## F. Full Paper
Accepted Abstract → Upload Full Paper → Submitted → Approved OR Revision Required → Re-upload version → Approved.

## G. Admin
Login → Select Current Conference → Manage conference content → Participants → Registrations → Payments → Submissions → Papers → Presentations → Certificates → Publications.

Admin must see the current conference context clearly.

## H. Data Isolation
ICHA 2026 and ICHA 2027 are independent by `conference_id`.

Bad:
`Speaker::all()`

Good:
`$conference->speakers()->get()`

## I. Core Transaction
Request → Form Request validation → Service → DB transaction → Save → Commit → Response.

Optional notification/background work happens after the core transaction and must not determine business success.
