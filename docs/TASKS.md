# IMPLEMENTATION TASK BACKLOG — ICHES 2027

**Version:** 2.0 | **Updated:** 21 November 2026 | **Status:** FND-001..006 / ACAD-001..004 / REV-001..003 owner UAT FINAL PASS (source checkpoint 154d919); **ACAD-005 NEXT TODO**. DEC-BULK-001, MSG-001B, DOC-LOA-001, FIN, FULL-001 policies approved but NOT CODED. Legacy manual DEC-001/002 and Full Paper without Finance Verified SUPERSEDED.
**READ FIRST:** [Current State & Decisions](CURRENT-STATE-AND-DECISIONS.md), [PRD](PRD.md), [Master Blueprint](../MASTER-BLUEPRINT-ICHES-2027.md). New owner decisions take priority over old milestone/UAT snapshots.
**Priority:** P0 = critical flow, P1 = essential follow-up, P2 = polish/post-event.  
**Never treat TODO as implemented.** Code changes begin only when the owner instructs us to start coding.

## A. Working with the backlog

- A task contains ID, PRD requirement, outcome, dependency, acceptance. Do not reintroduce manual per-paper DEC-001/002, Google Drive LoA, or Full Paper before Verified.
- Execute tasks in **short, testable bundles**. Do not spend time creating empty modules or ceremonial architectural layers.
- For each task record status TODO / IN PROGRESS / BLOCKED / DONE and a short proof (commit SHA, tests, browser scenario).
- Accept minor reorganizations that reduce time, provided the PRD's essential business behavior remains correct.
- No public landing page/content design without separate conversation and approval (WEB-001 is on HOLD).
- A command not actually run is not a test result; indicate BLOCKED/NOT RUN explicitly.

## B. P0 — Foundation, Identity & Core Abstract Submission

