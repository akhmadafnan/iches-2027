# Architecture note — UUID v7, email workflow & reviewer continuity

**Recorded:** 21 November 2026
**Status:** REV-001..003 FINAL UAT PASS and UUID v7 forward policy APPLIED; decision/LoA/Finance notifications NOT IMPLEMENTED. Active contract: [CURRENT STATE & DECISIONS](CURRENT-STATE-AND-DECISIONS.md).
**Sources:** Master Blueprint, PRD AC-06/07 and COM-01/02, TASKS, latest owner direction.
**Safety:** No destructive migration of existing IDs and no outbound email to real users.

## A. UUID v7 database architecture

Source migration audit: core users, programs, conference_tracks, papers, paper_authors, event settings, staff assignments and paper_status_histories currently use BIGINT auto-increment primary keys, with BIGINT foreign IDs.

The existing papers entity contains THREE different identifiers:
- papers.id: internal BIGINT primary key, also used by relational foreign keys
- papers.public_id: unique ULID used by private routes and file paths; ULID is not UUID v7
- papers.reference: permanent human-readable reference (e.g. ICH27-P000003), not a primary key

Laravel 13 supports native UUID v7 generation through HasUuids and Str::uuid7(). UUID v7 is time-ordered; IDs alone do not grant access.

### Forward policy (ARCH-001 applied to REV-002 on 21 November 2026)

1. Preserve existing primary keys, links, owner relationships, real paper data and human Paper IDs. Do NOT convert their types in place under REV-001.
2. For new independent academic business entities beginning REV-002 (e.g. paper review claims, review records), use UUID v7 primary keys via Laravel HasUuids. Relate them to existing users.id and papers.id using matching BIGINT foreignId columns; never point foreignUuid to a BIGINT column.
3. New external URLs for these new entities may use UUID v7 route identifiers. Existing paper routes continue using ULID until an approved compatibility migration.
4. If ALL existing critical primary keys must become UUID v7, create separate ARCH-002 migration work: full FK/external-reference inventory, backup, idempotent backfill with new UUID v7 columns, testing and dual read/writes, URL/file-path compatibility, rollback test, only then possible PK conversion. No destructive migration without explicit authorization.
5. Do not change or regenerate the permanent human-readable Paper ID even after an ID architecture upgrade.

REV-001 remains read-only. REV-002 now applies this forward-key standard through an additive UUID v7 paper_review_claims table without converting any old paper/user primary key. ARCH-002 full legacy conversion remains an unapproved separate project.

## B. Emails required by PRD COM-01 / COM-02

Fortify handles verification and password-reset flows in the existing authentication scaffold. **No academic or operational event mail is currently implemented or sent.** The example environment uses MAIL_MAILER=log, QUEUE_CONNECTION=database and example sender, not confirmed production SMTP.

| Event | Intended recipient | Trigger | Priority |
|---|---|---|---|
| Email Verification / Password Reset | Account holder | Fortify auth events | Existing framework flow; production smoke still needed |
| Abstract Submitted | Paper owner / corresponding contact policy TBD | Committed Draft → Submitted | High; never on Save Draft |
| Paper Claim Confirmed | Claiming reviewer | Committed claim, REV-002 | Medium; not an automatic blast to all reviewers |
| Review Submitted | Authorized academic coordinator | Committed review, REV-003 | Medium; internal only |
| Decision Published (Accept / Reject) | Owning submitter account | One authorized DEC-BULK-001 Publish & Notify batch, correction by authorized authority | P0: after commit, dedupe per paper/event/version, no early leak |
| Full Paper improvement guidance | Accepted author only | Approved General Comments shared after Published Accept | Not a separate abstract revision email or status |
| LoA Available | Owning account of Published Accepted paper | DOC-LOA-001 PDF truly Issued (not just Accepted) | Personal email, no premature link |
| Full Paper Received | Paper owner | Persisted Full Paper version | Medium |
| Payment Proof Received | Payer | Receipt recorded, NOT verification | Medium |
| Payment Verified / Correction Required | Affected payer | Finance-authorized committed action | High |
| Session Schedule Published / Changed | Affected presenter/participant | Approved schedule publish/change | High |
| Certificate Available | Eligible document owner | Explicit certificate publication | Medium |

