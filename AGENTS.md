# AGENTS.md — ICHES 2027 Coding Agent Instructions

**Repository:** akhmadafnan/iches-2027  
**Status sekarang (20 Nov 2026):** FND-001 Laravel/Livewire Starter Kit foundation is installed and locally verified in `D:\PINJAM-AFNAN\iches-2027`. Business modules remain TODO. Do not implement additional modules without the owner's instruction for a named task or scope.

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
