# DOC-LOA-001 — V2.1 Layout Precision Review (HTML → PDF)

**Date:** 21 November 2026. **Status:** **MASTER VISUAL DESIGN — OWNER DESIGN PASS (21 Nov 2026)** after final editorial cleanup and verified HTML→PDF A4 rendering. **DOC-LOA-001 application remains NOT CODED; no real LoA issued.**

## Why V2.1 was created

Owner provisionally accepted the V2 visual direction, requesting only adjustments to positioning and geometric precision. Do not redesign the visual language, invent imagery, alter official identity, rewrite the accepted business policy, or treat design draft as release authorization.

## Scoped updates (V2 remains untouched)

| Area | V2.1 adjustment |
|---|---|
| Horizontal alignment | Institutional logo cluster and primary letter text now share consistent **20mm page margins**; green footer label follows same line |
| Header grid | Tightened left original-logo cluster; widened right official conference-title column; retained original three-logo order and typography |
| Vertical rhythm | Reduced dead whitespace after letterhead by refining header grid minimum height and content top padding |
| Watermark | Centered one-color original ICHES image at **59.5mm** from page left, adjusted its top and reduced CSS-only opacity to **3.4%** |
| Signature/QR | Extra safe whitespace above 15mm green footer, without changing signer or layout orientation |
| Preserved | Real original logo files, Sora Bold brand text, full conference title on right, no horizontal header rule, complete English acceptance copy, dedicated ICHES letter-number pattern and green footer |

## Authoritative local design artifacts

**Portable source for current Laptop 1:** `design/loa/v2.1/ICHES-LOA-MASTER-V2.1.html` inside `D:\PROJEKKU\iches-2027`, with its sibling `assets/`, `render-pdf.ps1`, reference PDF and PNG. Original historical Laptop 2 locations below are kept as evidence, not as active Laptop 1 paths.

- Master HTML: `D:\PINJAM-AFNAN\ICHES\LOA-DESIGN-V2.1\ICHES-LOA-MASTER-V2.1.html`
- PDF rendered from same HTML: `D:\PINJAM-AFNAN\ICHES\LOA-DESIGN-V2.1\ICHES-2027-LoA-V2.1-HTML-render.pdf`
- Screenshot preview: `D:\PINJAM-AFNAN\ICHES\LOA-DESIGN-V2.1\ICHES-2027-LoA-V2.1-preview.png`
- Rebuild: `powershell -ExecutionPolicy Bypass -File "D:\PINJAM-AFNAN\ICHES\LOA-DESIGN-V2.1\render-pdf.ps1"`

## QA

- The Chrome-rendered PDF is **one A4 page** (verified in the remote PDF reader).
- Screenshot visually checked after all adjustments: logos are left-aligned, full conference name remains right-aligned, no forced letterhead separator, watermark non-distracting, and entire signer and QR placeholder comfortably above the footer.
- This is **synthetic example data**, no real accepted paper or generated verifiable QR. Extended titles and many authors remain an implementation-stage edge-case test; one-page current sample is not proof that any input length always fits.

## Final owner-approved cleanup — applied to the same HTML master

- Removed upper-label **OFFICIAL ACADEMIC CORRESPONDENCE** completely.
- Removed upper-right **Date: 15 December 2026** and **MASTER DESIGN PREVIEW · SAMPLE DATA ONLY** from the letter. The document number remains in the body heading section.
- Inserted the bare actual issuance date **15 December 2026** as an illustrative value **immediately above “Sincerely,”**. It is displayed exactly once and must be dynamically replaced from the real `issued_at` at issuance.
- Replaced right-hand green footer label with **UNISYA · INDONESIA**, retaining the left-hand ICHES text and existing green footer.
- **Final QA:** regenerated PDF from the same master HTML; checked **1 A4 page**, final preview screenshot; all signing/QR/footer content is visible without overlap. No HTML/PDF divergence.
- **OWNER DESIGN PASS** accepts the typography/layout/structure and these final adjustments; it does **not** authorize false issuance, signing with missing assets, backend source edits, or production deployment.

## Final type refinements — approved master HTML V2.1

- The complete visible official number, including `No.` and the generated number, uses **Courier New Bold**, `font-family: "Courier New", Courier, monospace`, `font-weight: 700`, within `.issue-details .letter-number`.
- The issuance date immediately above `Sincerely,` uses the **real bundled Sora font** (`.signature .issued-on`, `500 9.8pt/1.4`), with no `Date:` label.
- The footer-right text `UNISYA · INDONESIA` is **exactly 7.2pt**, matching the footer-left `ICHES 2027 · OFFICIAL CORRESPONDENCE` (`7.2pt`); both share `font-weight:700` and `.4pt` tracking. Footer green, text and spacing otherwise unchanged.
- The existing V2.1 master HTML was edited in place by explicit owner typography request and the **same HTML** was re-rendered to the existing PDF and PNG preview paths. Visual image inspected: no clipping or footer overflow; PDF reader confirms **one A4 page**. No Laravel source or official LoA issuance involved.

**Next:** [Implementation Work Order DOC-LOA-001](WO-DOC-LOA-001-HTML-PDF-IMPLEMENTATION.md) is a planning artifact; coding requires separate explicit owner GO. Do not commit application source, issue official PDF, or activate official QR until eligible paper, authorized signature and document verification implementation are actually ready.