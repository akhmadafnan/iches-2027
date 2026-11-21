# ICHES 2027 — Conference & Event Management System

**The 6th International Conference on Humanity, Education, and Society (ICHES 2027)**  
Universitas Islam Syarifuddin (UNISYA), Lumajang, Indonesia | **5–6 January 2027**

This repository now contains the **official Laravel 13 + Livewire 4 Starter Kit** foundation. Planned modules include the academic submission lifecycle, reviewer workflow, five ICHES programs, Co-Host and international package participation, manual payments, ID Cards, a personal QR, scanner services, hybrid parallel sessions and reporting. **Paper Drafts and Conference Track administration are now implemented; final abstract/PDF submission, reviewer workflows, enrollment, payments, QR and other operations remain future phases.**

> **Current status (21 November 2026): FND-001 through FND-006 and ACAD-001 owner UAT PASS; ACAD-002 Paper Drafts/ROR functionality owner UAT PASS, with an OJS-style Add/Edit Author modal refinement now ready for owner UI UAT.** Laravel 13/Livewire 4, Herd HTTPS, Fortify onboarding, role dashboards, five programs, Event Settings, seven dates, eight TOR tracks, and owner-managed Draft paper metadata/coauthors with permanent references are available. **Abstract/PDF upload and submission (ACAD-003/004), review, enrollment, finance and QR are NOT yet available.** Public website design remains on HOLD.

## Start here

| Document | Purpose |
|---|---|
| [Master Blueprint](MASTER-BLUEPRINT-ICHES-2027.md) | Project vision and full event process |
| [Product Requirements (PRD)](docs/PRD.md) | Actors, business rules, modules, acceptance |
| [Task Backlog](docs/TASKS.md) | Prioritized P0/P1/P2 tasks, dependencies and outcomes |
| [FND-006 TOR Master Data Baseline](docs/FND-006-TOR-BASELINE.md) | Verbatim source verification, canonical program codes, dates and safeguards |
| [ACAD-001 Conference Tracks](docs/ACAD-001-CONFERENCE-TRACKS.md) | Eight immutable-source TOR titles, track administration and UAT |
| [ACAD-002 Paper Drafts](docs/ACAD-002-PAPER-DRAFTS.md) | Owner-managed Drafts, compact contributor list, Flux Add/Edit modal, ROR affiliations, permanent IDs and UAT |
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

**IMPORTANT:** FND-004 validates permissions only. FND-005 adds role-aware dashboards but does not make paper review, QR scanning, program enrollment, or finance workflows functional.

## UAT FND-005 — Dashboard & navigation (local only)

1. Log in to `https://iches-2027.test/dashboard` using one of the four seeded `@iches.id` accounts (password `password`, development only).
2. **Admin:** dashboard shows live **Registered accounts**, **Verified emails**, and **Staff accounts** counts from the database. Only Admin sees the **Users & Access** workspace link.
3. **Reviewer:** dashboard has a link to the role-protected **Reviewer Workspace**. Review queues/self-claim are explicitly marked for later development, not shown as working.
4. **Committee:** dashboard links to **Committee Workspace**. Finance/Academic delegated-duty labels appear **only if actually assigned**; QR scan tools remain future work.
5. **User:** dashboard shows the chosen registration intention and tailored next-step text for **Submit Paper / Join Conference / Explore Programs**, with **Coming soon** on unbuilt modules. Profile editing is the real active CTA.
6. Sidebar uses **Overview**, role-scoped **Workspace** (hidden for User), and **My Account** with Profile/Security; the generic Laravel starter-kit Repository/Documentation links have been removed.
7. Confirm the same page works on phone widths. All private routes remain protected by `auth`, `verified`, `profile.complete`, and dedicated server-side role gates.

**Verification (20 Nov 2026):** **84 tests / 358 assertions PASS**; PHPStan no errors, Pint **65 files** PASS, npm build PASS; Microsoft Edge actual authenticated renders for all four seeded roles PASS, plus 390-pixel mobile User screen, no JavaScript errors or document horizontal overflow. No fictitious paper/payment/program counters or public frontend design were added.

## UAT FND-006 — Five Programs & Event Settings (local only)

