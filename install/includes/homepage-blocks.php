<?php

function sh_home_block_body_decode(string $value): string
{
    if (str_starts_with($value, 'b64:')) {
        $decoded = base64_decode(substr($value, 4), true);
        return $decoded !== false ? $decoded : $value;
    }
    return $value;
}

function sh_home_block_fingerprint(array $block): string
{
    $tplId = trim((string) ($block['template_id'] ?? ''));
    if ($tplId !== '') {
        return 'tpl:' . $tplId;
    }
    $type = (string) ($block['type'] ?? '');
    if ($type !== '' && $type !== 'custom') {
        return 'type:' . $type;
    }
    $parts = [];
    foreach (['uk', 'en', 'no'] as $code) {
        $title = trim((string) (($block['title'][$code] ?? '') ?: ''));
        $body = trim((string) (($block['body'][$code] ?? '') ?: ''));
        if ($title !== '' || $body !== '') {
            $parts[] = $title . '|' . mb_substr($body, 0, 400, 'UTF-8');
        }
    }
    if ($parts === []) {
        return 'custom:' . (string) ($block['id'] ?? 'empty');
    }
    return 'custom:' . md5(implode('||', $parts));
}

/** @param list<array<string,mixed>> $blocks @return list<array<string,mixed>> */
function sh_home_blocks_dedupe(array $blocks): array
{
    $seen = [];
    $out = [];
    foreach ($blocks as $block) {
        if (!is_array($block)) {
            continue;
        }
        $fp = sh_home_block_fingerprint($block);
        if (isset($seen[$fp])) {
            continue;
        }
        $seen[$fp] = true;
        $out[] = $block;
    }
    return $out;
}

/** Detach homepage templates removed from the blocks list. */
function sh_home_blocks_reconcile_templates(array $settings, array $blocks): array
{
    if (!function_exists('sh_block_templates_from_settings')) {
        require_once __DIR__ . '/block-templates.php';
    }
    $activeTplIds = [];
    foreach ($blocks as $block) {
        $tplId = trim((string) ($block['template_id'] ?? ''));
        if ($tplId !== '') {
            $activeTplIds[$tplId] = true;
        }
    }
    $templates = sh_block_templates_from_settings($settings);
    $changed = false;
    foreach ($templates as $idx => $tpl) {
        $id = (string) ($tpl['id'] ?? '');
        if ($id === '') {
            continue;
        }
        if (($tpl['placement'] ?? '') === 'homepage' && !isset($activeTplIds[$id])) {
            $templates[$idx]['placement'] = 'none';
            $changed = true;
        }
    }
    if ($changed) {
        $settings['block_templates'] = $templates;
    }
    return $settings;
}

/** @return array<string, array{label:string,icon:string,has_limit:bool}> */
function sh_home_block_types(): array
{
    return [
        'about'     => ['label' => 'About script', 'icon' => 'code', 'has_limit' => false],
        'stats'     => ['label' => 'Stats strip', 'icon' => 'chart-simple', 'has_limit' => false],
        'featured'  => ['label' => 'Featured products', 'icon' => 'star', 'has_limit' => true],
        'categories'=> ['label' => 'Categories grid', 'icon' => 'layer-group', 'has_limit' => true],
        'new'       => ['label' => 'New arrivals', 'icon' => 'sparkles', 'has_limit' => true],
        'platform'  => ['label' => 'Platform features', 'icon' => 'cubes', 'has_limit' => false],
        'steps'     => ['label' => 'How it works', 'icon' => 'list-ol', 'has_limit' => false],
        'why'       => ['label' => 'Why choose us', 'icon' => 'thumbs-up', 'has_limit' => false],
        'faq'       => ['label' => 'FAQ', 'icon' => 'circle-question', 'has_limit' => false],
        'custom'    => ['label' => 'Custom HTML', 'icon' => 'file-code', 'has_limit' => false],
    ];
}

function sh_home_blocks_defaults(): array
{
    $sort = 1;
    $blocks = [];
    foreach (['about', 'stats', 'featured', 'categories', 'new', 'platform', 'steps', 'why', 'faq'] as $type) {
        $blocks[] = [
            'id'      => $type,
            'type'    => $type,
            'enabled' => true,
            'sort'    => $sort++,
            'limit'   => match ($type) {
                'featured'   => 6,
                'new'        => 4,
                'categories' => 6,
                default      => 0,
            },
            'title'   => [],
            'subtitle'=> [],
            'body'    => [],
        ];
    }
    return $blocks;
}

