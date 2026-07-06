param(
    [string]$Tag = 'v1.5.1',
    [int]$ReleaseId = 349884745
)

$ErrorActionPreference = 'Stop'
$root = Split-Path $PSScriptRoot -Parent

$job = Start-Job {
    'protocol=https', 'host=github.com', '', '' | git credential fill 2>$null
}
if (-not (Wait-Job $job -Timeout 15)) {
    Stop-Job $job -Force
    throw 'git credential fill timeout'
}
$out = Receive-Job $job
Remove-Job $job -Force
$token = ($out -split "`n" | Where-Object { $_ -like 'password=*' }) -replace 'password=',''
if (-not $token) { throw 'GitHub token not available' }

$base = "https://uploads.github.com/repos/Ruslan-Bilohash/shop/releases/$ReleaseId/assets"
$zips = @(
    "shop-install-$Tag.zip",
    "shop-not-mysql-$Tag.zip"
)

foreach ($zip in $zips) {
    $path = Join-Path $root $zip
    if (-not (Test-Path $path)) {
        $folder = if ($zip -like '*not-mysql*') { 'not_mysql' } else { 'install' }
        $src = Join-Path $root $folder
        Compress-Archive -Path (Join-Path $src '*') -DestinationPath $path -CompressionLevel Fastest -Force
        Write-Host "Packed $zip"
    }
    $url = "$base`?name=$zip"
    $resp = Join-Path $root "_upload_$zip.json"
    $code = curl.exe --max-time 120 -sS -X POST `
        -H "Authorization: Bearer $token" `
        -H "Accept: application/vnd.github+json" `
        -H "Content-Type: application/zip" `
        --data-binary "@$path" `
        $url -o $resp -w "%{http_code}"
    Write-Host "$zip HTTP $code"
    if (Test-Path $resp) {
        $asset = Get-Content $resp -Raw | ConvertFrom-Json
        Write-Host "  $($asset.browser_download_url)"
    }
}