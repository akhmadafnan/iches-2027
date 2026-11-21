# DOC-LOA-001 design preview only — render from the SAME authoritative HTML source.
$ErrorActionPreference = 'Stop'
$folder = Split-Path -Parent $MyInvocation.MyCommand.Path
$html = Join-Path $folder 'ICHES-LOA-MASTER-V2.1.html'
$pdf = Join-Path $folder 'ICHES-2027-LoA-V2.1-HTML-render.pdf'
$browserPaths = @(
  'C:\Program Files\Google\Chrome\Application\chrome.exe',
  'C:\Program Files (x86)\Google\Chrome\Application\chrome.exe',
  'C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe',
  'C:\Program Files\Microsoft\Edge\Application\msedge.exe'
)
$browser = $browserPaths | Where-Object { Test-Path -LiteralPath $_ } | Select-Object -First 1
if (-not $browser) { throw 'No supported browser was found.' }
if (-not (Test-Path -LiteralPath $html)) { throw 'HTML master was not found.' }
$url = [Uri]::new($html).AbsoluteUri
$profile = Join-Path $env:TEMP 'iches-loa-v21-pdf-profile'
if (Test-Path -LiteralPath $pdf) { Remove-Item -LiteralPath $pdf -Force }
$args = @('--headless=new','--disable-gpu','--disable-extensions','--no-pdf-header-footer',"--print-to-pdf=$pdf","--user-data-dir=$profile",$url)
$p = Start-Process -FilePath $browser -ArgumentList $args -Wait -PassThru -NoNewWindow
if ($p.ExitCode -ne 0 -or -not (Test-Path -LiteralPath $pdf)) { throw "PDF rendering failed (exit $($p.ExitCode))." }
Write-Host "HTML master => PDF verified: $pdf"