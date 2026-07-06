<?php

/** @return list<array<string, mixed>> */
function sh_block_templates_from_settings(?array $settings = null): array
{
    $settings ??= function_exists('sh_site_settings') ? sh_site_settings() : [];
    $raw = $settings['block_templates'] ?? [];
    if (!is_array($raw)) {
        return [];
    }
    $out = [];
    foreach ($raw as $row) {
        if (!is_array($row)) {
            continue;
        }
        $id = trim((string) ($row['id'] ?? ''));
        if ($id === '') {
            continue;
        }
        $id = preg_replace('/[^a-z0-9_-]/', '', strtolower($id)) ?: $id;
        $placement = (string) ($row['placement'] ?? 'none');
        if (!in_array($placement, ['none', 'homepage', 'page'], true)) {
            $placement = 'none';
        }
        $out[] = [
            'id'        => $id,
            'name'      => trim((string) ($row['name'] ?? 'Block')),
            'prompt'    => trim((string) ($row['prompt'] ?? '')),
            'enabled'   => ($row['enabled'] ?? true) !== false,
            'placement' => $placement,
            'page_slug' => trim((string) ($row['page_slug'] ?? '')),
            'title'     => is_array($row['title'] ?? null) ? $row['title'] : [],
            'subtitle'  => is_array($row['subtitle'] ?? null) ? $row['subtitle'] : [],
            'body'      => is_array($row['body'] ?? null) ? $row['body'] : [],
            'updated'   => trim((string) ($row['updated'] ?? '')),
        ];
    }
    return $out;
}

function sh_block_template_by_id(string $id, ?array $settings = null): ?array
{
    $id = preg_replace('/[^a-z0-9_-]/', '', strtolower(trim($id)));
    foreach (sh_block_templates_from_settings($settings) as $tpl) {
        if (($tpl['id'] ?? '') === $id) {
            return $tpl;
        }
    }
    return null;
}

