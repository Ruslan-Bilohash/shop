# Deploy changed Shop CMS files to Hostinger production (bilohash.com/shop/).
# Usage:
#   powershell -File scripts/deploy-to-hostinger.ps1
#   powershell -File scripts/deploy-to-hostinger.ps1 -Files lang/no.php,lang/uk.php
#   powershell -File scripts/deploy-to-hostinger.ps1 -ChangedSinceGit

param(
    [string[]]$Files = @(),
    [switch]$ChangedSinceGit,
    [switch]$LangOnly
)

$ErrorActionPreference = 'Stop'
$root = Split-Path $PSScriptRoot -Parent
$configPath = Join-Path $PSScriptRoot 'deploy.config.local.ps1'
$examplePath = Join-Path $PSScriptRoot 'deploy.config.example.ps1'

if (-not (Test-Path $configPath)) {
    Write-Host "Missing $configPath"
    Write-Host "Copy deploy.config.example.ps1 → deploy.config.local.ps1 and set Hostinger SSH credentials."
    exit 1
}

$cfg = & { . $configPath; if ($RemoteRoot) { return @{ Host = $DeployHost; Port = $Port; User = $User; Password = $Password; RemoteRoot = $RemoteRoot } } }
if ($cfg -is [hashtable]) {
    $DeployHost = $cfg.Host
    $Port = $cfg.Port
    $User = $cfg.User
    $Password = $cfg.Password
    $RemoteRoot = $cfg.RemoteRoot
}
if (-not $RemoteRoot) { throw 'RemoteRoot not set in deploy.config.local.ps1' }

if ($LangOnly) {
    $Files = @(
        'lang/no.php','lang/uk.php','lang/admin-guides.php','lang/admin-settings-guides.php',
        'site/lang/no.php','site/lang/uk.php'
    )
}

if ($ChangedSinceGit) {
    Push-Location $root
    $gitFiles = git diff --name-only HEAD 2>$null
    if (-not $gitFiles) { $gitFiles = git diff --name-only 2>$null }
    Pop-Location
    if ($gitFiles) { $Files = @($gitFiles) }
}

if (-not $Files -or $Files.Count -eq 0) {
    Write-Host 'No files to deploy. Pass -Files or -ChangedSinceGit or -LangOnly.'
    exit 1
}

# Ensure Posh-SSH
if (-not (Get-Module -ListAvailable -Name Posh-SSH)) {
    Write-Host 'Installing Posh-SSH (CurrentUser)...'
    Install-Module Posh-SSH -Scope CurrentUser -Force -AllowClobber
}
Import-Module Posh-SSH -ErrorAction Stop

$keyFile = Join-Path $env:USERPROFILE '.ssh\id_ed25519'
if ($Password) {
    $secPass = ConvertTo-SecureString $Password -AsPlainText -Force
    $cred = New-Object System.Management.Automation.PSCredential ($User, $secPass)
} else {
    $cred = New-Object System.Management.Automation.PSCredential ($User, (New-Object System.Security.SecureString))
}

$scpParams = @{
    ComputerName = $DeployHost
    Port         = $Port
    Credential   = $cred
    AcceptKey    = $true
}
if (-not $Password) { $scpParams.KeyFile = $keyFile }

$session = New-SSHSession @scpParams -ErrorAction Stop
$sessionId = $session.SessionId
$ok = 0
$fail = 0

foreach ($rel in $Files) {
    $rel = $rel -replace '\\', '/'
    $src = Join-Path $root $rel
    if (-not (Test-Path $src)) {
        Write-Warning "SKIP missing local: $rel"
        $fail++
        continue
    }
    $remote = ($RemoteRoot.TrimEnd('/')) + '/' + $rel
    $remoteDir = ($remote -replace '/[^/]+$', '')
    $remoteName = Split-Path $remote -Leaf
    Invoke-SSHCommand -SessionId $sessionId -Command "mkdir -p '$remoteDir'" | Out-Null
    Set-SCPItem @scpParams -Path $src -Destination $remoteDir -NewName $remoteName
    Write-Host "OK $rel → $remote"
    $ok++
}

Remove-SSHSession -SessionId $sessionId | Out-Null
Write-Host "Deploy done: $ok uploaded, $fail skipped."