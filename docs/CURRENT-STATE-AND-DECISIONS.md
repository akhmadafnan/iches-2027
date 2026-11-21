# ICHES 2027 — CURRENT STATE, FINAL PRODUCT POLICIES & NEW-CHAT HANDOFF

**Status:** ACTIVE PRODUCT CONTRACT — confirmed by project owner on **21 November 2026**.
**Scope:** ICHES 2027 Laravel/Livewire application hosted by Universitas Islam Syarifuddin (UNISYA), Lumajang.
**Repository:** https://github.com/akhmadafnan/iches-2027 (branch `main`). Local development: `D:\PINJAM-AFNAN\iches-2027`; Herd HTTPS: `https://iches-2027.test` (local device only).
**Completed code evidence:** REV-003 checkpoint `154d919` and ACAD-005 owner UAT PASS on 21 November 2026 (see current Git history and TASKS). **Future DEC-BULK/MSG/LoA/Finance policies are NOT yet coded.**

> **READ THIS FIRST IN EVERY NEW CHAT / CODING SESSION.** This document supersedes conflicting older product-policy text, including archived task/UAT descriptions. Then consult [PRD](PRD.md) for acceptance, [TASKS](TASKS.md) for executable scope, [WORKING-RULES](WORKING-RULES.md) for change gates and the [Master Blueprint](../MASTER-BLUEPRINT-ICHES-2027.md) for the event vision/TOR. When source code and intended policy differ, **implement the documented policy only after explicit owner GO**; never describe planned features as implemented.

## 1. Product mission and architecture

Build a **fast, user-friendly conference and event management app**, not a manual office workflow disguised as software. One Laravel 13 / Livewire 4 / Flux UI Emerald monolith with MySQL, role-scoped private workspaces and a separate public event website. The system supports **five programs** (International Conference, Research/PKM Upgrading, International MoU, International Community Service, Sustainable Destination Study); presenter/co-host/package/finance rules remain distinct. Use existing Laravel OOP actions, policies, events/jobs and transactions as pragmatic implementation details **without forcing extra clicks on users**.

Four base roles: User, Reviewer, Committee, Admin; sensitive **AcademicAuthority** and Finance permissions require explicit assignment (Admin role by itself must not silently grant authority). Program codes, dates, TOR names, prices, document signers and official bank details cannot be invented. Website public design remains a separately scoped task.

## 2. LOCKED academic decisions

1. **ACAD-005 — One owned submission per account.** An account can own **at most one** Conference Paper ID, starting at Draft. Edit the existing Draft; after Submitted, Accepted or Rejected, never create a second paper. **Co-authorship on other people's papers is allowed** and does not count against the owner's one-paper quota. Protect via database owner unique constraint (after read-only duplicate audit), server create/action/policy, and UI. Do not delete or merge historical duplicate data automatically. The **one-paper rule is IMPLEMENTED and OWNER UAT PASS (21 Nov 2026)**: additive migration `000008` enforces a unique owner, with Policy/transaction/CTA guards. The existing paper IDs and reviewer history were preserved.
2. **REV-001 to REV-003 — already DONE.** Submitted abstracts are reviewer-eligible without administrative screening. One abstract has **one active reviewer** assigned by exclusive self-claim; claimant cannot be paper owner or verifiable coauthor (email conflict). Review UI shows Paper ID/title/track/abstract/keywords/PDF but **no author name, email or affiliation in UI**. Original uploaded PDF may itself reveal identity; do not claim it was anonymized. Reviewer fills **one General Comments** field and selects only **Accept** or **Reject**. Save Draft allowed incomplete; Submit Review requires both; submitted review is locked, timestamped and auditable. No score or Revisions Required, and no second abstract revision submission. Reviewer recommendation is **internal**, not yet a published final decision.
3. **DEC-BULK-001 — Replaces legacy DEC-001/DEC-002 per-paper clicks.** Authorized AcademicAuthority sees Review Submitted papers in a single Academic Decisions workspace. Default proposed decision = reviewer Accept/Reject. Authority can make **exception overrides**, especially Reject → Accepted, with mandatory internal reason and audit (actor/time/before/after). Allow all-ready and selected-batch processing; one **Publish & Notify** confirmation authorizes a safe, idempotent batch. Papers still Claimed/Draft/no review or previously published are skipped. On successful publication, Accepted/Rejected and publish timestamp become author-visible; release only approved General Comments, **never reviewer identity/internal recommendation/override rationale**. One published decision per paper with versioned correction history. Authorized **Correct Published Decision** can amend a previously published decision, explicitly logs reason, adjusts downstream entitlements and queues a correction email. A review alone cannot change author status.
4. **MSG-001B — Decision emails are part of the bulk publish capability, not an optional later manual task.** For each publication, send a **personal decision email to the owning submitter account** through an idempotent after-commit outbox/queue, with retry/failure visibility. Never blast recipient addresses; never leak internal reviews. Published dashboard status is immediate after a valid committed operation; inbox delivery may be delayed or fail. Differentiate Queued / Sent / Failed from actual provider-confirmed Delivered. Real SMTP/sender domain/queue workers require separate production configuration and QA. Use `MAIL_MAILER=log` / fakes in development. `MSG-001A` Abstract Submitted email is an independent prerequisite before real public abstract submission is opened.
5. **DOC-LOA-001 — Automatically generated official LoA, NO Google Drive handoff.** One **currently valid Letter of Acceptance per Accepted Published paper**, listing author/coauthors as saved. Use one formally approved English master template, approved UNISYA/ICHES identity, letter-number scheme and authorized official signatory/signature. At Published Accepted, enqueue an idempotent PDF generation job, snapshot paper/title/author/track + issuance/template version, assign a unique official document number, produce PDF on private storage and display **Preview / Download / Print** to the owning account. Embed a **secure random verification token QR** linked to a rate-limited public verification page (Valid / Revoked / Superseded / Not Found) with deliberately minimal published metadata. No public PDF directory; QR verifies database record, not itself a certified electronic signature. Keep audit/checksum/reissue/revoke/correction flow; no silent overwrite. Failed generation is retryable per document without retracting a valid Accepted decision. **LoA does not require payment Verified.** Both automatic generation and public QR verification are **APPROVED, NOT IMPLEMENTED**.
6. **FIN + FULL-001 — NEW hard payment gate.** Published Accepted unlocks invoice/payment and automatic LoA, **NOT** the Full Paper upload. Regular presenter must upload bank-transfer proof and have Finance-authorized **Verified** after checking official bank mutation; `Pending`, `Proof Uploaded` or `Correction Required` do not unlock. Full Paper is enabled only when `Published Accepted AND applicable payment entitlement Verified`, checked server-side for view/upload/download actions, not only button visibility. For verified sponsored Co-Host papers, a properly linked verified **institutional payment entitlement** satisfies the obligation; never issue a duplicate presenter invoice. Rejected authors may still view their own archived result and shareable comments but cannot proceed to LoA/payment/Full Paper, unless an authorized published correction changes the decision. Reversals after financial transactions require separate Finance reconciliation and audit. **Payment gate is APPROVED, NOT IMPLEMENTED**.
7. **No extra per-paper bureaucracy.** Do not reintroduce manual Internal Decision and Publish clicks per abstract, second review, abstract revision workflow, per-paper LoA/Drive uploading, per-paper mass-email editing, fabricated LoA signer/number, or mandatory Full Paper before finance verification.

