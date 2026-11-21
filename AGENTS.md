# AGENTS.md — ICHES 2027 Coding Agent Instructions

**Repository:** akhmadafnan/iches-2027  
**Status sekarang (21 Nov 2026):** FND-001 through FND-006 completed with owner UAT PASS; ACAD-001 owner UAT PASS and ACAD-002 implemented and automated/browser-tested locally in `D:\PINJAM-AFNAN\iches-2027` at Herd HTTPS `https://iches-2027.test`. Fortify login, registration, private photo/profile onboarding, role gates and staff assignments are active. Four local-only seeded accounts use `user`, `reviewer`, `committee`, `admin`. The `/dashboard` route is **App\Livewire\Dashboard** (class-based) with live Admin account counts, Reviewer/Committee workspace shortcuts, and a User CTA chosen by `registration_intention`; the private sidebar shows only authorized role links. Admin `/admin/users` changes other users' roles and delegated Finance/Academic Authority; Moderator/CoHost PIC authorization remains resource-scoped. Reviewer/Committee workspaces are still placeholders, NOT working review/QR modules. FND-006 supplies five immutable-code program masters, singleton Event Settings and seven TOR Important Dates via Admin-only `/admin/programs` and `/admin/event-settings`; mutations are guarded by Gates/validation, event start hour is NULL until approved, and seed reruns never overwrite Admin edits. User-facing program enrollment, ACAD submission/review, payments and QR remain TODO. FND-006 owner UAT PASS. ACAD-001 adds 8 official TOR Section E.2 Conference Tracks (`TRK-01`...`TRK-08`), immutable codes/official titles, editable display details/order/availability, and Admin-only `/admin/conference-tracks`; idempotent seed runs after EventMasterSeeder. Active track selection query is available for the future author form. ACAD-001 owner UAT PASS. ACAD-002 adds owner-scoped Paper/Author models, immutable human reference and ULID, Draft-only transactional saving, corresponding/ordered coauthors, Policy and private `/my/submissions` routes. Its 21 Nov revision adds ROR API v2 service, per-author canonical affiliation + verified ROR ID, explicit unlisted/manual fallback (unverified), and a centered no-pagination empty state. No invented ROR IDs; legacy affiliations remain unverified until reselected. My Profile affiliation is still free text and out of this revision's scope. The owner confirmed ACAD-002 Paper Draft/ROR UAT PASS, and requested an OJS-style **UI-only refinement**: compact ordered author directory showing name+institution+corresponding, buffered Flux modal for Add/Edit, cancel discards, ROR picker and server-side Save Draft unchanged. The owner confirmed ACAD-002 OJS-style modal UAT PASS and explicitly authorized ACAD-003. ACAD-003 adds nullable abstract text, JSON keyword tags (Enter/Add/Remove; case-insensitive duplicate guard), a 10MB validated PDF in Laravel private local storage, owner-only download and replacement/removal on Save Draft. It never changes Draft status, reviewer visibility, fees, or Paper identity. Numeric limits are **technical safety ceilings, not committee-approved academic requirements**. ACAD-003 is awaiting owner UAT. **Do not begin ACAD-004 without explicit owner instruction.** Do not implement new modules without owner instruction; demo password `password` is never for production.

**Auth/onboarding update (20 Nov 2026):** Fortify Passkeys are disabled and their login/security UI, Vite entry and JS dependency removed. New registration redirects to `/settings/profile` and sets `profile_setup_required`; verified private routes enforce completion. A user must upload a server-validated JPG/PNG/WebP (private `local` disk, max 3 MB, min 200×200) before entering the dashboard. Photo retrieval is owner-only behind auth; existing seeded UAT accounts are intentionally exempt from mandatory onboarding. ID Card crop 1:1 is **still TODO (ID-001)**. Keep email verification and 2FA working. This applies to the authenticated app, not the public website design.

## Official technical sources — MUST CONSULT

Use these **two upstream GitHub repositories as primary technical references** whenever installing, changing, or reviewing Laravel/Livewire code:

1. **Laravel 13 documentation:** [laravel/docs, branch `13.x`](https://github.com/laravel/docs/tree/13.x) — especially [Starter Kits](https://github.com/laravel/docs/blob/13.x/starter-kits.md), [Directory Structure](https://github.com/laravel/docs/blob/13.x/structure.md), [Release Notes](https://github.com/laravel/docs/blob/13.x/releases.md), plus relevant domain chapters.
2. **Official Laravel Livewire Starter Kit:** [laravel/livewire-starter-kit, branch `main`](https://github.com/laravel/livewire-starter-kit) — examine its [composer.json](https://github.com/laravel/livewire-starter-kit/blob/main/composer.json), source organization, routes, Fortify registration actions and Blade/Livewire templates.

**Verified reference snapshot (20 November 2026):** upstream kit specifies `laravel/framework: ^13.17`, `livewire/livewire: ^4.1`, `livewire/flux: ^2.13.1`, `laravel/fortify: ^1.37.2`, and `php: ^8.3`; the readme also identifies Tailwind and TypeScript. Verify fresh upstream/current installed versions **at execution time**, not from remembered versions alone. Laravel docs' default branch is `13.x` and the kit's default is `main`.

**Priority when technical guidance disagrees:** actual locked installed dependencies and source > matching official upstream Laravel 13 / Starter Kit docs for that version > repo project engineering standards > older snippets/agent assumptions. Do not replace built-in Fortify, starter-kit auth, or the stock Blade/Livewire structure without a concrete task justification. Add class-based Livewire components for ICHES business workflows, but preserve official scaffold patterns where they already work.

These repositories are **reference sources, not code to copy wholesale into this repo**. The ICHES PRD/TASKS remain authoritative for PRODUCT BEHAVIOR. No coding authorized by this section.

## Read order (minimal token use)

1. [docs/TASKS.md](docs/TASKS.md) — task ID, dependency, acceptance.
2. [docs/PRD.md](docs/PRD.md) — only the sections related to the assigned task.
3. [docs/WORKING-RULES.md](docs/WORKING-RULES.md) — simple working boundaries.
4. [docs/OOP-LARAVEL-LIVEWIRE-STANDARDS.md](docs/OOP-LARAVEL-LIVEWIRE-STANDARDS.md) — PHP/Laravel quality rules.
5. [MASTER-BLUEPRINT-ICHES-2027.md](MASTER-BLUEPRINT-ICHES-2027.md) — high-level intent only if needed.

Do **not** recursively ingest all repository docs, previous repo docs, or irrelevant files for every task.

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
- Reviewer claims eligible papers, cannot claim own paper; reviewer recommendation ≠ final academic decision.
- Accepted **Published** opens LoA and Full Paper regardless of payment Verified.
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

**Input:** task ID(s), expected flow, allowed scope and test target.  
**Action:** inspect only relevant code, implement minimal OOP solution, run safe targeted test when possible.  
**Output:** short report — DONE/PARTIAL/BLOCKED, modified paths, test command and actual result, commit SHA if created, blockers.

Update TASKS status only after acceptance and tests are verified.

**Reminder:** The goal is working software quickly, not producing another set of oversized planning documents.
