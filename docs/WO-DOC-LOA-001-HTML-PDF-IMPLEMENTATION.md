# WO-DOC-LOA-001 — Official Letter of Acceptance from Approved HTML Master

**Project:** ICHES 2027 Laravel 13 / Livewire 4 / Flux UI / MySQL
**Prepared:** 21 November 2026
**Status:** **READY FOR OWNER GO CODING — NOT IMPLEMENTED**. **Master visual HTML V2.1: OWNER DESIGN PASS.** Not an authorization to issue unsigned documents.
**Git rule:** do not commit/push application source until owner confirms UAT PASS. Document-only preparation is allowed; preserve untouched existing paper `ICH27-P000003`, its review and history. No `migrate:fresh` or broad demo data reset.

## 0. Mandatory source of truth

Read in order:
1. `docs/CURRENT-STATE-AND-DECISIONS.md`, `docs/TASKS.md`, `docs/PRD.md`, `docs/WORKING-RULES.md`, `docs/DOC-LOA-001-VISUAL-DESIGN-ASSETS.md`.
2. `docs/DOC-LOA-001-V2.1-PRECISION-REVIEW.md` (**DESIGN PASS**) and `docs/DOC-LOA-001-V1-COPY-AND-VISUAL-REVIEW.md` (English master copy).
3. Actual **approved HTML/CSS** source: `design/loa/v2.1/ICHES-LOA-MASTER-V2.1.html` (**repository-relative master synchronized to Laptop 1**); PDF comparison proof: `ICHES-2027-LoA-V2.1-HTML-render.pdf`; repeatable renderer: `render-pdf.ps1` in same folder.
4. Original logo copies and Sora font are in the *standalone prototype folder* `assets/`; verify asset/license conditions when integrating into Laravel. Do not invent or redraw logos/signatures.

## 1. Fixed scope and accepted decisions

- **Design is locked:** all three genuine small logos (FORPIM / UNISYA / ICHES + Sora Bold ICHES 2027) on left, full exact conference name/host right, no header dividing line, one-color light-gray ICHES watermark (CSS from original), emerald footer.
- Final layout intentionally has **no** top `OFFICIAL ACADEMIC CORRESPONDENCE` line, **no** top `Date:` field and **no** `MASTER DESIGN PREVIEW · SAMPLE DATA ONLY` caption. **Final font refinements are also locked:** the entire visible `No. 0001/ICHES/LOA/XII/2026` label uses **Courier New Bold 700**; the bottom issuance date (above `Sincerely,`) uses **Sora 500**; footer-right `UNISYA · INDONESIA` exactly matches the footer-left label at **7.2pt, 700 weight and .4pt tracking**.
- Show actual issuance date **once**, immediately above `Sincerely,`, without the word `Date`; right footer **`UNISYA · INDONESIA`**, left footer **`ICHES 2027 · OFFICIAL CORRESPONDENCE`**.
- A4 portrait; use the same HTML+print CSS as authority for browser preview AND PDF (Chrome-based renderer or equivalent verified for parity). Do not design a separate PDF.
- Exact English event identity: **The 6th International Conference on Humanity, Education, and Society (ICHES 2027)**, hosted by **Universitas Islam Syarifuddin (UNISYA), Lumajang, Indonesia**.
- The LoA confirms **ABSTRACT accepted for presentation**, not accepted full paper, guaranteed proceedings/journal publication, accepted reviewer recommendation, or verified payment.
- Eligible only after **published final Accepted decision**, not `Submitted`, Review recommendation Accept, or unpaid/pending states. LoA is **independent of Finance Verified**.
- **One currently valid LoA per paper**. Include Paper ID (`ICH27-P...`), title, ALL ordered author/coauthor names, official conference track, decision and issue date in an immutable issued snapshot.
- Authorized signatory currently proposed: **Dr. Ahmad Ihwanul Muttaqin, M.Pd.I. — Steering Committee**, subject to approval of authentic signature/authorization. **No document is Issued without approved signature configuration.** Do not generate a facsimile signature.
- Number scheme: `0001/ICHES/LOA/XII/2026` for 15 December 2026; dynamically `sequence/ICHES/LOA/Roman issue month/calendar issue year`. The last year is the **actual calendar year of issuance**, not a hardcoded conference-edition year. Separate number from Paper ID and random verification token.

## 2. Implementation batches — execute only after OWNER GO CODING

### DOC-LOA-001A — Data contract, numbering and authorized template configuration

