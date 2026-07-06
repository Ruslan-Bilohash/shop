# Hotfix zip for public_html/shop/ — missing ecosystem includes + chat widget assets
$ErrorActionPreference = 'Stop'
$root = Split-Path $PSScriptRoot -Parent
& (Join-Path $PSScriptRoot 'sync-ecosystem.ps1') | Out-Null

$out = Join-Path $root 'shop-hotfix-ecosystem.zip'
$includeFiles = @(
    'bh-cms-site-settings.php',
    'cms-contact.php',
    'cms-contact-form.php',
    'cms-contact.css',
    'bh-mail.php',
    'bh-chat-widget.php',
    'mail-config.example.php',
    'ecosystem-i18n.php',
    'ecosystem-defs.php',
    'bh-cms-links.php',
    'ecosystem-load.php'
)
$adminFiles = @(
    'bh-cms-admin/admin-settings.css',
    'bh-cms-admin/settings-tabs.php',
    'bh-cms-admin/form-appearance.php',
    'bh-cms-admin/form-chat.php',
    'bh-cms-admin/form-recaptcha.php',
    'bh-cms-admin/complete-settings-page.php',
    'bh-cms-admin/page-shell.php'
)
$assetFiles = @(
    'assets/css/bh-chat-widget.css',
    'assets/js/bh-chat-widget.js'
)

if (Test-Path $out) { Remove-Item $out -Force }

$staging = Join-Path $env:TEMP ("shop-ecosystem-hotfix-" + [guid]::NewGuid().ToString())
New-Item -ItemType Directory -Path $staging -Force | Out-Null
try {
    foreach ($f in $includeFiles) {
        $src = Join-Path $root "includes\$f"
        if (-not (Test-Path $src)) {
            Write-Warning "Skip missing: includes/$f"
            continue
        }
        $dst = Join-Path $staging "includes\$f"
        $parent = Split-Path $dst -Parent
        if (-not (Test-Path $parent)) {
            New-Item -ItemType Directory -Path $parent -Force | Out-Null
        }
        Copy-Item $src $dst -Force
    }
    foreach ($f in $adminFiles) {
        $src = Join-Path $root "includes\$f"
        if (-not (Test-Path $src)) {
            Write-Warning "Skip missing: includes/$f"
            continue
        }
        $dst = Join-Path $staging "includes\$f"
        $parent = Split-Path $dst -Parent
        if (-not (Test-Path $parent)) {
            New-Item -ItemType Directory -Path $parent -Force | Out-Null
        }
        Copy-Item $src $dst -Force
    }
    foreach ($f in $assetFiles) {
        $src = Join-Path $root $f
        if (-not (Test-Path $src)) {
            Write-Warning "Skip missing: $f"
            continue
        }
        $dst = Join-Path $staging $f
        $parent = Split-Path $dst -Parent
        if (-not (Test-Path $parent)) {
            New-Item -ItemType Directory -Path $parent -Force | Out-Null
        }
        Copy-Item $src $dst -Force
    }

    & tar -a -c -f $out -C $staging includes assets
    Write-Host "Created: $out ($((Get-Item $out).Length) bytes)"
    Write-Host 'Upload to public_html/shop/ and extract (merge includes/ and assets/).'
}
finally {
    if (Test-Path $staging) {
        Remove-Item $staging -Recurse -Force
    }
}