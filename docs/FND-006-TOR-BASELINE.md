# FND-006 — TOR-verified master-data baseline

**Status:** IN PROGRESS; not ready for UAT or release. **Primary reference:** user-provided `TOR ICHES 6 UNISYA 2027.docx` (nine pages), sections E, F, H, L, cross-checked with `docs/PRD.md` and `MASTER-BLUEPRINT-ICHES-2027.md`.

## 1. Official event identity
- Full name: **The 6th International Conference on Humanity, Education, and Society (ICHES 2027)**.
- Short name: **ICHES 2027**.
- Theme (verbatim from TOR): **ISLAMIC VALUES IN A CHANGING WORLD: EDUCATION, ACCOUNTABILITY, AND SOCIAL RESILIENCE FOR INCLUSIVE DEVELOPMENT**.
- Main conference location: **Universitas Islam Syarifuddin (UNISYA), Lumajang**.
- Conference date: **5 January 2027**. Field program date: **6 January 2027**.
- Event-wide date range: **5–6 January 2027**.
- Timezone for local settings: **Asia/Jakarta (WIB)**.
- **Do not seed an arbitrary event-wide starting hour**. A specific schedule slot cannot be substituted for an officially approved countdown start.
- Unconfirmed contact email, precise room/location, program capacity, new tariffs, and speakers must not be invented.

## 2. Canonical five programs

Stable machine codes must never be changed or deleted by the Admin form.

| code | Initial master display name | Date | Venue / mode baseline |
|---|---|---|---|
| `conference` | International Conference | 5 Jan 2027 | UNISYA Lumajang, hybrid confirmed by TOR |
| `research_pkm` | Pembinaan Riset dan PKM | 5 Jan 2027 | UNISYA Lumajang; mode not definitively specified |
| `international_mou` | Penandatanganan International MoU | 5 Jan 2027 | UNISYA Lumajang; mode not definitively specified |
| `community_service` | International Community Service | 6 Jan 2027 | Desa Sidomulyo, Kabupaten Lumajang; onsite |
| `destination_study` | Sustainable Destination Study | 6 Jan 2027 | Teras Semeru and Tumpak Sewu, Kabupaten Lumajang; onsite |

Editable Admin fields: program title, description, start/end calendar dates, venue, optional delivery mode, and display order. Do **not** create/destroy a sixth/one of these five canonical program identities through generic CRUD; associated records in later phases will depend on them.

## 3. Important Dates — TOR calendar

These dates are **planning / communication milestones**. They are not automatic entitlement or status gates.

| Immutable code | Milestone | TOR start | TOR end |
|---|---|---|---|
| `abstract_submission` | Abstract Submission | 2026-12-01 | 2026-12-20 |
| `abstract_decision` | Abstract Acceptance Announcement | 2026-12-22 | 2026-12-22 |
| `full_paper` | Full Paper Submission TOR target | 2026-12-23 | 2026-12-28 |
| `registration_payment` | Registration / Payment TOR target | 2026-12-24 | 2026-12-28 |
| `conference_day` | International Conference | 2027-01-05 | 2027-01-05 |
| `field_day` | Community Service / Destination Study | 2027-01-06 | 2027-01-06 |
| `proceedings` | Proceedings Publication Target | 2027-01-28 | 2027-01-28 |

Admin may edit milestone name, dates, and optional notes. **Accepted Published** (not merely date and not Finance Verified) unlocks Full Paper; payment verification is a separate action. Abstract reviewer self-claim starts on valid Submitted, without Administrative Screening. Invoice and program-access gates will be implemented in their respective later phases, not by reading milestones as boolean eligibility.

## 4. FND-006 implementation boundary

Expected scoped artifacts:
- `event_settings`: singleton event identity and date/timezone configuration; optional start time remains NULL until approved.
- `programs`: five machine-identifiable masters with unique codes and human-editable presentation fields.
- `event_milestones`: seven canonical, unique machine-code milestones with admin-editable target dates and optional notes.
- Idempotent seed from the TOR: **re-running must preserve Admin edits**, never produce duplicates, and never truncate existing data.
- Authenticated **Admin only** management in class-based Livewire + Flux UI Emerald, with named routes under `/admin/`; server Gates on route, component, and mutating actions.
- Tables: left-hand sequential **No.**, footer **Page X of Y** on left and **Previous/Next** on right; modal edit; responsive layouts.
- Keep public site design/content untouched. FND-006 does NOT implement participant program enrollment, financial invoices/package purchasing, academic papers/tracks, or QR services.

## 5. Acceptance and mandatory UAT

1. On isolated local MySQL, migrate additively (no `migrate:fresh`, resets or truncation). Verify exactly 1 event row, 5 program codes, 7 milestones, with documented values.
2. Seed twice and verify same record counts and persistent identifiers.
3. As Admin, edit program title/venue/date/mode/display order; persist; re-seed and confirm edits are preserved.
4. As Admin, edit general event identity/venue/theme/date/timezone and milestones. Verify validation prevents invalid or reversed date ranges, unsupported modes, excessively long fields, and invalid timezone.
5. As User, Reviewer, or Committee, visiting admin routes returns 403; forged Livewire method calls must also be forbidden. A new account still completes profile before entering any private routes.
6. Inspect actual Edge desktop and 390px mobile renders (table, modal, buttons, input validity, horizontal scroll). No JS/page errors.
7. Run targeted and complete PHP tests, Pint, PHPStan, and npm build. Commit/push only after PASS, then perform local site UAT.

**Note:** This document captures the verified source baseline and engineering constraints. It does **not** mark FND-006 as implemented or UAT PASS.