| Task | PRD | Dependency | Acceptance / tangible output |
|---|---|---|---|
| **FND-001 [DONE]** Laravel + Livewire Starter Kit installation | ACC-01 | — | Laravel 13.43, Livewire 4.4; MySQL `iches_2027` migrated; Herd HTTP 200/302; PHP 38 tests/97 assertions PASS, Pint 45 PASS, PHPStan PASS, npm build PASS (20 Nov 2026) |
| **FND-002 [DONE]** Fortify register/login/reset/verification | ACC-01, ACC-02 | FND-001 | HTTPS site live; registration saves institution/country/intention; no self-assigned admin role; six registration tests PASS; full suite 42 tests/122 assertions, Pint/PHPStan PASS (20 Nov 2026). Contextual CTA remains FND-005. |
| **FND-003 [DONE]** Profile, institution, country + private photo upload | ACC-03, ACC-04 | FND-002 | Authenticated `/settings/profile` edits personal details and stores photo privately; newly registered accounts must upload photo before dashboard, old demo accounts exempt. Photo crop 1:1 remains ID-001. |
| **FND-004 [DONE]** Simple permissions and assignment | ACC-06 | FND-002 | Roles User/Reviewer/Committee/Admin enforced by Gates/Policies and 403 route guards; Admin Livewire `/admin/users` edits others' roles & Finance/Academic Authority assignments; Moderator/CoHost PIC gates are resource-scoped; test suite verifies unauthorized access. Completed 20 Nov 2026. |
| **FND-005 [DONE]** Minimal dashboards and navigation | ACC-03 | FND-004 | Class-based Livewire `/dashboard` shows real Admin account counts, Reviewer/Committee workspace routes, User starting CTA per intention; role-scoped Overview/Workspace/My Account navigation; no fake unbuilt modules. 20 Nov 2026. |
| **FND-006 [DONE — OWNER UAT PASS]** Five program records + event settings | PG-01 | FND-001 | Exactly five immutable-code master records + singleton event settings + seven TOR milestones seeded idempotently in MySQL; Admin CRUD editing via Flux modal and server Gates, 11 targeted tests PASS; owner UAT PASS. |
| **ACAD-001 [DONE — OWNER UAT PASS]** Eight official conference tracks | AC-01 | FND-004, FND-006 | 8 exact TOR Section E.2 track titles seeded with immutable codes, originals preserved, Admin editing/search/filter/active selection scope implemented; active-track selection is now wired into ACAD-002 Draft editor. |
| **ACAD-002 [DONE — OWNER UAT PASS]** Draft paper, authors, ROR affiliations, stable IDs | AC-03 | FND-002, ACAD-001 | Paper Draft/ROR and OJS-style compact Add/Edit author modal owner UAT PASS; Cancel discards, corresponding and ordering retained, ROR verified. No Submit. |
| **ACAD-003 [DONE — OWNER UAT PASS]** Abstract form and private PDF upload | AC-04 | ACAD-002 | OJS-style individual keyword tags, abstract text, local-private PDF upload/download/remove/replacement, validated files; 18 focused and 155 full tests PASS; Edge desktop/mobile 390px PASS; owner UAT PASS (21 Nov 2026); implementation commit `ccf1ba9`; no Submit. See [ACAD-003 evidence](ACAD-003-ABSTRACT-KEYWORDS-PRIVATE-PDF.md). |
| **ACAD-004 [DONE — OWNER UAT PASS]** Submit action and version/status timeline | AC-04, AC-05, AC-10 | ACAD-003 | Icon-only Edit/Submit/View in My Submissions; Submit visible only after server-side persisted completeness; confirmation, atomic Draft→Submitted, owner-only read-only View, status/version history; no administrative screening; owner UAT PASS (21 Nov 2026), including final hero and abstract whitespace polish. [ACAD-004 evidence](ACAD-004-SUBMIT-STATUS-TIMELINE.md). |
| **ACAD-005 [TODO — NEXT AFTER OWNER GO]** One owned paper per submitter | AC-03 | ACAD-004 | First audit existing duplicate owner data read-only (never auto-delete), then DB unique owner + authorization and create UI guard. One Draft/Paper ID per owner including Submitted/Accepted/Rejected; coauthor on other papers still allowed; concurrent creates safe, security/browser/UAT tests. APPROVED POLICY, NOT CODED. |
| **REV-001 [DONE — OWNER UAT PASS]** Reviewer available index and filters | AC-06 | ACAD-004, FND-004 | Reviewer-only read-only Available Papers / preview / private PDF, title/ID search, eight track filters, pagination; Draft and own paper excluded; no claim yet. Owner UAT PASS (21 Nov 2026) with accepted directory and preview UI. See [REV-001 evidence](REV-001-REVIEWER-INDEX.md). |
| **ARCH-001 [FORWARD STANDARD APPLIED]** UUID v7 new business entities, legacy-key audit | Cross-cutting | REV-001 | [Architecture review](ARCH-UUIDV7-EMAIL-REVIEWER-ROADMAP.md): REV-002 claims now use UUID v7 PK and BIGINT legacy foreign keys, preserving paper ULID and human reference. ARCH-002 legacy PK migration remains separate and not authorized. |
| **MSG-001A [PLANNED BEFORE PUBLIC SUBMISSION]** Submitted email confirmation | COM-01, COM-02 | ACAD-004, recipient policy | Transactional after-commit, idempotent event/version, local Mail fake + queue failure tests; **NOT IMPLEMENTED**. See [email roadmap](ARCH-UUIDV7-EMAIL-REVIEWER-ROADMAP.md). |
| **REV-002 [DONE — OWNER UAT FINAL PASS]** Exclusive reviewer self-claim and conflict check | AC-06 | REV-001, ARCH-001 | One active reviewer per abstract enforced by DB unique active_paper_id and transactional row lock; owner/coauthor denied; UI only Paper ID + academic content (no author names/affiliations); Claim confirmation, Available Papers / My Reviews, UUID v7 claim PK; release fields prepared but no unauthorized Release UI. See [REV-002 evidence](REV-002-EXCLUSIVE-SELF-CLAIM.md). |
| **REV-003 [DONE — OWNER UAT FINAL PASS]** General Comments + Accept/Reject review | AC-07 | REV-002 | UUID v7 Review and audit events; assigned reviewer only; Save Draft incomplete permitted; Submit Review validates comments and Accept/Reject, confirms and locks; My Reviews progress and dated Review completed timeline; no decision/email leak. See [REV-003 evidence](REV-003-ABSTRACT-REVIEW.md). |
| **DEC-BULK-001 [TODO — POLICY APPROVED]** Bulk Publish & Notify, Steering override and correction | AC-08, AC-10 | REV-003, FND-004 | AcademicAuthority only; default final proposal from Review Submitted Accept/Reject; mandatory audited reason on override, preview and optional selected/all-ready batch; one Publish & Notify confirmation; skip unreviewed/already published; per-paper idempotent status and date; approved General Comments visible to owner after publish; authorized corrected published decision audited and emailed, no private leaks. No per-paper manual DEC stages. |
| **DOC-LOA-001 [TODO — POLICY APPROVED]** Auto official LoA PDF + QR verification | DOC-01, DOC-02 | DEC-BULK-001, official template/signature | One valid document per Published Accepted paper; approved English master template, signed/numbered snapshot, background idempotent PDF generation, private owner Preview/Download/Print, random-token public QR verification, audit/revoke/reissue/failed retry. No per-paper Drive upload; LoA does not wait for payment. NOT CODED. |
| **MSG-001B [TODO — P0 WITH DEC-BULK]** Published decision and LoA-available email outbox | COM-01, COM-02 | DEC-BULK-001 for decision mail; DOC-LOA-001 Issued for LoA mail | One personal decision message per paper owner/event/version, after-commit queued, retry/send failure visibility, versioned correction email, LoA notice only after document Issued; never leak reviewer identity/override rationale; no production SMTP without authorization. NOT IMPLEMENTED. |
| QA-001 P0 feature tests + browser user journey | ACC/AC/REV/DEC/MSG | All relevant P0 | One owned paper → review → bulk publish, override/correction and queued personal message; later LoA QR and Verified gate. PASS only with actual evidence. |

