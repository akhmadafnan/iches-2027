# ICHES 2027 — Conference & Event Management System

**The 6th International Conference on Humanity, Education, and Society (ICHES 2027)**  
Universitas Islam Syarifuddin (UNISYA), Lumajang, Indonesia | **5–6 January 2027**

A planned **Laravel + Livewire Starter Kit** application supporting the academic submission lifecycle, reviewer workflow, five ICHES programs, Co-Host and international package participation, manual payments, on-site ID Cards, a personal QR, configurable scanner services, hybrid parallel sessions and operations reporting.

> **Current status (20 November 2026): Documentation and product preparation ONLY.** No Laravel app, database migrations, installed starter kit or tested business feature has been created in this repository. Implementation begins on the owner's instruction. No production URL is known.

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

## Repository layout now

    .
    ├── README.md
    ├── AGENTS.md
    ├── MASTER-BLUEPRINT-ICHES-2027.md
    └── docs/
        ├── PRD.md
        ├── TASKS.md
        ├── WORKING-RULES.md
        ├── OOP-LARAVEL-LIVEWIRE-STANDARDS.md
        └── PUBLIC-FRONTEND-TBD.md

**There is no Laravel source tree yet.** Install project source only after GO CODING. Later, application quick-start and test commands should be documented here based on actual installed dependencies and environment—not guessed in advance.

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
