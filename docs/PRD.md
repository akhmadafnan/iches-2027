# PRODUCT REQUIREMENTS DOCUMENT — ICHES 2027

**Version:** 2.0 | **Updated:** 21 November 2026 | **Status:** CURRENT OWNER-APPROVED PRODUCT POLICY (future modules may still be TODO; no authorization to code).
**Product owner:** Penyelenggara/pemilik proyek ICHES UNISYA.  
**Source of truth (read order):** [CURRENT STATE & DECISIONS](CURRENT-STATE-AND-DECISIONS.md) → [Master Blueprint](../MASTER-BLUEPRINT-ICHES-2027.md) → this PRD → [Tasks](TASKS.md). New owner-approved policies supersede obsolete historical text. Old ICHES repository is only a reference.
**Frontend publik:** **HOLD** until separate discussion and approval. See [Public Frontend](PUBLIC-FRONTEND-TBD.md).

## 1. Product summary

ICHES 2027 is one responsive web application for an international conference and four companion programs. Each person registers once, may join multiple programs, submits papers when applicable, tracks academic decisions, sees schedules and documents, and uses one individual QR and ID Card for on-site services. Reviewers, committee, finance and authorized academic decision-makers operate through focused workspaces. Admin manages program data, QR services, participants, finance and reports through simple CRUD.

**Technical direction:** new Laravel + official Livewire Starter Kit monolith, Blade/Livewire/Tailwind, MySQL, no separate Vue/Inertia frontend. OOP PHP and idiomatic Laravel; see [engineering standard](OOP-LARAVEL-LIVEWIRE-STANDARDS.md).

### Goals
- End-to-end **one owned abstract → single reviewer Accept/Reject → bulk Published Decision & personal email → auto LoA + invoice → Finance Verified → Full Paper**.
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

Event is **5–6 January 2027**. Legacy TOR target: Abstract Submission **1–20 December 2026**, decision publication **22 December 2026**, Full Paper **23–28 December 2026**, registration/payment **24–28 December 2026**. Admin can change event/date configurations; start hour must not be invented. **NEW HARD GATE: Full Paper requires BOTH Published Accepted AND Finance Verified for the applicable presenter/Co-Host obligation**. Legacy Full Paper and payment windows overlap inconsistently with this new gate: organizer must officially align deadlines before production; do not assume extra days, invent dates or bypass Finance. Review begins immediately on valid Submitted abstracts, without Administrative Screening.

Product prices/relations retained from previous approved decisions:
- **Co-Host:** IDR 3,000,000 per institution; one invoice; maximum five representatives, maximum five sponsored Conference papers, one paper per representative. Roster/paper entitlement activates only after institution Finance Verified. Sponsored paper does not receive a second personal Conference invoice. Every paper still undergoes academic review.
- **Personal International Program Package:** IDR 1,000,000 once per person; one invoice for **four** access rights (International MoU, Research/PKM Upgrading, International Community Service, Sustainable Destination Study), activated together after Finance Verified. Buying this does not give MoU signatory authority.
- **Regular Conference:** presenter/participant may have online/onsite category; apply already-established official categories only. Regular presenter payment obligation arises only after Accepted Published.
- **Manual finance:** one official bank account configured by authorized Admin; user uploads proof; Finance checks bank mutation before Verified.
- Do not invent unknown banks, speaker lists, capacities, conference start times, exact catering entitlements or new tariffs.

## 4. Core lifecycle