**Historical demo checkpoint (22 Nov 2026; not proof of implementation):** demonstrate the longest *fully working* P0 journey possible using real functions and clearly labeled demo users/data. **Not guaranteed production-ready**. Do not consume time on landing-page aesthetics before academic core works. QR prototype optional if time allows.

**HISTORICAL UAT EVIDENCE:** Dated FND/ACAD notes below describe the project at the time of those phases; old pending/TODO phrasing in snapshots cannot override the active task table or Current State & Decisions.

**FND-001 evidence (20 Nov 2026):** official `laravel/livewire-starter-kit` branch `main` scaffold, PHP 8.4.27, PHP dependencies and npm dependencies locked, dedicated database `iches_2027`, five migrations Ran, locally linked `https://iches-2027.test` via Herd (Secure Site enabled 20 Nov 2026), 38 tests/97 assertions, Pint 45 files, PHPStan, and build PASS. Changes are limited to baseline Laravel app, configuration examples, README/agent/task status; no ICHES business module or final website content built.

**FND-002 evidence (20 Nov 2026):** Herd TLS active and HTTPS GET `/`, `/register`, `/login`, `/forgot-password` return 200; HTTP redirects 301; `.env` APP_URL local HTTPS. Default Fortify login/logout, reset password, email verification and two-factor flows retained. Added nullable MySQL registration fields, typed enum intention, server-side validation and registration form; six targeted registration tests passed. Full suite **42 tests / 122 assertions**, Pint **47 files** and PHPStan passed. No privilege chosen at signup; FND-003 profile editor and FND-005 intent-based dashboard CTA remain TODO. No public landing-page design has been approved or implemented.

**DEV-001 / supporting seeder evidence (20 Nov 2026):** added `UserRole` enum and default `user` database column, with **role excluded from User's mass-assignable fields**. `DatabaseSeeder` invokes a local/testing-only demo seeder with `user@iches.id`, `reviewer@iches.id`, `committee@iches.id`, and `admin@iches.id`; all have hashed development password `password` and verified email. Rerun is idempotent, existing accounts are not overwritten. The role field is only preparatory data for FND-004, **not** finished permissions.

**FND-003 evidence (20 Nov 2026):** extended official class-backed Livewire profile component and shared profile validation rules to persist institution/country for the authenticated user. Factory defaults and authorization/validation regression tests added; **49 tests / 165 assertions PASS, Pint 51 files PASS, PHPStan PASS**. No public/frontend layout was changed.

**FND-004 evidence (20 Nov 2026):** Added `UserPolicy`, Laravel authorization Gates, a `staff_assignments` table with nonnullable resource scope+unique constraint, scoped permission checks, an Admin-only class-based Livewire Users & Access page, UpdateUserAccess Action (transaction/lock), and role-restricted Reviewer/Committee placeholder workspaces. FND-005 remains responsible for fully contextual dashboards/navigation; academic review/QR features remain TODO. UAT: login as local-only accounts, confirm `/admin/users`, `/reviewer/workspace`, `/committee/workspace` enforce 200 for the matching role and 403 for others. Finance/Academic privileges require explicit global assignment even for Admin; Moderator/CoHost PIC require exact resource scope.

