# DOC-LOA-001 — HTML Letterhead Master V2 (Owner Design Review)

**Updated:** 21 November 2026
**Status:** V2 prototype superseded by final refined **V2.1 OWNER DESIGN PASS**. See [V2.1](DOC-LOA-001-V2.1-PRECISION-REVIEW.md). No Laravel generator, migration, QR verification, or issued LoA.
**Supersedes visually:** `DOC-LOA-001-V1-COPY-AND-VISUAL-REVIEW.md` (English copy/business logic continues unless explicitly revised).
**Related rules:** `DOC-LOA-001-VISUAL-DESIGN-ASSETS.md`, `CURRENT-STATE-AND-DECISIONS.md`, `PRD.md`.

## Owner-requested revisions implemented in standalone V2

1. Make **editable HTML/CSS the sole design master**. Preview PDF is rendered from that exact HTML using headless Chrome / Edge. Never independently redesign a PDF after HTML approval.
2. **Header left:** all original FORPIM, UNISYA and ICHES logos grouped on the left at modest, optically balanced sizes. Adjacent ICHES text **ICHES 2027** uses genuine Sora Bold. Preserve the ratio of each original logo.
3. **Header right:** the FULL official conference name `THE 6TH INTERNATIONAL CONFERENCE ON HUMANITY, EDUCATION, AND SOCIETY` and host `Hosted by Universitas Islam Syarifuddin (UNISYA), Lumajang, Indonesia`; right-aligned, no shortened name.
4. **No header horizontal divider**. Letterhead visual language follows the minimal stationery samples the owner supplied, without copying their brand or ornament.
5. **Subtle watermark:** reuse the original ICHES WebP logo as a one-color neutral light gray watermark by **CSS only** (`filter: brightness(0); opacity: .045`). Do not generate or redraw a counterfeit mark; artwork remains intact.
6. **A4 portrait, white-dominant**, carefully spaced editorial body, quiet field labels, academic metadata without heavy shaded boxes, green horizontal footer like visual reference #3.
7. **Engrained academic wording:** abstract accepted for presentation only, not full manuscript, final full paper publication, journal indexing or a guaranteed session.
8. **Signature/QR not fabricated:** provisional Dr. Ahmad Ihwanul Muttaqin, M.Pd.I. (Steering Committee), authorized signature is a placeholder; QR verification is placeholder until official Issued backend.

## Single authoritative design source and reproducible print output on owner's Windows computer

- Standalone directory: `D:\PINJAM-AFNAN\ICHES\LOA-DESIGN-V2`
- **HTML/CSS MASTER:** `ICHES-LOA-MASTER-V2.html`
- Master-relative local artwork assets: `assets/logo-forpim.webp`, `assets/logo-unisya.webp`, `assets/logo-iches.webp`; font `assets/Sora-Variable.ttf` for exact Sora appearance (keep appropriate font licensing/embedding when integrated).
- Render command: `powershell -ExecutionPolicy Bypass -File "D:\PINJAM-AFNAN\ICHES\LOA-DESIGN-V2\render-pdf.ps1"`
- Chrome-rendered A4 PDF: `ICHES-2027-LoA-V2-HTML-render.pdf`
- Browser screenshot: `ICHES-2027-LoA-V2-preview.png`
- `README.txt` explains source-of-truth workflow, sample content, rendering and integration.

### Verification performed

- Same HTML loaded in browser and printed with headless Chrome. **One-page A4 PDF verified** via PDF reader/extracted text.
- Visual screenshot manually reviewed: left institutional mark trio, right full conference identification, no horizontal header rule, gray one-color ICHES watermark, all signature/QR lines above footer.
- Signature/footer collision was identified during first V2 QA and corrected by CSS spacing in the SAME HTML master before PDF regenerated.
- `render-pdf.ps1` re-run successfully and reproduced the PDF (526078 bytes in reviewed local run).
- Synthetic test content only: sample `ICH27-P0000XX`, example research title/authors/track, `0001/ICHES/LOA/XII/2026` aligned to **15 December 2026**. These are not real issuance credentials.

## Sign-off and pending inputs

- Confirm the owner-selected original `logo-iches.webp` file is the preferred official ICHES logo (FORPIM/UNISYA paths already specified).
- Evaluate layout and faint watermark opacity from real HTML/PDF, not only Markdown or AI mockups.
- Provide official signature image or an authorized signature procedure; provisional signatory needs formal confirmation.
- QR verification URL will be generated only when DOC-LOA-001 feature receives coding GO, not in static prototype.
- Once approved, port the **same HTML structure and print CSS** to versioned Laravel Blade/PDF rendering, use dynamic immutable paper/author/track snapshots; test very long titles and lists of coauthors and the exact print engine.
- Keep HTML/PDF content snapshots stable on issuance; edits to later master versions cannot silently change already issued LoAs.

**Git status:** standalone preview is outside application source. Documentation records can remain local and uncommitted until separate owner approval. No source commit/push without explicit owner authorization.