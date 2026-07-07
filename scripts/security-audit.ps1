# Quick security & PHP error scan for Shop CMS.
# Usage: powershell -File scripts/security-audit.ps1

$ErrorActionPreference = 'Continue'
$root = Split-Path $PSScriptRoot -Parent
$issues = [System.Collections.Generic.List[string]]::new()

function Add-Issue($msg) { $issues.Add($msg) | Out-Null; Write-Host "ISSUE: $msg" }

# 1. PHP syntax
Get-ChildItem $root -Recurse -Filter '*.php' -File |
    Where-Object { $_.FullName -notmatch '\\vendor\\|\\node_modules\\' } |
    ForEach-Object {
        $out = php -l $_.FullName 2>&1
        if ($LASTEXITCODE -ne 0) { Add-Issue "Syntax: $($_.FullName) — $out" }
    }

# 2. Secrets in repo
$secretPatterns = @(
    'password\s*=\s*[''"][^''"]{4,}',
    'api[_-]?key\s*=\s*[''"][^''"]{8,}',
    'sk_live_',
    'BEGIN (RSA |OPENSSH )?PRIVATE KEY'
)
Get-ChildItem $root -Recurse -Include *.php,*.json,*.env,*.md -File |
    Where-Object { $_.Name -notmatch 'example|\.sample|README' } |
    ForEach-Object {
        $c = Get-Content $_.FullName -Raw -ErrorAction SilentlyContinue
        if (-not $c) { return }
        foreach ($pat in $secretPatterns) {
            if ($c -match $pat) { Add-Issue "Possible secret in $($_.FullName) (pattern: $pat)" }
        }
    }

# 3. Dangerous patterns
$danger = @(
    @{ Pattern = 'eval\s*\('; Label = 'eval()' },
    @{ Pattern = 'shell_exec\s*\('; Label = 'shell_exec()' },
    @{ Pattern = 'passthru\s*\('; Label = 'passthru()' },
    @{ Pattern = 'unserialize\s*\(\s*\$_'; Label = 'unserialize from request' },
    @{ Pattern = '\$_(GET|POST|REQUEST)\s*\[[^\]]+\]\s*\)\s*;'; Label = 'dynamic include from request' }
)
Get-ChildItem $root -Recurse -Filter '*.php' -File |
    Where-Object { $_.FullName -notmatch '\\vendor\\' } |
    ForEach-Object {
        $lines = Get-Content $_.FullName
        for ($i = 0; $i -lt $lines.Count; $i++) {
            foreach ($d in $danger) {
                if ($lines[$i] -match $d.Pattern) {
                    Add-Issue "$($d.Label) in $($_.FullName):$($i+1)"
                }
            }
        }
    }

# 4. Writable / exposed config
@(
    'data/db.config.php',
    'data/admin.config.php',
    'includes/mail-config.php'
) | ForEach-Object {
    $p = Join-Path $root $_
    if (Test-Path $p) {
        $ht = Join-Path (Split-Path $p) '.htaccess'
        if (-not (Test-Path $ht)) { Add-Issue "No .htaccess near sensitive file: $_" }
    }
}

# 5. Duplicate function declarations (common fatal)
$funcMap = @{}
Get-ChildItem (Join-Path $root 'includes') -Filter '*.php' -File -ErrorAction SilentlyContinue | ForEach-Object {
    $matches = [regex]::Matches((Get-Content $_.FullName -Raw), 'function\s+(sh_[a-z0-9_]+)\s*\(')
    foreach ($m in $matches) {
        $fn = $m.Groups[1].Value
        if ($funcMap.ContainsKey($fn)) {
            Add-Issue "Duplicate function $fn in $($funcMap[$fn]) and $($_.Name)"
        } else {
            $funcMap[$fn] = $_.Name
        }
    }
}

Write-Host "`nTotal issues: $($issues.Count)"
$out = Join-Path $root 'scripts/security-audit-last.txt'
$issues | Set-Content $out -Encoding UTF8
if ($issues.Count -gt 0) { exit 1 }