**Account onboarding hardening (20 Nov 2026, user-requested before FND-005):** Fortify Passkeys feature disabled and removed from app auth/security UI plus frontend build. New account registration now redirects to `/settings/profile` and requires verified format profile photo upload before accessing verified private routes; existing accounts intentionally exempted. Files stored privately with owner-only authenticated photo endpoint. Fortify verification, reset and 2FA remain. **Full suite 75 tests / 298 assertions PASS; PHPStan, Pint and frontend build PASS.** ID-001 image crop remains not started.

**FND-005 evidence (20 Nov 2026):** Replaced starter-kit dashboard placeholder with class-based `App\Livewire\Dashboard`, Flux UI Emerald responsive view, contextual role/intention texts and routes, actual admin User counts, explicit delegated-duty labels, private profile summary, and sidebar groups Overview/Workspace/My Account (hidden unauthorized workspace links). FND-006, paper review, program enrollment, Finance workflows and QR are deliberately **not represented as active**. Full regression suite **84 tests / 358 assertions PASS**, PHPStan PASS, Pint 65 files PASS, frontend build PASS. Real Microsoft Edge logins/render for Admin/Reviewer/Committee/User and 390px User mobile PASS, no JS errors or document overflow. No database migration, seed data mutation or public frontend design changes.

**FND-006 evidence (20 Nov 2026):** Reopened original nine-page `TOR ICHES 6 UNISYA 2027.docx` and recorded the source-verified master baseline in [FND-006 TOR baseline](FND-006-TOR-BASELINE.md). Database migration created three new tables additively; EventMasterSeeder created five fixed program identities (`conference`, `research_pkm`, `international_mou`, `community_service`, `destination_study`), one event setting with optional initial clock time NULL, and seven distinct Important Dates. Seeding is idempotent and preserves Admin edits. Admin-only `/admin/programs` and `/admin/event-settings` use Livewire class components + Flux UI Emerald, leftmost No., left Page X of Y, right Previous/Next, and edit modals. No fictional schedules, tariffs, bank details, payment, submission, package entitlement or public website edits. **11 targeted FND-006 tests / 92 assertions PASS; full regression 95 tests / 450 assertions PASS, Pint 74 files PASS, PHPStan no errors, Blade compilation and npm build PASS.** MySQL migration Ran; two consecutive seed runs kept 1/5/7 row counts. **Owner reported FND-006 UAT PASS before ACAD-001 began on 21 Nov 2026**. Historical initial automated browser inspection was not completed in that phase. Next phase ACAD-001 (eight TOR tracks) unless owner changes priority.

**ACAD-001 evidence (21 November 2026):** Actual TOR `TOR ICHES 6 UNISYA 2027.docx` Section E.2 re-read; eight original titles and stable `TRK-01`..`TRK-08` codes stored under International Conference via additive migration `2026_11_21_000001`. Idempotent `ConferenceTrackSeeder` preserves Admin edits; `ConferenceTrack::available()` provides active ordered options for future paper forms. Admin-only `/admin/conference-tracks` includes Flux UI Emerald table, search/status filter, row numbers, bottom pagination, modal, validation and safeguards for official titles and last active track. Targeted **10 tests / 96 assertions PASS**, PHPStan and Blade compilation PASS. Microsoft Edge browser inspection: 8 rows, modal, responsive desktop/mobile 390px (document no overflow), JS errors none, non-admin route 403; frontend build PASS. **Full regression 105 tests / 546 assertions PASS; Laravel Pint 79 files PASS; PHPStan no errors; npm build PASS.** **Owner reported ACAD-001 UAT PASS on 21 November 2026.** Do not confuse track masters with the yet-unbuilt Abstract Submission workflow. See [ACAD-001 Conference Tracks](ACAD-001-CONFERENCE-TRACKS.md).