## 3. Intended state machine — distinguish independent domains

```text
ONE OWNER ACCOUNT -> ONE PAPER: Draft -> Submitted
Submitted -> available for exclusive reviewer Claim -> Review Draft -> Review Submitted (Accept/Reject recommendation, INTERNAL)
Review Submitted -> AcademicAuthority exception review -> one Bulk Publish & Notify confirmation
  -> Published Accepted -> author sees decision + approved General Comments
       -> automatic LoA PDF + QR (independent of payment)
       -> Invoice / bank transfer -> Proof Uploaded -> Finance Verified
       -> Full Paper upload permitted (same Paper ID)
  -> Published Rejected -> read-only decision + approved General Comments, downstream access BLOCKED

Authorized published decision correction:
  Rejected -> Accepted: publish correction, new eligibility + LoA/invoice workflows + correction email
  Accepted -> Rejected: published correction, revoke applicable LoA/entitlements, reconcile any payment separately
```

**Never conflate** reviewer recommendation, publication status, LoA issue status, payment verification, Full Paper eligibility, session Presented/No Show, and certificate availability.

### Timeline visibility
- Reviewer sees submission, own Claim, Draft save and Review Submitted timestamps (WIB).
- Before Publish, author sees Draft created → Submitted → Abstract claimed → Review completed, with timestamps, **no reviewer identity or private recommendation**.
- After Publish, author sees Published Accepted/Rejected and publish timestamp plus General Comments approved for sharing.
- LoA status (Pending/Generating/Issued/Failed/Revoked/Superseded) and payment/Full Paper status are separate, truthful user-facing cards.

## 4. Existing modules vs new implementation

| Task | Actual status on 21 Nov 2026 |
|---|---|
| FND-001..006 | DONE, owner UAT PASS |
| ACAD-001..004 | DONE, owner UAT PASS |
| REV-001..003 | DONE, owner UAT FINAL PASS; commit `154d919` is REV-003 |
| ARCH-001 UUID v7 forward policy | APPLIED to new claims/reviews/events; existing users/papers BIGINT, paper public ULID and human Paper ID unchanged |
| ACAD-005 one owned paper | **DONE — OWNER UAT PASS (21 Nov 2026)**; DB unique owner, policy/transaction and contextual UI; full regression 214 tests / 1,289 assertions PASS. Independent Edge visual QA and dedicated concurrent-request stress NOT RUN |
| DEC-BULK-001 bulk publish/override/corrections | POLICY APPROVED; TODO |
| MSG-001A abstract submitted mail | PLANNED, not implemented; do before public submission |
| MSG-001B decision notification/outbox | POLICY APPROVED; TODO, tightly integrated with DEC-BULK |
| DOC-LOA-001 auto PDF + QR | POLICY APPROVED; TODO |
| FIN-001..004 / FIN-005, FULL-001 | POLICY APPROVED; TODO |
| Session, QR service/ID cards, other programs, attendance, certificates | Other backlog work; do not imply complete |

