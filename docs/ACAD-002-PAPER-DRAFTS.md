# ACAD-002 — Paper Drafts, Authors & Permanent Identifiers

**Status:** Implemented; full automated suite **119 tests / 652 assertions PASS**, Pint **88 files PASS**, PHPStan **no errors**, Blade compilation and frontend build PASS. Edge desktop/mobile and add/remove-coauthor checks PASS, no JS error or document overflow. **Owner UAT pending.**
**Functional contract:** [PRD §5.2 AC-03](PRD.md), [TASKS](TASKS.md), [Master Blueprint §3.1](../MASTER-BLUEPRINT-ICHES-2027.md).
**Technology:** Laravel 13, official Livewire Starter Kit/Livewire 4, Flux UI Emerald, MySQL.

## Implemented scope

- `/my/submissions` — the **authenticated person's own paper list**, with search, No. column, status, track, permanent paper ID, Page X of Y and Previous/Next. Empty state is responsive on mobile.
- `/my/submissions/create` — creates a **Draft**, never an actual academic submission.
- `/my/submissions/{paper:public_id}/edit` — edits only the current owner's Draft. Other accounts cannot even open this edit URL, and a non-Draft cannot be edited.
- Metadata: working paper title, one **active** Conference Track (from the eight official TOR subthemes), full author list with author order, full name, email, optional institution and country, and **exactly one corresponding author**.
- Author controls: add, remove, move up/down, and change corresponding author. The authenticated user's profile is prefilled into the **first row only as a convenience**, but the order and corresponding author remain editable. Coauthor records do **not** confer paper ownership or give coauthors account access.
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

## Manual UAT

1. On **Laptop 2** (`DESKTOP-PE5D86C`), log in to `https://iches-2027.test` using **local-only** `user@iches.id` / `password`.
2. Open `/my/submissions` in the sidebar. Verify the centered empty state and the **New paper draft** action, both on desktop and mobile.
3. Open `/my/submissions/create`. Enter a title and choose one of the eight conference tracks; the initial author row should already show the account's name/email/affiliation.
4. Add a coauthor, enter a different email, move the author up/down, choose a corresponding author, and click **Save draft**.
5. Return to My Submissions: see a stable `ICH27-P...` paper ID and status **Draft**. Click **Edit draft**, update fields, save again, refresh and confirm **the paper ID does not change**.
6. Try the same email for two author rows, invalid email, missing title/track, or missing author name: validation must block saving.
7. Log in as a different local account and try the first user's edit URL; it must return **404**, and their My Submissions list must not expose the original paper.
8. Confirm mobile editor has no horizontal page overflow. If testing creates a synthetic Draft, it remains in your private Demo account until you choose how to manage that data. No delete function is provided in ACAD-002.

## Explicitly not implemented

ACAD-003 adds **abstract text, keywords, and private PDF validation/upload** to the same owned Draft. ACAD-004 adds the **Submit** action, submission timestamp, and transition into reviewer eligibility. A saved Draft is never automatically available for Reviewer self-claim. No LoA, payment obligation, parallel session, public website content, or coauthor account-sharing workflow is introduced here.
