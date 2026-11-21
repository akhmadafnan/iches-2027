# REV-002 — Exclusive Reviewer Self-Claim

**Status:** **DONE — OWNER UAT FINAL PASS (INCLUDING TIMELINES)** (21 November 2026). No source commit/push.
**Authority:** PRD AC-06, Master Blueprint §5.2, prior owner decision, and the owner's final clarification: show Paper ID and academic content only, **never author name or institution in reviewer UI**.
**Safety boundary:** No new author submission, review form, decision publication, email or payment. Submitted paper remains Submitted.

## Accepted single-reviewer contract

- **One active reviewer per abstract**. Claims are exclusive, first successful claim wins. Neither claiming nor previewing a paper changes the paper's status or immutable reference.
- Only verified accounts with Reviewer role can Claim. A reviewer cannot claim their own paper (owner match) or a paper where a co-author email matches their account (case-insensitive). Same-name or same-institution matches are not reliably provable and therefore are not claimed to be detected.
- Reviewer Workspace uses two accessible tabs within the existing Flux Emerald UI: Available Papers (only eligible, unclaimed Submitted abstracts) and My Reviews (active claims for authenticated reviewer).
- Row action buttons are icon-only Preview and Claim with tooltips and accessible labels. After Claim, the item disappears from Available Papers and appears in My Reviews; it is unavailable to all other reviewers.
- Confirm Claim modal shows only the human-readable Paper ID; Cancel creates no assignment. Claiming from Preview is supported. Confirmation rechecks all permission and conflict rules under a DB transaction.
- The reviewer index and preview show Paper ID, paper title, track, submission date, abstract, keywords and authenticated private PDF download, but **do not render author names, author affiliations or author email addresses**.
- An uploaded PDF might intrinsically contain author names/affiliations. REV-002 does not rewrite the author's file. For PDF-level anonymity, submission rules would need to require an anonymized PDF and validate/anonymize it separately; this is not silently assumed.
- Review comments/recommendations are deferred to REV-003. There is no numeric score, publish decision, or communication channel with authors in REV-002.

## Database and concurrency

New additive migration: `2026_11_21_000006_create_paper_review_claims_table.php`. A new `paper_review_claims` table uses Laravel HasUuids -> **UUID v7 primary key**, while `paper_id`, `reviewer_id` and `active_paper_id` use BIGINT foreign keys matching existing tables. No existing Paper ID, ULID, author ID, or path changes.

The **unique nullable active_paper_id** key is the database-backed one-active-claim-per-paper invariant. On a future authorized release the active_paper_id may be cleared to NULL while preserving the historical row, actor, claimed_at and release fields. Multiple historical released rows are permitted but never two active rows. **A release UI is NOT implemented** until its authorization rules are defined, avoiding an undocumented reviewer self-release policy.

`App\Actions\Reviewer\ClaimPaper` locks the Paper row in a transaction, validates Submitted status, reviewer role, owner/coauthor conflicts, checks active claim, and creates one UUID v7 row. A database unique constraint catches races not resolved by app locks. Duplicate attempts fail without changing the existing assignment. A read-only ReviewerPaperAccess service consistently filters list/preview/PDF access.

## Files

- app/Actions/Reviewer/ClaimPaper.php
- app/Models/PaperReviewClaim.php, app/Models/Paper.php
- app/Services/Reviewer/ReviewerPaperAccess.php
- database/migrations/2026_11_21_000006_create_paper_review_claims_table.php
- app/Livewire/Reviewer/AvailablePapers.php, PaperPreview.php
- app/Http/Controllers/ReviewerAbstractDownloadController.php
- resources/views/livewire/reviewer/available-papers.blade.php, paper-preview.blade.php
- tests/Feature/Reviewer/ClaimPaperTest.php
- tests/Feature/Reviewer/AvailablePapersTest.php (updated REV-001 expected UI, preserving security tests)
- docs/PRD.md, docs/TASKS.md, docs/ARCH-UUIDV7-EMAIL-REVIEWER-ROADMAP.md

## QA evidence

