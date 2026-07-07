# Deploy a zip bundle to Hostinger in ONE SSH session (avoids connection limit).
param(
    [string[]]$Files = @(),
    [switch]$RunBuildOverlays
)

$ErrorActionPreference = 'Stop'
$root = Split-Path $PSScriptRoot -Parent
$configPath = Join-Path $PSScriptRoot 'deploy.config.local.ps1'
if (-not (Test-Path $configPath)) { throw "Missing $configPath" }
. $configPath

if (-not $Files -or $Files.Count -eq 0) {
    throw 'Pass -Files with relative paths'
}

Import-Module Posh-SSH -ErrorAction Stop
$secPass = ConvertTo-SecureString $Password -AsPlainText -Force
$cred = New-Object PSCredential ($User, $secPass)
$scpParams = @{ ComputerName = $DeployHost; Port = $Port; Credential = $cred; AcceptKey = $true }

$staging = Join-Path $env:TEMP ("shop-bundle-" + [guid]::NewGuid().ToString())
New-Item -ItemType Directory -Path $staging -Force | Out-Null
$ok = 0
foreach ($rel in $Files) {
    $rel = $rel -replace '\\', '/'
    $src = Join-Path $root ($rel -replace '/', '\')
    if (-not (Test-Path $src)) { Write-Warning "SKIP $rel"; continue }
    $dst = Join-Path $staging $rel
    $dir = Split-Path $dst -Parent
    if (-not (Test-Path $dir)) { New-Item -ItemType Directory -Path $dir -Force | Out-Null }
    Copy-Item $src $dst -Force
    $ok++
}
if ($ok -eq 0) { throw 'No files staged' }

$zipPath = Join-Path $env:TEMP ("shop-bundle-" + (Get-Date -Format 'yyyyMMdd-HHmmss') + '.zip')
if (Test-Path $zipPath) { Remove-Item $zipPath -Force }
Compress-Archive -Path (Join-Path $staging '*') -DestinationPath $zipPath -Force
Remove-Item $staging -Recurse -Force

$remoteZip = '/tmp/shop-bundle.zip'
$remoteRoot = $RemoteRoot.TrimEnd('/')

Write-Host "Uploading $ok files as zip..."
$s = New-SSHSession @scpParams -ConnectionTimeout 45
try {
    Set-SCPItem @scpParams -Path $zipPath -Destination '/tmp' -NewName 'shop-bundle.zip'
    $cmd = @"
cd '$remoteRoot' && unzip -o '$remoteZip' -d '$remoteRoot' && rm -f '$remoteZip'
"@
    $r = Invoke-SSHCommand -SessionId $s.SessionId -Command $cmd -TimeOut 120
    if ($r.ExitStatus -ne 0) { Write-Host $r.Output; throw "unzip failed exit $($r.ExitStatus)" }
    Write-Host "Extracted bundle to $remoteRoot"

    if ($RunBuildOverlays) {
        $build = Invoke-SSHCommand -SessionId $s.SessionId -Command "php '$remoteRoot/scripts/build-admin-overlays.php' 2>&1" -TimeOut 120
        Write-Host $build.Output
    }
} finally {
    Remove-SSHSession -SessionId $s.SessionId | Out-Null
    Remove-Item $zipPath -Force -ErrorAction SilentlyContinue
}
Write-Host "Bundle deploy done: $ok files."