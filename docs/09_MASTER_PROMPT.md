# MASTER PROMPT — ICHA VIBE CODING (REVIEWER REVISED)

Build ICHA using Laravel 13, Inertia.js, Vue 3, Tailwind CSS and MySQL.

Read all project docs before coding.

## Non-Negotiable
1. Multi-conference system.
2. Conference is root entity.
3. Roles: super_admin, admin, reviewer, participant.
4. Reviewer is a real application role.
5. Reviewer first login requires profile completion.
6. Abstract review is blinded.
7. One abstract review round requires three distinct reviewers.
8. Abstract locks after three submitted reviews.
9. Abstract review has two criteria.
10. Each criterion uses five scoring levels.
11. Total >= 5 = ORAL.
12. Total < 5 = POSTER.
13. Historical reviews must be preserved.
14. Revision creates a new review round.
15. Reviewer may access only assigned work.
16. Author identity must not reach reviewer Inertia props in blinded mode.
17. Full-paper review uses the same architecture but exact criteria must not be invented.
18. Core review submission is synchronous.
19. Shared hosting is mandatory target.
20. Use Form Requests, Policies, Services, Eloquent and DB transactions.
21. Keep Participant, Reviewer and Admin layouts/pages separate.

## Review Submission Algorithm
```text
verify reviewer profile
→ verify assignment ownership
→ verify round open
→ validate criteria
→ validate 1..5 scores
→ calculate total
→ calculate recommendation
→ transaction:
   create review
   mark assignment submitted
   count submitted reviews
   if count >= required_reviewers:
      lock round
      calculate final result
→ commit
→ optional notification
```

The supplied guideline is authoritative for abstract review: three blinded reviewers, lock after three reviews, two criteria, five levels, ORAL/POSTER threshold and confirmation flow. fileciteturn2file0L44-L52 fileciteturn2file0L103-L130

Do not use `submissions.reviewer_id`.

Do not invent full-paper criteria.

Before each implementation step inspect the current project and make the smallest coherent change. After implementation report changed files, migrations, routes, authorization, tests and deployment considerations.