Guest → one Account → Profile → Personal Dashboard → **one owned Conference Paper (Draft → Submitted)** → exclusive Claim → General Comments + Accept/Reject review → authorized **Bulk Publish & Notify** with audited overrides → **Published Accepted** unlocks auto-generated LoA and payment/invoice; **Full Paper only after Verified** → sessions/program participation → attendance/services → certificates/reports. **Published Rejected** means read-only decision/comments with no continuation unless a published authorized correction is made. A user may co-author papers owned by other accounts.

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
| AC-03 | One owned Conference paper per account, Draft metadata and co-authors | **ACAD-005 new hard rule:** unique papers.owner_id after safe duplicate audit; edit existing Draft only, block second paper at UI/server/DB even after Submitted/Rejected/Accepted; co-author on other papers allowed; stable human Paper ID/ULID |
| AC-04 | Abstract text, keywords, PDF upload and Submit | Mandatory server validation; private files |
| AC-05 | Direct eligibility after Submitted | No separate administrative screening |
| AC-06 | Reviewer index filter + self-claim | **Owner decision 21 Nov 2026:** one active reviewer per abstract, exclusive self-claim; no own-paper/co-author claim; concurrent attempts cannot double-assign; release history retained. **Reviewer UI shows Paper ID and academic content, but not author names or affiliations**; existing PDF may still contain identity. Reviewer names are not exposed to authors in REV-002. |
| AC-07 | Abstract review: General Comments + Accept/Reject only | Exclusive claimant can Save Draft (incomplete allowed) or Submit Review (comments and Accept/Reject required); UUID v7, timestamped audit, immutable after Submit; no numeric score, no Revisions Required and no automatic decision |
| AC-08 | **One-step bulk academic publication** with authority-scoped exception overrides and published corrections | Default decision derives from Review Submitted Accept/Reject, authorized AcademicAuthority previews/overrides with mandatory reason, one Publish & Notify batch confirms per-paper decisions atomically/idempotently; author sees only published result/comments/date, no internal rationale or reviewer identity; subsequent corrections versioned/audited |
| AC-09 | Superseded for abstract stage by owner decision 21 Nov 2026 | No abstract Revisions Required/resubmit; suggested improvements are written in General Comments and may guide Full Paper after an Accepted decision is published; original paper history remains intact |
| AC-10 | My Submissions status timeline | Author knows current state and next action |
| AC-11 | Published Accepted opens automatic LoA + invoice/payment, NOT Full Paper | DOC-LOA-001 queues official PDF/QR after Published Accepted independent of payment. **FULL-001 only opens after Published Accepted AND applicable Finance Verified (including verified Co-Host sponsorship entitlement)**. Proof Uploaded/Pending/Correction Required never grants access |
| AC-12 | Payment-gated Full Paper upload and private access | One existing Paper ID, server-side Published Accepted + relevant Finance Verified gate on form/upload/private read; Rejected locked; no duplicate regular invoice for paid Co-Host paper |
| AC-13 | Parallel session, room, modality, moderator, paper placement | Presenter sees actual assigned session |
| AC-14 | Online Join Zoom URL entered manually by Admin | No Zoom API; sensitive host info hidden |
| AC-15 | Moderator marks Presented/No Show | Scoped to assigned session, not QR attendance |

No rubric, numerical score, multi-reviewer voting or abstract Revisions Required. **Exactly one active reviewer claim per abstract is already locked and implemented.** Reviewer recommendations are prefilled **proposals** in an authorized bulk decision batch, never automatically published without one explicit authority confirmation.

### 5.2A Locked bulk decision / LoA / finance contract (owner-approved 21 Nov 2026)

**One submission:** enforce one paper per owner at database/server/UI; co-author role elsewhere does not consume quota. Existing duplicates require read-only audit and explicit handling before adding uniqueness.

**Bulk Decisions:** only staff with an explicit AcademicAuthority assignment may Publish & Notify; reviewer Submit Review alone is private. Show ready Accept/Reject totals and selection, skip unreviewed/already-published, permit audited override (especially Reject → Accepted), maintain immutable reviewer recommendation, one decision per paper with versioned corrections, no manual DEC-001/DEC-002 click sequence. On published correction adjust LoA/payment/Full Paper eligibility and notify affected owner; any paid invoice requires Finance reconciliation rather than destructive reversal.

**Email:** decision publication commits author-visible status and records a unique per-paper/event-version notification for the **owning submitter email**. Queue after commit, retry failed attempts and expose delivery states; do not leak reviewer identity, override reason or internal notes. LoA email only on actual document Issued. No real outbound mail from prototype.

**Official LoA:** DOC-LOA-001 generates a PDF directly in system from an approved English template/signature/letter scheme, snapshot author/coauthor/track/title, private storage, stable official number, secure random QR verification token, public minimally revealing verification, revoke/supersede/reissue history. One currently valid LoA per Published Accepted paper; if generation fails, allow retry while Accepted remains valid. Never require one Google Drive link upload per paper. LoA does not wait for payment.

