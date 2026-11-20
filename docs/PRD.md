# PRODUCT REQUIREMENTS DOCUMENT — ICHES 2027

**Version:** 1.0 | **Date:** 20 November 2026 | **Status:** Draft baseline for owner review, not authorization to code.  
**Product owner:** Penyelenggara/pemilik proyek ICHES UNISYA.  
**Source of truth:** [Master Blueprint](../MASTER-BLUEPRINT-ICHES-2027.md), this PRD, and [Tasks](TASKS.md). Old ICHES repository is a reference, not the new codebase.  
**Frontend publik:** **HOLD** until separate discussion and approval. See [Public Frontend](PUBLIC-FRONTEND-TBD.md).

## 1. Product summary

ICHES 2027 is one responsive web application for an international conference and four companion programs. Each person registers once, may join multiple programs, submits papers when applicable, tracks academic decisions, sees schedules and documents, and uses one individual QR and ID Card for on-site services. Reviewers, committee, finance and authorized academic decision-makers operate through focused workspaces. Admin manages program data, QR services, participants, finance and reports through simple CRUD.

**Technical direction:** new Laravel + official Livewire Starter Kit monolith, Blade/Livewire/Tailwind, MySQL, no separate Vue/Inertia frontend. OOP PHP and idiomatic Laravel; see [engineering standard](OOP-LARAVEL-LIVEWIRE-STANDARDS.md).

### Goals
- End-to-end working **Abstract Submission → Reviewer → Published Decision → Full Paper/LoA**.
- One account for five programs and clear online/onsite eligibility.
- One personal QR, two approved ID Card styles, multi-purpose Admin-configurable scanner services.
- Simple manual bank-transfer finance, Co-Host institute invoice and personal international package.
- Admin able to update routine program data without code changes.
- Verified behavior, clear status, safe access to each user's private information.

### Non-goals (do not introduce unless requested)
Complex field quotas, bundled facility breakdown, generic reviewer rubric builder, arbitrary fee-combination matrix, automated certificate eligibility for all activities, inventory ERP, native Android scanner, full offline synchronization, payment gateway, automatic Zoom API, OJS integration, page builder, generalized workflow engine, multi-event SaaS, AI features or complex scheduler. Simplification never overrides authentication, authorization, file and payment safety, or anti-duplicate scan controls.

## 2. Audience, access and registration

| Actor | Purpose | Permissions |
|---|---|---|
| Guest | Public content, register/login | Published information only |
| User | Presenter, ordinary attendee, bundle purchaser | Own profile, papers, orders, ID Card, programs |
| Reviewer | Read/claim/review papers | Eligible papers and own reviews only |
| Committee | Scanner and assigned event services | Assigned QR service/session only |
| Admin | Operate the event, CRUD, reports | Scoped permissions for sensitive actions |
| Assigned Academic Authority | Final decision + publish | Explicit delegated authorization |
| Assigned Finance Officer | Match bank mutation, verify payments | Explicit delegated authorization |
| Assigned Moderator | Confirm paper Presented/No Show | Assigned session only |
| Assigned Co-Host PIC | Manage institution's representatives | Own institution only |

Four broad access categories: User, Reviewer, Committee, Admin; the last four activities above are **assignments/permissions**, not additional public-selectable roles. A user with multiple assignments stays one account.

Registration captures: name, email, password, institution, country, and **intention** (Submit Paper / Join Conference / Explore Programs). Intention influences dashboard guidance, not permission. Upload profile photo later. User may join another program without registering again.

## 3. Dates and business baseline

Event is **5–6 January 2027**. TOR target: Abstract Submission **1–20 December 2026**, decision publication **22 December 2026**, Full Paper **23–28 December 2026**, registration/payment **24–28 December 2026**. Admin can change event/date configurations; start hour must not be invented. The Full Paper gate is **ACCEPT decision formally published to author**, independently of date and Finance Verified. Review begins on valid submitted abstracts without post-submission Administrative Screening.