Current UAT sample: `ICH27-P000003` has a submitted internal reviewer **Accept** review; this is **not** an author-visible accepted decision or issued LoA. Avoid modifying real UAT records without explicit owner instruction.

## 5. Delivery order / dependencies

1. **DOC-POLICY-REFRESH (this documentation task)** — synchronize Blueprint, PRD, TASKS, README, AGENTS and this contract; documentation-only, no source migration.
2. **ACAD-005 (DONE — OWNER UAT PASS)** — audited 0 duplicate owners, applied owner unique index on local MySQL; guards and regression completed, existing paper preserved. Source commit/push follows the owner PASS.
3. **DEC-BULK-001 + MSG-001B foundation** — authority-scoped review list, overrides/corrections, atomic idempotent bulk publish, author status/timeline and persisted outbox queued messages; admin preview of recipient/batch counts, delivery status, retry. One confirmation for batch, never N manual decisions.
4. **DOC-LOA-001** — master template approval, official numbering/authorized signatory, generation queue, secure verification, private PDF, reissue/revoke; notify LoA Available upon genuine Issued.
5. **FIN-001..005** — bank settings, invoice + payer-price snapshots, proof upload, bank-mutation verification, audit, Co-Host institutional entitlement and no duplicate invoice.
6. **FULL-001 (replaces old DEC-004 description)** — same Paper ID, only Published Accepted plus Verified applicable payment entitlement. Cover bypasses and test Co-Host.
7. Remaining program / QR / session / certificate tasks as prioritized; existing owner-approved policy remains authoritative.

**Important schedule issue (not silently decided):** Legacy TOR windows have Full Paper 23–28 Dec 2026 but payment 24–28 Dec 2026. With newly required Verified gate, organizers may need to revise their announced deadlines or plan fast verification. **Do not bypass payment requirement, change dates, or invent a new deadline.** Resolve before production.

## 6. Details requiring official inputs (not barriers to initial code scaffolding)

- Formal LoA logo, English wording, letter-number format, authorized signatory name/title and approved signature asset; verification domain not yet public. An unsigned/unapproved template cannot be marked an issued official LoA.
- Official registration fees/categories and bank account (never fabricate); payment-to-Co-Host entitlement mapping must be confirmed on real data.
- Email sending domain/transport, rate limits, legal recipient policy and production queue configuration; decision mail should default to paper-owner account only. Explicit release/sending requires operational authorization.
- Authority for published-decision corrections and any reverse Accepted → Rejected policy is sensitive. Require explicit assigned AcademicAuthority with audit and financial reconciliation.
- General Comments are shared after publication per current owner design; internal override reasons are never shared.
- Existing duplicate papers must be audited before adding `UNIQUE(papers.owner_id)`, with no destructive auto-repair.

## 7. Non-negotiable working constraints

- Do **not** code solely because policies appear here; wait for specific owner GO and task scope.
- Keep approved REVIEW 001–003 UAT behavior and UI, paper identifiers, private storage, authors/ROR, official tracks intact.
- Use Laravel Actions/Policies/DB transactions/constraints and Livewire Flux Emerald UI. Test authorization, idempotency/concurrency, failure cases, desktop/mobile UI, no secret leakage; no fictitious data.
- Follow [WORKING-RULES](WORKING-RULES.md). Never `migrate:fresh` on the user's data, force-push, send real bulk emails or deploy without explicit authorization. For **source changes**, seek owner UAT before scoped commit/push.
- Docs may be updated independently on user request; **documentation changes do not mark future modules DONE**.
- Historical task evidence may describe the old product; it is **archived evidence, not active business policy**.

## 8. Suggested new-chat handoff

> Continue the ICHES 2027 Laravel Livewire project at `D:\PINJAM-AFNAN\iches-2027` / `akhmadafnan/iches-2027`. Read `docs/CURRENT-STATE-AND-DECISIONS.md` FIRST, then task-specific `docs/PRD.md`, `docs/TASKS.md`, `docs/WORKING-RULES.md`. Owner UAT passed FND-001..006, ACAD-001..005, REV-001..003; ACAD-005 done and its implementation recorded in TASKS. New locked policy: one owned paper per account but coauthors allowed; one reviewer per abstract, General Comments + Accept/Reject; authorize bulk Publish & Notify with exception overrides/corrections; auto LoA PDF+QR (no Drive); Accepted enables LoA and payment, but Full Paper only after Finance Verified; Rejected ends progress; email via after-commit idempotent queue. **DEC-BULK-001 + MSG-001B are the next coding scope only upon owner GO.** No DEC/LoA/finance modules are yet implemented; no code without explicit authorization.
