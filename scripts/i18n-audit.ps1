# Audit lang/*.php completeness vs en.php - target 100% for no, uk, ru, sv, lt.
# Usage: powershell -File scripts/i18n-audit.ps1 [-FailUnder 100]

param([int]$FailUnder = 100)

$ErrorActionPreference = 'Stop'
$root = Split-Path $PSScriptRoot -Parent
$dirs = @('lang', 'site/lang')
$langs = @('no', 'uk', 'ru', 'sv', 'lt')
$report = @()
$hasFail = $false

function Get-PhpLeafKeys([string]$Content) {
    $Content = $Content -replace '^\xEF\xBB\xBF', ''
    $keys = [System.Collections.Generic.List[string]]::new()
    $stack = [System.Collections.Generic.List[string]]::new()
    foreach ($rawLine in ($Content -split "`r?`n")) {
        $line = $rawLine.Trim()
        if ($line -eq '' -or $line.StartsWith('//')) { continue }
        $keyOnLine = $null
        $opensInline = $false
        if ($line -match "['`"]([^'`"]+)['`"]\s*=>") {
            $keyOnLine = $Matches[1]
            $after = $line.Substring($line.IndexOf('=>') + 2).Trim()
            if ($after.StartsWith('[')) {
                $stack.Add($keyOnLine) | Out-Null
                $opensInline = $true
            } elseif ($after -notmatch '^\s*require\b' -and $after -notmatch '^\s*array_replace') {
                $path = if ($stack.Count -gt 0) { ($stack.ToArray() -join '.') + '.' + $keyOnLine } else { $keyOnLine }
                $keys.Add($path) | Out-Null
            }
        }
        $closes = ([regex]::Matches($line, '\]')).Count
        if ($opensInline) { $closes = [Math]::Max(0, $closes - 1) }
        for ($i = 0; $i -lt $closes; $i++) {
            if ($stack.Count -gt 0) { $stack.RemoveAt($stack.Count - 1) }
        }
    }
    return @($keys | Select-Object -Unique)
}

function Get-AllKeys($path) {
    if (-not (Test-Path $path)) { return @() }
    $c = [IO.File]::ReadAllText($path)
    $keys = Get-PhpLeafKeys $c
    $enPath = Join-Path (Split-Path $path) 'en.php'
    $inheritsEn = ($c -match "require\s+__DIR__\s*\.\s*'/en\.php'") -or ($c -match '\$en\s*=\s*require\s+__DIR__')
    if ($inheritsEn -and (Test-Path $enPath)) {
        $keys = @($keys + (Get-PhpLeafKeys ([IO.File]::ReadAllText($enPath)))) | Select-Object -Unique
    }
    return $keys
}

foreach ($d in $dirs) {
    $dir = Join-Path $root $d
    $en = Join-Path $dir 'en.php'
    if (-not (Test-Path $en)) { continue }
    $baseKeys = Get-AllKeys $en
    Write-Host ""
    Write-Host "=== $d (base=$($baseKeys.Count)) ==="
    foreach ($lang in $langs) {
        $f = Join-Path $dir "$lang.php"
        if (-not (Test-Path $f)) {
            Write-Host "  $lang : MISSING FILE"
            $hasFail = $true
            continue
        }
        $lk = Get-AllKeys $f
        $missing = @($baseKeys | Where-Object { $_ -notin $lk })
        $pct = [math]::Round((($baseKeys.Count - $missing.Count) / [math]::Max(1, $baseKeys.Count)) * 100, 1)
        if ($pct -ge $FailUnder) {
            $status = 'OK'
        } else {
            $status = 'FAIL'
            $hasFail = $true
        }
        Write-Host "  $lang : $pct% ($status) - $($missing.Count) missing"
        if ($missing.Count -gt 0 -and $missing.Count -le 15) {
            Write-Host "    $($missing -join ', ')"
        } elseif ($missing.Count -gt 15) {
            Write-Host "    first 15: $($missing[0..14] -join ', ')"
        }
        $report += [PSCustomObject]@{ Dir = $d; Lang = $lang; Pct = $pct; Missing = $missing.Count }
    }
}

$outJson = Join-Path $root 'scripts/i18n-audit-last.json'
$report | ConvertTo-Json -Depth 3 | Set-Content $outJson -Encoding UTF8
Write-Host ""
Write-Host "Report: $outJson"
if ($hasFail) { exit 1 }