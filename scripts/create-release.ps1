# Create GitHub release with install + not_mysql zip assets
# Usage: powershell -File scripts/create-release.ps1 -Version 1.5.1

param(
    [string]$Version = '1.5.1'
)

$ErrorActionPreference = 'Stop'
$root = Split-Path $PSScriptRoot -Parent
$tag = "v$Version"

$credIn = "protocol=https`nhost=github.com`n`n"
$credOut = $credIn | git credential fill 2>$null
$token = ($credOut -split "`n" | Where-Object { $_ -like 'password=*' }) -replace 'password=',''
if (-not $token) { throw 'GitHub token not available from git credential' }

$notesFile = Join-Path $root "RELEASE_$tag.md"
if (-not (Test-Path $notesFile)) { throw "Missing release notes: $notesFile" }
$notes = [IO.File]::ReadAllText($notesFile)

$payload = @{
    tag_name = $tag
    name = "Shop CMS $tag"
    body = $notes
    draft = $false
    prerelease = $false
} | ConvertTo-Json -Compress
$jsonPath = Join-Path $root '_release_payload.json'
[IO.File]::WriteAllText($jsonPath, $payload, [Text.UTF8Encoding]::new($false))

$respPath = Join-Path $root '_release_resp.json'
$code = & curl.exe --max-time 60 -sS -X POST `
    -H "Authorization: Bearer $token" `
    -H "Accept: application/vnd.github+json" `
    -H "Content-Type: application/json" `
    --data-binary "@$jsonPath" `
    "https://api.github.com/repos/Ruslan-Bilohash/shop/releases" `
    -o $respPath -w "%{http_code}"
Write-Host "Create HTTP: $code"
$release = Get-Content $respPath -Raw | ConvertFrom-Json
if (-not $release.id) {
    Get-Content $respPath
    throw 'Release creation failed'
}
Write-Host "Release: $($release.html_url)"

$assets = @(
    @{ Zip = "shop-install-$tag.zip"; Folder = 'install' },
    @{ Zip = "shop-not-mysql-$tag.zip"; Folder = 'not_mysql' }
)

foreach ($asset in $assets) {
    $zip = Join-Path $root $asset.Zip
    if (Test-Path $zip) { Remove-Item $zip -Force }
    $src = Join-Path $root $asset.Folder
    if (-not (Test-Path $src)) { throw "Missing folder: $src" }
    Compress-Archive -Path (Join-Path $src '*') -DestinationPath $zip -Force
    Write-Host "Packed: $($asset.Zip)"

    $uploadBase = ($release.upload_url -replace '\{\?name,label\}', '')
    $uploadUrl = "${uploadBase}?name=$($asset.Zip)"
    $assetPath = Join-Path $root "_asset_$($asset.Folder).json"
    $acode = & curl.exe --max-time 180 -sS -X POST `
        -H "Authorization: Bearer $token" `
        -H "Accept: application/vnd.github+json" `
        -H "Content-Type: application/zip" `
        --data-binary "@$zip" `
        $uploadUrl `
        -o $assetPath -w "%{http_code}"
    Write-Host "Asset $($asset.Zip) HTTP: $acode"
    $uploaded = Get-Content $assetPath -Raw | ConvertFrom-Json
    Write-Host "Download: $($uploaded.browser_download_url)"
}