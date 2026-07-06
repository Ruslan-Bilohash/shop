<?php
/**
 * Shop CMS — 3D product presets for homepage viewers.
 */
declare(strict_types=1);

/** @return array{preset:string,color:string,model:?string} */
function sh_product_3d_config(array $product): array
{
    $id = (string) ($product['id'] ?? '');
    $model = trim((string) ($product['model_3d'] ?? $product['model3d'] ?? ''));
    $modelUrl = $model !== '' ? sh_product_3d_model_url($model) : null;

    $map = [
        'wireless-headphones-pro' => ['preset' => 'headphones', 'color' => '#2563eb'],
        'smartwatch-fitness'      => ['preset' => 'watch', 'color' => '#0f766e'],
        'merino-wool-sweater'     => ['preset' => 'apparel', 'color' => '#64748b'],
        'leather-crossbody-bag'   => ['preset' => 'bag', 'color' => '#92400e'],
        'ceramic-coffee-set'      => ['preset' => 'mug', 'color' => '#b45309'],
        'yoga-mat-premium'        => ['preset' => 'mat', 'color' => '#7c3aed'],
    ];

    if (isset($map[$id])) {
        return ['preset' => $map[$id]['preset'], 'color' => $map[$id]['color'], 'model' => $modelUrl];
    }

    $cat = (string) ($product['category'] ?? '');
    $preset = 'default';
    if (str_contains($cat, 'electronic') || str_contains($cat, 'smart')) {
        $preset = 'watch';
    } elseif (str_contains($cat, 'fashion') || str_contains($cat, 'cloth')) {
        $preset = 'apparel';
    } elseif (str_contains($cat, 'home') || str_contains($cat, 'kitchen')) {
        $preset = 'mug';
    } elseif (str_contains($cat, 'sport')) {
        $preset = 'mat';
    }

    $hue = crc32($id) % 360;
    $color = sprintf('hsl(%d, 62%%, 48%%)', $hue);

    return ['preset' => $preset, 'color' => $color, 'model' => $modelUrl];
}

function sh_product_3d_model_url(string $path): string
{
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    return sh_url(ltrim($path, '/'));
}