**Hard finance gate:** Published Accepted allows invoice/proof; Finance verifies against real bank mutation. Only Published Accepted **plus Verified applicable payment entitlement** unlocks Full Paper. Co-Host verified institutional entitlement substitutes for individual Conference invoice; no double charge. Published Rejected can see read-only decision/comments only. This new rule explicitly supersedes legacy AC-11/FI-05 and TOR-derived payment-independent Full Paper language.

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
| FI-05 | Regular presenter invoice after Accepted Published | No invoice before publish; no duplicate personal invoice if verified Co-Host sponsorship covers the paper; **Full Paper stays locked until that paper's applicable financial entitlement is Verified** |
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
| DOC-02 | **DOC-LOA-001 auto-generate official LoA PDF + QR verification** | One authorized template, signer, number; after Published Accepted generate idempotently via queue, immutable snapshot, owner-private download/print, minimal public QR validity, versioned revoke/supersede/reissue and retry. **No per-paper Google Drive upload.** |
| DOC-03 | Certificate links/files, publication by authorized staff | No automatic public certificate before release |
| COM-01 | Key emails: verification, Submitted, Decision Published, LoA, Full Paper, Verified, schedule, certificate | Templates and on/off switches where useful |
| COM-02 | Reliable personal decision notification/outbox | Publish & Notify writes idempotent per-paper recipient/event/version after DB commit; queue, retries, status monitor, no claims of delivery without provider proof, no early decision leaks. LoA notification after actual Issued. |
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

- **Paper ownership:** no paper → one Draft → Submitted; **no second paper for one owner**, co-author elsewhere permitted.
- **Review:** Submitted → one exclusive reviewer Claimed → Review Draft → Review Submitted (Accept/Reject recommendation, INTERNAL). General Comments and reviewer identity remain private before publication.
- **Decision publication:** eligible Review Submitted → default Accept/Reject proposed → authorized optional override → one batch Publish & Notify → Published Accepted / Rejected per paper; audited versioned correction only by AcademicAuthority. No mandatory manual internal decision per paper.
- **LoA (independent):** Published Accepted → Pending/Generating → Issued or Failed/retry; later Revoked/Superseded by authorized correction. One active official PDF per paper; QR validity reflects current record.
- **Finance:** Unpaid → Proof Submitted/Pending Verification → Verified or Correction Required; ONLY Finance-authorized actor confirms after bank mutation check.
- **Full Paper entitlement:** requires **Published Accepted AND applicable Finance Verified** (regular presenter OR verified Co-Host sponsor), always server-enforced. Published Rejected has no downstream right.
- **Enrollment:** Registered → Eligible/Active after applicable criteria verified → Attended, separately per program.
- **QR service:** Eligible → Scanned → Confirmed for physical goods; Already Used/Not Eligible blocked.
- **Other documents:** Prepared → Published/Available when actually issued. Do not conflate documents, attendance, payment, reviewer recommendation and decision.

Actual database schemas may use separate event/version/audit tables rather than one mixed status.

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

**High-value end-to-end acceptance:** register → one owned PDF abstract → exclusive reviewer Claim + Accept/Reject review → authorized bulk Publish & Notify, with audited override/correction and personal queued email → Published Accepted gets auto LoA PDF with public QR verification and invoice → **only Verified relevant payment** enables Full Paper. Published Rejected remains read-only. One Verified personal package unlocks exactly four programs; Co-Host has one institutional invoice and no duplicate presenter fee; scanner behavior remains separately tested.

## 9. Deliberately not a blocker

Do not debate field participant quotas, item-by-item facilities, intricate certificate rules, scoring rubric configuration, reviewer count policy, or tariff permutations before delivering core flow. Never invent corresponding business rules or perform sensitive actions without authorization.

## 10. Traceability and authority

The [Master Blueprint](../MASTER-BLUEPRINT-ICHES-2027.md) sets vision. This PRD defines testable requirements. [TASKS](TASKS.md) maps requirements to implementation tasks. [WORKING-RULES](WORKING-RULES.md) defines execution behavior. [OOP Standards](OOP-LARAVEL-LIVEWIRE-STANDARDS.md) defines quality. A feature is **DONE only when actual tests/inspection confirm it**, not when code was merely written.

**This PRD is the owner-approved **product behavior contract**, not permission to start coding unfinished features. See [CURRENT STATE & DECISIONS](CURRENT-STATE-AND-DECISIONS.md) for implemented vs planned.**
