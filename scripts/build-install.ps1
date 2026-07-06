# Shop CMS — sync development tree into commercial install/ package
# Usage: powershell -File scripts/build-install.ps1

$ErrorActionPreference = 'Stop'
$root = Split-Path $PSScriptRoot -Parent
$dest = Join-Path $root 'install'

$exclude = @(
    'install', '.git', 'scripts', 'screenshot', 'data\*.json'
)

$dirs = @(
    'admin', 'api', 'assets', 'includes', 'lang', 'site', 'uploads'
)

$files = @(
    'index.php', 'init.php', 'config.php', 'cart.php', 'checkout.php',
    'contact.php', 'login.php', 'logout.php', 'news.php', 'news-article.php',
    'page.php', 'product.php', 'search.php', 'solutions.php', 'vertical.php',
    'track.php', 'track-np.php', 'mobile-app.php', '404.php', '_health.php',
    'sitemap.php', 'sitemap-index.php', 'sitemap-pages.php', 'sitemap-products.php',
    'sitemap-categories.php', 'sitemap-news.php', 'sitemap-verticals.php',
    'robots.txt', 'llms.txt', 'license', 'changelog.md',
    'readme.md', 'readme-uk.md', 'readme-no.md', 'readme-sv.md', 'readme-lt.md'
)

Write-Host "Shop CMS build-install: $root -> $dest"

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

# Standalone path fixes inside install package
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

# Ecosystem deps (standalone, no parent bilohash/)
$ecoSrc = 'C:\bilohash\includes'
$ecoDst = Join-Path $dest 'includes'
foreach ($f in @('bh-cms-site-settings.php','cms-contact.php','ecosystem-i18n.php','ecosystem-defs.php','bh-cms-links.php')) {
    $p = Join-Path $ecoSrc $f
    if (Test-Path $p) { Copy-Item $p $ecoDst -Force }
}

# install.php and schema stay in install/ — do not overwrite from root
Write-Host 'Done. Package ready at install/'