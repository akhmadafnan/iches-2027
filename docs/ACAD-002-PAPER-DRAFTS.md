# ACAD-002 — Paper Drafts, Authors & Permanent Identifiers

**Status:** ACAD-002 Paper Draft, ROR and OJS-style compact contributor modal **owner UAT PASS (21 Nov 2026)**. Historical automated checkpoint: 137 tests / 763 assertions PASS, Pint 92 files PASS, PHPStan, Blade and npm build PASS. ACAD-003 has since been implemented and is awaiting separate owner UAT; see [ACAD-003](ACAD-003-ABSTRACT-KEYWORDS-PRIVATE-PDF.md).
**Functional contract:** [PRD §5.2 AC-03](PRD.md), [TASKS](TASKS.md), [Master Blueprint §3.1](../MASTER-BLUEPRINT-ICHES-2027.md).
**Technology:** Laravel 13, official Livewire Starter Kit/Livewire 4, Flux UI Emerald, MySQL.

## Implemented scope

- `/my/submissions` — the **authenticated person's own paper list**, with search, No. column, status, track, permanent paper ID, Page X of Y and Previous/Next when records exist. With zero drafts: a centered, spacious empty state with one primary CTA; no redundant hero CTA, search or pagination.
- `/my/submissions/create` — creates a **Draft**, never an actual academic submission.
- `/my/submissions/{paper:public_id}/edit` — edits only the current owner's Draft. Other accounts cannot even open this edit URL, and a non-Draft cannot be edited.
- Metadata: working paper title, one **active** TOR Conference Track, ordered authors, full name, email, **explicit institution affiliation** (ROR selected or unlisted/manual), optional author country independent of campus country, and **exactly one corresponding author**.
- Author controls: compact publication-order directory showing **only name, institution, and corresponding badge**. Buttons manage Add/Edit (Flux modal), remove with confirmation, up/down and corresponding designation. The authenticated user's profile prefills the first contributor record as a convenience; it is not considered ROR-verified until selected. Coauthors never receive ownership or access to the account.
- Paper ownership is always the authenticated **creator's account**; every verified, profile-complete account can create personal Drafts, independent of whether their global system role is User, Reviewer, Committee or Admin.

## Database and identity

Additive migration: `2026_11_21_000002_create_papers_and_paper_authors_tables.php`.

`papers` stores `owner_id` (FK, restrictive deletion), `conference_track_id` (FK), `public_id` (ULID, unique), `reference` (unique), `title`, `status` (`draft`), and timestamps. On first save, the transaction generates a unique **human-readable reference** such as `ICH27-P000001`, based on the database record's stable ID; the reference and ULID are never regenerated when title, track or authors change. The opaque ULID is used for route binding; **owner checks are mandatory even when the URL is unguessable**.

`paper_authors` stores an ordered snapshot of authors (`position`, `name`, `email`, optional `institution`, `country`, `is_corresponding`) with a unique position and unique email **per paper**. Author emails are normalized to lowercase on saving. The save Action updates paper metadata and replaces the ordered author list **inside one transaction**, preventing partial author writes.

## OOP and permission boundaries

- `App\Models\Paper`, `App\Models\PaperAuthor`, `App\Enums\PaperStatus`.
- `App\Policies\PaperPolicy`: creation and list access for authenticated actors, but viewing/editing a record requires the **matching owner_id**. `update` also requires `PaperStatus::Draft`.
- `App\Actions\Conference\SavePaperDraft`: central transaction, repeated authorization (including after row lock), conference-track integrity check and immutable reference creation.
- `App\Livewire\Conference\MyPapers` and `PaperEditor`: validated browser forms, owner-scoped queries, no direct changes to privileged status/ownership.
- Authorization is enforced in the route group (`auth`, `verified`, `profile.complete`) **and server-side Policy**, not by hiding a button alone.
- A disabled track cannot be selected by new Drafts, but an existing Draft may keep its previously selected track when it becomes disabled. No draft submission or reviewer queue is activated in this phase.
- No paper seeder and no synthetic paper stored in the shared development MySQL. **Demo user's My Submissions starts empty.**

## ROR Institution / Affiliation revision — 21 November 2026

