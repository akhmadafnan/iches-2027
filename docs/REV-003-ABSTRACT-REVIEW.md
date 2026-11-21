# REV-003 — Abstract Review: General Comments and Accept / Reject

**Status:** **DONE — OWNER UAT FINAL PASS** (21 November 2026). Automated regression and read-only browser QA PASS. Scoped Git checkpoint follows.
**Depends on:** REV-001 and REV-002 FINAL PASS, single exclusive reviewer per abstract, author-identity-safe reviewer UI, UUID v7 for new business entities.
**Source rule:** Owner approved an intentionally simple abstract review: **General Comments + Accept / Reject ONLY**, no abstract Revisions Required option, no numerical scores.

## User workflow

- Reviewer opens **Reviewer Workspace → My Reviews → Open review form**. Same Paper Preview page and Emerald/Flux visual system; no second reviewer dashboard.
- Reviewer sees **Paper ID, title, track, submitted date, abstract, keywords and authorized private PDF**. Reviewer UI does not render author name, email or affiliation. Original uploaded PDFs may still contain names; no PDF anonymization is claimed.
- Only the reviewer with an **active claim** can see or operate the Review Form. Available/unclaimed preview shows the existing Claim action, not an editable review.
- Form has **one General Comments field**, and exactly two mutually exclusive recommendations: **Accept** or **Reject**. Suggested improvements for a future Full Paper belong in General Comments. No numeric rubric, revision option, upload or author–reviewer messaging.
- **Save Draft** accepts partial/incomplete fields and records a dated draft event. Repeated Save Draft updates the same UUID v7 review and increments an optimistic lock version.
- **Submit Review** requires nonblank General Comments and one valid Accept/Reject choice. A separate confirmation modal shows the Paper ID, chosen recommendation and irreversible consequences. Cancel creates no record.
- Confirm Submit rechecks role, active claim, paper status, ownership, completeness, lock version and already-submitted state in a DB transaction. It saves the review atomically and records a Submitted event/version/timestamp. After submit, the reviewer sees a **read-only record**, not Save Draft/Submit controls.
- **My Reviews** shows a truthful per-paper badge: Claimed / Review Draft / Review Submitted.
- Reviewer workflow timeline displays actual dates in **WIB** (Paper submitted, Claimed by you, Review draft saved, Review submitted). Events that have not occurred are labelled pending and never assigned fabricated timestamps.
- Author Status Timeline gains **Review completed** with WIB date only after Submit Review. It does **NOT** show reviewer identity, comments, recommendation or internal academic decision.
- **NEW ACTIVE POLICY, replacing this original historic plan:** DEC-BULK-001 lets assigned AcademicAuthority publish accepted/rejected recommendations in bulk, after optional audited overrides. Author only sees published decision and approved General Comments after Publish & Notify; REV-003 itself sends no decision email, LoA or payment rights. See [current contract](CURRENT-STATE-AND-DECISIONS.md).

## Database / integrity

Additive migration `2026_11_21_000007_create_paper_reviews_tables.php`:
- `paper_reviews.id`: UUID v7 via Laravel HasUuids.
- `paper_reviews.paper_review_claim_id`: UUID FK and **UNIQUE** (one review per claim); legacy `paper_id` / `reviewer_id` remain BIGINT FKs compatible with REV-002 and existing users/papers.
- `general_comments` nullable in Draft; max 10,000 chars enforced on Save and Submit. `recommendation` null / accept / reject only, never revisions; review status draft/submitted; `lock_version` for stale-tab detection; `submitted_at` timestamp.
- `paper_review_events`: UUID v7 event ID, actor, draft_saved/review_submitted, monotonically increasing version and actual timestamp, UNIQUE(review_id, version).
- `SavePaperReview` serializes writes with DB transaction and locked Paper + active Claim + existing Review; rejects foreign/own/co-authored/inactive assignments, submitted review edits, and stale expected versions.
- Existing Paper ID, ULID, author/co-author metadata, abstract PDF, status Submitted, submission_version and claim identity are preserved.
- No Release Claim UI, no auto decision, no mail.

## Quality assurance

- Dedicated `tests/Feature/Reviewer/PaperReviewWorkflowTest.php` covers incomplete Draft saves; repeated saves and event history; Accept and Reject submission; read-only lock; one reviewer/UUID; stale browser state; cancelled confirmation; invalid/missing recommendation; author privacy; released claims; future review list status and academic authority separation.
- Earlier REV-001/002 tests retained and updated only when obsolete text placeholders became active features.
- **Final full regression: 206 PASS / 1,248 assertions**; reviewer-only regression: **40 PASS / 304 assertions**.
- **PHPStan: 0 errors**; Laravel Pint: **118 files PASS**; Blade compilation PASS; Vite frontend production build PASS; git diff whitespace check PASS.
- MySQL additive migration applied on Laptop 2: **batch 13**; previous submission/claim records were not modified.
- Browser QA on authenticated reviewer account and existing claimed paper `ICH27-P000003`: workspace and preview HTTP 200, Abstract Review card visible, precisely **one Accept radio and one Reject radio, zero revision options**; modal shows Paper ID and opens/cancels safely; author identity not in UI; desktop 1440px and mobile 390px no full-page overflow; **zero JavaScript errors**.
- Browser QA deliberately did NOT press Confirm Submit Review or Save Draft on actual paper. Development MySQL contained **0 paper_reviews rows after QA**; no real review was transmitted, no email sent.
- Additional tests cover conflicts added after Claim, paper status changing after Claim, 10,001-character input rejection, and assert that review submission triggers **no email or notification**.
- No regression in REV-001/002, ACAD-001..004, or auth roles; all feature tests pass.

## Manual UAT

1. Reviewer login → `https://iches-2027.test/reviewer/workspace?tab=mine`; claimed paper should have **Open review form** (pencil) icon and status Claimed.
2. Open paper preview: same Paper ID/abstract/PDF, no author identities in reviewer UI, and an **Abstract review** card with General Comments and **Accept / Reject only**.
3. Enter a partial comment, click **Save Draft**; review remains editable, status Review Draft appears in My Reviews, and timeline displays a real saved timestamp.
4. Enter meaningful comments describing what should improve in a full paper, choose Accept or Reject, click **Submit Review**. First click Cancel and ensure Draft/status do not change.
5. Confirm only for a test or intentionally final review: status becomes Review Submitted, timestamp appears, form is locked and read-only, Paper ID unchanged.
6. Login as author: Status Timeline now shows **Review completed + date**, with no reviewer identity, General Comments, Accept/Reject or internal decision leaked.
7. Reviewer with a different account cannot open/change an assigned review. PDF access remains protected. Check desktop and mobile responsive layout.
8. Review completion alone must NOT publish a decision, enable Full Paper/LoA, or send any real email.

**Owner acceptance:** REV-003 UAT PASS confirmed 21 November 2026. Post-UAT live browser read-only verification confirmed paper ICH27-P000003 has one submitted Accept review, one active claim, reviewer record locked, and author sees only Review completed dated 21 Nov 2026 17:14 WIB (no decision/recommendation leak). Full regression 206 PASS / 1,248 assertions; Pint 118 PASS; PHPStan 0 error. Next: scoped Git checkpoint, then DEC-001/DEC-002 design discussion only; no DEC coding before owner approval.