**ACAD-002 evidence (21 November 2026):** Implemented additive migration `2026_11_21_000002` for `papers` and `paper_authors` (both Ran on local MySQL). Typed PaperStatus (`draft`/future `submitted`), models/relationships, PaperPolicy owner and Draft-only checks, and `SavePaperDraft` Action with transactional metadata, unique permanent `ICH27-P000001` reference plus opaque ULID, ordered authors and exactly one corresponding author. Class-backed Livewire routes `/my/submissions`, `/my/submissions/create`, `/my/submissions/{paper:public_id}/edit` have Flux UI Emerald styling, search, row numbers/pagination, responsive empty state, author add/remove/reorder and server validation. `user@iches.id` dashboard now links to Draft creation. **14 targeted tests / 105 assertions PASS, full regression 119 tests / 652 assertions PASS, Laravel Pint 88 files PASS, PHPStan no errors, Blade compilation and npm build PASS.** Edge desktop/mobile 390px verified HTTP 200, Add/Remove coauthor functionality, no whole-document overflow, no JS errors; no synthetic paper data inserted into MySQL by browser QA. **Owner UAT pending; do not start ACAD-003 without approval.** See [ACAD-002 Paper Drafts](ACAD-002-PAPER-DRAFTS.md).

**ACAD-002 revision evidence (21 Nov 2026):** User-requested empty-state alignment fixes: min-height centered content, single primary CTA with no search/pagination when zero papers, compact responsive author controls. ROR API v2 `query` search (aliases/acronyms, city/country, canonical ROR URL) implemented via `RorOrganizationService` (fixed endpoint, cache, timeout) and Livewire search per author/co-author. On server-side save, a ROR ID is independently resolved to the official name; unlisted/manual institutions have `ror_id=NULL` and explicit `affiliation_source=manual`. Additive migration `2026_11_21_000003` preserves prior rows; unknown historical sources remain unverified; author country, paper identity, and owner policies unchanged. Verified real `UNISYA` -> `https://ror.org/041rf3s62`, Edge desktop/mobile 390px with live ROR suggestions and coauthor fallback, 11 dedicated ROR tests, **130 full tests / 710 assertions PASS**, Pint **91 files PASS**, PHPStan, Blade, npm build PASS. MySQL create/edit with real ROR service PASS; rollback kept original paper counts. **Owner UAT pending. ACAD-003 not started.** See [ACAD-002 Paper Drafts](ACAD-002-PAPER-DRAFTS.md).

**ACAD-002 OJS-style UI refinement evidence (21 Nov 2026):** Owner accepted initial Paper Draft / ROR UAT and authorized a compact contributor list with Flux Add/Edit Author modal. `PaperEditor` stages changes in a separate `authorDraft` buffer; Cancel leaves original contributors untouched, Save Author validates full metadata and ROR, and final Save Draft persists them transactionally using existing `SavePaperDraft`. List shows name, institution, corresponding badge and quick order/corresponding/remove actions; no author email/country/ID clutter on the parent page. ROR API v2 integration and server validation remain intact. No migrations, no paper identity/status changes, no keywords/PDF/submission. **7 new modal tests, full 137 tests / 763 assertions PASS, Pint 92 files PASS, PHPStan no errors, Blade/Vite PASS**. Edge verified modal, ROR suggestions, Cancel, coauthor staging, desktop/mobile; owner UI UAT pending. See [ACAD-002 Paper Drafts](ACAD-002-PAPER-DRAFTS.md).

## C. P0 Critical Finance & Full Paper, plus P1 Co-Host/Packages

