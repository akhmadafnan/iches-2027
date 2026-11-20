# ICHES 2027 — Conference & Event Management System

**The 6th International Conference on Humanity, Education, and Society (ICHES 2027)**  
Universitas Islam Syarifuddin (UNISYA), Lumajang, Indonesia | **5–6 January 2027**

This repository now contains the **official Laravel 13 + Livewire 4 Starter Kit** foundation. Planned modules include the academic submission lifecycle, reviewer workflow, five ICHES programs, Co-Host and international package participation, manual payments, ID Cards, a personal QR, scanner services, hybrid parallel sessions and reporting. **These business modules are not implemented yet.**

> **Current status (20 November 2026): FND-001 through FND-004 DONE.** Laravel 13/Livewire 4, Herd HTTPS, Fortify authentication, ICHES registration/profile, and server-enforced roles and staff assignments are implemented. Four development-only role demo accounts are seeded. **Contextual dashboards (FND-005), abstract/review workflows, QR and other program modules remain TODO.** Public landing page remains the starter-kit placeholder. No production URL is known.

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

**IMPORTANT:** These are disposable local/testing credentials only; never use them on an internet-accessible deployment. All new public registrations default to `user` regardless of submitted fields. Role/Gate/Policy checks are now active (FND-004). Admin can manage other users' roles and the global Finance/Academic Authority assignments. Specific Reviewer and Committee routes exist only as access-verified placeholders; the actual academic review and QR scan modules will be built in their respective tasks.

**FND-003 + onboarding enhancement (20 Nov 2026):** `/settings/profile` edits name, email, institution and country, and now supports a securely validated **private profile-photo upload** (JPG/PNG/WebP, max 3 MB, min 200×200). New registrants are immediately redirected to `/settings/profile`; they must upload a photo before opening verified private dashboard/role pages. Existing accounts/seeded UAT users are not retroactively blocked. Photos are accessed only by their authenticated owner at `/settings/profile/photo`; they are not on a public storage disk. Login/password confirmation remain available; **Passkeys are fully disabled** in Fortify routes, auth/security UI and JS build. **Cropping a 1:1 ID Card photo remains ID-001 TODO.**

**Verification (20 Nov 2026):** four demo roles seeded on MySQL, second seed run succeeded without duplicates; **49 tests / 165 assertions** passed; Pint **51 files** and PHPStan no errors; dedicated migration marked `Ran`.

## UAT new-account onboarding and Passkeys (local only)

1. Open `https://iches-2027.test/register` with a new test email; complete normal registration.
2. Confirm the redirect to `/settings/profile` and the `Complete your ICHES profile` notice.
3. Attempt Save without a photo: the system requires a photo. Upload JPG, PNG or WebP (max 3 MB, min 200×200); preview it and Save.
4. After saving, the user is redirected to the dashboard (if email is not yet verified, Fortify's email-verification page remains in force). Private photos cannot be accessed by a guest or another account.
5. Login and Security screens no longer expose Passkeys; normal password, reset and 2FA flows remain available.

Four existing `@iches.id` demo accounts are **not** forced into onboarding. Their profile page allows an optional photo upload. Upload replaces old private file on save; deleting or changing a user role cannot be done from Profile. The **photo crop/ID Card pipeline is separate and not yet implemented**.

## UAT FND-004 — Roles & access (local only)

1. Open `https://iches-2027.test/login`. Log in with the development accounts listed above (password `password`).
2. As **Admin**, open `https://iches-2027.test/admin/users`. The Users & Access screen permits changing **other users'** primary roles. An Admin cannot edit their own role.
3. As **Reviewer**, `/reviewer/workspace` must load; `/admin/users` and `/committee/workspace` must return **403 Forbidden**.
4. As **Committee**, `/committee/workspace` must load; Admin/Reviewer routes must return 403.
5. As **User**, all three special role routes must return 403. The personal profile settings remain available.
6. In Admin → Users & Access, grant a **Committee** user Finance or Academic Authority, then save. Such duty **does not** follow from being Admin/Committee by default; only an explicit assignment grants it. Clear the checkbox before changing the primary role to User. Moderator/Co-Host PIC resource assignments are deferred until sessions/institutions exist.

**IMPORTANT:** This phase verifies permissions, not full reviewer/admin/QR workflows or role-specific dashboards. FND-005 will provide contextual dashboards and navigation.

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
