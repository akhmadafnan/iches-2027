# ACAD-001 — Conference Tracks Master Data

**Status:** implemented and automated/browser-verified on 21 November 2026 (**105 full tests / 546 assertions PASS**, Pint 79 files, PHPStan no errors, frontend build PASS). **Owner UAT pending.**
**Official source:** `TOR ICHES 6 UNISYA 2027.docx`, **Section E.2, sub-themes (a–h)**.
**Scope:** the International Conference program only; this task does not build papers, submissions, reviewers, or the public website.

## Eight official academic tracks

The `official_title` column is immutable in the Admin UI and keeps the wording of the original TOR. The `name` column is an editable display label.

| Stable code | Official TOR sub-theme |
|---|---|
| TRK-01 | Islamic Studies, Ethics, and Contemporary Civilization |
| TRK-02 | Education, Pedagogy, and Human Development |
| TRK-03 | Leadership, Governance, and Institutional Accountability |
| TRK-04 | Economics, Sustainable Business, and Inclusive Development |
| TRK-05 | Law, Human Rights, Social Justice, and Public Policy |
| TRK-06 | Social Sciences, Humanities, Culture, and Community Resilience |
| TRK-07 | Digital Transformation, Responsible Innovation, and Society |
| TRK-08 | Environmental Sustainability, and Human Well-Being |

## Technical structure

- Migration: `2026_11_21_000001_create_conference_tracks_table.php`. Additive table, FK to the existing `programs` table. The FK prevents orphaned tracks if a program is removed.
- Model: `App\Models\ConferenceTrack`. The `Program::conferenceTracks()` relation limits author-facing options to the International Conference. The `available()` Eloquent scope filters active tracks and sorts by display order and record ID.
- Master seeder: `ConferenceTrackSeeder`, run after `EventMasterSeeder` in `DatabaseSeeder`. Exactly eight original TOR titles are initially inserted with codes TRK-01...TRK-08; descriptions are **not fabricated**.
- Idempotent behavior: repeating this seeder does not duplicate records, overwrite edited titles, change availability, alter order, or reset descriptions. Permanent `code`, `official_title`, and `program_id` are intentionally not mass assignable.
- Admin route: `GET /admin/conference-tracks` (`admin.tracks`), guarded by `auth`, `verified`, `profile.complete`, and `can:manage-conference-tracks`.
- Class-backed Livewire 4: `App\Livewire\Admin\ManageConferenceTracks`. Index has live search, status filter, numbered rows, Page X of Y, Previous/Next, and Flux UI Emerald modal.
- Admin may update `name`, optional `description`, `sort_order` (1–99), and `is_active`. Duplicate display names, invalid fields, and disabling the last active track are prohibited. There is no deletion of official tracks and no speculative creation of extra tracks.
- Normal users, reviewers, and committee cannot open or invoke the Admin component. Future ACAD-003 paper forms should select `id/name` via `$conferenceProgram->conferenceTracks()->available()->get(...)` and **validate the track still belongs to the conference and is active** when saving.

## UAT checklist

1. Log in with local-only `admin@iches.id` and open `https://iches-2027.test/admin/conference-tracks`.
2. See eight official titles, TRK-01 to TRK-08 in order, active badges, and live counts. Check **No.** and bottom **Page 1 of 1** left, **Previous / Next** right.
3. Click **Edit track**. Confirm the permanent code and TOR original title are read-only. Edit display title/description/order or availability; save and refresh to verify persistence. If changed, restore original values afterward.
4. Search by code/title; filter Active/Inactive. Attempt duplicate titles or an out-of-range order to verify validation.
5. In a narrow mobile viewport, scroll the table **inside its card**. No horizontal document overflow.
6. Log in as `user@iches.id` and confirm the Admin route returns **403 Forbidden**.

**Boundary:** These eight records are not themselves abstract submissions. ACAD-002/003/004 will implement paper ownership, the author track-select field, PDF uploads, and submission workflows. Reviewer self-claim/academic decisions remain future work. The public frontend remains on HOLD.
