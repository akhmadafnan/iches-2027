# AUTH-MR-001 — SUPERADMIN, MULTI-ROLE & ACTIVE WORKSPACE SWITCHING

**ICHES 2027 • 21 November 2026**
**Status:** **DONE — OWNER UAT FINAL PASS (21 November 2026)**; code, documentation and local demo-role seeder completed and released in one scoped Git commit/push to `main`.
**Priority:** COMPLETED PRE-REQUISITE BEFORE DOC-LOA-001. DOC-LOA-001 itself remains NOT CODED and requires a separate owner GO.
**Authoritative policy:** Single authenticated account can own multiple roles, but EXACTLY ONE role is active per browser session; all module permissions are server-gated using the active role, not a union of assigned roles.

## A. Final role taxonomy

| Assigned role | Active workspace permissions |
|---|---|
| **Participant** (`user`) | Own academic submissions, personal account. Ownership never expands to other authors. |
| **Reviewer** (`reviewer`) | Reviewer Workspace and eligible review operations. Cannot review own paper or verified coauthored paper. |
| **Committee** (`committee`) | Committee operational workspace. Sensitive AcademicAuthority or Finance requires its separate existing delegated duty. |
| **Administrator** (`admin`) | Users & Access, programs, conference tracks, event settings. Academic decisions and financial verification STILL require separate explicit delegated duty. |
| **Superadmin** (`superadmin`) | **Every installed application module gate**: Admin, Committee, Reviewer, AcademicAuthority, Finance and scoped operational features; system owner controls. Personal paper ownership, reviewer conflicts, audit/idempotency, and private participant identities remain protected. |

**Important terms:** A *Switch Role / Switch Workspace* changes the current user's active permissions; it NEVER logs in as another account (no identity impersonation), changes password/email, changes paper ownership, or merges identities. All audit events continue to identify the REAL authenticated user ID.

## B. Data architecture

- Retain existing `users.role` as **default/legacy primary role**, avoiding breaking seeded accounts or historical callers.
- Add `user_roles` pivot-like membership records (`user_id`, enum string `role`, timestamps, unique user+role). **Additive migration `2026_11_21_000010`**, preserving all users/staff assignments, automatically backfilling every existing `users.role`.
- Roles are fixed application enums (not a permissive free-text permission editor). Future capabilities are added centrally through server Gates and Policies.
- A user has one default role and at least one membership. Admin can assign multiple normal roles, but cannot grant Superadmin. **Only an active Superadmin** can grant or revoke another Superadmin; nobody can edit their own role set using the admin panel. Last root membership may not be removed.
- Existing `staff_assignments` for Finance/AcademicAuthority (global) and Moderator/CoHost PIC (scoped) remain separate. Those specific delegated duties are still required in Committee/Admin mode; root mode handles all active module gates.
- The browser-session setting `access.active_role` and bound user ID is only a **preference**. Every request verifies live membership; a forged/revoked/stale role is rejected or falls back to an authorized default. Sessions for other users cannot inherit it.
- The LOCAL/TESTING-only `DatabaseSeeder → DemoAccountsSeeder` now creates a distinct verified `superadmin@iches.id` demo account with all five role memberships; other preexisting accounts are not promoted or reset. The demo root credential is generated uniquely and written only to local private storage. Production never invokes these demo seeders.

## C. Interaction/UX

- **Desktop:** accessible "Active workspace" switcher just above the existing account menu at the bottom of the left sidebar. Lists only memberships assigned to the account; single-role accounts see a read-only active role.
- **Mobile:** matching role options in the mobile account dropdown.
- Choosing a role submits an authenticated CSRF-protected `POST /workspace/switch-role`, validates membership SERVER-side, refreshes the session, logs actor + from/to, and returns to Dashboard with the chosen view. A role switch is not a new login.
- **Users & Access:** multiselect role checkboxes with a selected default login role, root-only Superadmin assignment, explicit Finance/Academic Authority duty toggles, and multiple role badges in the directory. The role filter matches ANY membership, not just legacy primary.
- **Dashboard and sidebar:** rendered based on the **active role**; Superadmin sees all installed admin/reviewer/committee/academic workspaces; reviewer mode hides admin actions even if that same person is also an administrator.

## D. Local role seeder and first SUPERADMIN account

**Single source:** `database/seeders/DemoAccountsSeeder.php` is called from `DatabaseSeeder` ONLY in `local/testing`, and now includes ALL five enum roles. The older separate `LocalSuperadminUatSeeder.php` was removed to prevent conflicting provisioning paths. This seeder is safe to rerun: it does not reset existing users' passwords or names and ensures primary and SUPERADMIN role memberships even when `DatabaseSeeder` suppresses Eloquent model events.

**Verified on local MySQL (`iches_2027`, 21 Nov 2026):** `php artisan db:seed --class=DemoAccountsSeeder --no-interaction` completed successfully. The separate account `superadmin@iches.id` exists, has verified email, `users.role=superadmin` and all five role memberships. Four existing demo accounts still use their prior development password. The new root account uses a generated 32-character password, with its private local credential in `storage/app/private/uat/superadmin/credentials.txt` (not Git); it does **not** use the shared `password`. The primary paper `ICH27-P000003` remains Submitted with its one original review. Test validation includes default `DatabaseSeeder` (model events off), role memberships and idempotent reseeding.

To inspect the local SUPERADMIN login:

