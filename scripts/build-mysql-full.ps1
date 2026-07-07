# Shop CMS — full MySQL deployment package (install + migration tools)
# Usage: powershell -File scripts/build-mysql-full.ps1

$ErrorActionPreference = 'Stop'
$root = Split-Path $PSScriptRoot -Parent

Write-Host "Step 1: sync dev -> install/"
& (Join-Path $PSScriptRoot 'build-install.ps1')

Write-Host "Step 2: migration & schema files"
$copyRoot = @(
    'schema.sql', 'migrate-to-mysql.php', 'install.php'
)
foreach ($f in $copyRoot) {
    $src = Join-Path $root $f
    if (Test-Path $src) {
        Copy-Item $src (Join-Path $root "install\$f") -Force
    }
}

$migrateIncludes = @(
    'mysql-migrate.php', 'mysql-init.stub.php'
)
$incDst = Join-Path $root 'install\includes'
foreach ($f in $migrateIncludes) {
    $src = Join-Path $root "includes\$f"
    if (Test-Path $src) {
        Copy-Item $src (Join-Path $incDst $f) -Force
    }
}

$mysqlDir = Join-Path $root 'includes\mysql'
$mysqlDst = Join-Path $incDst 'mysql'
if (-not (Test-Path $mysqlDst)) { New-Item -ItemType Directory -Path $mysqlDst -Force | Out-Null }
if (Test-Path $mysqlDir) {
    Copy-Item (Join-Path $mysqlDir '*') $mysqlDst -Force
}

Write-Host "Step 3: zip shop-mysql-full"
$zipPath = Join-Path $root 'shop-mysql-full-v1.6.0.zip'
if (Test-Path $zipPath) { Remove-Item $zipPath -Force }
Compress-Archive -Path (Join-Path $root 'install\*') -DestinationPath $zipPath -Force

Write-Host "Done: $zipPath"