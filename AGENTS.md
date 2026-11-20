# AGENTS.md — ICHES 2027 Coding Agent Instructions

**Repository:** akhmadafnan/iches-2027  
**Status sekarang:** planning-only. **No Laravel application source yet.** Do not begin implementation until the owner instructs GO CODING for a named task or scope.

## Read order (minimal token use)

1. [docs/TASKS.md](docs/TASKS.md) — task ID, dependency, acceptance.
2. [docs/PRD.md](docs/PRD.md) — only the sections related to the assigned task.
3. [docs/WORKING-RULES.md](docs/WORKING-RULES.md) — simple working boundaries.
4. [docs/OOP-LARAVEL-LIVEWIRE-STANDARDS.md](docs/OOP-LARAVEL-LIVEWIRE-STANDARDS.md) — PHP/Laravel quality rules.
5. [MASTER-BLUEPRINT-ICHES-2027.md](MASTER-BLUEPRINT-ICHES-2027.md) — high-level intent only if needed.

Do **not** recursively ingest all repository docs, previous repo docs, or irrelevant files for every task.

## Required implementation stack

- Official Laravel + Livewire Starter Kit, MySQL, Blade/Tailwind and compatible starter-kit UI.
- **Modern OOP PHP**: typed code, PSR-4, PSR-12/Pint, clear responsibilities.
- Class-based Livewire components for nontrivial domain workflows, idiomatic Eloquent Models, Policies, Actions/Services for business operations, validation and domain invariants.
- Use the installed framework's actual version/API; do not use imaginary classes/features.
- Smallest working implementation meeting task acceptance; no speculative architectural layers.
- Private files and scoped access, safe payments and QR concurrency are mandatory.

## Product rules never to regress

- One account / many program enrollments / one personal QR for ICHES.
- Public user registration selects participation intention, **not privileged role**.
- Five programs: International Conference, Research/PKM Upgrading, International MoU, International Community Service, Destination Study.
- Abstract text + PDF valid Submitted immediately reviewer-claimable, **no administrative screening**.
- Reviewer claims eligible papers, cannot claim own paper; reviewer recommendation ≠ final academic decision.
- Accepted **Published** opens LoA and Full Paper regardless of payment Verified.
- Co-Host: one IDR 3m institutional invoice, after Verified five representative slots/five paper entitlements; one sponsored paper per personal account, no duplicate personal Conference charge.
- Personal IDR 1m International Package: **one** invoice, **four** program entitlements after Verified; MoU attendance ≠ signatory.
- QR: one private token per person/event, many configurable Admin QR Services, no duplicate physical redemption, program attendance separate.
- ID Card matches official user-shared Participant green/Committee navy reference, cropped square photo; online-only excluded from physical print list.
- Scanner browser mobile first; Zoom URL manual; no native Android or Zoom API in MVP.
- **PUBLIC FRONTEND IS ON HOLD:** do not invent or implement final site content, menu, branding or public layout. Consult [docs/PUBLIC-FRONTEND-TBD.md](docs/PUBLIC-FRONTEND-TBD.md).

## Do-not-do list

- No code creation before authorization.
- No unrelated refactors or more features than the requested task.
- No direct editing of old ICHES repo.
- No force push, DB wipe, destructive migration, production email send or deploy without explicit permission.
- No credentials, actual personal/payment data, .env or private files in Git.
- No claim of test PASS or production readiness without observed evidence.
- No bureaucracy for tasks that can be completed with simple Laravel conventions.

## Per-task execution contract

**Input:** task ID(s), expected flow, allowed scope and test target.  
**Action:** inspect only relevant code, implement minimal OOP solution, run safe targeted test when possible.  
**Output:** short report — DONE/PARTIAL/BLOCKED, modified paths, test command and actual result, commit SHA if created, blockers.

Update TASKS status only after acceptance and tests are verified.

**Reminder:** The goal is working software quickly, not producing another set of oversized planning documents.