| Task | PRD | Dependency | Acceptance / tangible output |
|---|---|---|---|
| **DEC-001/DEC-002/DEC-003 [SUPERSEDED]** Legacy manual decision/revision tasks | AC-08, AC-09 | — | DEC-BULK-001 replaces them. No manual per-paper decisions and no abstract Revisions Required. Names retained only for audit, never schedule them. |
| **FULL-001 [TODO — P0 HARD GATE]** Full Paper after Published Accepted plus Finance Verified | AC-11, AC-12 | DEC-BULK-001, FIN-004, FIN-005 | Same Paper ID; server UI/upload/access lock until applicable presenter/Co-Host payment Verified, no proof-only bypass; Rejected cannot proceed. Old DEC-004 without payment is SUPERSEDED. |
| **DOC-001 [SUPERSEDED]** Legacy manual per-paper PDF link | DOC-01, DOC-02 | DOC-LOA-001 | No more one-by-one Google Drive uploads; DOC-LOA-001 is the active automatic document work order. |
| **FIN-001 [TODO — P0]** Official bank settings | FI-02 | FND-004 | Only authorized Admin; no invented bank details or real credentials |
| **FIN-002 [TODO — P0]** Invoice and immutable price/payer snapshot | FI-01 | FIN-001, DEC-BULK-001 | Separate personal/institution obligation; verified Co-Host entitlement avoids double presenter charge |
| **FIN-003 [TODO — P0]** Private bank-transfer proof and status | FI-03, FI-04 | FIN-002 | Validate JPG/PNG/PDF, owner-scoped; Uploaded/Pending never unlock Full Paper |
| **FIN-004 [TODO — P0]** Finance Verified/Correction Required and ledger | FI-04, FI-07 | FIN-003 | Assigned Finance actor checks bank mutation; audit state changes, only Verified provides payment entitlement |
| **FIN-005 [TODO — P0]** Presenter billing and verified Co-Host coverage | FI-05 | FIN-002, DEC-BULK-001 | Invoice after Published Accepted only; no second fee for sponsored paper; Verified payment is mandatory before Full Paper |
| PACK-001 Personal IDR 1m package one order | PG-07 | FIN-002, FND-006 | One product, exactly four program entitlements |
| PACK-002 Package deadline CRUD and server validation | PG-09 | PACK-001 | Admin can adjust, expired blocks new order only |
| PACK-003 Verified unlocks four entitlements | PG-08, FI-06 | PACK-001, FIN-004 | Proof Submitted never unlocks; no duplicate order |
| CH-001 Register Co-Host and one institutional invoice | CH-01 | FIN-002 | IDR 3m per institution, one obligation |
| CH-002 Verified PIC roster, five account slots | CH-02, CH-03 | CH-001, FIN-004 | Before verified disabled; 5 max |
| CH-003 Sponsored paper link & no duplicate billing | CH-04, CH-05 | CH-002, ACAD-004 | One paper per representative, separate academic decisions |
| QA-002 Finance/Co-Host/package integration tests | FI/PG/CH | Relevant tasks | Verified gates, owner scope, no second invoice tested |

## D. P1 — QR, ID Cards, Committee Operations & Event Scheduling

| Task | PRD | Dependency | Acceptance / tangible output |
|---|---|---|---|
| ID-001 Profile photo modal Cropper.js 1:1 | ACC-05 | FND-003 | Zoom/drag/reset/cancel/save; validated result |
| ID-002 Personal QR event pass and human code | QR-01, QR-02 | FND-002 | Token random, private, revocable, stable display code |
| ID-003 Participant green / Committee navy ID templates | QR-03 | ID-001, ID-002 | Preserve official original logos/reference layout |
| ID-004 Server PNG QR / ID Card download | QR-04 | ID-003 | Downloadable accurate, scannable and high resolution |
| ID-005 Onsite-only print listing, batching | QR-05 | ID-004, PROG-001 | No printing online-only, 1 card for multiple onsite enrollments |
| QRS-001 QR service CRUD, basic service types | QR-06 | FND-004 | Admin makes registration/kit/lunch/attendance services in UI |
| QRS-002 Service eligibility/window/limits/operator | QR-07 | QRS-001, PROG-001 | Persisted rules enforced server-side |
| QRS-003 Browser HP scanner, select assigned service | QR-08 | QRS-002, ID-002 | HTTPS camera, staff sees only assigned services |
| QRS-004 Attendance/redemption ledger | QR-09, QR-11 | QRS-003 | Physical kit requires handover confirmation; program attendance separate |
| QRS-005 Atomic duplicate guard and clear errors | QR-10, QR-12 | QRS-004 | Concurrent duplicate cannot both succeed |
| QRS-006 QR report and export | QR-12 | QRS-005 | By service/date/user/actor with authorized export |
| PROG-001 Program enrollment and onsite/online by program | PG-02 | FND-006 | Single account, multiple participation modes |
| PROG-002 Program Admin forms: Upgrading and MoU | PG-03, PG-04 | PROG-001 | Agenda, speaker/institution/PIC/delegation, attendance |
| PROG-003 Program Admin forms: PKM and Destination | PG-05, PG-06 | PROG-001 | Manifest, groups/PIC, itinerary and attendance |
| SESS-001 Parallel session CRUD and paper assignment | AC-13 | DEC-BULK-001 | Published Accepted paper gets room/slot/track/moderator; never infer Presented |
| SESS-002 Online/onsite modes, Zoom URL | AC-14 | SESS-001 | Presenter sees link or room; no secret host credentials |
| SESS-003 Moderator Presented/No Show workspace | AC-15 | SESS-001 | Only own assigned session can be marked |
| QA-003 QR device/concurrency and session field test | QR/AC-13..15 | Tasks above | Real-phone scan, reject duplicate, online-only, session tests |

