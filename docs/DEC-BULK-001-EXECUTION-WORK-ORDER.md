# DEC-BULK-001 + MSG-001B — Execution Work Order

**Status:** PREPARED / AUDITED — NO CODING UNTIL OWNER GO.
**Code baseline:** ACAD-005 owner UAT FINAL, commit 71aa86a, branch main.
**Precedence:** CURRENT-STATE-AND-DECISIONS.md > PRD.md > TASKS.md. This document only operationalizes approved policies.

## Verified starting state (read-only, 21 Nov 2026)

- FND-001..006 / ACAD-001..005 / REV-001..003 owner UAT PASS. Do not change reviewer production code without narrowly justified dependency.
- Papers currently support Draft/Submitted only. Reviews support Draft/Submitted and Accept/Reject; UUID v7 reviews and active reviewer claim uniqueness exist.
- Existing publish-decisions Gate permits Committee or Admin WITH an explicitly assigned global academic_authority duty. Base Admin alone does not qualify; assignment is managed at /admin/users.
- Runtime sample: 1 Submitted paper, 1 active final review; 0 assigned Academic Authorities. Preserve UAT sample ICH27-P000003 and its existing reviewer result.
- No decision entity, published correction audit, decision outbox or Academic Decisions workspace exists.
- Development mail driver = log, queue driver = database; database queue after_commit = false. Jobs must use explicit afterCommit dispatch.

## Outcome and workspace

1. Create gate-protected /academic/decisions workspace, with scoped sidebar link. Tabs: Ready, Awaiting Review and Published; searchable Paper ID/title, track filter, counts, pagination, responsive Flux Emerald UI.
2. Ready list shows source reviewer Accept/Reject proposed result, final review time, and private review preview. Ordinary rows use recommendation automatically; do NOT require an internal decision edit for every paper.
3. Permit exception overrides through a small modal requiring an INTERNAL reason and old/new result; do not modify the actual submitted reviewer recommendation.
4. Support Publish All Ready and Publish Selected, including selection across pages; one Publish & Notify confirmation shows selected, accepted, rejected, overridden, skipped and recipient counts. Server revalidates all IDs at confirmation.
5. Publish only Submitted papers with an ACTIVE claimant's Submitted review and valid recommendation, with no prior published decision. Review Draft, unreviewed, released and already published are skipped.
6. General Comments can be shared only as an authority-approved PUBLIC SNAPSHOT. Allow withholding an identifying/sensitive comment without preventing bulk publication. No reviewer name/email, hidden recommendation or internal override rationale on author payload.
7. Rare authorized Correct Published Decision action is separate from ordinary bulk publication. It requires an internal reason and confirmation, immutable versioned before/after events, owner status update and distinct correction notification.

## Persistence and concurrency

- Add Accepted/Rejected published outcomes to PaperStatus. Preserve existing Paper ID, public ULID, owner, authors, submission_version, reviews and historical timeline.
- Use additive schema with one unique current decision per paper (e.g., paper_decisions with UUID v7 PK) and immutable decision events (unique paper + decision version). Track source review, current outcome, actor/time, approved public comment snapshot, and internal override/correction reason separately.
- Use explicit row locks/transactions and database unique keys. A single UI batch action can process eligible paper records in bounded per-paper transactions, with truthful published/skipped/failed results; avoid one long lock across 300 papers.
- Handle stale selection, double click, concurrent authorities and partial worker failures. Reviewer Submit alone never publishes.
- Update owner PaperDetails, My Submissions, Dashboard and PaperActivityTimeline so Accepted/Rejected status, date and only approved comments appear truthfully. Keep Draft/Submitted behavior intact.
- Do not open LoA, invoice, payment or Full Paper as an invented completed capability. Future LoA/Finance subscribers will use the persisted decision/correction events.

## MSG-001B integrated outbox

- Within each successful decision transaction, insert one immutable/idempotent personal outbox record per owner email + paper + event type + decision version; snapshot recipient and approved public payload.
- Dispatch database queue Job explicitly after commit. Maintain Queued / Sent / Failed, attempts, errors, operator retry and recovery from crash between commit and dispatch.
- Separate events: Decision Published, Decision Corrected; LoA Available is reserved until DOC-LOA-001 genuinely issues a document.
- Dev mailer remains log/fake, no real production email/recipient blast, no fabricated Delivered status. Failure to send must not roll back an accepted published decision.

## Scope boundaries

Permitted: additive migrations, new decision/outbox Models/Enums/Actions/Jobs/Mail/Livewire views, PaperStatus, Paper relations, PaperActivityTimeline and owner UI, routes/sidebar, focused tests. Reuse existing staff gate, Laravel OOP patterns and UUID v7 policy.
Out of scope: reviewer redesign, admin screening, second reviewer, scoring/revisions, DOC-LOA PDF, Finance, Full Paper, MSG-001A, public website, real SMTP credentials, changing existing UAT records.

## Acceptance / QA checklist

- Unauthorized Admin without AcademicAuthority => 403; assigned Committee/Admin => allowed, with server Action guard.
- Ready / not-ready / already-published checks match active final review; proposed decision unchanged until Publish.
- Default bulk outcome and exceptional override (mandatory reason) PASS; no 300 manual per-paper clicks.
- All-ready/selected, stale selection, double-submit, concurrent publication and database uniqueness PASS.
- Versioned corrections preserve old event and create one unique correction outbox entry.
- Author status/date/approved comment visible; reviewer identity, private reviewer recommendation and override reason never leak in UI/API/mail.
- After-commit mail fake/log, idempotency, worker retry/recovery, independent queue state and failure behavior PASS.
- Existing reviewer regressions PASS; focused tests, full regression, Pint, PHPStan, Blade, build and browser desktop/mobile QA are recorded, not assumed.
- Confirm real data preservation, scope-only diff, then OWNER UAT. **No source commit/push before UAT PASS.**

## Execution gate

Planning document only. No DEC-BULK migration, source implementation, outbound mail, AcademicAuthority assignment, or UAT data changes until the owner authorizes GO CODING DEC-BULK-001 + MSG-001B.
