# Hotfix zip for public_html/shop/ — missing ecosystem includes (bh-mail.php, cms-contact.php, …)
$ErrorActionPreference = 'Stop'
$root = Split-Path $PSScriptRoot -Parent
& (Join-Path $PSScriptRoot 'sync-ecosystem.ps1') | Out-Null

$out = Join-Path $root 'shop-hotfix-ecosystem.zip'
$files = @(
    'bh-cms-site-settings.php',
    'cms-contact.php',
    'cms-contact-form.php',
    'bh-mail.php',
    'mail-config.example.php',
    'ecosystem-i18n.php',
    'ecosystem-defs.php',
    'bh-cms-links.php',
    'ecosystem-load.php'
)

if (Test-Path $out) { Remove-Item $out -Force }

$args = @('-a', '-c', '-f', $out, '-C', (Join-Path $root 'includes'))
foreach ($f in $files) {
    if (Test-Path (Join-Path $root "includes\$f")) {
        $args += $f
    }
}
& tar @args

Write-Host "Created: $out ($((Get-Item $out).Length) bytes)"
Write-Host 'Upload to public_html/shop/ and extract into includes/ (merge folders).'