- Reviewer focus tests (REV-001 + REV-002, including timeline): **24 PASS / 181 assertions**.
- REV-002 focused ClaimPaperTest: **17 PASS / 124 assertions**. Includes UUID version 7, immutable Paper ID and status, one-active-per-paper unique constraint, second-reviewer rejection and preview/PDF access, coauthor email conflict, forged dialog state, stale modal after another Claim, cancel, My Reviews, and no Review Form.
- Local MySQL additive migration: **Ran, batch 12**; migration does NOT assign or change existing development paper.
- PHPStan: **0 errors**. Laravel Pint: **111 files PASS**. Blade compilation: PASS. Frontend Vite build: PASS.
- **Final full regression after timeline polish: 190 tests PASS / 1,125 assertions**; final reviewer-focused tests: **24 PASS / 181 assertions**. The prior baseline before timeline polish was 186 PASS / 1,082 assertions.
- Chrome actual local application: Reviewer Workspace HTTP 200, icon-only Claim and Preview visible, Claim dialog shows Paper ID, Cancel closes without mutation, two tabs work, preview and PDF link visible, no author names/affiliations in UI, 1440px desktop and 390px mobile without document overflow, no JavaScript page errors. Browser QA did NOT claim existing real paper.

## Owner-requested timeline refinement — 21 November 2026

**Owner acceptance (21 November 2026): REV-002 FINAL PASS, including the timeline revision on reviewer preview and author submission view. Full regression and live browser QA PASS.**

- Reviewer Paper Preview now shows a dated vertical **Review workflow**: `Paper submitted` from `papers.submitted_at`, `Claimed by you` from the active `paper_review_claims.claimed_at`, and a clearly **pending** `Review submitted — Not yet available · REV-003` item with **no fabricated timestamp**. Before Claim, the second item reads `Claim paper — Not claimed yet` without an invented date.
- Author read-only Paper Submission now shows a chronological Status timeline of **actual recorded events**: Draft created, Paper submitted, Abstract claimed (with its WIB date), and (if historically applicable) Reviewer assignment released. It does not expose the reviewer's name, email, ID, or affiliation.
- All dates are formatted in **Asia/Jakarta (WIB)**. Reviewer assignment dates come only from the claim record, not paper `updated_at`.
- A reusable owner timeline service `App\Services\Conference\PaperActivityTimeline` explicitly allows owner-visible events. Future private reviewer recommendations and internal decisions are **not** rendered prematurely. REV-003 will record Review Completed, and DEC-002 must publish the final Accept/Reject decision before the outcome becomes author-visible. These are **future dependencies**, not active features or placeholder dated events.
- Browser QA against an already-claimed real paper: reviewer timeline HTTP 200 had Paper submitted **21 Nov 2026 15:23 WIB** and Claimed by you **21 Nov 2026 16:32 WIB**; owner timeline HTTP 200 showed the same Claim date plus Draft created and Paper submitted. No reviewer email/name leaked to author, no author name leaked to reviewer, no fake Review Completed/Decision Published, zero JavaScript errors, no document overflow at 1440px/390px. No claim or paper record was changed by browser QA.
- Additional tests verify real WIB dates on both roles, chronological event order, historical release timestamp, and protection from leaking unpublished internal recommendations.
- No new migration, status transition, review form, email, claim or paper metadata change was necessary for this UI refinement; existing claimed papers render their prior dates.

## Owner UAT checklist — before commit

1. Open `https://iches-2027.test/reviewer/workspace` as local reviewer account. Available Papers displays Submitted unclaimed abstracts, only Paper ID and academic content; author name/affiliation must not appear.
2. Click Claim icon for a test abstract: confirm modal includes Paper ID and consequence. Cancel first: no changes.
3. Click Confirm Claim **only if ready to assign that specific abstract to this reviewer**. My Reviews gains the paper, Available Papers loses it; the Paper ID and author Submitted status do not change.
4. As a second reviewer, confirm claimed paper cannot be claimed again and its private preview/PDF is unavailable. Owner/coauthor reviewer cannot claim their own work.
5. In My Reviews, Preview opens the assigned abstract, PDF and status; REV-003 Review form is visibly forthcoming but not active.
6. Test search, track filters, desktop 1440px and mobile 390px. Navigation and tab controls must be understandable and without page overflow.

**Owner gate:** Final UAT PASS explicitly received from owner. Academic emails remain planned but NOT SENT. REV-003 remains planning only and is not implemented in this checkpoint.
