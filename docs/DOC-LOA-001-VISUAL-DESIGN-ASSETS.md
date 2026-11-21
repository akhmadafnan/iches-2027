# DOC-LOA-001 — Visual Design Decisions & Original Assets

**Date:** 21 November 2026
**Status:** **MASTER VISUAL DESIGN APPROVED (OWNER DESIGN PASS, 21 Nov 2026)** using locked V2.1 HTML/CSS after final cleanup; formal signed issuance, QR verification and PDF generator are NOT IMPLEMENTED. Final design record: [V2.1 closeout](DOC-LOA-001-V2.1-PRECISION-REVIEW.md).
**Boundary:** Design specification only. No code, migration, font/logo copy or PDF generator prior to separate owner GO.

## Approved visual direction

- **Official Institutional Letterhead:** A4 portrait, one-page target, dominant white, restrained emerald-green accents; formal academic document, not decorated certificate.
- **Body/layout:** reference image 2, minimalist editorial letter, professionally typeset English. Dynamically include generated letter number/date, paper title/ID, all authors and coauthors in stored order, and official conference track. Explicitly accept the abstract for presentation only.
- **Footer:** restrained full-width green band inspired by reference image 3. **Approved exact labels:** left `ICHES 2027 · OFFICIAL CORRESPONDENCE`; right `UNISYA · INDONESIA`. No version or preview labels on visible printed LoA.
- **Header reading order, approved by owner:** (1) original FORPIM, (2) original UNISYA, (3) original ICHES with adjacent “ICHES 2027” in genuine **Sora Bold / 700**; all three logos modestly sized and grouped **left**, full conference title and host text **right**. No horizontal rule beneath the header. Preserve native aspect ratios. The portable master on Laptop 1 is `D:\PROJEKKU\iches-2027\design\loa\v2.1\ICHES-LOA-MASTER-V2.1.html` (the original standalone Laptop 2 source remains historical).
- **Signature:** provisional signatory approved for the current visual draft: **Dr. Ahmad Ihwanul Muttaqin, M.Pd.I. — Steering Committee** (owner supplied wording: "Stering Committee"; render the standard English spelling "Steering Committee"). This is a **temporary designation**, editable before the official master is locked. Actual handwritten/digital signature asset and final signing authorization are not yet supplied. One discrete QR verifies document authenticity near bottom/signature; it is not a certified electronic signature.
- **Avoid:** generic globe/circuit/wave ornaments, fabricated brand marks, unnecessary textures or gradients, certificate-style borders.

## Provisional signer details (owner decision)

| Field | Provisional value | Rule |
|---|---|---|
| Signatory name | Dr. Ahmad Ihwanul Muttaqin, M.Pd.I. | Preserve the owner-provided spelling and academic credentials |
| Position on English LoA | Steering Committee | Standardize spelling from the owner's "Stering Committee" for the rendered English letter |
| Approval level | Temporary for Master Template V1 draft | Do not treat as permanently approved institutional signatory until owner confirms |
| Signature image | Not yet provided | No invented or AI-generated signature; keep the space available |
| Letter-number organization code | ICHES only | **Owner approved:** no FORPIM/UNISYA code in LoA numbers |
| Official LoA number structure | `No. 0001/ICHES/LOA/XII/2027` | **Owner proposed and clarified:** 0001 = sequential number, ICHES = document issuer code, LOA = document type, XII = Roman month of issuance, 2027 = **calendar year of issuance, NOT conference edition** |

**Proposed signature block (layout only):**

    Sincerely,
    [Approved signature image — pending]
    Dr. Ahmad Ihwanul Muttaqin, M.Pd.I.
    Steering Committee
    ICHES 2027

## Official LoA numbering — owner-approved scope

- **Locked decision:** issue LoA with an ICHES-specific letter code, without FORPIM/UNISYA administrative codes, and with **month/year matching the actual issuance date**. The final four-digit year is the **calendar year**, never a hardcoded ICHES 2027 edition label.
- **Owner-proposed structure:** `No. 0001/ICHES/LOA/XII/2027`. Sequence = 0001; issuer code = ICHES; document type = LOA; Roman month = XII (December); calendar year = 2027. For an issued LoA on **15 December 2026**, the number must end `/XII/2026`, whereas a document issued **5 January 2027** ends `/I/2027`. Displayed issuance date and number month/year must agree. Do not silently label a 2026-issued letter as issued in 2027.
- Sequence must be unique for the document series and concurrency-safe. The precise scope of resetting the sequential counter (if any) is an implementation detail to settle before coding; never reset it merely because the month changes.
- Allocate a number **only on actual issuance**, transactionally with a unique database constraint; never derive it from paper ID, reviewer ID, a count query, or the current date. Number and issued timestamp are immutable on an issued PDF snapshot.
- Generating again or retrying a failed job must never create a second valid number for the same current document. A genuine authorized reissue creates a new audited document version (with its own unique number) and supersedes/revokes the previous one; preserving its historical number and verification status.
- A document-number sequence is separate from `ICH27-P...` Paper ID and from QR verification token; QR verifies the issued document status and does not encode the entire private paper record.

## Verified original logo files on owner's Windows computer

| Asset | Exact source path | Pixels | Status |
|---|---|---|---|
| FORPIM | D:\PINJAM-AFNAN\ICHES\LOGO WEBP\logo-forpim.webp | 1079 × 432 | Owner specified, file and WebP dimensions verified |
| UNISYA | D:\PINJAM-AFNAN\ICHES\LOGO WEBP\logo-unisya.webp | 1378 × 432 | Owner specified, file and WebP dimensions verified |
| ICHES | D:\PINJAM-AFNAN\ICHES\LOGO WEBP\logo-iches.webp | 695 × 548 | Candidate found in same folder; owner has not yet explicitly selected this exact file |

The original supplied logos have been visually inspected and copied **only to the standalone local HTML design folder** for design/PDF output, not yet to Laravel app assets. Preserve original aspect ratios and retain the original source art; A4 Chrome PDF render has been visually checked. The candidate `logo-iches.webp` was used in the owner-approved V2.1 visual draft; no replacement artwork was created.

**Font:** Sora Bold must use the real Sora font with proper installation/embedding for the final PDF; current Laravel assets show Instrument Sans, not a verified Sora font. Do not silently substitute another font.

**Important:** Current Laravel app-logo-icon is a generic SVG; it is not verified as the official ICHES logo. Do not repurpose it for the LoA.

## Business rules already approved (not implemented)

One currently valid issued LoA per Published Accepted paper; one versioned approved English master; generated unique official number; paper and authors immutable snapshot; private author Preview/Download/Print; public minimal verification status via QR; idempotent queue/retry; audited revoke/supersede/reissue; payment-independent issuance. No Google Drive upload. The provisional signatory and role are now recorded, but the actual approved signature asset, definitive signatory authorization, exact typography/punctuation of the proposed number, and official letterhead master still require final validation before implementation. The ICHES-only number structure `sequence/ICHES/LOA/Roman-month/calendar-year` is confirmed, and V2.1 HTML visual master is OWNER DESIGN PASS; **real authorized signature asset/signatory finalization and backend issuance remain pending**.
