# ICHES 2027 — Development Workstations & Local Synchronization

**Updated:** 21 November 2026. **Decision:** Laptop 1 is now the primary development workstation. Windows paths identify the WORKSTATION, not an immutable machine requirement.

## Official machine mapping

| Attribute | Laptop 1 — CURRENT PRIMARY | Laptop 2 — previous source / secondary |
|---|---|---|
| Remote device display | `Laptop_Afnan` | `DESKTOP-PE5D86C` |
| Repository path | **`D:\PROJEKKU\iches-2027`** | `D:\PINJAM-AFNAN\iches-2027` |
| Git remote / branch | `https://github.com/akhmadafnan/iches-2027.git` / `main` | same repository, `main` |
| Laravel app environment | local Herd PHP + MySQL | local Herd PHP + MySQL |
| Local URL | should resolve via local Herd `https://iches-2027.test` if linked/trusted | `https://iches-2027.test` on Laptop 2 |
| Approved LoA source | **`design\loa\v2.1\ICHES-LOA-MASTER-V2.1.html` within the repository** | same after sync; historical standalone master also at `D:\PINJAM-AFNAN\ICHES\LOA-DESIGN-V2.1` |

## Repo / code / document authority

1. The repository on **Laptop 1** at `D:\PROJEKKU\iches-2027` is the only ACTIVE day-to-day project directory for subsequent development. **Do not create `D:\PINJAM-AFNAN` on Laptop 1**, do not copy into `C:\Users\...\Herd` as a separate project, and do not assume historical UAT files from Laptop 2 are present or required.
2. GitHub `main` is shared for **approved committed work**, not local `.env` or user uploads. `docs/CURRENT-STATE-AND-DECISIONS.md`, `docs/TASKS.md`, `docs/PRD.md`, this file, `docs/WO-DOC-LOA-001-HTML-PDF-IMPLEMENTATION.md` and `design/loa/v2.1` are the current source of truth.
3. Historical statements such as 'ran on Laptop 2 (DESKTOP-PE5D86C)' remain valid evidence for that historical run, not an instruction to work there again. Interpret hard-coded Laptop 2 paths in older UAT notes as historical and replace only for commands actually executed on Laptop 1.
4. The approved LoA master is **HTML/CSS**, with the PDF produced from the same master in `design/loa/v2.1`. Editing the PDF as an independent design is prohibited. Final typography: number Courier New Bold 11pt, issuance date Sora 500 8pt, both footer labels Sora Bold 7.2pt. No new layout without owner design GO.
5. Original source code and user-owned project data must never be silently erased during regular sync. This initial transition is an explicit owner request to **overwrite target working files without backups**. Future changes still require targeted testing and OWNER UAT PASS before source commit/push.

## Local-only .env / DB / uploaded files

- Owner explicitly requested **Laptop 1 `.env` be copied from Laptop 2** for DEVELOPMENT, and intends to adjust individual settings later. `.env` and `APP_KEY` are secrets: do not commit, print values into docs, or publish through GitHub.
- `DB_DATABASE=iches_2027` is a local per-machine MySQL database, not a remote shared database. Sync its schema and development records explicitly and check migration state; never assume git pull transports database records.
- Local user/role/account data, paper `ICH27-P000003`, its review and other created academic records must remain available for UAT. Transfer private uploads privately; never copy to public webroot just to synchronize machines.
- Keep environments in `local` and mailer as current development settings; do not mistake local log mailer for real SMTP. `.env` can be adjusted for each machine only when needed, per the owner's instructions.
- A synced `.env` may point at a different MySQL user or database from the old laptop configuration. Validate `php artisan about`, database connectivity and migrations on Laptop 1 after transfer.

## After takeover — commands from Laptop 1

```powershell
Set-Location 'D:\PROJEKKU\iches-2027'
git status --short --branch
composer install --no-interaction
npm ci
php artisan migrate --force
php artisan optimize:clear
npm run build
php artisan test --compact
```

Run full and targeted tests sequentially on Windows to avoid Blade cache collision. Always distinguish a verified successful local test from a historical Laptop 2 result.

**Release gate:** DOC-LOA-001 Master Design OWNER PASS; its actual backend/QR/signature coding remains TODO until separate owner GO.