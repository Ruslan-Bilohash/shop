# Copy shared Bilohash ecosystem includes into Shop CMS packages (storefront + install).
# Usage: powershell -File scripts/sync-ecosystem.ps1

$ErrorActionPreference = 'Stop'
$root = Split-Path $PSScriptRoot -Parent
$bilohash = Split-Path $root -Parent
$ecoSrc = Join-Path $bilohash 'includes'
$assetsSrc = Join-Path $bilohash 'assets'

if (-not (Test-Path $ecoSrc)) {
    throw "Ecosystem source not found: $ecoSrc"
}

$files = @(
    'bh-cms-site-settings.php',
    'cms-contact.php',
    'cms-contact-form.php',
    'cms-contact.css',
    'bh-mail.php',
    'bh-chat-widget.php',
    'mail-config.example.php',
    'ecosystem-i18n.php',
    'ecosystem-defs.php',
    'bh-cms-links.php'
)

$dirs = @(
    'bh-cms-admin'
)

$assetFiles = @(
    'css/bh-chat-widget.css',
    'js/bh-chat-widget.js'
)

$includeTargets = @(
    (Join-Path $root 'includes'),
    (Join-Path $root 'install\includes'),
    (Join-Path $root 'not_mysql\includes')
)

foreach ($dst in $includeTargets) {
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
    foreach ($d in $dirs) {
        $srcDir = Join-Path $ecoSrc $d
        if (-not (Test-Path $srcDir)) {
            Write-Warning "Missing source dir: $d"
            continue
        }
        $dstDir = Join-Path $dst $d
        if (Test-Path $dstDir) {
            Remove-Item $dstDir -Recurse -Force
        }
        Copy-Item $srcDir $dstDir -Recurse -Force
        Write-Host "OK $d/ -> $dst"
    }
}

$assetDst = Join-Path $root 'assets'
if (-not (Test-Path $assetDst)) {
    New-Item -ItemType Directory -Path $assetDst -Force | Out-Null
}
foreach ($rel in $assetFiles) {
    $src = Join-Path $assetsSrc $rel
    if (-not (Test-Path $src)) {
        Write-Warning "Missing asset: $rel"
        continue
    }
    $dst = Join-Path $assetDst $rel
    $parent = Split-Path $dst -Parent
    if (-not (Test-Path $parent)) {
        New-Item -ItemType Directory -Path $parent -Force | Out-Null
    }
    Copy-Item $src $dst -Force
    Write-Host "OK assets/$rel -> $assetDst"
}

Write-Host 'Ecosystem sync done.'