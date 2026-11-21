ICHES 2027 — LoA Master Design V2.1 — OWNER DESIGN PASS (21 NOV 2026)

This is a precision-layout revision of V2; it does not change the approved LoA business policy or launch the generator.

DESIGN FILES
ICHES-LOA-MASTER-V2.1.html                 Authoritative editable HTML/CSS design
ICHES-2027-LoA-V2.1-HTML-render.pdf        1-page A4 PDF rendered directly from the above HTML
ICHES-2027-LoA-V2.1-preview.png           Screenshot preview of HTML design
render-pdf.ps1                             Rebuild PDF from HTML using Chrome/Edge
assets/                                    Copies of original FORPIM, UNISYA and candidate ICHES logos, plus print font

HOW TO VIEW OR REGENERATE
1. Open ICHES-LOA-MASTER-V2.1.html in Chrome or Edge.
2. Edit only the HTML/CSS source, never separately redraw the PDF.
3. Rebuild PDF with:
   powershell -ExecutionPolicy Bypass -File "D:\PINJAM-AFNAN\ICHES\LOA-DESIGN-V2.1\render-pdf.ps1"

WHAT CHANGED FROM V2
- Header, body and footer share 20mm left/right geometry; the logo cluster is narrower and better aligned.
- Wider right header text area; identical official English conference name and host text, no dividing rule.
- Reduced unnecessary white space after the header without crowding it.
- Recentered the authentic ICHES watermark and reduced opacity to 3.4% to keep the academic text readable.
- Signature, verification placeholder and authorized signer have greater breathing room above the green footer.
- Original three logos maintained, Sora Bold retained for ICHES 2027.
- Owner-approved final cleanup: removed the upper `OFFICIAL ACADEMIC CORRESPONDENCE` kicker, upper `Date:` and `MASTER DESIGN PREVIEW` text; moved issue date to the line immediately above `Sincerely,` with NO `Date` label; changed right footer to `UNISYA · INDONESIA`.
- Owner-approved typography refinement: visible `No.` plus full LoA number = Courier New Bold (`700`); issued date above `Sincerely,` = true Sora (`500`); both footer labels use **7.2pt**, bold `700` and letter-spacing `.4pt`.

VALIDATION
- Preview PNG visually reviewed.
- Actual PDF generated from the same HTML source and confirmed as 1 A4 page.
- Sample academic metadata/number are NOT REAL; signature and QR remain placeholders.
- Very long paper titles/many coauthors should undergo separate overflow tests before production.
- No official Issued LoA, backend QR, migrated database table, or committed Laravel changes.

STATUS: OWNER DESIGN PASS — final visual HTML V2.1 and its 1-page A4 PDF are approved. V2 remains preserved in its original folder. This is design acceptance only: actual signature, issued documents and QR/backend generator are not implemented; see `D:\PINJAM-AFNAN\iches-2027\docs\WO-DOC-LOA-001-HTML-PDF-IMPLEMENTATION.md`. The sample number/date/title/authors remain illustrative.