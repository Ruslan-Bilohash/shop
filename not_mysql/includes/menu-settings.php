<?php

function sh_menu_settings_defaults(): array
{
    return [
        'menu_show_signin'    => true,
        'menu_show_admin'     => false,
        'header_nav_links'    => sh_header_nav_links_defaults(),
    ];
}

/** @return list<array<string, mixed>> */
function sh_header_nav_links_defaults(): array
{
    return [
        [
            'id'       => 'sale',
            'url'      => 'search.php?sale=1',
            'external' => false,
            'active'   => true,
            'label'    => [],
        ],
        [
            'id'       => 'contact',
            'url'      => 'contact.php',
            'external' => false,
            'active'   => true,
            'label'    => [],
        ],
        [
            'id'       => 'track',
            'url'      => 'track.php',
            'external' => false,
            'active'   => true,
            'label'    => [],
        ],
        [
            'id'       => 'solutions',
            'url'      => 'solutions.php',
            'external' => false,
            'active'   => false,
            'label'    => [],
        ],
    ];
}

function sh_menu_settings(?array $settings = null): array
{
    $settings ??= function_exists('sh_site_settings') ? sh_site_settings() : [];
    $merged = array_merge(sh_menu_settings_defaults(), array_intersect_key($settings, sh_menu_settings_defaults()));
    $merged['header_nav_links'] = sh_header_nav_links($settings);
    return $merged;
}

/** @return list<array<string, mixed>> */
function sh_header_nav_links(?array $settings = null): array
{
    if ($settings === null) {
        $settings = function_exists('sh_site_settings') ? sh_site_settings() : [];
    }

    $raw = $settings['header_nav_links'] ?? null;
    if (is_array($raw) && $raw !== []) {
        $out = [];
        foreach ($raw as $link) {
            if (!is_array($link)) {
                continue;
            }
            $url = trim((string) ($link['url'] ?? ''));
            if ($url === '') {
                continue;
            }
            $out[] = [
                'id'       => trim((string) ($link['id'] ?? '')) ?: ('nav-' . count($out)),
                'url'      => $url,
                'external' => !empty($link['external']),
                'active'   => ($link['active'] ?? true) !== false,
                'label'    => is_array($link['label'] ?? null) ? $link['label'] : [],
            ];
        }
        if ($out !== []) {
            return $out;
        }
    }

    $defaults = sh_header_nav_links_defaults();
    $legacy = [
        'sale'      => 'menu_show_sale',
        'contact'   => 'menu_show_contact',
        'track'     => 'menu_show_track',
        'solutions' => 'menu_show_solutions',
    ];
    foreach ($defaults as &$link) {
        $key = $legacy[$link['id']] ?? null;
        if ($key !== null && array_key_exists($key, $settings)) {
            $link['active'] = !empty($settings[$key]);
        }
    }
    unset($link);

    return $defaults;
}

function sh_header_nav_link_href(array $link): string
{
    $url = trim($link['url'] ?? '');
    if ($url === '') {
        return sh_url('index.php');
    }
    if (!empty($link['external']) || str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
        return $url;
    }
    return sh_url(ltrim($url, '/'));
}

function sh_header_nav_link_label(array $link, string $lang): string
{
    $labels = $link['label'] ?? [];
    if (is_array($labels)) {
        foreach ([$lang, 'uk', 'en', 'no', 'ru', 'sv', 'lt'] as $code) {
            $val = trim((string) ($labels[$code] ?? ''));
            if ($val !== '') {
                return $val;
            }
        }
    }

    global $t;
    $id = (string) ($link['id'] ?? '');
    $fallbacks = [
        'sale'      => $t['nav']['sale'] ?? 'Sale',
        'contact'   => $t['nav']['help'] ?? $t['nav']['contact'] ?? 'Contact',
        'track'     => $t['nav']['track'] ?? 'Track parcel',
        'solutions' => $t['footer']['solutions'] ?? 'Solutions',
    ];

    return $fallbacks[$id] ?? ($id !== '' ? ucfirst(str_replace(['-', '_'], ' ', $id)) : 'Link');
}

function sh_header_nav_link_active(array $link, string $currentPage): bool
{
    $url = strtolower(trim((string) ($link['url'] ?? '')));
    if ($url === '') {
        return false;
    }
    $map = [
        'contact.php'  => 'contact',
        'track.php'    => 'track',
        'solutions.php'=> 'solutions',
    ];
    $basename = basename(parse_url($url, PHP_URL_PATH) ?: $url);
    if (isset($map[$basename])) {
        return $currentPage === $map[$basename];
    }
    if (str_contains($url, 'sale=1') && $currentPage === 'search') {
        return !empty($_GET['sale']);
    }
    return false;
}

function sh_header_nav_links_apply_post(array $post, array $settings): array
{
    $rows = [];
    $indices = $post['header_nav_idx'] ?? [];
    if (!is_array($indices)) {
        $indices = [];
    }
    foreach ($indices as $i) {
        $i = (int) $i;
        $labels = [];
        foreach (sh_langs() as $code => $_info) {
            $labels[$code] = trim($post['header_nav_label_' . $code . '_' . $i] ?? '');
        }
        $url = trim($post['header_nav_url_' . $i] ?? '');
        if ($url === '' && $labels['en'] === '' && ($labels['no'] ?? '') === '' && ($labels['uk'] ?? '') === '') {
            continue;
        }
        $rows[] = [
            'id'       => trim($post['header_nav_id_' . $i] ?? '') ?: ('nav-' . $i),
            'url'      => $url,
            'external' => !empty($post['header_nav_external_' . $i]),
            'active'   => !empty($post['header_nav_active_' . $i]),
            'label'    => $labels,
        ];
    }
    if ($rows !== []) {
        $settings['header_nav_links'] = $rows;
    }

    $settings['menu_show_signin'] = !empty($post['menu_show_signin']);
    $settings['menu_show_admin'] = !empty($post['menu_show_admin']);

    return $settings;
}

function sh_menu_settings_apply_post(array $post, array $settings): array
{
    return sh_header_nav_links_apply_post($post, $settings);
}