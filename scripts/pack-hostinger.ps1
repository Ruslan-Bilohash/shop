# Zip install/ package for Hostinger upload (bilohash.com/shop/install/)
$ErrorActionPreference = 'Stop'
$root = Split-Path $PSScriptRoot -Parent
$install = Join-Path $root 'install'
$out = Join-Path $root ('shop-install-hostinger-' + (Get-Date -Format 'yyyyMMdd-HHmm') + '.zip')

if (-not (Test-Path $install)) {
    Write-Host 'Run build-install.ps1 first'
    exit 1
}

& (Join-Path $PSScriptRoot 'build-install.ps1') | Out-Null

if (Test-Path $out) { Remove-Item $out -Force }
Compress-Archive -Path (Join-Path $install '*') -DestinationPath $out -CompressionLevel Optimal
Write-Host "Created: $out"
Write-Host 'Upload to: public_html/shop/install/ (extract, keep data/db.config.php)'