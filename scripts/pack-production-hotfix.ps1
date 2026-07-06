# Combined production hotfix for public_html/shop/ — ecosystem warnings + invoice demo preview
$ErrorActionPreference = 'Stop'
$root = Split-Path $PSScriptRoot -Parent
& (Join-Path $PSScriptRoot 'sync-ecosystem.ps1') | Out-Null

$out = Join-Path $root 'shop-hotfix-production-jul7.zip'
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
    'ecosystem-load.php',
    'invoice-settings.php',
    'invoice-render.php',
    'invoice-print-designs.php'
)
$adminFiles = @(
    'bh-cms-admin/admin-settings.css',
    'bh-cms-admin/settings-tabs.php',
    'bh-cms-admin/form-appearance.php',
    'bh-cms-admin/form-chat.php',
    'bh-cms-admin/form-recaptcha.php',
    'bh-cms-admin/complete-settings-page.php',
    'bh-cms-admin/page-shell.php',
    'admin/includes/form-invoice.php'
)
$assetFiles = @(
    'assets/css/bh-chat-widget.css',
    'assets/js/bh-chat-widget.js',
    'assets/css/invoice-print.css',
    'assets/css/invoice-print-designs.css'
)
$langFiles = @(
    'lang/en.php',
    'lang/uk.php'
)

if (Test-Path $out) { Remove-Item $out -Force }

$staging = Join-Path $env:TEMP ("shop-prod-hotfix-" + [guid]::NewGuid().ToString())
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
        if ($f -like 'admin/*') {
            $src = Join-Path $root $f
            $dst = Join-Path $staging $f
        } else {
            $src = Join-Path $root "includes\$f"
            $dst = Join-Path $staging "includes\$f"
        }
        if (-not (Test-Path $src)) {
            Write-Warning "Skip missing: $f"
            continue
        }
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
    foreach ($f in $langFiles) {
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

    & tar -a -c -f $out -C $staging includes admin assets lang
    Write-Host "Created: $out ($((Get-Item $out).Length) bytes)"
    Write-Host 'Upload to public_html/shop/ and extract (merge folders). Do not overwrite database.php or data/db.config.php.'
}
finally {
    if (Test-Path $staging) {
        Remove-Item $staging -Recurse -Force
    }
}