Product prices/relations retained from previous approved decisions:
- **Co-Host:** IDR 3,000,000 per institution; one invoice; maximum five representatives, maximum five sponsored Conference papers, one paper per representative. Roster/paper entitlement activates only after institution Finance Verified. Sponsored paper does not receive a second personal Conference invoice. Every paper still undergoes academic review.
- **Personal International Program Package:** IDR 1,000,000 once per person; one invoice for **four** access rights (International MoU, Research/PKM Upgrading, International Community Service, Sustainable Destination Study), activated together after Finance Verified. Buying this does not give MoU signatory authority.
- **Regular Conference:** presenter/participant may have online/onsite category; apply already-established official categories only. Regular presenter payment obligation arises only after Accepted Published.
- **Manual finance:** one official bank account configured by authorized Admin; user uploads proof; Finance checks bank mutation before Verified.
- Do not invent unknown banks, speaker lists, capacities, conference start times, exact catering entitlements or new tariffs.

## 4. Core lifecycle

Guest → Account → Profile → Personal Dashboard → (Submit Paper / Conference Participant / Personal Package / Co-Host Representative) → Submission review and decisions if presenter → LoA & Full Paper if Accepted Published → program fee verification as applicable → session/program schedule → photo + ID Card/QR → attendance and services → publication of documents/certificates → reports.

**Keep separate:** Submission status, internal decision, published decision, Full Paper, payment, package eligibility, attendance, physical-item redemption, paper Presented and certificate availability. They may coexist and never overwrite one another.

## 5. Functional requirements

### 5.1 Account and profile
| ID | Function | Acceptance |
|---|---|---|
| ACC-01 | Register/login/logout, reset password, email verification | Unique email; authenticated session, non-owner blocked |
| ACC-02 | Registration intention instead of role choice | No privilege escalation, different initial CTA |
| ACC-03 | Personal dashboard with dynamic program cards | Single account supports multiple programs |
| ACC-04 | Profile editor and personal affiliation | Users cannot edit each other's data |
| ACC-05 | Photo upload + 1:1 modal crop | Drag/zoom/reset/confirm; validated square result stored |
| ACC-06 | Admin role and scoped staff assignment | Actions checked server-side, forbidden returns 403 |

### 5.2 Academic Conference
| ID | Function | Acceptance |
|---|---|---|
| AC-01 | Eight editable conference tracks | Track selectable in paper form |
| AC-02 | Conference enrollment: Presenter/Participant, Online/Onsite | Program-level mode stored (not global user flag) |
| AC-03 | New/edit Draft paper, metadata and co-authors | Owner-managed, stable unique paper ID |
| AC-04 | Abstract text, keywords, PDF upload and Submit | Mandatory server validation; private files |
| AC-05 | Direct eligibility after Submitted | No separate administrative screening |
| AC-06 | Reviewer index filter + self-claim | No own-paper claim; concurrent last-slot claims safe |
| AC-07 | Review draft, comments and recommendation | Reviewer can only submit own assigned review |
| AC-08 | Internal academic decision separate from Publish Decision | Author cannot view internal status/notes |
| AC-09 | Revision Required and resubmit | Version trail retained under same paper ID |
| AC-10 | My Submissions status timeline | Author knows current state and next action |
| AC-11 | Accepted Published opens LoA and Full Paper | Not opened on internal accept; payment not prerequisite |
| AC-12 | Full Paper upload and private access | Authorized users only |
| AC-13 | Parallel session, room, modality, moderator, paper placement | Presenter sees actual assigned session |
| AC-14 | Online Join Zoom URL entered manually by Admin | No Zoom API; sensitive host info hidden |
| AC-15 | Moderator marks Presented/No Show | Scoped to assigned session, not QR attendance |

Review rubric, fixed reviewer count, automatic paper ranking and elaborate post-presentation evaluation are not prerequisites for initial delivery. Decisions remain authority-owned; reviewer recommendations never auto-publish final decisions.