1. Sign in at `https://iches-2027.test/login` using the **local-only** `admin@iches.id` demo account.
2. Open `https://iches-2027.test/admin/programs`: confirm five programs, accurate TOR names/dates, venue/mode labels, column **No.**, **Page 1 of 1** at bottom-left and **Previous/Next** on right. Search by name/venue or permanent code.
3. Click **Edit** on any program: modal opens, immutable code is visible, and Admin can edit title, description, program start/end dates, venue, optional mode and sort order. Try invalid end-before-start dates; they must be rejected. Restore the official data after testing any real changes.
4. Open `https://iches-2027.test/admin/event-settings`: edit event name, theme, organizer, venue, date range, optional time and timezone; all start times are initially **blank** unless officially confirmed. Save, refresh and verify persistence.
5. In **Important dates**, verify seven TOR milestones; **Edit** opens a modal, and invalid date ranges are rejected. The milestones **do not** independently open Full Paper, publish decisions or verify payments.
6. Sign in as `user@iches.id`, `reviewer@iches.id`, or `committee@iches.id`: both Admin paths must yield 403, with Admin-only navigation hidden. New accounts still complete profile before entering dashboards.
7. Review desktop and mobile layout. On narrow screens tables scroll **inside** their card; no horizontal overflow should appear across the entire document.

**Automated verification:** **95 tests / 450 assertions PASS** (11 new FND-006 tests), Pint **74 files**, PHPStan no errors, Blade compilation and npm build PASS. MySQL migration `2026_11_20_000005` ran safely; re-running `EventMasterSeeder` retains **1 event, 5 programs, 7 milestones** and does not overwrite Admin modifications. **Owner UAT for FND-006 was reported PASS before ACAD-001.** Initial automated browser inspection of that earlier phase was not completed.

## UAT ACAD-001 — Conference Tracks (local only)

1. As local-only `admin@iches.id` (password `password`), open `https://iches-2027.test/admin/conference-tracks`. Verify eight English TOR titles, TRK-01 through TRK-08, all initially Active.
2. Check the **No.** column, **Page 1 of 1** bottom-left, and **Previous / Next** buttons bottom-right. Search and filter Active/Inactive.
3. Click **Edit track**: permanent code and original TOR title are read-only. Edit the display title, description, sort order, or availability; click **Save track**, refresh and verify persistence. Restore any test changes afterward.
4. Test invalid order or duplicated display name: changes must be rejected. The last remaining active track cannot be disabled.
5. On mobile, horizontally scroll the table within its card. Sign in as `user@iches.id` and verify the Admin URL returns **403 Forbidden**.

Source: [ACAD-001 Conference Tracks](docs/ACAD-001-CONFERENCE-TRACKS.md). The active-track selector is now wired into **ACAD-002 Draft editor**, while abstract text/PDF uploading and final submission are still reserved for ACAD-003/004. **Verification:** 105 application tests / 546 assertions PASS; Laravel Pint 79 files PASS; PHPStan and npm build PASS; Edge desktop/mobile visual QA and role-based HTTP 403 checks PASS. **ACAD-001 owner UAT PASS.**

## UAT ACAD-002 — Paper Drafts (local only)

1. Log in at `https://iches-2027.test/login` as `user@iches.id` / `password` (local demo only), then open **My Submissions** or `https://iches-2027.test/my/submissions`.
2. If the directory is empty, click **Start a draft**; otherwise select **New paper draft**. Enter a paper title and one of the eight official academic tracks.
3. The Authors & Affiliations card displays only contributor **name, institution, and corresponding status**. Click **Edit** for the first author or **Add author**: a Flux modal opens with full name/email, ROR search, country, and corresponding options. Search **UNISYA**, click the real ROR result, confirm verified name+ID, then click **Save author**. The directory updates in memory; the outer **Save draft** writes it to MySQL. For an organization absent from ROR use the explicit unverified/manual option (no fabricated ROR ID).
4. Open **Add author**, type provisional details and click **Cancel**: no blank co-author must appear. Open **Edit** on an existing author, change a field and cancel: the directory must remain unchanged. Add a valid co-author, use Up/Down or Make corresponding in the directory, then **Save draft**. Reopen and confirm the saved author list and the permanent `ICH27-P...` ID are unchanged by re-editing.
5. Try a missing/invalid title, duplicate author emails or invalid author data; the form must reject them.
6. Log in as another account; the other user's paper must not appear, and its private edit URL must return **404**.
7. On mobile verify the **compact author directory**, scrolling Flux modal and ROR suggestions; no whole-page horizontal overflow. Profile institution text remains only a search hint; **My Profile ROR integration and ACAD-003 Keywords/PDF are explicitly outside this refinement**.

See [ACAD-002 implementation and full UAT checklist](docs/ACAD-002-PAPER-DRAFTS.md). **OJS-style author-modal refinement verification:** 137 tests / 763 assertions PASS, Pint 92 files PASS, PHPStan/Blade/npm build PASS; Edge Add/Edit, ROR search, Cancel, coauthor ordering and mobile 390px PASS. **New modal owner UI UAT pending.**

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
