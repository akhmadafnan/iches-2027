# Architecture note — UUID v7, email workflow & reviewer continuity

**Recorded:** 21 November 2026
**Status:** Planning recorded; legacy-ID conversion and transactional emails NOT IMPLEMENTED.
**Sources:** Master Blueprint, PRD AC-06/07 and COM-01/02, TASKS, latest owner direction.
**Safety:** No destructive migration of existing IDs and no outbound email to real users.

## A. UUID v7 database architecture

Source migration audit: core users, programs, conference_tracks, papers, paper_authors, event settings, staff assignments and paper_status_histories currently use BIGINT auto-increment primary keys, with BIGINT foreign IDs.

The existing papers entity contains THREE different identifiers:
- papers.id: internal BIGINT primary key, also used by relational foreign keys
- papers.public_id: unique ULID used by private routes and file paths; ULID is not UUID v7
- papers.reference: permanent human-readable reference (e.g. ICH27-P000003), not a primary key

Laravel 13 supports native UUID v7 generation through HasUuids and Str::uuid7(). UUID v7 is time-ordered; IDs alone do not grant access.

### Recommended forward policy (ARCH-001; pending owner final decision)

1. Preserve existing primary keys, links, owner relationships, real paper data and human Paper IDs. Do NOT convert their types in place under REV-001.
2. For new independent academic business entities beginning REV-002 (e.g. paper review claims, review records), use UUID v7 primary keys via Laravel HasUuids. Relate them to existing users.id and papers.id using matching BIGINT foreignId columns; never point foreignUuid to a BIGINT column.
3. New external URLs for these new entities may use UUID v7 route identifiers. Existing paper routes continue using ULID until an approved compatibility migration.
4. If ALL existing critical primary keys must become UUID v7, create separate ARCH-002 migration work: full FK/external-reference inventory, backup, idempotent backfill with new UUID v7 columns, testing and dual read/writes, URL/file-path compatibility, rollback test, only then possible PK conversion. No destructive migration without explicit authorization.
5. Do not change or regenerate the permanent human-readable Paper ID even after an ID architecture upgrade.

REV-001 is read-only and adds no new database table; it can proceed safely while ARCH-001 is decided. **ARCH-001 choice required before REV-002 schema.**

## B. Emails required by PRD COM-01 / COM-02

Fortify handles verification and password-reset flows in the existing authentication scaffold. **No academic or operational event mail is currently implemented or sent.** The example environment uses MAIL_MAILER=log, QUEUE_CONNECTION=database and example sender, not confirmed production SMTP.

| Event | Intended recipient | Trigger | Priority |
|---|---|---|---|
| Email Verification / Password Reset | Account holder | Fortify auth events | Existing framework flow; production smoke still needed |
| Abstract Submitted | Paper owner / corresponding contact policy TBD | Committed Draft → Submitted | High; never on Save Draft |
| Paper Claim Confirmed | Claiming reviewer | Committed claim, REV-002 | Medium; not an automatic blast to all reviewers |
| Review Submitted | Authorized academic coordinator | Committed review, REV-003 | Medium; internal only |
| Decision Published | Author/paper owner | Explicit Publish Decision, not internal decision | High; no early leak |
| Revision Required | Author/paper owner | Published revision decision | High |
| LoA Available | Eligible paper owner | Explicit LoA release after Accepted Published | High |
| Full Paper Received | Paper owner | Persisted Full Paper version | Medium |
| Payment Proof Received | Payer | Receipt recorded, NOT verification | Medium |
| Payment Verified / Correction Required | Affected payer | Finance-authorized committed action | High |
| Session Schedule Published / Changed | Affected presenter/participant | Approved schedule publish/change | High |
| Certificate Available | Eligible document owner | Explicit certificate publication | Medium |

### Engineering safeguards for MSG-001

- Queue event notifications only AFTER the successful business DB transaction commit; sending failures must be logged and retryable without rolling back committed paper/payment/review.
- Deduplicate by entity + event + version/status transition, with recorded attempts, send status and sent_at. Never claim delivered when the job was merely queued.
- Branded HTML and plaintext templates; authenticate download links; do not attach private PDFs by default.
- Separate email event publication from internal academic decision visibility; never leak non-published decisions or staff-only comments.
- Confirm policy for corresponding author, coauthors, opt-outs, sender domain and SMTP credentials before production. Tests use Mail::fake / Notification::fake rather than sending external messages.
- Event-based messages should not introduce invented due-date reminder schedules.

Recommended staged delivery: MSG-000 = event inventory and recipient policy; MSG-001A = Submitted confirmation before submission publicly opens; MSG-001B = Published Decision, revision and LoA before publishing decisions; MSG-001C = finance, scheduling, documents before their respective launches. These are planning child IDs; the original MSG-001 remains the umbrella PRD requirement.

## C. Reviewer UX contract for REV-001 / 002 / 003

- **REV-001 (current):** single green reviewer workspace, live Available Papers count, filters for title/Paper ID and eight conference tracks, responsive paginated table, icon-only preview, read-only paper details and private PDF download. Exclude Drafts and the reviewer's own papers. Preview makes NO assignment.
- **REV-002 (future):** add Confirm Claim to an eligible paper and My Reviews assigned list within the same workspace, DB-transactional claim with race protection, own-paper prohibition, duplicate protection and auditable release. Do NOT invent required reviewer count or number of slots; approve these rules before the claim migration.
- **REV-003 (future):** private review draft with comments and recommendation, Save Draft vs Confirm Submit Review, exactly the claiming reviewer authorized, submitted review history/read-only view. Recommendations never auto-publish academic decisions.

Reviewer anonymity / double-blind rules are still NOT specified in this project's PRD. REV-001 minimizes personal account/author details on the page, but author-uploaded PDF may disclose identities. Do not claim double-blind until a formal anonymization policy and PDF process are decided.

## Decision gates

- Owner REV-001 UAT PASS before code commit.
- ARCH-001 UUID v7 strategy finalized before REV-002 adds new business tables.
- Academic reviewer identity / reviewer count / claim-capacity policy finalized before production claim logic.
- MSG recipient policy and SMTP + queue QA finalized before sending any non-auth email to participants.
