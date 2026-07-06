<?php
/**
 * Shared Bilohash ecosystem product URLs — filter by excluding current CMS slug.
 * Lang-specific labels live in each CMS lang file; this is the canonical link map.
 */
function bh_ecosystem_catalog(): array
{
    return [
        'booking' => [
            'icon' => 'calendar-check', 'short' => 'Booking',
            'url' => 'https://bilohash.com/booking/site/', 'demo' => 'https://bilohash.com/booking/',
        ],
        'auction' => [
            'icon' => 'gavel', 'short' => 'Auction',
            'url' => 'https://bilohash.com/auction/site/', 'demo' => 'https://bilohash.com/auction/',
        ],
        'shop' => [
            'icon' => 'bag-shopping', 'short' => 'Shop',
            'url' => 'https://bilohash.com/shop/site/', 'demo' => 'https://bilohash.com/shop/',
        ],
        'pizza' => [
            'icon' => 'pizza-slice', 'short' => 'Pizza',
            'url' => 'https://bilohash.com/pizza/site/', 'demo' => 'https://bilohash.com/pizza/',
        ],
        'freelance' => [
            'icon' => 'briefcase', 'short' => 'Freelance',
            'url' => 'https://bilohash.com/freelance/site/', 'demo' => 'https://bilohash.com/freelance/',
        ],
        '3d' => [
            'icon' => 'cube', 'short' => '3D',
            'url' => 'https://bilohash.com/3d/', 'demo' => 'https://bilohash.com/3d/',
        ],
        'ai' => [
            'icon' => 'robot', 'short' => 'AI',
            'url' => 'https://bilohash.com/ai/', 'demo' => 'https://bilohash.com/ai/',
        ],
        'wordpress' => [
            'icon' => 'wordpress', 'short' => 'WordPress',
            'url' => 'https://bilohash.com/wordpress/', 'demo' => 'https://bilohash.com/wordpress/',
            'plugin' => 'https://wordpress.org/plugins/bilohash-ai-chat-consultant/',
            'ai_demo' => 'https://bilohash.com/ai/wordpress/',
        ],
        'today' => [
            'icon' => 'newspaper', 'short' => 'Today',
            'url' => 'https://bilohash.com/today/', 'demo' => 'https://bilohash.com/today/',
        ],
        'gamehub' => [
            'icon' => 'gamepad', 'short' => 'GameHub',
            'url' => 'https://bilohash.com/gamehub/site/', 'demo' => 'https://bilohash.com/gamehub/',
        ],
        'tavle' => [
            'icon' => 'car', 'short' => 'Bilen CMS',
            'url' => 'https://bilohash.com/tavle/site/', 'demo' => 'https://bilohash.com/tavle/',
        ],
        'faktura' => [
            'icon' => 'file-invoice-dollar', 'short' => 'Faktura',
            'url' => 'https://bilohash.com/faktura/', 'demo' => 'https://bilohash.com/faktura/',
        ],
        'news' => [
            'icon' => 'bullhorn', 'short' => 'News',
            'url' => 'https://bilohash.com/news/', 'demo' => 'https://bilohash.com/news/',
        ],
    ];
}

function bh_ecosystem_merge_labels(array $labels, string $exclude = ''): array
{
    $catalog = bh_ecosystem_catalog();
    $items = [];
    foreach ($catalog as $slug => $meta) {
        if ($slug === $exclude) {
            continue;
        }
        $label = $labels[$slug] ?? [];
        $items[] = array_merge($meta, [
            'name' => $label['name'] ?? ucfirst($slug),
            'desc' => $label['desc'] ?? '',
        ]);
    }
    return $items;
}