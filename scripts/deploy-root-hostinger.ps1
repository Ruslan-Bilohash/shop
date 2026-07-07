# Deploy files to bilohash.com root (public_html/) — license.php, api/, includes/
param([string[]]$Files = @())

$ErrorActionPreference = 'Stop'
$configPath = Join-Path $PSScriptRoot 'deploy.config.local.ps1'
if (-not (Test-Path $configPath)) { throw "Missing $configPath" }
. $configPath

$RemoteRoot = '/home/u762384583/domains/bilohash.com/public_html'
$LocalRoot  = 'C:\bilohash'

if (-not $Files -or $Files.Count -eq 0) { throw 'Pass -Files' }

Import-Module Posh-SSH -ErrorAction Stop
$secPass = ConvertTo-SecureString $Password -AsPlainText -Force
$cred = New-Object PSCredential ($User, $secPass)
$scpParams = @{ ComputerName = $DeployHost; Port = $Port; Credential = $cred; AcceptKey = $true }

$session = New-SSHSession @scpParams
try {
    foreach ($rel in $Files) {
        $rel = $rel -replace '\\', '/'
        $src = Join-Path $LocalRoot ($rel -replace '/', '\')
        if (-not (Test-Path $src)) { Write-Warning "SKIP $rel"; continue }
        $remote = ($RemoteRoot.TrimEnd('/')) + '/' + $rel
        $remoteDir = ($remote -replace '/[^/]+$', '')
        Invoke-SSHCommand -SessionId $session.SessionId -Command "mkdir -p '$remoteDir'" -TimeOut 10 | Out-Null
        Set-SCPItem @scpParams -Path $src -Destination $remoteDir -NewName (Split-Path $remote -Leaf) -ConnectionTimeout 60
        Write-Host "OK $rel"
    }
} finally {
    Remove-SSHSession -SessionId $session.SessionId | Out-Null
}