- Read-only audit of existing decisions/paper ownership and saved metadata. Keep prior records intact.
- Add additive schema for versioned official LoA documents, issuance snapshot, status (`Pending`, `Generating`, `Issued`, `Failed`, `Revoked`, `Superseded`), unique number, template version, signature snapshot, issued timestamp, file path/checksum, verification token hash/reference, actor and audit trail. Use UUID v7 for new independent document/event IDs according to forward-key policy; keep existing BIGINT paper/users PKs.
- Configure exact original asset sources, signatory/position/signature and a versioned HTML master **without creating a free-form template editor**. Only authorize active signer/template after explicit official approval.
- Reserve/allocate unique number concurrency-safely only for real issuance. Decide sequence reset policy with owner before implementation; do not infer from row counts, paper IDs or month alone. Preserve old issued numbers upon reissue.

### DOC-LOA-001B — Idempotent HTML→PDF issuance

- On genuine Published Accepted, enqueue a deduplicated issue operation after committed decision. Queue execution should not block academic Publish & Notify.
- Snapshot live paper/title/ordered authors/track and template/signatory/number/version once; render PDF using the **approved HTML/CSS** adapted for Blade escaping and print fidelity. Retain Sora and original logos.
- Store PDF privately, guarded by owner paper policy and real active role; never public-write PDFs and never rely on hidden buttons for authorization. Enable owner Preview/Download/Print.
- Handle failures/retry without duplicate Issued records or double numbers. If signature/official configuration missing, remain Pending/Blocked/Failed with truthful status; do not sign fictitiously or email 'LoA Available'.
- Test extreme title lengths, many coauthors, Unicode/Arabic names, very long affiliations, browser-preview vs server-PDF differences, missing font/logo/QR, and A4 overflow. **Never silently truncate author names or paper title**. If one page cannot fit legibly, explicitly choose and test a policy before owner sign-off.

### DOC-LOA-001C — Document verification, correction and notifications

- Embed one QR linked to secure random verification endpoint. Public verifier shows only deliberately minimal metadata and `Valid/Revoked/Superseded/Not Found` status, with rate limiting. A QR lookup is NOT a certified electronic signature.
- Support audited revoke/reissue/supersede without overwriting an earlier issued PDF and preserve each version's historical number/snapshot/checksum.
- Published Accepted→Rejected correction automatically invalidates current LoA via scoped reconciliation. Published Rejected→Accepted correction may generate a fresh one. A retry/correction must never yield two simultaneous valid LoAs.
- LoA Available personal email to **owning submitter account only**, queued after actual `Issued` with idempotency/retry and no private PDF attachment by default; use existing decision outbox conventions where suitable. No fake SMTP delivery claims.

## 3. UI, tests and acceptance

- Participant's My Submissions / My Documents shows separate LoA status and issued access actions; no premature Download/Print links.
- Authorized admin/Superadmin role-only status view for generation failures, template/signature availability, reissue/revoke audit. Respect Switch Workspace active-role boundaries.
- Targeted feature tests: original Submitted + internal Accept must NOT issue; genuine Published Accepted issues once; negative permissions; duplicate queue; concurrent issue; numbering across Dec 2026 / Jan 2027; number/date mismatch; long names; private storage; QR public minimum; revoke/reissue; decision correction; missing official signer; no premature email; Co-Host and payment independence.
- Run sequential targeted/full tests on Windows (avoid racing `view:cache` with tests), Pint, PHPStan, Blade compilation and Vite as appropriate. Real browser/mobile QA must be reported separately from automated tests.
- Protect original paper `ICH27-P000003` and one real review, all existing users and outbox; use isolated test DB or tightly scoped synthetic fixtures and clean exact IDs after UAT.
- Mandatory OWNER UAT PASS **after implementation**, followed by scoped code/docs Git commit/push only on owner's explicit closeout.

## 4. Not included; blocked items

- No mass LoA upload/Google Drive handoff; no per-author LoA splitting; no premature Full Paper rights. Full Paper stays `Published Accepted AND applicable Finance Verified`.
- No fake signature, no active public QR until verifier exists, no published PDF until actual Issued, and no redesign of header/footer/fonts outside a new owner design decision.
- No automatic Git commit/push or deployment from this planning Work Order.

## 5. Before coding — unresolved operational inputs

- Official original signature image or signer-authorized signature workflow and final authority confirmation.
- Owner decision on counter reset policy (recommended one sequence per official ICHES LoA series, continuous through calendar-year boundary; do not silently assume it).
- Confirm the `logo-iches.webp` artwork used in the accepted V2.1 prototype is also the approved document asset.
- Renderer/asset embedding/license QA before enabling live issuance.

**STOP/GATE:** Master Design V2.1 is OWNER DESIGN PASS; DOC-LOA-001 backend remains TODO. Begin code only after explicit **OWNER GO CODING DOC-LOA-001**.