<?php

/** 12 print layouts + 4 page formats. */
function sh_inv_print_designs(): array
{
    return [
        'classic-blue' => ['accent' => '#1e40af', 'name' => ['en' => 'Classic Blue', 'uk' => 'Класичний синій', 'no' => 'Klassisk blå', 'ru' => 'Классический синий', 'sv' => 'Klassisk blå']],
        'minimal-mono' => ['accent' => '#18181b', 'name' => ['en' => 'Minimal Mono', 'uk' => 'Мінімалізм', 'no' => 'Minimal mono', 'ru' => 'Минимализм', 'sv' => 'Minimal mono']],
        'nordic-light' => ['accent' => '#475569', 'name' => ['en' => 'Nordic Light', 'uk' => 'Північний світлий', 'no' => 'Nordisk lys', 'ru' => 'Северный светлый', 'sv' => 'Nordisk ljus']],
        'corporate-navy' => ['accent' => '#0f172a', 'name' => ['en' => 'Corporate Navy', 'uk' => 'Корпоративний navy', 'no' => 'Corporate marine', 'ru' => 'Корпоративный navy', 'sv' => 'Corporate marin']],
        'teal-fresh' => ['accent' => '#0d9488', 'name' => ['en' => 'Teal Fresh', 'uk' => 'Бірюзовий', 'no' => 'Teal frisk', 'ru' => 'Бирюзовый', 'sv' => 'Teal fräsch']],
        'forest-green' => ['accent' => '#166534', 'name' => ['en' => 'Forest Green', 'uk' => 'Лісовий зелений', 'no' => 'Skoggrønn', 'ru' => 'Лесной зелёный', 'sv' => 'Skogsgrön']],
        'wine-elegant' => ['accent' => '#881337', 'name' => ['en' => 'Wine Elegant', 'uk' => 'Винний елегант', 'no' => 'Vin elegant', 'ru' => 'Винный элегант', 'sv' => 'Vin elegant']],
        'sunset-orange' => ['accent' => '#c2410c', 'name' => ['en' => 'Sunset Orange', 'uk' => 'Помаранчевий захід', 'no' => 'Solnedgang oransje', 'ru' => 'Оранжевый закат', 'sv' => 'Solnedgång orange']],
        'violet-creative' => ['accent' => '#6d28d9', 'name' => ['en' => 'Violet Creative', 'uk' => 'Фіолетовий креатив', 'no' => 'Fiolett kreativ', 'ru' => 'Фиолетовый креатив', 'sv' => 'Violett kreativ']],
        'slate-pro' => ['accent' => '#334155', 'name' => ['en' => 'Slate Pro', 'uk' => 'Сланцевий pro', 'no' => 'Skifer pro', 'ru' => 'Сланцевый pro', 'sv' => 'Skiffer pro']],
        'stripe-classic' => ['accent' => '#1d4ed8', 'name' => ['en' => 'Stripe Classic', 'uk' => 'Смугастий класик', 'no' => 'Stripe klassisk', 'ru' => 'Полосатый классик', 'sv' => 'Rand klassisk']],
        'formal-border' => ['accent' => '#1e3a5f', 'name' => ['en' => 'Formal Border', 'uk' => 'Офіційна рамка', 'no' => 'Formell ramme', 'ru' => 'Официальная рамка', 'sv' => 'Formell ram']],
    ];
}

function sh_inv_print_formats(): array
{
    return [
        'a4' => ['css' => 'A4', 'width' => '210mm', 'name' => ['en' => 'A4 (210 × 297 mm)', 'uk' => 'A4 (210 × 297 мм)', 'no' => 'A4 (210 × 297 mm)', 'ru' => 'A4 (210 × 297 мм)', 'sv' => 'A4 (210 × 297 mm)']],
        'a5' => ['css' => 'A5', 'width' => '148mm', 'name' => ['en' => 'A5 (148 × 210 mm)', 'uk' => 'A5 (148 × 210 мм)', 'no' => 'A5 (148 × 210 mm)', 'ru' => 'A5 (148 × 210 мм)', 'sv' => 'A5 (148 × 210 mm)']],
        'letter' => ['css' => 'letter', 'width' => '8.5in', 'name' => ['en' => 'US Letter (8.5 × 11 in)', 'uk' => 'US Letter (8.5 × 11 дюймів)', 'no' => 'US Letter', 'ru' => 'US Letter', 'sv' => 'US Letter']],
        'legal' => ['css' => 'legal', 'width' => '8.5in', 'name' => ['en' => 'US Legal (8.5 × 14 in)', 'uk' => 'US Legal (8.5 × 14 дюймів)', 'no' => 'US Legal', 'ru' => 'US Legal', 'sv' => 'US Legal']],
    ];
}

