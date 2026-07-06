# Minimal hotfix zip for Hostinger — ecosystem paths + admin init
$ErrorActionPreference = 'Stop'
$root = Split-Path $PSScriptRoot -Parent
$install = Join-Path $root 'install'
$staging = Join-Path $env:TEMP ('shop-hotfix-' + [guid]::NewGuid().ToString('n'))
$out = Join-Path $root 'shop-install-hotfix-ecosystem.zip'

& (Join-Path $PSScriptRoot 'build-install.ps1') | Out-Null

New-Item -ItemType Directory -Path (Join-Path $staging 'includes') -Force | Out-Null
New-Item -ItemType Directory -Path (Join-Path $staging 'admin') -Force | Out-Null

Copy-Item (Join-Path $install 'includes\*') (Join-Path $staging 'includes') -Recurse -Force
New-Item -ItemType Directory -Path (Join-Path $staging 'admin') -Force | Out-Null
Get-ChildItem (Join-Path $install 'admin') -Filter '*.php' -File | ForEach-Object {
    Copy-Item $_.FullName (Join-Path $staging "admin\$($_.Name)") -Force
}

if (Test-Path $out) { Remove-Item $out -Force }
Compress-Archive -Path (Join-Path $staging '*') -DestinationPath $out -CompressionLevel Optimal
Remove-Item $staging -Recurse -Force

Write-Host "Created: $out"
Write-Host 'Upload to public_html/shop/install/ and extract (merge folders).'