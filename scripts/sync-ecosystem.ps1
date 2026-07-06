# Copy shared Bilohash ecosystem includes into Shop CMS packages (storefront + install).
# Usage: powershell -File scripts/sync-ecosystem.ps1

$ErrorActionPreference = 'Stop'
$root = Split-Path $PSScriptRoot -Parent
$ecoSrc = Join-Path (Split-Path $root -Parent) 'includes'

if (-not (Test-Path $ecoSrc)) {
    throw "Ecosystem source not found: $ecoSrc"
}

$files = @(
    'bh-cms-site-settings.php',
    'cms-contact.php',
    'cms-contact-form.php',
    'bh-mail.php',
    'mail-config.example.php',
    'ecosystem-i18n.php',
    'ecosystem-defs.php',
    'bh-cms-links.php'
)

$targets = @(
    (Join-Path $root 'includes'),
    (Join-Path $root 'install\includes')
)

foreach ($dst in $targets) {
    if (-not (Test-Path $dst)) {
        New-Item -ItemType Directory -Path $dst -Force | Out-Null
    }
    foreach ($f in $files) {
        $src = Join-Path $ecoSrc $f
        if (-not (Test-Path $src)) {
            Write-Warning "Missing source: $f"
            continue
        }
        Copy-Item $src (Join-Path $dst $f) -Force
        Write-Host "OK $f -> $dst"
    }
}

Write-Host 'Ecosystem sync done.'