# ICHES 2027 — Conference & Event Management System

**The 6th International Conference on Humanity, Education, and Society (ICHES 2027)**  
Universitas Islam Syarifuddin (UNISYA), Lumajang, Indonesia | **5–6 January 2027**

This repository now contains the **official Laravel 13 + Livewire 4 Starter Kit** foundation. Planned modules include the academic submission lifecycle, reviewer workflow, five ICHES programs, Co-Host and international package participation, manual payments, ID Cards, a personal QR, scanner services, hybrid parallel sessions and reporting. **These business modules are not implemented yet.**

> **Current status (20 November 2026): FND-001, FND-002 and FND-003 DONE.** Official Laravel 13/Livewire 4 foundation, Herd HTTPS, Fortify authentication, ICHES registration and personal profile editing are implemented and tested. Four development-only role demo accounts are seeded. **Role-based authorization and assignments (FND-004), personalized dashboard, submissions, QR and other program workflows remain TODO.** Public landing page remains the unmodified starter-kit placeholder. No production URL is known.

## Start here

| Document | Purpose |
|---|---|
| [Master Blueprint](MASTER-BLUEPRINT-ICHES-2027.md) | Project vision and full event process |
| [Product Requirements (PRD)](docs/PRD.md) | Actors, business rules, modules, acceptance |
| [Task Backlog](docs/TASKS.md) | Prioritized P0/P1/P2 tasks, dependencies and outcomes |
| [Working Rules](docs/WORKING-RULES.md) | Lightweight way of working, safety and Git |
| [OOP Laravel/Livewire Standards](docs/OOP-LARAVEL-LIVEWIRE-STANDARDS.md) | Architecture, naming, actions, policies, testing |
| [AI Agent Instructions](AGENTS.md) | Scope and quick guidance for AI coding assistants |
| [Public Frontend — Discussion Pending](docs/PUBLIC-FRONTEND-TBD.md) | Decisions intentionally on hold |

## Local development — Foundation and account registration verified

**Working directory:** `D:\PINJAM-AFNAN\iches-2027` on Windows.
**Local Herd URL:** `https://iches-2027.test` (Herd Secure Site; only available on the configured development computer).
**Installed foundation:** PHP 8.4.27, Laravel 13.43.0, Livewire 4.4.8, Flux UI 2.22.0, MySQL 9.7.

For a fresh clone, create `.env` from `.env.example`, configure an **isolated MySQL development database** and credentials, then:

```powershell
composer install
npm install
php artisan key:generate
php artisan migrate
php artisan db:seed # local/testing only; creates demo accounts
npm run build
php artisan test --compact
composer run lint:check
composer run types:check
```

Do not commit `.env`, passwords, keys, personal data, or vendor/node_modules. `composer.lock` and `package-lock.json` are committed for repeatable installs. The Herd link on this computer is `iches-2027.test`, not an official public website.

**Verified FND-001:** five default migrations on isolated MySQL `iches_2027`; 38 PHP tests / 97 assertions, Pint 45 files, PHPStan, npm build PASS.

**Verified Herd HTTPS + FND-002 (20 Nov 2026):** Secure Site generated a local certificate; HTTP redirects to HTTPS (301); HTTPS `/`, `/login`, `/register`, `/forgot-password` return 200; `.env` `APP_URL` uses HTTPS locally. ICHES registration stores institution, country and typed participation intention, without accepting privileged role selection; Fortify login, reset password, email verification and 2FA remain native. One additional migration ran without resetting data. All **42 PHP tests / 122 assertions** PASS; Pint 47 files and PHPStan PASS. Registration-intention dashboard CTA and profile editing remain future tasks. Windows curl certificate-revocation checking required `--ssl-no-revoke` for the local certificate check (no `-k` bypass).

## Local-only seeded demo accounts (NOT FOR PRODUCTION)

Run `php artisan db:seed` in the `local` environment to create the following *verified* demo identities. The seeder is **guarded against production** and is idempotent; rerunning does not reset existing passwords or promote colliding accounts.

| Base role | Email | Development password |
|---|---|---|
| User | `user@iches.id` | `password` |
| Reviewer | `reviewer@iches.id` | `password` |
| Committee | `committee@iches.id` | `password` |
| Admin | `admin@iches.id` | `password` |

**IMPORTANT:** These are disposable local/testing credentials only; never use them on an internet-accessible deployment. All new public registrations default to `user` regardless of submitted fields. The stored role column/enum is groundwork for **FND-004**; Admin/Reviewer/Committee access-control policies and staff assignments are **not yet implemented**, so role-labeled accounts do not imply working role-specific menus.