```bat
cd /d D:\PINJAM-AFNAN\iches-2027
notepad storage\app\private\uat\superadmin\credentials.txt
```

If the database is reset in a separate local development environment, seed again with `php artisan db:seed` (master data and demo roles) or `php artisan db:seed --class=DemoAccountsSeeder` (demo accounts only). Never run `migrate:fresh` on the current data-bearing database for this task. Production provisioning is separately authorized and does not use demo accounts. The alternate `access:bootstrap-superadmin` command remains for a separate verified personal-account bootstrap **only when no Superadmin exists**; it does not modify this seeded root.

## E. OWNER UAT (do not modify original paper)

Use the already seeded LOCAL demo `superadmin@iches.id` to test all five workspaces; credentials are in `storage/app/private/uat/superadmin/credentials.txt`. For delegated role assignment tests, use another dedicated test account or existing normal demo account and preserve real owner identities. Avoid changing the original `ICH27-P000003` paper/reviewer/decision. The LOCAL role-demo Superadmin was created by `DemoAccountsSeeder`; no new paper/review UAT data was created.

| Test | Action | Expected result |
|---|---|---|
| UAT-01 | Admin opens Users & Access, edits another ordinary user; assigns Reviewer + Committee, selects Reviewer default | Both role memberships persist; default Reviewer; no Superadmin privilege |
| UAT-02 | Login as multi-role account and switch to Committee using sidebar bottom control | Committee workspace opens; Reviewer area 403 |
| UAT-03 | Switch back to Reviewer | Reviewer workspace opens; Admin and Committee areas 403 |
| UAT-04 | Try to POST an unassigned role or `superadmin` directly | 403; active role unchanged |
| UAT-05 | While in Reviewer mode, an authorized Admin revokes Reviewer membership | Access immediately falls back to an assigned role; reviewer routes 403 |
| UAT-06 | Ordinary Admin tries to edit Superadmin or self-promote | Denied server-side, not only hidden in UI |
| UAT-07 | Authenticated Superadmin active context opens Users & Access, Reviewer Workspace, Committee Workspace, Academic Decisions | All installed module routes permitted, with no extra Finance/Academic duty assignments |
| UAT-08 | Superadmin switches to Reviewer mode | Privileged Admin/Academic actions become inaccessible until switching back |
| UAT-09 | Superadmin tries to view another submitter's private paper as if its owner, or review own paper | Ownership/reviewer conflict protections still enforce rules |
| UAT-10 | Test sidebar/mobile responsiveness and switch repeatedly | UI labels remain correct; no crash and no accidental identity switch |

**Automated QA verified:** full regression **255 tests / 1,559 assertions PASS** (run sequentially), PHPStan/Laravel Pint/Blade PASS; existing Vite build PASS from prior AUTH-MR baseline. Targeted `DemoAccountsSeederTest` and `MultiRoleSuperadminTest` PASS, including `DatabaseSeeder` with model events suppressed. Migration `000010` backfilled 5 previous users / 5 role memberships; subsequently LOCAL demo seeding created one separate root user with five memberships (database now 6 users). Original paper `ICH27-P000003` and its one review preserved. The test suite uses an isolated test database; it is not evidence of browser/mobile UAT. **OWNER UAT FINAL PASS explicitly confirmed on 21 Nov 2026; implementation eligible for scoped Git commit/push.**

## F. Explicit boundaries

- NO account impersonation or switch-as-another-user functionality.
- NO automatic promotion of the existing Admin account to Superadmin.
- NO creation of a generic custom permission editor or menu toggles that can bypass server Gates.
- NO modifications to review outcomes, paper ownership, LoA, payment, QR, or public website.
- **DOC-LOA-001 remains not implemented.** AUTH-MR-001 has been finalized with OWNER UAT PASS; starting DOC-LOA requires a separate owner GO.

## G. Final owner acceptance and Git closeout (21 Nov 2026)

- **OWNER UAT PASS:** confirmed explicitly after testing the locally seeded five-role SUPERADMIN and Switch Workspace.
- **Database preserved:** six local users including demo `superadmin@iches.id` with five assigned roles, original paper `ICH27-P000003` still Submitted with one real review. No production account provisioning; demo credential is private local storage, not source control.
- **QA evidence:** sequential full regression **255 tests / 1,559 assertions PASS**; targeted `DemoAccountsSeederTest` and `MultiRoleSuperadminTest` PASS; PHPStan, Pint and Blade compilation PASS. Vite production build was successful in the earlier AUTH-MR cycle. Independent browser automation and concurrent-session stress testing are not claimed.
- **Technical scope:** five fixed roles, additive `user_roles`, legacy primary role compatibility, active-role authorization, Superadmin root workspace, bottom-sidebar/mobile role switch, multi-role Users & Access UI, idempotent LOCAL-only demo seeding, and no unrelated paper or decision changes.
- **UUID v7:** separately audited in [ARCH-UUIDV7 roadmap](ARCH-UUIDV7-EMAIL-REVIEWER-ROADMAP.md): the six review/decision/notification business tables use Laravel `HasUuids → Str::uuid7()`; old user/paper/master BIGINT identifiers and paper public ULID remain unchanged. **ARCH-002 legacy conversion is not approved or done.**
- Git scope for this closeout includes only AUTH-MR-001 code/tests and associated authoritative documentation. Private credentials, existing local database content, and unfinished DOC-LOA-001 are excluded from the Git commit.