- Official [ROR API v2 query](https://ror.readme.io/docs/api-query) powers university name/acronym search. A **Flux Input + class-backed Livewire dropdown** is used instead of licensed Flux Pro autocomplete/combobox components.
- Per-author search is debounced (450ms), limited to 10 active results and displays official name, aliases, city/country, and ROR ID. Search results are cached for 12 hours, individual record lookups for 7 days, and HTTP requests use a fixed HTTPS ROR API URL with a timeout. ROR API errors produce a user-visible retry message, never an invented match.
- Clicking a result retrieves the authoritative ROR record. Final Draft save **revalidates the ID server-side** and stores the canonical `ror_id` URL and official name; untrusted client-entered labels cannot be saved as ROR-verified.
- The **explicit** *Institution not listed in ROR* option permits a full manual institution name only with `affiliation_source=manual`; `ror_id` stays NULL and UI marks this as unverified. Merely typing an institution name is insufficient; no default ROR auto-selection is performed.
- Additive migration `2026_11_21_000003_add_ror_affiliation_fields_to_paper_authors.php` preserves existing rows with nullable `ror_id` and `affiliation_source`. Existing rows without provenance are marked **legacy/unverified** in the Draft editor until the owner explicitly selects a verified institution or confirms the manual fallback.
- ROR does **not** overwrite the author's separate country field. Saved Draft paper IDs, status, owner, author ordering and corresponding-author rules are preserved.
- **My Profile is not modified** in this revision; the prefilled plain-text institution serves only as a search suggestion and is not silently treated as ROR-verified. A future independent scope may harmonize profile affiliations.
- Validation and behavior tested with **11 dedicated ROR tests**, 130 full tests / 710 assertions, Pint 91, PHPStan, Edge real API search `UNISYA` -> [UNISYA ROR record](https://ror.org/041rf3s62), modal/select interaction, mobile 390px, and MySQL create/edit/rollback without persisted fake papers.


## OJS-style Author & Affiliation UI refinement — 21 November 2026

- The Paper Draft page now shows a **compact author directory**, displaying the ordered contributor's **name, institution, and corresponding author status** only. Emails, country and ROR fields are available inside the modal, not spread across the parent page.
- **Add author** and each **Edit** button open a class-backed Livewire/Flux modal with name, email, ROR lookup, optional country and corresponding author choice. Search-by-acronym, canonical ROR IDs and explicit unlisted/manual fallback are unchanged.
- Modal edits use a separate temporary `authorDraft`. **Cancel discards all unsaved modal changes**, including a newly drafted coauthor; **Save author** validates name/email/affiliation and stages a contributor in the editor. The outer **Save draft** remains the only action that persists paper/author records to MySQL.
- The directory supports **Move Up/Down**, **Make corresponding**, **Edit** and **Remove** (with confirmation). Corresponding index adjusts after reordering/removal and remains exactly one author. Duplicate author emails are rejected case-insensitively within the modal.
- No database migration, altered Paper ID, new status, academic submission, keywords, PDF upload, review, or public-page changes. Existing Draft authors and their ROR IDs are loaded into the modal unchanged.
- The existing 25 Paper/ROR tests remain PASS, plus **7 new targeted modal tests** covering cancel/add/edit, duplicate checks, ROR selection, corresponding order and stable Paper ID. Full suite **137 tests / 763 assertions PASS**, Pint **92 files PASS**, PHPStan no errors, Blade/npm build PASS. Edge browser QA confirms ROR dropdown, Cancel, coauthor staging and responsive directory on desktop/mobile; owner UI UAT remains pending.

## Manual UAT

1. On **Laptop 2** (`DESKTOP-PE5D86C`), log in to `https://iches-2027.test` using **local-only** `user@iches.id` / `password`.
2. Open `/my/submissions` in the sidebar. Verify the centered empty state and the **New paper draft** action, both on desktop and mobile.
3. Open `/my/submissions/create`. Enter a title and choose one of the eight conference tracks. The initial author row prefills name and email; any profile institution is only a search hint until the user selects an official ROR result.
4. The contributor list shows only names, affiliations and corresponding status. Click **Edit author 1** (or **Add author**) to open a Flux modal with name/email/ROR/country/corresponding controls. Search **UNISYA** or **Universitas Islam Syarifuddin**, choose the actual ROR `041rf3s62` result, then click **Save author**. For an unlisted institution select the explicit manual fallback. Contributor changes are staged until the separate outer **Save draft** button is clicked.
5. Return to My Submissions: see a stable `ICH27-P...` paper ID and status **Draft**. Click **Edit draft**, update fields, save again, refresh and confirm **the paper ID does not change**.
6. Test **Add author → type → Cancel** and **Edit author → change fields → Cancel**: contributor directory must not change. Try duplicate email, invalid email, missing institution, invalid ROR, and incomplete details within the modal; **Save author** must reject invalid data. Missing paper title/track must still prevent Save draft.
7. Log in as a different local account and try the first user's edit URL; it must return **404**, and their My Submissions list must not expose the original paper.
8. Verify compact contributor list, **modal with ROR suggestions** and corresponding controls in 390px mobile without page-level overflow. Typing a campus name without selecting an actual ROR result cannot save as verified. A manually saved UAT Draft stays in its owner's account until further authorized action. The app has no paper delete workflow in ACAD-002.

## Explicitly not implemented

The subsequent ACAD-003 now adds **abstract text, OJS-style individual keywords, and private PDF validation/upload** to the same owned Draft (see [ACAD-003](ACAD-003-ABSTRACT-KEYWORDS-PRIVATE-PDF.md)). ACAD-004 adds the **Submit** action, submission timestamp, and transition into reviewer eligibility. A saved Draft is never automatically available for Reviewer self-claim. No LoA, payment obligation, parallel session, public website content, or coauthor account-sharing workflow is introduced here.
