<?php

function sh_menu_settings_defaults(): array
{
    return [
        'menu_show_sale'      => true,
        'menu_show_track'     => true,
        'menu_show_solutions' => false,
        'menu_show_contact'   => true,
        'menu_show_signin'    => true,
    ];
}

function sh_menu_settings(?array $settings = null): array
{
    $settings ??= function_exists('sh_site_settings') ? sh_site_settings() : [];
    return array_merge(sh_menu_settings_defaults(), array_intersect_key($settings, sh_menu_settings_defaults()));
}

function sh_menu_settings_apply_post(array $post, array $settings): array
{
    $settings['menu_show_sale'] = !empty($post['menu_show_sale']);
    $settings['menu_show_track'] = !empty($post['menu_show_track']);
    $settings['menu_show_solutions'] = !empty($post['menu_show_solutions']);
    $settings['menu_show_contact'] = !empty($post['menu_show_contact']);
    $settings['menu_show_signin'] = !empty($post['menu_show_signin']);
    return $settings;
}