### Engineering safeguards for MSG-001

- Queue event notifications only AFTER the successful business DB transaction commit; sending failures must be logged and retryable without rolling back committed paper/payment/review.
- Deduplicate by entity + event + version/status transition, with recorded attempts, send status and sent_at. Never claim delivered when the job was merely queued.
- Branded HTML and plaintext templates; authenticate download links; do not attach private PDFs by default.
- After-commit outbox event for each published decision version, correction notices for changed published decisions, recipient = paper-owning account, LoA email only after actual PDF Issued. No reviewer identity/recommendation or override rationale leak and no shared multi-address broadcast.
- Confirm policy for corresponding author, coauthors, opt-outs, sender domain and SMTP credentials before production. Tests use Mail::fake / Notification::fake rather than sending external messages.
- Event-based messages should not introduce invented due-date reminder schedules.

Recommended staged delivery: MSG-000 = event inventory and recipient policy; MSG-001A = Submitted confirmation before submission publicly opens; MSG-001B = Published Accept/Reject Decision and LoA before publishing decisions; MSG-001C = finance, scheduling, documents before their respective launches. These are planning child IDs; the original MSG-001 remains the umbrella PRD requirement.

## C. Reviewer UX contract for REV-001 / 002 / 003

- **REV-001 (current):** single green reviewer workspace, live Available Papers count, filters for title/Paper ID and eight conference tracks, responsive paginated table, icon-only preview, read-only paper details and private PDF download. Exclude Drafts and the reviewer's own papers. Preview makes NO assignment.
- **REV-002 (DONE, owner UAT FINAL PASS):** single-reviewer exclusive self-claim: one active reviewer slot per submitted abstract. Available Papers and My Reviews tabs, Confirm Claim from list/preview, DB unique active_paper_id plus locked transaction, own-paper/coauthor denial. UUID v7 claim PK. Release fields prepared, but no Release UI/automatic expiration without an owner-approved policy. No separate cap per reviewer invented.
- **REV-003 (DONE, owner UAT FINAL PASS; source commit 154d919):** one General Comments field and exactly Accept / Reject recommendation. Save Draft allows incomplete data; Submit Review requires both, confirms, timestamps, records versioned UUID v7 history, and locks review. Only active claim holder can operate. Author sees Review completed date only; the internal recommendation does not auto-publish the decision. See [REV-003](REV-003-ABSTRACT-REVIEW.md).

**Owner UI clarification 21 Nov 2026:** REV-002 continues the REV-001 preview approach: reviewer-facing screens show **Paper ID and academic content but never author name, email or affiliation**; reviewer identity is not displayed to the author in this phase. This is a presentation rule, not proof that the uploaded PDF is anonymized (the PDF may contain identifying text). One active reviewer per abstract removes inter-reviewer disagreements but does not inherently eliminate individual bias; REV-003 has owner-approved General Comments + Accept/Reject only; DEC-BULK-001 replaces legacy DEC-001 as AcademicAuthority publication boundary.

## Decision gates

- Owner REV-001 UAT PASS before code commit.
- ARCH-001 UUID v7 forward standard **applied** for REV-002 claim records; legacy PK conversion remains separate and unapproved.
- Reviewer count is final ONE active reviewer per abstract, UAT PASS. Reviewer UI hides authors, and author does not see reviewer identity; PDF itself might expose author identity without separate anonymization.
- MSG recipient policy and SMTP + queue QA finalized before sending any non-auth email to participants.

## D. New downstream policy (21 Nov 2026)

See [CURRENT STATE & DECISIONS](CURRENT-STATE-AND-DECISIONS.md). Next ACAD-005; then DEC-BULK-001 + MSG-001B, DOC-LOA-001 official PDF/QR, FIN and FULL-001 Verified gate. Manual DEC-001/002, per-paper Google Drive LoA and payment-independent Full Paper are WITHDRAWN. None of the future modules is implemented just because these docs were updated.