function sh_inv_print_margins(): array
{
    return [
        '5mm'  => ['en' => 'Narrow (5 mm)', 'uk' => 'Вузькі (5 мм)', 'no' => 'Smal (5 mm)', 'ru' => 'Узкие (5 мм)', 'sv' => 'Smal (5 mm)'],
        '8mm'  => ['en' => 'Standard (8 mm)', 'uk' => 'Стандарт (8 мм)', 'no' => 'Standard (8 mm)', 'ru' => 'Стандарт (8 мм)', 'sv' => 'Standard (8 mm)'],
        '12mm' => ['en' => 'Wide (12 mm)', 'uk' => 'Широкі (12 мм)', 'no' => 'Bred (12 mm)', 'ru' => 'Широкие (12 мм)', 'sv' => 'Bred (12 mm)'],
        '15mm' => ['en' => 'Extra wide (15 mm)', 'uk' => 'Дуже широкі (15 мм)', 'no' => 'Ekstra bred (15 mm)', 'ru' => 'Очень широкие (15 мм)', 'sv' => 'Extra bred (15 mm)'],
    ];
}

function sh_inv_normalize_print_design(?string $id): string
{
    return isset(sh_inv_print_designs()[$id]) ? $id : 'classic-blue';
}

function sh_inv_normalize_print_format(?string $id): string
{
    return isset(sh_inv_print_formats()[$id]) ? $id : 'a4';
}

function sh_inv_localized_name(array $meta, string $lang): string
{
    $lang = strtolower($lang);
    if ($lang === 'ua') {
        $lang = 'uk';
    }
    $names = $meta['name'] ?? [];
    return (string) ($names[$lang] ?? $names['en'] ?? '');
}

function sh_inv_print_design_name(string $id, string $lang): string
{
    $designs = sh_inv_print_designs();
    return sh_inv_localized_name($designs[sh_inv_normalize_print_design($id)] ?? [], $lang);
}

function sh_inv_print_format_name(string $id, string $lang): string
{
    $formats = sh_inv_print_formats();
    return sh_inv_localized_name($formats[sh_inv_normalize_print_format($id)] ?? [], $lang);
}

function sh_inv_print_classes(string $design, string $format): string
{
    return 'sh-inv-sheet sh-inv-design-' . sh_inv_normalize_print_design($design)
        . ' sh-inv-fmt-' . sh_inv_normalize_print_format($format);
}

function sh_inv_print_format_css(string $format): string
{
    $formats = sh_inv_print_formats();
    return $formats[sh_inv_normalize_print_format($format)]['css'] ?? 'A4';
}

function sh_inv_render_design_picker(string $fieldName, string $selected, string $lang): void
{
    $selected = sh_inv_normalize_print_design($selected);
    echo '<div class="sh-inv-design-picker" role="radiogroup">';
    foreach (sh_inv_print_designs() as $id => $meta) {
        $sel = $id === $selected;
        $label = sh_inv_localized_name($meta, $lang);
        $cls = 'sh-inv-design-' . $id;
        echo '<label class="sh-inv-design-option' . ($sel ? ' selected' : '') . '">';
        echo '<input type="radio" name="' . htmlspecialchars($fieldName, ENT_QUOTES) . '" value="' . htmlspecialchars($id, ENT_QUOTES) . '"' . ($sel ? ' checked' : '') . '>';
        echo '<div class="sh-inv-design-thumb ' . htmlspecialchars($cls, ENT_QUOTES) . '"><div class="sh-inv-design-thumb-inner">';
        echo '<div class="sh-inv-design-thumb-bar"></div>';
        echo '<div class="sh-inv-design-thumb-line"></div><div class="sh-inv-design-thumb-line short"></div>';
        echo '</div></div><span class="sh-inv-design-label">' . htmlspecialchars($label) . '</span></label>';
    }
    echo '</div>';
}