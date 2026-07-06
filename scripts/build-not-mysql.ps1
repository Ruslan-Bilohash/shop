# Shop CMS — sync development tree into not_mysql/ JSON package
# Usage: powershell -File scripts/build-not-mysql.ps1

$ErrorActionPreference = 'Stop'
$root = Split-Path $PSScriptRoot -Parent
$dest = Join-Path $root 'not_mysql'

$dirs = @('admin', 'api', 'assets', 'lang', 'site', 'uploads')

$files = @(
    'index.php', 'init.php', 'config.php', 'cart.php', 'checkout.php', 'contact.php', 'login.php', 'logout.php',
    'news.php', 'news-article.php', 'page.php', 'product.php', 'search.php', 'solutions.php',
    'vertical.php', 'track.php', 'track-np.php', 'mobile-app.php', '404.php', '_health.php',
    'sitemap.php', 'sitemap-index.php', 'sitemap-pages.php', 'sitemap-products.php',
    'sitemap-categories.php', 'sitemap-news.php', 'sitemap-verticals.php',
    'robots.txt', 'llms.txt', 'license', 'changelog.md',
    'readme.md', 'readme-uk.md', 'readme-no.md', 'readme-sv.md', 'readme-lt.md',
    'LICENSE.txt'
)

$jsonIncludes = @(
    'json-store.php', 'storage.php', 'category-storage.php', 'news-storage.php',
    'leads-storage.php', 'customer-profile.php', 'payment-settings.php', 'shop-mode.php'
)

Write-Host "Shop CMS build-not-mysql: $root -> $dest"

foreach ($d in $dirs) {
    $srcDir = Join-Path $root $d
    if (-not (Test-Path $srcDir)) { continue }
    $dstDir = Join-Path $dest $d
    if (-not (Test-Path $dstDir)) { New-Item -ItemType Directory -Path $dstDir -Force | Out-Null }
    Copy-Item -Path (Join-Path $srcDir '*') -Destination $dstDir -Recurse -Force
}

foreach ($f in $files) {
    $src = Join-Path $root $f
    if (Test-Path $src) {
        Copy-Item $src (Join-Path $dest $f) -Force
    }
}

# Shared includes (except JSON-specific storage layer)
$includeSrc = Join-Path $root 'includes'
$includeDst = Join-Path $dest 'includes'
Get-ChildItem $includeSrc -Filter '*.php' | ForEach-Object {
    if ($jsonIncludes -contains $_.Name) { return }
    if ($_.Name -eq 'database.php') { return }
    Copy-Item $_.FullName $includeDst -Force
}

foreach ($jf in $jsonIncludes) {
    $src = Join-Path $includeDst $jf
    if (-not (Test-Path $src)) {
        $alt = Join-Path $root "includes/$jf"
        if (Test-Path $alt) { Copy-Item $alt $includeDst -Force }
    }
}

# JSON init (no MySQL redirect)
@'
<?php
require_once __DIR__ . '/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/includes/storage.php';
require_once __DIR__ . '/includes/i18n.php';
$shAuthFile = __DIR__ . '/includes/customer-auth.php';
if (is_file($shAuthFile)) {
    require_once $shAuthFile;
}
$shModeFile = __DIR__ . '/includes/shop-mode.php';
if (is_file($shModeFile)) {
    require_once $shModeFile;
}
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/seo.php';
require_once __DIR__ . '/includes/site-settings.php';
require_once __DIR__ . '/includes/version.php';
if (function_exists('sh_boot_dev_errors')) {
    sh_boot_dev_errors();
}
sh_bootstrap_data();
if (function_exists('sh_shop_maybe_maintenance')) {
    sh_shop_maybe_maintenance();
}
'@ | Out-File (Join-Path $dest 'init.php') -Encoding utf8NoBOM

# Standalone path fixes
$fixDirs = @(
    (Join-Path $dest 'admin\api'),
    (Join-Path $dest 'admin\includes')
)
foreach ($dir in $fixDirs) {
    if (-not (Test-Path $dir)) { continue }
    Get-ChildItem $dir -Filter '*.php' | ForEach-Object {
        $c = [IO.File]::ReadAllText($_.FullName)
        $n = $c -replace "dirname\(__DIR__\) \. '/init\.php'", "dirname(__DIR__, 2) . '/init.php'"
        $n = $n -replace "dirname\(__DIR__\) \. '/includes/", "dirname(__DIR__, 2) . '/includes/"
        $n = $n -replace "dirname\(__DIR__\) \. '/lang/", "dirname(__DIR__, 2) . '/lang/"
        if ($n -ne $c) { [IO.File]::WriteAllText($_.FullName, $n) }
    }
}

$dbFile = Join-Path $dest 'includes\database.php'
if (Test-Path $dbFile) { Remove-Item $dbFile -Force }

Write-Host 'Done. JSON package ready at not_mysql/'