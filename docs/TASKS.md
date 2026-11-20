# IMPLEMENTATION TASK BACKLOG — ICHES 2027

**Version:** 1.0 | **Date:** 20 November 2026 | **Status:** FND-001 + FND-002 DONE; all other tasks TODO
**Source:** [PRD](PRD.md) + [Master Blueprint](../MASTER-BLUEPRINT-ICHES-2027.md).  
**Priority:** P0 = critical flow, P1 = essential follow-up, P2 = polish/post-event.  
**Never treat TODO as implemented.** Code changes begin only when the owner instructs us to start coding.

## A. Working with the backlog

- A task contains ID, PRD requirement, outcome, dependency, acceptance.
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
| FND-003 Profile, institution and country | ACC-03, ACC-04 | FND-002 | Profile only editable by owner |
| FND-004 Simple permissions and assignment | ACC-06 | FND-002 | User/Reviewer/Committee/Admin boundaries checked on server |
| FND-005 Minimal dashboards and navigation | ACC-03 | FND-004 | Initial CTA based on intention; role menus scoped |
| FND-006 Five program records + event settings | PG-01 | FND-001 | Actual data from TOR seeded and Admin-readable |
| ACAD-001 Conference tracks CRUD | AC-01 | FND-004 | 8 TOR tracks seeded, selectable in form |
| ACAD-002 Paper model, authors, stable identifiers | AC-03 | FND-002, ACAD-001 | Draft create/edit scoped to owner |
| ACAD-003 Abstract form and private PDF upload | AC-04 | ACAD-002 | Text, keywords, author metadata, PDF; server rejects invalid files |
| ACAD-004 Submit action and version/status timeline | AC-04, AC-05, AC-10 | ACAD-003 | Draft remains editable; Submitted valid immediately indexable |
| REV-001 Reviewer available index and filters | AC-06 | ACAD-004, FND-004 | Submitted eligible paper visible; draft not |
| REV-002 Atomic self-claim and conflict check | AC-06 | REV-001 | Own paper blocked; concurrent claim safe |
| REV-003 Save/submit review and recommendation | AC-07 | REV-002 | Only claimant can edit, review history kept |
| DEC-001 Internal academic decision | AC-08 | REV-003 | Authorized officer can decide; author sees nothing prematurely |
| DEC-002 Explicit Publish Decision & tracking | AC-08, AC-10 | DEC-001 | Author notified by dashboard, status and history accurate |
| QA-001 P0 feature tests + browser user journey | ACC/AC/REV | All relevant P0 | Register→Submit→Review→Decision PASS or exact blocker listed |

**Demo checkpoint (22 Nov 2026):** demonstrate the longest *fully working* P0 journey possible using real functions and clearly labeled demo users/data. **Not guaranteed production-ready**. Do not consume time on landing-page aesthetics before academic core works. QR prototype optional if time allows.

**FND-001 evidence (20 Nov 2026):** official `laravel/livewire-starter-kit` branch `main` scaffold, PHP 8.4.27, PHP dependencies and npm dependencies locked, dedicated database `iches_2027`, five migrations Ran, locally linked `https://iches-2027.test` via Herd (Secure Site enabled 20 Nov 2026), 38 tests/97 assertions, Pint 45 files, PHPStan, and build PASS. Changes are limited to baseline Laravel app, configuration examples, README/agent/task status; no ICHES business module or final website content built.

**FND-002 evidence (20 Nov 2026):** Herd TLS active and HTTPS GET `/`, `/register`, `/login`, `/forgot-password` return 200; HTTP redirects 301; `.env` APP_URL local HTTPS. Default Fortify login/logout, reset password, email verification and two-factor flows retained. Added nullable MySQL registration fields, typed enum intention, server-side validation and registration form; six targeted registration tests passed. Full suite **42 tests / 122 assertions**, Pint **47 files** and PHPStan passed. No privilege chosen at signup; FND-003 profile editor and FND-005 intent-based dashboard CTA remain TODO. No public landing-page design has been approved or implemented.

## C. P1 — Academic Completion, Co-Host & Finance

| Task | PRD | Dependency | Acceptance / tangible output |
|---|---|---|---|
| DEC-003 Revision Required flow | AC-09 | DEC-002 | Revision is a version on same paper ID, comments scoped |
| DEC-004 Accepted Published unlocks Full Paper | AC-11, AC-12 | DEC-002 | Internal ACCEPT does not unlock; Verified not required |
| DOC-001 Per-paper LoA document link in dashboard | DOC-01, DOC-02 | DEC-004 | Paper owner sees only own LoA URL/file |
| FIN-001 Authorized bank settings | FI-02 | FND-004 | No fake bank information, authorized actor only |
| FIN-002 Invoice models & snapshot of price/payer | FI-01 | FND-002 | Institution/personal invoices distinguished |
| FIN-003 Private payment proof and status | FI-03, FI-04 | FIN-002 | Upload JPG/PNG/PDF, Pending not Verified |
| FIN-004 Finance verification / correction / ledger | FI-04, FI-07 | FIN-003 | Finance authorization, matches real bank statement |
| FIN-005 Regular presenter billing only on published ACCEPT | FI-05 | FIN-002, DEC-002 | No premature invoice; Full Paper remains independent |
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
| SESS-001 Parallel session CRUD and paper assignment | AC-13 | DEC-002 | Accepted paper gets room/slot/track/moderator |
| SESS-002 Online/onsite modes, Zoom URL | AC-14 | SESS-001 | Presenter sees link or room; no secret host credentials |
| SESS-003 Moderator Presented/No Show workspace | AC-15 | SESS-001 | Only own assigned session can be marked |
| QA-003 QR device/concurrency and session field test | QR/AC-13..15 | Tasks above | Real-phone scan, reject duplicate, online-only, session tests |

**Operational checkpoint:** all QRS and session tasks must be exercised on actual committee phone/network before 5 January. No pretending offline scan confirms delivery.

## E. P2 — Documents, Notification, Reporting, and Public Site

| Task | PRD | Dependency | Acceptance / tangible output |
|---|---|---|---|
| DOC-002 Documents Center, certificates via PDF/link | DOC-01, DOC-03 | FND-003, SESS-003 | Only authorized/ready files visible, publish explicit |
| MSG-001 Event-based email notifications and queue | COM-01, COM-02 | Core domains | Sends after transaction commit; failures reported |
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
**Sprint 1:** REV-001..003, DEC-001..002, QA-001.  
**Sprint 2:** DEC-003..004, finance and package, Co-Host, QA-002.  
**Sprint 3:** ID cards, QR service builder/scanner, session, program forms, QA-003.  
**Sprint 4:** Notifications/reports/documents; public design after WEB-001; operational readiness.

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