### 5.3 Five programs and package
| ID | Function | Acceptance |
|---|---|---|
| PG-01 | Five program master records | Conference, Pembinaan Riset/PKM, International MoU, International Community Service, Destination Study |
| PG-02 | Per-person enrollment and per-program attendance mode | Adding program never creates second account |
| PG-03 | Research/PKM Upgrading | Agenda, speakers, participating users, attendance, materials |
| PG-04 | International MoU | Institutions, PIC/delegation, signatory designation, agenda/documents, attendance |
| PG-05 | International Community Service | Manifest, groups, field PIC, location, attendance, evidence |
| PG-06 | Sustainable Destination Study | Manifest, itinerary, group, check-in and evidence |
| PG-07 | International Package offer and 1 personal order | IDR 1m once for four programs, not four invoices |
| PG-08 | Package access after Finance Verified | Pending/uploaded proof cannot activate |
| PG-09 | Admin configurable package registration deadline | Server checks eligibility on order, prior orders retained |
| CH-01 | Co-Host institutional registration and invoice | One IDR 3m invoice per institution |
| CH-02 | Co-Host five representative slots/papers | Five distinct accounts, each one sponsored paper |
| CH-03 | Co-Host rights after Finance Verified | Slot activation blocked until verified |
| CH-04 | No auto-accept or duplicate sponsored paper fee | Each paper reviewed independently |
| CH-05 | PIC scope separate from personal bundle | Personal package invoices not visible as PIC's institutional debt |

### 5.4 Finance
| ID | Function | Acceptance |
|---|---|---|
| FI-01 | Invoice record with owner, currency, price snapshot and type | Personal and institution invoices distinct |
| FI-02 | Bank settings controlled by authorized Admin | No hardcoded actual bank details |
| FI-03 | Payment proof JPG/JPEG/PNG/PDF in private storage | Upload validated, owner-scoped |
| FI-04 | Pending/Verified/Correction Required with transaction history | Only Finance-authorized actor verifies against bank mutation |
| FI-05 | Presenter regular invoice after Accepted Published | No invoice before publish; Full Paper independent of finance |
| FI-06 | Verified access triggers for bundle/Co-Host | One payment activates only corresponding entitlements |
| FI-07 | Finance reporting and audit | Author, time and payment evidence traceable |

### 5.5 Identity, ID Card, QR and scanner
| ID | Function | Acceptance |
|---|---|---|
| QR-01 | One personal event pass/QR | Secure random token; human code e.g. ICH27-0001 is separate |
| QR-02 | QR revoked/regenerated by authorized process | Old token no longer accepted |
| QR-03 | Participant card green, Committee card navy | Match user-provided visual references and official logos |
| QR-04 | Personal preview and download QR PNG + Card PNG | Cropped photo, real data and scannable code |
| QR-05 | Onsite print list/batch | Online-only excluded; person in multiple onsite programs one physical card |
| QR-06 | Admin QR Services Builder | Create check-in, access, item/kit and meal services without code |
| QR-07 | Service configuration eligibility, program, window, limit, operators | Server enforces newest saved rules |
| QR-08 | Committee web mobile scanner | Select assigned service, camera scans, displays name/photo/result |
| QR-09 | Manual handover confirmation for physical items | Scanning does not prematurely consume kit |
| QR-10 | Duplicate-scan race protection | Atomic database transaction and constraint |
| QR-11 | Attendance and redemption separate per service/program | Lunch does not consume attendance or kit |
| QR-12 | Service ledger, reports and CSV export | Actor/time/service/result preserved |

Use browser camera scanner first, HTTPS and tested device compatibility. If no network, manually record then reconcile: do not claim local scan confirmed on server. One QR image across all eligible services. Admin can configure existing service types, not define arbitrary executable workflow rules.

### 5.6 Documents, notifications, reports
| ID | Function | Acceptance |
|---|---|---|
| DOC-01 | My Documents per user/paper | Only eligible links/files visible |
| DOC-02 | Admin link LoA PDF from Drive or private file | Direct paper-specific link, no public directory of private LoAs |
| DOC-03 | Certificate links/files, publication by authorized staff | No automatic public certificate before release |
| COM-01 | Key emails: verification, Submitted, Decision Published, LoA, Full Paper, Verified, schedule, certificate | Templates and on/off switches where useful |
| COM-02 | Reliable delivery after transaction commit | Failure logged, no false transaction notifications |
| REP-01 | Admin filterable CSV/Excel exports | Paper, participants, finance, program, service and attendance, scope enforced |

