<?php
/**
 * Storefront design theme presets for admin design demos.
 */

/** @return list<array{id:string,title:string,desc:string,class:string,tags:list<string>}> */
function sh_design_themes(?array $labels = null): array
{
    $labels = is_array($labels) ? $labels : [];
    $t = static fn(string $key, string $fallback): string => (string) ($labels[$key] ?? $fallback);

    return [
        [
            'id'    => 'nordic',
            'title' => $t('demo_nordic_title', 'Nordic minimal'),
            'desc'  => $t('demo_nordic_desc', 'Clean whites, blue accent, airy product grid.'),
            'class' => 'adm-dd--nordic',
            'tags'  => ['minimal', 'light', 'blue', 'scandinavian', 'clean'],
        ],
        [
            'id'    => 'dark',
            'title' => $t('demo_dark_title', 'Dark premium'),
            'desc'  => $t('demo_dark_desc', 'Charcoal background, gold CTA, luxury feel.'),
            'class' => 'adm-dd--dark',
            'tags'  => ['dark', 'premium', 'gold', 'luxury', 'night'],
        ],
        [
            'id'    => 'fresh',
            'title' => $t('demo_fresh_title', 'Fresh market'),
            'desc'  => $t('demo_fresh_desc', 'Green accents, rounded cards, organic typography.'),
            'class' => 'adm-dd--fresh',
            'tags'  => ['green', 'organic', 'market', 'rounded', 'eco'],
        ],
        [
            'id'    => 'bold',
            'title' => $t('demo_bold_title', 'Bold sale'),
            'desc'  => $t('demo_bold_desc', 'High contrast reds, urgency banners, promo blocks.'),
            'class' => 'adm-dd--bold',
            'tags'  => ['sale', 'red', 'promo', 'urgency', 'contrast'],
        ],
        [
            'id'    => 'ocean',
            'title' => $t('demo_ocean_title', 'Ocean breeze'),
            'desc'  => $t('demo_ocean_desc', 'Teal gradients, wave shapes, travel & lifestyle mood.'),
            'class' => 'adm-dd--ocean',
            'tags'  => ['teal', 'ocean', 'travel', 'gradient', 'blue'],
        ],
        [
            'id'    => 'coral',
            'title' => $t('demo_coral_title', 'Coral boutique'),
            'desc'  => $t('demo_coral_desc', 'Warm peach tones, soft shadows, fashion boutique.'),
            'class' => 'adm-dd--coral',
            'tags'  => ['coral', 'fashion', 'warm', 'boutique', 'peach'],
        ],
        [
            'id'    => 'retro',
            'title' => $t('demo_retro_title', 'Retro vinyl'),
            'desc'  => $t('demo_retro_desc', 'Muted cream, serif headlines, vintage record-shop vibe.'),
            'class' => 'adm-dd--retro',
            'tags'  => ['retro', 'vintage', 'serif', 'cream', 'music'],
        ],
        [
            'id'    => 'neon',
            'title' => $t('demo_neon_title', 'Neon tech'),
            'desc'  => $t('demo_neon_desc', 'Dark UI with cyan neon edges — gadgets & gaming.'),
            'class' => 'adm-dd--neon',
            'tags'  => ['neon', 'gaming', 'tech', 'cyan', 'dark'],
        ],
    ];
}

function sh_design_theme_by_id(string $id, ?array $labels = null): ?array
{
    foreach (sh_design_themes($labels) as $theme) {
        if ($theme['id'] === $id) {
            return $theme;
        }
    }
    return null;
}