function sh_home_blocks_from_settings(?array $settings = null): array
{
    $settings ??= function_exists('sh_site_settings') ? sh_site_settings() : [];
    $raw = $settings['home_blocks'] ?? null;
    if (!is_array($raw) || $raw === []) {
        return sh_home_blocks_defaults();
    }
    $types = sh_home_block_types();
    $blocks = [];
    foreach ($raw as $row) {
        if (!is_array($row)) {
            continue;
        }
        $type = (string) ($row['type'] ?? '');
        if (!isset($types[$type])) {
            continue;
        }
        $id = trim((string) ($row['id'] ?? $type)) ?: $type;
        $limit = max(0, min(24, (int) ($row['limit'] ?? 0)));
        if ($type === 'categories' && $limit < 1) {
            $limit = 6;
        }
        $blocks[] = [
            'id'      => preg_replace('/[^a-z0-9_-]/', '', strtolower($id)) ?: $type,
            'type'    => $type,
            'enabled' => ($row['enabled'] ?? true) !== false,
            'sort'    => max(1, (int) ($row['sort'] ?? 99)),
            'limit'   => $limit,
            'title'   => is_array($row['title'] ?? null) ? $row['title'] : [],
            'subtitle'=> is_array($row['subtitle'] ?? null) ? $row['subtitle'] : [],
            'body'    => is_array($row['body'] ?? null) ? $row['body'] : [],
        ];
    }
    if ($blocks === []) {
        return sh_home_blocks_defaults();
    }
    usort($blocks, fn($a, $b) => ($a['sort'] ?? 99) <=> ($b['sort'] ?? 99));
    return sh_home_blocks_dedupe($blocks);
}

function sh_home_blocks_sorted_active(?array $settings = null): array
{
    return array_values(array_filter(
        sh_home_blocks_from_settings($settings),
        fn(array $b): bool => !empty($b['enabled'])
    ));
}

function sh_home_blocks_apply_post(array $post, array $settings): array
{
    $types = sh_home_block_types();
    $indices = $post['home_block_idx'] ?? [];
    if (!is_array($indices) || $indices === []) {
        return $settings;
    }
    $blocks = [];
    foreach ($indices as $i) {
        $i = (int) $i;
        $type = (string) ($post['home_block_type_' . $i] ?? '');
        if (!isset($types[$type])) {
            continue;
        }
        $id = trim((string) ($post['home_block_id_' . $i] ?? $type));
        $id = preg_replace('/[^a-z0-9_-]/', '', strtolower($id)) ?: $type;
        $block = [
            'id'      => $id,
            'type'    => $type,
            'enabled' => !empty($post['home_block_enabled_' . $i]),
            'sort'    => max(1, (int) ($post['home_block_sort_' . $i] ?? 99)),
            'limit'   => max(0, min(24, (int) ($post['home_block_limit_' . $i] ?? 0))),
            'title'   => [],
            'subtitle'=> [],
            'body'    => [],
        ];
        $tplId = trim((string) ($post['home_block_template_id_' . $i] ?? ''));
        if ($tplId !== '') {
            $block['template_id'] = preg_replace('/[^a-z0-9_-]/', '', strtolower($tplId));
        }
        foreach (sh_langs() as $code => $_info) {
            $block['title'][$code] = trim((string) ($post['home_block_title_' . $code . '_' . $i] ?? ''));
            $block['subtitle'][$code] = trim((string) ($post['home_block_subtitle_' . $code . '_' . $i] ?? ''));
            $block['body'][$code] = trim(sh_home_block_body_decode(trim((string) ($post['home_block_body_' . $code . '_' . $i] ?? ''))));
        }
        $blocks[] = $block;
    }
    $blocks = sh_home_blocks_dedupe($blocks);
    usort($blocks, fn($a, $b) => ($a['sort'] ?? 99) <=> ($b['sort'] ?? 99));
    $sort = 1;
    foreach ($blocks as &$b) {
        $b['sort'] = $sort++;
    }
    unset($b);
    $settings['home_blocks'] = $blocks;
    return sh_home_blocks_reconcile_templates($settings, $blocks);
}

function sh_home_block_label(array $block, string $field, string $lang, string $fallback = ''): string
{
    $map = $block[$field] ?? [];
    if (!is_array($map)) {
        return $fallback;
    }
    $val = trim((string) ($map[$lang] ?? ''));
    if ($val !== '') {
        return $val;
    }
    foreach (['uk', 'en', 'no'] as $code) {
        $val = trim((string) ($map[$code] ?? ''));
        if ($val !== '') {
            return $val;
        }
    }
    return $fallback;
}