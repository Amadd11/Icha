# DATABASE RULES — ICHA

## Naming
- Tables: plural snake_case
- Columns: snake_case
- Foreign keys: `{model}_id`
- Models: singular StudlyCase

Examples:
`Conference`, `conference_id`, `registration_types`.

## Foreign Keys
Conference-specific tables should contain:
`conference_id`

Use:
`$table->foreignId('conference_id')->constrained()->cascadeOnUpdate();`

Choose delete behavior carefully. Operational data should generally be protected from accidental cascading deletes.

## Status
Prefer PHP backed enums or constants for complex statuses once the project structure supports them. Keep database values aligned with the PRD.

## Money
Do not use floating point for money. Use decimal, for example:
`decimal('price', 15, 2)`

Store currency explicitly when multiple currencies may be supported.

## Files
Store paths, not file binaries, in database columns.
Example:
`proof_file`, `file_path`, `hero_image`.

## Timestamps
Use Laravel timestamps.

## Soft Deletes
Use SoftDeletes on records where recovery is valuable, especially CMS content and operational records, after considering foreign key behavior.

## Data Integrity
Business rules should be enforced at both:
- application/service layer
- database constraints where practical

Examples:
- unique conference slug
- unique registration number
- unique certificate number
- foreign keys
- required conference context

## Indexing
Index:
- conference_id
- status
- dates used for filtering
- registration_number
- certificate_number
- slug

Use composite indexes when query patterns justify them.
