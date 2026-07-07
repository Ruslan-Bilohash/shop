<?php

/** @return array<string, array{label:string,mode:string,ext:string,description:string}> */
function sh_code_editor_files(): array
{
    return [
        'llms.txt' => [
            'label'       => 'llms.txt',
            'mode'        => 'markdown',
            'ext'         => 'txt',
            'description' => 'LLM / AI crawler context for ChatGPT, Perplexity, etc.',
        ],
        'robots.txt' => [
            'label'       => 'robots.txt',
            'mode'        => 'text/plain',
            'ext'         => 'txt',
            'description' => 'Search engine crawl rules and sitemap reference.',
        ],
        'data/custom.php' => [
            'label'       => 'data/custom.php',
            'mode'        => 'php',
            'ext'         => 'php',
            'description' => 'Optional PHP hooks — return arrays only; included if file exists.',
        ],
    ];
}

function sh_code_editor_file_id_valid(string $id): bool
{
    return array_key_exists($id, sh_code_editor_files());
}

function sh_code_editor_root(): string
{
    return dirname(__DIR__);
}

function sh_code_editor_abs_path(string $id): ?string
{
    if (!sh_code_editor_file_id_valid($id)) {
        return null;
    }
    $root = realpath(sh_code_editor_root());
    if ($root === false) {
        return null;
    }
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $id);
    $dir = dirname($path);
    if (!is_dir($dir)) {
        return null;
    }
    $realDir = realpath($dir);
    if ($realDir === false || !str_starts_with($realDir, $root)) {
        return null;
    }
    return $path;
}

function sh_code_editor_default_content(string $id): string
{
    if ($id === 'data/custom.php') {
        return <<<'PHP'
<?php
/**
 * Shop CMS — optional custom PHP (demo).
 * Return arrays only. This file is NOT executed as a page.
 */
return [
    // 'footer_note' => 'Custom demo hook placeholder.',
];
PHP;
    }
    if ($id === 'llms.txt') {
        return "# Shop CMS — llms.txt\n# https://bilohash.com/shop/\n\n> Edit this file for AI crawlers.\n";
    }
    if ($id === 'robots.txt') {
        return "User-agent: *\nAllow: /\nDisallow: /admin/\n\nSitemap: https://bilohash.com/shop/sitemap.xml\n";
    }
    return '';
}

function sh_code_editor_read(string $id): ?string
{
    $path = sh_code_editor_abs_path($id);
    if ($path === null) {
        return null;
    }
    if (!is_file($path)) {
        return sh_code_editor_default_content($id);
    }
    $content = file_get_contents($path);
    return $content === false ? null : $content;
}

function sh_code_editor_write(string $id, string $content): bool
{
    $path = sh_code_editor_abs_path($id);
    if ($path === null) {
        return false;
    }
    $dir = dirname($path);
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        return false;
    }
    if ($id === 'data/custom.php' && trim($content) !== '' && !str_contains($content, '<?php')) {
        $content = "<?php\n" . ltrim($content);
    }
    return file_put_contents($path, $content, LOCK_EX) !== false;
}