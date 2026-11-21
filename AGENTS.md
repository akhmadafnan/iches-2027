# AGENTS.md — ICHES 2027 Coding Agent Instructions

**Repository:** akhmadafnan/iches-2027  
**ACTIVE STATUS (21 Nov 2026):** **READ [docs/CURRENT-STATE-AND-DECISIONS.md](docs/CURRENT-STATE-AND-DECISIONS.md) BEFORE ANY TASK.** FND-001..006, ACAD-001..005, REV-001..003, DEC-BULK-001 + MSG-001B and AUTH-MR-001 are **DONE — OWNER UAT PASS**. Multi-role/Superadmin and active Switch Workspace are installed. **NEXT: DOC-LOA-001**, where its Official HTML Letterhead V2.1 has **OWNER DESIGN PASS** and is tracked under `design/loa/v2.1/`; the generator/QR/backend remains **NOT CODED** until owner GO. One owned paper per account, coauthor elsewhere allowed; one exclusive reviewer, Accept/Reject, author-facing General Comments only after Published decision. MSG-001A, Finance and Full Paper remain TODO. Do not make unauthorized source edits. **CURRENT ACTIVE LAPTOP IS LAPTOP 1** at `D:\PROJEKKU\iches-2027`; Laptop 2 `D:\PINJAM-AFNAN\iches-2027` is previous development machine. Read [workstation mapping](docs/DEVELOPMENT-WORKSTATIONS.md); older laptop2 UAT references are historical evidence.


**Auth/onboarding update (20 Nov 2026):** Fortify Passkeys are disabled and their login/security UI, Vite entry and JS dependency removed. New registration redirects to `/settings/profile` and sets `profile_setup_required`; verified private routes enforce completion. A user must upload a server-validated JPG/PNG/WebP (private `local` disk, max 3 MB, min 200×200) before entering the dashboard. Photo retrieval is owner-only behind auth; existing seeded UAT accounts are intentionally exempt from mandatory onboarding. ID Card crop 1:1 is **still TODO (ID-001)**. Keep email verification and 2FA working. This applies to the authenticated app, not the public website design.

## Official technical sources — MUST CONSULT

Use these **two upstream GitHub repositories as primary technical references** whenever installing, changing, or reviewing Laravel/Livewire code:

1. **Laravel 13 documentation:** [laravel/docs, branch `13.x`](https://github.com/laravel/docs/tree/13.x) — especially [Starter Kits](https://github.com/laravel/docs/blob/13.x/starter-kits.md), [Directory Structure](https://github.com/laravel/docs/blob/13.x/structure.md), [Release Notes](https://github.com/laravel/docs/blob/13.x/releases.md), plus relevant domain chapters.
2. **Official Laravel Livewire Starter Kit:** [laravel/livewire-starter-kit, branch `main`](https://github.com/laravel/livewire-starter-kit) — examine its [composer.json](https://github.com/laravel/livewire-starter-kit/blob/main/composer.json), source organization, routes, Fortify registration actions and Blade/Livewire templates.

**Verified reference snapshot (20 November 2026):** upstream kit specifies `laravel/framework: ^13.17`, `livewire/livewire: ^4.1`, `livewire/flux: ^2.13.1`, `laravel/fortify: ^1.37.2`, and `php: ^8.3`; the readme also identifies Tailwind and TypeScript. Verify fresh upstream/current installed versions **at execution time**, not from remembered versions alone. Laravel docs' default branch is `13.x` and the kit's default is `main`.

**Priority when technical guidance disagrees:** actual locked installed dependencies and source > matching official upstream Laravel 13 / Starter Kit docs for that version > repo project engineering standards > older snippets/agent assumptions. Do not replace built-in Fortify, starter-kit auth, or the stock Blade/Livewire structure without a concrete task justification. Add class-based Livewire components for ICHES business workflows, but preserve official scaffold patterns where they already work.

These repositories are **reference sources, not code to copy wholesale into this repo**. The ICHES PRD/TASKS remain authoritative for PRODUCT BEHAVIOR. No coding authorized by this section.

## Read order (minimal token use)

1. [docs/CURRENT-STATE-AND-DECISIONS.md](docs/CURRENT-STATE-AND-DECISIONS.md) — locked policies, source-vs-plan, next task and new-chat handoff.
2. [docs/TASKS.md](docs/TASKS.md) — task ID, dependency, acceptance.
3. [docs/PRD.md](docs/PRD.md) — only task-relevant rules.
4. [docs/WORKING-RULES.md](docs/WORKING-RULES.md) — coding/UAT/Git safety gates.
5. [docs/OOP-LARAVEL-LIVEWIRE-STANDARDS.md](docs/OOP-LARAVEL-LIVEWIRE-STANDARDS.md) — technical patterns.
6. [MASTER-BLUEPRINT-ICHES-2027.md](MASTER-BLUEPRINT-ICHES-2027.md) — overall product and TOR context.

Do not ingest old repository docs or unrelated files. **An old UAT note is not a current implementation instruction.**

## ICHES application UI conventions — locked 20 Nov 2026

- Use the **installed Flux UI** for application controls (tables, inputs, selects, buttons, badges, modals, pagination) and Tailwind for page composition. Refer to [official Flux theming](https://fluxui.dev/docs/theming), [table](https://fluxui.dev/components/table) and [modal](https://fluxui.dev/components/modal) docs.
- The global app theme lives in `resources/css/app.css`: **emerald-600 accent**, emerald-400 accent-content in dark mode, white foreground; preserve these Flux CSS variables unless the owner explicitly requests a different palette.
- For management indexes, enforce clear heading hierarchy, generous spacing, a full-width responsive table with leftmost sequential **No.**, **Page X of Y** at bottom-left and **Previous / Next** at bottom-right; use a modal for compact edit forms. Avoid flat, cramped, default-looking tables.
- Test behavior with Livewire/feature tests and **visually inspect actual browser rendering** whenever the environment allows. Do not claim a visual review without a screenshot/browser check.
- These are **authenticated app UI** conventions only. The **public frontend** remains on HOLD until explicitly discussed.

## Required implementation stack

- Official Laravel + Livewire Starter Kit, MySQL, Blade/Tailwind and compatible starter-kit UI.
- **Modern OOP PHP**: typed code, PSR-4, PSR-12/Pint, clear responsibilities.
- Class-based Livewire components for nontrivial domain workflows, idiomatic Eloquent Models, Policies, Actions/Services for business operations, validation and domain invariants.
- Use the installed framework's actual version/API; do not use imaginary classes/features.
- Smallest working implementation meeting task acceptance; no speculative architectural layers.
- Private files and scoped access, safe payments and QR concurrency are mandatory.

## Product rules never to regress

- One account / many program enrollments / one personal QR for ICHES.
- Public user registration selects participation intention, **not privileged role**.
- Five programs: International Conference, Research/PKM Upgrading, International MoU, International Community Service, Destination Study.
- Abstract text + PDF valid Submitted immediately reviewer-claimable, **no administrative screening**.
- Reviewer claims exactly one active reviewer per abstract; no owner/co-author claim. General Comments + Accept/Reject only; reviewer recommendation is INTERNAL until AcademicAuthority bulk Publish & Notify.
- One submitting owner account can own only one paper, even after Rejected; co-author elsewhere remains allowed (ACAD-005 TODO).
- Published Accepted opens automatic LoA + invoice/payment; Full Paper ONLY after applicable Finance Verified (verified Co-Host institutional entitlement may substitute personal payment). Rejected has no forward academic access.
- DEC-BULK-001 must replace per-paper DEC-001/002; AcademicAuthority can override with reason and correct published decision with audit. Author decision mail uses after-commit idempotent queue. LoA is auto PDF+QR and NEVER a per-paper Drive upload.
- Co-Host: one IDR 3m institutional invoice, after Verified five representative slots/five paper entitlements; one sponsored paper per personal account, no duplicate personal Conference charge.
- Personal IDR 1m International Package: **one** invoice, **four** program entitlements after Verified; MoU attendance ≠ signatory.
- QR: one private token per person/event, many configurable Admin QR Services, no duplicate physical redemption, program attendance separate.
- ID Card matches official user-shared Participant green/Committee navy reference, cropped square photo; online-only excluded from physical print list.
- Scanner browser mobile first; Zoom URL manual; no native Android or Zoom API in MVP.
- **PUBLIC FRONTEND IS ON HOLD:** do not invent or implement final site content, menu, branding or public layout. Consult [docs/PUBLIC-FRONTEND-TBD.md](docs/PUBLIC-FRONTEND-TBD.md).

## Do-not-do list

- No code creation before authorization.
- No unrelated refactors or more features than the requested task.
- No direct editing of old ICHES repo.
- No force push, DB wipe, destructive migration, production email send or deploy without explicit permission.
- No credentials, actual personal/payment data, .env or private files in Git.
- No claim of test PASS or production readiness without observed evidence.
- No bureaucracy for tasks that can be completed with simple Laravel conventions.

## Per-task execution contract

**Input:** owner-authorized task ID(s), expected flow, allowed scope and test target. When legacy sources conflict with CURRENT-STATE-AND-DECISIONS.md, respect latest product policy, but do not modify source outside explicit owner GO.
**Action:** inspect only relevant code, implement minimal OOP solution, run safe targeted test when possible.  
**Output:** short report — DONE/PARTIAL/BLOCKED, modified paths, test command and actual result, commit SHA if created, blockers.

Update TASKS status only after acceptance and tests are verified.

**Reminder:** The goal is working software quickly, not producing another set of oversized planning documents.