### 5.7 Website PUBLIC — hold
| ID | Function | Acceptance |
|---|---|---|
| WEB-01 | Responsive official ICHES site | Layout, sections, final copy require owner discussion |
| WEB-02 | Public entry to register/login | Navigation approved later |
| WEB-03 | Basic Admin-editable CMS | No drag-and-drop; published-only content |
| WEB-04 | Important dates and countdown | From Event Settings, no invented official start hour |
| WEB-05 | SEO metadata and media | After design/content sign-off |

**Mandatory design HOLD:** Do not finalize or invent hero headline, imagery, brand arrangement, speakers, public text, section order, site colors, menu hierarchy, official URL, or marketing content. See [PUBLIC-FRONTEND-TBD.md](PUBLIC-FRONTEND-TBD.md). Development can proceed on backend, auth and private dashboards.

## 6. Status and transitions

- **Paper:** Draft → Submitted → Review ongoing → Academic Decision internally → Decision Published (Accepted / Revision Required / Rejected) → Full Paper for Published Accepted → Scheduled → Presented or No Show.
- **Review:** Available → Claimed → Draft Review → Submitted; assigned coordinator may release stale claim without erasing history.
- **Finance:** Unpaid → Proof Submitted/Pending Verification → Verified or Correction Required.
- **Enrollment:** Registered → Eligible/Active if criteria met → attended, separately per program.
- **QR service:** Eligible → Scanned → Confirmed for physical goods, Already Used/Not Eligible blocked.
- **Document:** Prepared → Published/Available.
The actual database schema may use distinct tables/enums rather than a single mixed status.

## 7. Non-functional requirements

**Security:** password/session security, validated files, private downloads, policies, scoped operator permissions, no credentials in repo, least privilege.  
**Integrity:** unique codes, idempotency and transactions for claims/payment/QR redemptions, audit actor/timestamps.  
**UX:** understandable states, loading/error feedback, mobile-friendly scanner and personal dashboard.  
**Performance:** paginated queries, no N+1, queue email when appropriate, avoid unnecessary infrastructure.  
**Maintainability:** modern typed OOP PHP, Laravel idioms, fat-controller avoidance, tested domain actions.  
**Reliability:** backup, mail test, HTTPS scanner, concurrency tests, deployment checklist before real users.  
**Privacy:** no personal/photo/payment details in QR payload or public logs.

## 8. Delivery and milestone acceptance

| Milestone | Expected deliverable | Note |
|---|---|---|
| **22 November 2026** | Demonstration: functioning account → abstract → reviewer → published decision, visible roadmap and optional QR prototype | Demo scope, not full production readiness |
| Before **1 December 2026** | Auth, submission PDF, reviewer and security/UAT ready | Do not enable public submissions before verified |
| During December 2026 | Decision/LoA/Full Paper, finance and package/Co-Host | Preserve business gates |
| Before **5 January 2027** | Session/Zoom, ID Card QR, mobile scanner, program attendance | Test on real staff phones/network |
| After event | Documents, certificates, data reports | Publication handoff separate |

**High-value end-to-end acceptance:** register → submit real PDF → reviewer can claim and review → decision not leaked before Publish → author sees result and Full Paper; one Verified package unlocks exactly four programs; Co-Host only one institutional invoice; online-only excluded from print; scanner accepts eligible QR, rejects repeat and cannot double redeem concurrently; history appears in Admin.

## 9. Deliberately not a blocker

Do not debate field participant quotas, item-by-item facilities, intricate certificate rules, scoring rubric configuration, reviewer count policy, or tariff permutations before delivering core flow. Never invent corresponding business rules or perform sensitive actions without authorization.

## 10. Traceability and authority

The [Master Blueprint](../MASTER-BLUEPRINT-ICHES-2027.md) sets vision. This PRD defines testable requirements. [TASKS](TASKS.md) maps requirements to implementation tasks. [WORKING-RULES](WORKING-RULES.md) defines execution behavior. [OOP Standards](OOP-LARAVEL-LIVEWIRE-STANDARDS.md) defines quality. A feature is **DONE only when actual tests/inspection confirm it**, not when code was merely written.

**This PRD is planning documentation, not permission to start coding.**
