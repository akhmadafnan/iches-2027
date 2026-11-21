# ACAD-003 — Abstract, OJS-style Keywords & Private PDF

**Status:** IMPLEMENTED, verified in local MySQL/Edge and automated tests; **awaiting owner UAT**. Changes are version-controlled on main.
**Date:** 21 November 2026.
**Source:** [PRD §AC-04](PRD.md), [TASKS](TASKS.md); builds on owner-approved ACAD-002 Paper Editor.

## Scope implemented

- Reuses the existing `/my/submissions/create` and `/my/submissions/{paper:public_id}/edit` Paper Draft Editor; preserves permanent `reference`, ULID `public_id`, owner, ROR affiliation, author order and corresponding author.
- Draft `abstract_text` and JSON list `keywords` are stored as additive nullable fields. Text may be unfinished. Technical limit: 10,000 characters; not an approved academic abstract word count.
- Keywords are separate OJS-like chips: add with **Enter** or **Add keyword**, trim/collapse spaces, reject case-insensitive duplicates, remove individual chips; last unsaved input is captured by **Save draft**. Technical limits: 2–80 characters per tag, max 15 tags. Academic rules still TBD.
- PDF uploaded to Laravel `local` private storage under `paper-abstracts/{owner_id}/{paper_public_id}/`, random storage filename, original filename for display, size and timestamp recorded. Draft may be saved with no file. Upload validation includes actual PDF MIME/extension, maximum 10 MB and `%PDF-` header check.
- Owner-only authenticated download route `papers.abstract.download`; Policy guard, path namespace and file existence check, no direct public file URL. A different user gets 403 on download; guest is redirected to login. Replacing or removing PDF is staged until **Save draft**, and old file is deleted after successful DB commit. Failed save attempts preserve the previous PDF.
- There is **no Submit action**, no `Submitted` transition, no reviewer access, and no fee/payment behavior. Those belong to later tasks; ACAD-004 implements Submit.

## Files

- Migration: `database/migrations/2026_11_21_000004_add_abstract_fields_to_papers.php`.
- Action: `app/Actions/Conference/SavePaperDraft.php`.
- Livewire: `app/Livewire/Conference/PaperEditor.php`.
- View: `resources/views/livewire/conference/paper-editor.blade.php`.
- Model: `app/Models/Paper.php`.
- Download controller: `app/Http/Controllers/PaperAbstractDownloadController.php`.
- Route: `routes/web.php`.
- Tests: `tests/Feature/Conference/PaperAbstractDraftTest.php`.

## Verified results — Laptop 2 (DESKTOP-PE5D86C)

- MySQL additive migration `2026_11_21_000004`: **Ran (batch 10)**; existing Paper ID and authors schema unchanged.
- `php artisan test --compact`: **155 tests PASS / 872 assertions**.
- Focused `PaperAbstractDraftTest`: **18 tests PASS / 109 assertions**.
- `php vendor/bin/pint --test --parallel`: **95 files PASS**.
- PHPStan: **0 errors**.
- Vite frontend build, Blade compilation and `git diff --check`: PASS.
- Microsoft Edge headless on the real Herd HTTPS application: authenticated existing Draft opened successfully; abstract/keyword/PDF sections rendered; Enter creates a keyword chip, case-insensitive duplicate blocked, second chip added/removed, mobile 390px has zero horizontal overflow, JavaScript page errors none; no Submit button.
- Microsoft Edge uploaded a local temporary PDF through the Livewire file input and showed its filename/selected status without persisting anything. **No real paper was saved by the browser QA**. Private file create/replacement/removal/download behavior is proven by automated feature tests, not by an owner manual browser cycle.

## Owner UAT — complete before ACAD-004

1. Login on Laptop 2 at `https://iches-2027.test` (local demo account, or your own test account), open `/my/submissions` and **Edit draft** or **New paper draft**.
2. Confirm the compact authors/affiliation modal from ACAD-002 is unchanged. Record title/track/authors once; save as **Draft** and note `ICH27-P...`.
3. Enter the abstract text, add **Islamic Education** via Enter, **Pesantren Studies** via Add keyword; try the case-insensitive duplicate `islamic education` (must be rejected), then remove one tag. Save and reopen; verify persistence without creating a second Paper ID.
4. Upload a genuine `.pdf` up to 10 MB, save Draft, reopen, **Download** as owner; verify PDF opens. Upload an invalid/non-PDF file: it must be rejected without losing the existing PDF.
5. Replace PDF with a different genuine PDF, save/reopen/download; verify new file. Test **Remove → Undo** without saving, then **Remove → Save Draft** and verify file is gone.
6. Verify another account cannot open the Draft edit route or download the private PDF; reviewer must not see any Draft.
7. Verify status stays **Draft**, no Submit button, no payment or email triggered. Inspect on desktop and mobile.

**Decision gate:** Owner marks ACAD-003 UAT PASS/FAIL after testing. Do not begin ACAD-004 or invent official academic limits before explicit approval.

## Known academic decisions to set before ACAD-004

The official abstract word range, official keyword count, and officially required PDF limit have **not** yet been agreed in PRD/TOR for this task. Current numeric limits are technical safeguards only; do not present them as committee rules.
