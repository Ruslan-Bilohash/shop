<?php

function sh_admin_parse_seo_post(array $post, string $ctx): array
{
    $meta_title = [];
    $meta_description = [];
    $meta_keywords = [];
    $intro = [];

    foreach (sh_langs() as $code => $_info) {
        $meta_title[$code] = trim($post['seo_meta_title_' . $code] ?? '');
        $meta_description[$code] = trim($post['seo_meta_description_' . $code] ?? '');
        $meta_keywords[$code] = trim($post['seo_meta_keywords_' . $code] ?? '');
        if ($ctx === 'category') {
            $intro[$code] = trim($post['seo_intro_' . $code] ?? '');
        }
    }

    $seo = [
        'meta_title'       => $meta_title,
        'meta_description' => $meta_description,
        'meta_keywords'    => $meta_keywords,
        'og_image'         => trim($post['seo_og_image'] ?? ''),
        'canonical_override' => trim($post['seo_canonical_override'] ?? ''),
        'schema'           => [],
    ];

    if ($ctx === 'product') {
        $seo['brand'] = trim($post['seo_brand'] ?? '');
        $seo['gtin'] = trim($post['seo_gtin'] ?? '');
        $seo['mpn'] = trim($post['seo_mpn'] ?? '');
        $seo['rating_value'] = trim($post['seo_rating_value'] ?? '');
        $seo['rating_count'] = max(0, (int)($post['seo_rating_count'] ?? 0));
        $seo['schema'] = [
            'product'           => !empty($post['seo_schema_product']),
            'offer'             => !empty($post['seo_schema_offer']),
            'breadcrumb'        => !empty($post['seo_schema_breadcrumb']),
            'aggregate_rating'  => !empty($post['seo_schema_aggregate_rating']),
        ];
    } else {
        $seo['intro'] = $intro;
        $seo['schema'] = [
            'collection'  => !empty($post['seo_schema_collection']),
            'itemlist'    => !empty($post['seo_schema_itemlist']),
            'breadcrumb'  => !empty($post['seo_schema_breadcrumb']),
        ];
    }

    return $seo;
}