**Operational checkpoint:** all QRS and session tasks must be exercised on actual committee phone/network before 5 January. No pretending offline scan confirms delivery.

## E. P2 — Documents, Notification, Reporting, and Public Site

| Task | PRD | Dependency | Acceptance / tangible output |
|---|---|---|---|
| DOC-002 Documents Center, certificates via PDF/link | DOC-01, DOC-03 | FND-003, SESS-003 | Only authorized/ready files visible, publish explicit |
| **MSG-001 [PLANNED]** Event-based email notifications and queue | COM-01, COM-02 | Core domains | See [event, recipient and after-commit roadmap](ARCH-UUIDV7-EMAIL-REVIEWER-ROADMAP.md). Prioritize Submitted confirmation before public opening; Decision Published/LoA before decision release; verified finance/schedule/documents per feature launch. No academic emails sent yet. |
| REP-001 Filterable exports: paper/users/programs/payment | FI-07, REP-01 | Domain data | Scoped report in CSV/Excel; no unfiltered PII leak |
| REP-002 Attendance/service summary for organizer | REP-01 | QRS-006, PROG-003 | Per-program and per-service actual figures |
| WEB-001 Decide public frontend with owner (HOLD) | WEB-01..05 | Separate discussion | Approved page map, content, imagery and visual direction |
| WEB-002 Build public design and navigation | WEB-01, WEB-02 | WEB-001 | Responsive layout matches approved brief |
| WEB-003 Simple CMS for approved content | WEB-03 | WEB-001 | Editable/published-only content; no page builder |
| WEB-004 Date/countdown and basic SEO | WEB-04, WEB-05 | WEB-003 | From event settings, no invented hours |
| OPS-001 Production readiness / backup / mail / HTTPS | Cross-cutting | Appropriate tests | Explicit deployment checklist and smoke validation |
| OPS-002 Postevent certificates and final reports | DOC/REP | DOC-002, REP-002 | Report/documents accessible and exportable |

**WEB-001 HOLD does not block Laravel auth/dashboard/backend.** It blocks inventing the public-facing content/design.

## F. Practical execution sequence

**Sprint 0:** FND-001..006, ACAD-001..004.  
**Next on explicit coding GO:** ACAD-005; then DEC-BULK-001 + MSG-001B, DOC-LOA-001, FIN-001..005, FULL-001. All are planned modules, not source code yet.
**Historical code checkpoint:** REV-001..003 already DONE; subsequent packages/Co-Host, sessions and QR follow core. Full Paper/payment TOR deadlines need official reconciliation, not invented extensions.
**Next group:** ID cards, QR service builder/scanner, session, program forms, QA-003.
**Production readiness:** SMTP/queue testing, template/signatory approvals, official bank verification, HTTPS QR domain, public design after WEB-001.

These are **logical groups**, not commitments of exact hours. Always deliver a complete user-facing workflow, not a shell of disconnected modules.

## G. Definition of Done (any task)

- Meets stated PRD requirement and acceptance, no unrelated feature change.
- OOP and Laravel conventions followed; no duplicated business rules in multiple Livewire components.
- Authorization and server validation applied.
- Targeted tests pass or are labeled NOT RUN/BLOCKED with real reason.
- UI flows manually checked when applicable; mobile scanner checked on real hardware before event.
- Commit has meaningful scope and message; TASKS status updated only after evidence.
- Short handoff: files changed, test results, what works, blockers and commit ref.
- No public content/design implementation without owner discussion.

## H. Compact work-order template for AI

**Task(s):** ID(s) from this backlog.  
**Target flow:** what actor can do from entry to completion.  
**Read:** only relevant sections of PRD/standard + touched source files.  
**Allowed scope:** specific modules/files; no unrelated refactor.  
**Forbidden:** public/frontend HOLD, fabricated business values, destructive DB, leaked secrets.  
**Validation:** targeted tests + one direct browser scenario if UI.  
**Report:** short evidence + status + next eligible task.  
**Authorization:** implementation only after owner directs coding.