**FND-003 profile:** the authenticated user's `/settings/profile` page can edit name, email, institution and country, with server validation and email re-verification on address change. Role and initial intention are not editable from this form. Profile photo crop is a separate planned task (ID-001), not part of FND-003.

**Verification (20 Nov 2026):** four demo roles seeded on MySQL, second seed run succeeded without duplicates; **49 tests / 165 assertions** passed; Pint **51 files** and PHPStan no errors; dedicated migration marked `Ran`.

## Core application areas

- **Personal account:** register once, choose initial intention, access multiple programs and personal dashboard.
- **International Conference:** Abstract text/PDF, reviewer self-claim, academic decisions, revision, LoA, Full Paper, parallel session and Zoom.
- **Four companion activities:** International MoU, Research & PKM Upgrading, International Community Service, Sustainable Destination Study.
- **Payments:** manual bank transfer, one Co-Host institute invoice for max five representatives/papers, one IDR 1m personal bundle for four activities, verified finance workflow.
- **Digital identity:** cropped square profile photo, Participant green and Committee navy ID Card, QR/ID Card PNG.
- **QR operations:** Admin QR Service Builder; panitia select assigned service, scan QR on mobile web, confirm check-in/kit/meal, record history and prevent double redemption.
- **Administration:** program CRUD, participant management, assignments, session and speaker/moderator data, documents, exports.

**Essential separation:** paper decisions, program registration, verified payment, QR attendance, physical-service redemption and certificates are different events and must not share one ambiguous status.

## Product direction and intentional exclusions

- Laravel 13 compatible official Livewire Starter Kit, MySQL, Blade/Tailwind/Flux UI where appropriate.
- Modern object-oriented PHP and idiomatic Laravel, with simple Actions/Services, Models, Policies and targeted tests.
- No Vue/Inertia rewrite, custom REST frontend, microservices, native Android scanner, Zoom API, payment gateway, OJS integration, complicated quota/fee/rubric/stock system or drag-and-drop CMS in MVP.
- **The website PUBLIC's content, structure, exact visual styling, imagery and marketing copy are not decided yet.** They will be discussed separately with the owner. Please do not implement them as final.

## Key milestone targets

| Date | Intended checkpoint |
|---|---|
| 22 Nov 2026 | Honest prototype/demo for leadership |
| Before 1 Dec 2026 | Submission + reviewer flow tested for real opening |
| December 2026 | Academic decisions, Full Paper, finance, Co-Host/package |
| Before 5 Jan 2027 | ID Card/QR, sessions, Zoom manual, program operations tested |
| 5–6 Jan 2027 | ICHES 2027 event operations |
| After event | Certificate/documents and final reports |

Milestones are goals; no functionality is claimed complete simply because it appears in a document.

## Repository layout

The project root now contains the **standard Laravel application tree** (`app/`, `bootstrap/`, `config/`, `database/`, `public/`, `resources/`, `routes/`, `storage/`, `tests/`) alongside `artisan`, Composer/npm lock files, the original Blueprint, `README.md`, `AGENTS.md`, and `docs/`. Starter-kit authentication stays intact; build ICHES workflows in later authorized tasks.

## How AI contributors should work

1. Select an authorized task ID from [TASKS](docs/TASKS.md).
2. Read only matching PRD sections and [OOP Standard](docs/OOP-LARAVEL-LIVEWIRE-STANDARDS.md).
3. Build the smallest complete flow with correct server-side authorization and data integrity.
4. Run focused tests and user-flow checks, then report real results briefly.
5. Keep edits limited to that scope. Do not invent public frontend content or business rules.

## Repository About — proposed values

These are suggested GitHub About values until the repository metadata is set:

- **Description:** ICHES 2027 Conference & Event Management System — Laravel Livewire platform for submissions, programs, QR ID Cards, and event operations.
- **Website:** leave empty until an official deployment URL exists; do not pretend the GitHub repository URL is the public event site.
- **Topics:** laravel, livewire, php, mysql, conference-management, event-management, abstract-submission, qr-code, unisya, iches-2027.

## Source and status notes

The [previous repository](https://github.com/akhmadafnan/iches-unisya) is used only as an approved business reference; this is a fresh codebase. Confirmed TOR facts and earlier decisions are incorporated in the PRD, while unresolved implementation details are deliberately not turned into blockers.

**Primary goal:** usable software quickly, with professional design and clean Laravel OOP; not paperwork or architectural overengineering.
