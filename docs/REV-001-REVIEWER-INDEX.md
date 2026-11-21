# REV-001 — Reviewer Available Papers / Read-only Preview

**Status:** **DONE — OWNER UAT PASS (21 November 2026)**. Automated QA PASS; owner accepted Reviewer Workspace list and Paper Preview screenshots. Scoped Git checkpoint follows.
**Source:** docs/PRD.md AC-05 / AC-06, Master Blueprint chapter V, docs/TASKS.md.
**Continuity:** docs/ARCH-UUIDV7-EMAIL-REVIEWER-ROADMAP.md (UUID v7 / email / REV-002 and REV-003 UX).

## Scope implemented

- Existing /reviewer/workspace route becomes a class-backed Livewire Reviewer Workspace using the same Flux UI Emerald design language as My Submissions. No second redundant dashboard.
- Actual Available Papers count, distinct represented tracks, and a truthful "My Reviews coming in REV-002/003" section; no fake review counts or dead Claim action.
- Submitted-only index excludes drafts and papers owned by the reviewer. No post-submission administrative screening. Search by title/Paper ID, filter by conference track, paginated 10 rows; responsive layout with contained horizontal table scrolling on mobile.
- Each row offers icon-only Preview (eye) with tooltip and accessible aria-label.
- Owner-safe read-only preview of paper title, track, submitted date, abstract, keyword tags, and private PDF download. Does not show author account credentials, emails or reviewer notes. A PDF may nevertheless disclose author identity; double-blind has NOT been promised.
- PDF is served only from Laravel local private disk with authenticated Reviewer role, Submitted status, non-owner check, owner namespace check and 404 on missing path; not a public file URL. Non-reviewer gets 403; guest redirects to login.
- Preview is not a claim and cannot create a review, change status, send email, or create a payment.
- No new database migration, no changed PK, no UUID v7 conversion, no modification of ACAD-001..004 stable identities.

## Forward UX contract

- REV-002 will introduce guarded Claim confirmation and an actual My Reviews listing in this workspace; audited claim identity and concurrency checks must be implemented before the button is made active.
- REV-003 will add Review Draft / Submit Review and recommendation only to assigned reviewers, with read-only view after submission, without automatically publishing a decision.
- Claim count, anonymization policy, and UUID v7 schema standard require owner decision before REV-002, not assumptions.

## Modified/new files

- app/Livewire/Reviewer/AvailablePapers.php
- app/Livewire/Reviewer/PaperPreview.php
- app/Http/Controllers/ReviewerAbstractDownloadController.php
- resources/views/livewire/reviewer/available-papers.blade.php
- resources/views/livewire/reviewer/paper-preview.blade.php
- routes/web.php
- tests/Feature/Reviewer/AvailablePapersTest.php
- docs/TASKS.md
- docs/ARCH-UUIDV7-EMAIL-REVIEWER-ROADMAP.md
- docs/REV-001-REVIEWER-INDEX.md

## Automated QA

- Focused AvailablePapersTest: **7 PASS / 57 assertions**, covers route role restriction, Submitted vs Draft/own-paper scope, title and track filter, preview + PDF, cross-user/missing-file/foreign namespace denial, and no side effects.
- PHPStan: **0 errors**; Laravel Pint: **105 files PASS**; Blade compile **PASS**; Git diff check **PASS**.
- Full regression: **173 tests PASS / 1001 assertions**.
- Browser on real Herd HTTPS: reviewer role login and workspace HTTP 200, one existing Submitted paper visible; real preview route HTTP 200 with PDF link, desktop 1440px and mobile 390px no whole-page overflow, **0 JavaScript page errors**. No real paper status or data changed.
- No synthetic paper inserted into real development MySQL for QA; automated tests use isolated refreshable test DB.

## Manual owner UAT — PASS (21 November 2026)

1. Login as local reviewer account (e.g. reviewer@iches.id in the preexisting development seeder) and open https://iches-2027.test/reviewer/workspace.
2. Confirm Available Papers includes the existing Submitted paper with ID ICH27-P000003, but no Draft. Confirm the three summary cards have actual/accurate semantics, and "My Reviews" is marked upcoming.
3. Test search by exact Paper ID and title; select at least two track filters and then Clear.
4. Click eye icon to Preview; verify reference, track, abstract, keywords and private Download PDF. Return to index.
5. Ensure there is no active Claim, Submit Review, reviewer rating, or administrative decision action yet.
6. Try the preview/download URL as ordinary User / guest; access must be forbidden or redirected. A paper that belongs to the reviewer must not be claimable; it is excluded in REV-001.
7. Inspect 1440px desktop and 390px mobile layout; table scroll stays within the table, no whole-page overflow.

**Owner acceptance:** REV-001 UAT PASS explicitly confirmed on 21 November 2026, including directory, preview and reviewer-only PDF access. Next: scoped Git checkpoint, then ARCH-001 UUID v7 policy and reviewer claim-capacity/anonymization decisions before REV-002 implementation. Email events remain separately tracked under MSG-001.
