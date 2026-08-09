# ERD — ICHA

## Core Relationship

Conference is the root of conference-specific data.

```text
Conference
├── Speakers
├── Categories
├── Committee Members
├── Timelines
├── Registration Types
├── Sponsors
├── Registrations
│   └── Payments
└── Submissions
    ├── Authors
    ├── Paper Versions
    ├── Presentations
    ├── Certificates
    └── Publications
```

## Suggested Tables

### conferences
id, title, slug, year, tagline, description, theme, start_date, end_date, venue, city, country, email, phone, website, logo, hero_image, status, is_active, timestamps

### users
id, name, email, password, role, timestamps

Roles:
super_admin, admin, participant

### participants
id, user_id, affiliation, phone, country, address, profile fields, timestamps

### categories
id, conference_id, name, description, sort_order, timestamps

### speakers
id, conference_id, name, title, institution, country, biography, photo, sort_order, timestamps

### committee_members
id, conference_id, name, role, institution, photo, sort_order, timestamps

### timelines
id, conference_id, title, description, date, sort_order, timestamps

### registration_types
id, conference_id, name, description, price, currency, registration_deadline, timestamps

### registrations
id, conference_id, participant_id, registration_type_id, registration_number, status, registered_at, timestamps

A participant may have separate registrations for different conferences.

### payments
id, registration_id, amount, payment_method, proof_file, paid_at, verified_at, verified_by, status, notes, timestamps

### submissions
id, conference_id, participant_id, category_id, registration_id, title, abstract, keywords, status, submitted_at, timestamps

### authors
id, submission_id, name, email, institution, country, is_corresponding, is_presenter, author_order, timestamps

### paper_versions
id, submission_id, version, file_path, status, submitted_at, notes, timestamps

### presentations
id, submission_id, type, room, presentation_date, start_time, end_time, file_path, status, timestamps

### certificates
id, conference_id, participant_id, registration_id, submission_id nullable, type, certificate_number, file_path, issued_at, timestamps

### publications
id, conference_id, submission_id nullable, type, name, volume, issue, doi, url, publication_date, status, timestamps

### sponsors
id, conference_id, name, logo, website, sponsorship_level, sort_order, timestamps

### faqs
id, conference_id nullable, question, answer, sort_order, timestamps

## Foreign Key Rules
Use foreign keys with appropriate `cascade`/`restrict` behavior.

Conference deletion should normally be restricted once operational records exist. Prefer archiving over deleting a conference.

## Unique Constraints
Recommended:
- conferences.slug unique
- conferences.year may be indexed
- registration_number unique
- certificate_number unique
- optionally `(conference_id, title)` only if business rules require it

## Important Rule
Never use a global child query where the page requires conference-specific data without applying `conference_id`.
