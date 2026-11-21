# DEC-BULK-001 + MSG-001B — OWNER UAT FINAL PASS & CLOSURE

**Date:** 21 November 2026
**State:** **DONE — OWNER UAT PASS** (including the final status-column UI adjustment).
**Scope:** Conference Academic Decisions, permission-gated batch publication and correction, owner-visible decisions and reviewer General Comments, personal after-commit decision/correction notifications.
**Local environment:** Laravel 13 / Livewire 4 / Flux UI Emerald, database `iches_2027`, `MAIL_MAILER=log`, `QUEUE_CONNECTION=database`. Production SMTP and worker deployment not configured.

## 1. Final implemented behavior

- Only explicitly assigned **AcademicAuthority** (authorized Committee/Admin) can open `/academic/decisions`, publish or correct decisions. Admin alone does not bypass delegated authorization.
- **Ready** includes a Submitted paper with an active claim's **Submitted** Accept/Reject review; Draft/unreviewed/released/already published are not accidentally published. Authority has **Ready / Awaiting / Published** tabs, search/track filter, pagination and an accessible checkbox per row.
- Header checkbox selects only the currently visible page, individual selections persist across pages, **Publish Selected** acts only on checked IDs, and **Publish All Ready** processes all eligible IDs regardless of filters/selection. All-ready requires **typed confirmation** `PUBLISH ALL READY`; both modes show a batch preview with paper IDs and counts.
- Reviewer Accept/Reject is an **internal recommendation**. Academic Authority may stage exceptional overrides with a mandatory private reason, preserve the original reviewer recommendation and immutable actor/time/version audit.
- One confirmation **Publish & Notify** safely publishes each still-eligible paper as **Accepted** or **Rejected** with a published timestamp. Author My Submissions, Dashboard, Paper Details and timeline show the published result. **Every submitted reviewer General Comments field is automatically author-visible on publication, regardless of Accepted or Rejected**. Author does not see reviewer identity, internal original recommendation or override/correction rationale.
- A rare **Correction** has a mandatory internal reason, versioned immutable audit, author-visible current result and a distinct personal correction notification. No silent overwrite or second initial publication.
- Per-paper/per-decision-version **personal outbox** and after-commit job; Queued/Sent/Failed, explicit retry and recovery command. Status Sent denotes mailer handoff, **not recipient-inbox delivery**.
- My Submissions index UI post-UAT polish: fixed-size status/date columns, two-line **Published decision / Accepted-or-Rejected** treatment with no badge/date overlap; local Owner confirmed final UAT PASS.

## 2. What was actually verified

- **Owner UAT PASS** was given explicitly after hands-on testing with synthetic Submit, final reviewer decisions, one three-paper batch (Accept, Reject and exceptional Reject→Accept), author-side results and notification worker, plus the index badge/date visual fix. Owner supplied screenshots of published Accepted/Rejected rows and a queue worker transcript showing **four completed jobs**; the audited decision outbox before cleanup had **three Sent** records. These counts measure different things and must not be conflated.
- Automated coverage: authorization, ready criteria, select-page/selected/all, preview/confirm/cancel, override reason, automatic General Comments (including Rejected), correction + privacy + immutable versions, outbox idempotency/retry/recovery, owner dashboard/index rendering. **Final post-cleanup full regression: 242 tests / 1,473 assertions PASS.** Focused Academic Decisions + Submit regression: 39 tests / 256 assertions PASS.
- PHPStan, Laravel Pint, Blade compilation and Vite production build PASS. **Independent authenticated visual-browser automation and a genuine simultaneous multiactor load/stress test were NOT RUN**. Owner's hands-on UAT, not automated browser replay, is the visual acceptance evidence.

## 3. SYNTHETIC TINKER UAT DATA — DELETED AFTER OWNER APPROVAL

Exact cleanup batch: `UATDEC-20261121-nd8dbt7y`, with strict recorded-ID allowlist, verified LOCAL application/database/mail settings and an initial dry-run.

| Entity | Deleted UAT entries |
|---|---:|
| Fake login accounts | 18 |
| Fake paper records | 15 |
| Fake paper author rows | 30 |
| Fake paper status events | 14 |
| Fake reviewer claims | 13 |
| Fake reviewer reviews | 13 |
| Fake review events | 13 |
| Fake published decisions | 3 |
| Fake decision events | 3 |
| Fake decision notification outbox rows | 3 |
| Fake paper PDF directories | 15 |
| Queued/failed UAT jobs found | 0 |

**Post-cleanup verification:** exactly **1 original paper** (`ICH27-P000003`, **Submitted**, owner ID 1), its **1 original reviewer record**, **0 synthetic users**, **0 synthetic papers**, **0 published decisions**, **0 decision outbox entries**, and **0 queued jobs**. Non-UAT paper, track/master data, reviewer review, staff duty assignments, and database migrations were preserved.

The private synthetic batch directory `storage/app/private/uat/dec-bulk-001` (including Tinker seed/verify/cleanup scripts, the cleaned batch manifest and its now-removed credential file) was also deleted after verification. Temporary local hard-coded UAT-only publication guards and their synthetic-only tests were removed from app source after database cleanup; generic permission, selected-batch boundaries, and **typed All Ready confirmation** remain.

**Logging caveat:** Existing Laravel application log files were **not truncated or deleted** because they can contain other diagnostic data. Past simulated email entries may remain in `storage/logs/laravel.log` until ordinary log rotation. No external SMTP emails were sent; the mailer was `log`.

## 4. Code handoff and boundaries

- GitHub: `https://github.com/akhmadafnan/iches-2027`, branch `main`.
- The finalized implementation and this evidence note are in the scoped Git commit closing DEC-BULK-001 + MSG-001B. Use `git log --oneline` for the exact commit hash.
- **NOT implemented by this module:** DOC-LOA-001 official PDF + QR; LoA Available notification (must follow genuine issued document); FIN-001..005, FULL-001 Verified finance gate; MSG-001A email on abstract submit; actual production SMTP and mail deliverability; new public website changes.
- Continue only the next approved work order; do **not** recreate the deleted local synthetic UAT batch or treat it as a permanent database fixture.