function sh_block_template_label(array $tpl, string $field, string $lang, string $fallback = ''): string
{
    $map = $tpl[$field] ?? [];
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

function sh_block_template_decode_field(string $value): string
{
    if (str_starts_with($value, 'b64:')) {
        $decoded = base64_decode(substr($value, 4), true);
        return $decoded !== false ? $decoded : $value;
    }
    return $value;
}

function sh_block_templates_apply_post(array $post, array $settings): array
{
    require_once __DIR__ . '/homepage-blocks.php';
    $deleteIds = $post['delete_tpl_ids'] ?? [];
    if (!is_array($deleteIds)) {
        $deleteIds = $deleteIds !== '' && $deleteIds !== null ? [(string) $deleteIds] : [];
    }
    $deleteIds = array_values(array_filter(array_map(
        static fn($id): string => preg_replace('/[^a-z0-9_-]/', '', strtolower(trim((string) $id))),
        $deleteIds
    )));

    $ids = $post['tpl_id'] ?? [];
    if (!is_array($ids)) {
        $ids = ($ids !== '' && $ids !== null) ? [(string) $ids] : [];
    }
    $templates = [];
    foreach ($ids as $i) {
        $i = (int) $i;
        if (!empty($post['tpl_delete_' . $i])) {
            continue;
        }
        $id = trim((string) ($post['tpl_id_val_' . $i] ?? ''));
        $id = preg_replace('/[^a-z0-9_-]/', '', strtolower($id));
        if ($id === '' || in_array($id, $deleteIds, true)) {
            continue;
        }
        $placement = (string) ($post['tpl_placement_' . $i] ?? 'none');
        if (!in_array($placement, ['none', 'homepage', 'page'], true)) {
            $placement = 'none';
        }
        $tpl = [
            'id'        => $id,
            'name'      => trim((string) ($post['tpl_name_' . $i] ?? 'Block')),
            'prompt'    => trim((string) ($post['tpl_prompt_' . $i] ?? '')),
            'enabled'   => !empty($post['tpl_enabled_' . $i]),
            'placement' => $placement,
            'page_slug' => trim((string) ($post['tpl_page_slug_' . $i] ?? '')),
            'title'     => [],
            'subtitle'  => [],
            'body'      => [],
            'updated'   => gmdate('Y-m-d\TH:i:s\Z'),
        ];
        foreach (sh_langs() as $code => $_info) {
            $tpl['title'][$code] = trim((string) ($post['tpl_title_' . $code . '_' . $i] ?? ''));
            $tpl['subtitle'][$code] = trim((string) ($post['tpl_subtitle_' . $code . '_' . $i] ?? ''));
            $tpl['body'][$code] = sh_block_template_decode_field(trim((string) ($post['tpl_body_' . $code . '_' . $i] ?? '')));
        }
        $templates[] = $tpl;
    }
    $newName = trim((string) ($post['new_tpl_name'] ?? ''));
    $newPrompt = trim((string) ($post['new_tpl_prompt'] ?? ''));
    $newBodies = [];
    foreach (sh_langs() as $code => $_info) {
        $newBodies[$code] = sh_block_template_decode_field(trim((string) ($post['new_tpl_body_' . $code] ?? '')));
    }
    $hasNewBody = false;
    foreach ($newBodies as $body) {
        if ($body !== '') {
            $hasNewBody = true;
            break;
        }
    }
    $hasNew = $newName !== '' || $newPrompt !== '' || $hasNewBody;
    if ($hasNew) {
        $newId = 'tpl' . substr((string) time(), -8);
        $placement = (string) ($post['new_tpl_placement'] ?? 'none');
        if (!in_array($placement, ['none', 'homepage', 'page'], true)) {
            $placement = 'none';
        }
        $newTpl = [
            'id'        => $newId,
            'name'      => $newName !== '' ? $newName : ('Block ' . date('Y-m-d H:i')),
            'prompt'    => $newPrompt,
            'enabled'   => !empty($post['new_tpl_enabled']),
            'placement' => $placement,
            'page_slug' => trim((string) ($post['new_tpl_page_slug'] ?? '')),
            'title'     => [],
            'subtitle'  => [],
            'body'      => [],
            'updated'   => gmdate('Y-m-d\TH:i:s\Z'),
        ];
        foreach (sh_langs() as $code => $_info) {
            $newTpl['title'][$code] = trim((string) ($post['new_tpl_title_' . $code] ?? ''));
            $newTpl['subtitle'][$code] = trim((string) ($post['new_tpl_subtitle_' . $code] ?? ''));
            $newTpl['body'][$code] = $newBodies[$code];
        }
        $templates[] = $newTpl;
    }

    $templates = array_values(array_filter(
        $templates,
        static fn(array $tpl): bool => !in_array((string) ($tpl['id'] ?? ''), $deleteIds, true)
    ));

    $settings['block_templates'] = $templates;
    return sh_block_templates_sync_placements($settings);
}

function sh_block_templates_sync_placements(array $settings): array
{
    require_once __DIR__ . '/homepage-blocks.php';
    $templates = sh_block_templates_from_settings($settings);
    $blocks = sh_home_blocks_from_settings($settings);
    $tplById = [];
    foreach ($templates as $tpl) {
        $tplById[$tpl['id']] = $tpl;
    }

    $linkedHome = [];
    foreach ($blocks as $idx => $block) {
        $tplId = (string) ($block['template_id'] ?? '');
        if ($tplId !== '' && !isset($tplById[$tplId])) {
            unset($blocks[$idx]);
            continue;
        }
        if ($tplId === '') {
            continue;
        }
        $tpl = $tplById[$tplId];
        if (($tpl['placement'] ?? '') !== 'homepage' || empty($tpl['enabled'])) {
            unset($blocks[$idx]);
            continue;
        }
        $blocks[$idx]['type'] = 'custom';
        $blocks[$idx]['enabled'] = true;
        $blocks[$idx]['title'] = $tpl['title'];
        $blocks[$idx]['subtitle'] = $tpl['subtitle'];
        $blocks[$idx]['body'] = $tpl['body'];
        $linkedHome[$tplId] = true;
    }
    $blocks = array_values($blocks);

    $sort = max(1, count($blocks));
    foreach ($templates as $tpl) {
        if (($tpl['placement'] ?? '') !== 'homepage' || empty($tpl['enabled'])) {
            continue;
        }
        if (!empty($linkedHome[$tpl['id']])) {
            continue;
        }
        $blocks[] = [
            'id'          => 'tpl_' . $tpl['id'],
            'type'        => 'custom',
            'enabled'     => true,
            'sort'        => ++$sort,
            'limit'       => 0,
            'template_id' => $tpl['id'],
            'title'       => $tpl['title'],
            'subtitle'    => $tpl['subtitle'],
            'body'        => $tpl['body'],
        ];
    }

    usort($blocks, fn($a, $b) => ($a['sort'] ?? 99) <=> ($b['sort'] ?? 99));
    $n = 1;
    foreach ($blocks as &$b) {
        $b['sort'] = $n++;
    }
    unset($b);
    $settings['home_blocks'] = $blocks;
    return $settings;
}

/** @return list<array<string, mixed>> */
function sh_block_templates_for_page(string $slug, ?array $settings = null): array
{
    $slug = trim($slug);
    if ($slug === '') {
        return [];
    }
    return array_values(array_filter(
        sh_block_templates_from_settings($settings),
        static fn(array $t): bool => !empty($t['enabled'])
            && ($t['placement'] ?? '') === 'page'
            && ($t['page_slug'] ?? '') === $slug
    ));
}

function sh_render_block_template(array $tpl, string $lang): void
{
    $title = sh_block_template_label($tpl, 'title', $lang, '');
    $subtitle = sh_block_template_label($tpl, 'subtitle', $lang, '');
    $body = sh_block_template_label($tpl, 'body', $lang, '');
    if ($title === '' && $subtitle === '' && $body === '') {
        return;
    }
    echo '<section class="sh-container sh-section-tight sh-block-template sh-block-template--' . htmlspecialchars($tpl['id']) . '">';
    if ($title !== '') {
        echo '<h2 class="sh-section-title">' . htmlspecialchars($title) . '</h2>';
    }
    if ($subtitle !== '') {
        echo '<p class="sh-section-sub">' . htmlspecialchars($subtitle) . '</p>';
    }
    if ($body !== '') {
        echo '<div class="sh-block-template-body">' . $body . '</div>';
    }
    echo '</section>';
}

function sh_render_page_block_templates(string $slug, ?array $settings = null): void
{
    global $lang;
    foreach (sh_block_templates_for_page($slug, $settings) as $tpl) {
        sh_render_block_template($tpl, $lang);
    }
}