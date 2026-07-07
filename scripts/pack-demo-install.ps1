# Shop CMS — commercial demo package (30-day trial + license.php keys)
$ErrorActionPreference = 'Stop'
$root = Split-Path $PSScriptRoot -Parent
$version = '1.7.1'
if (Test-Path (Join-Path $root 'includes\version.php')) {
    $vContent = Get-Content (Join-Path $root 'includes\version.php') -Raw
    if ($vContent -match "define\('SH_VERSION',\s*'([^']+)'\)") {
        $version = $Matches[1]
    }
}
$out = Join-Path $root ("shop-demo-30d-v{0}-{1}.zip" -f $version, (Get-Date -Format 'yyyyMMdd-HHmm'))

& (Join-Path $PSScriptRoot 'build-install.ps1') | Out-Null

if (Test-Path $out) { Remove-Item $out -Force }
Compress-Archive -Path (Join-Path $root 'install\*') -DestinationPath $out -CompressionLevel Optimal
Write-Host "Created demo package: $out"
Write-Host "Trial: 30 days | License keys: https://bilohash.com/license.php"