<?php

/** @return list<array<string, mixed>> */
function sh_block_presets(): array
{
    return [
        [
            'id'    => 'contact-blue',
            'name'  => 'Contact form',
            'icon'  => 'envelope',
            'color' => '#2563eb',
            'prompt' => 'Blue contact form with name, email, message fields',
            'template' => sh_block_preset_contact('#2563eb'),
        ],
        [
            'id'    => 'cta-green',
            'name'  => 'CTA banner',
            'icon'  => 'bullhorn',
            'color' => '#059669',
            'prompt' => 'Green call-to-action banner with shop button',
            'template' => sh_block_preset_cta('#059669'),
        ],
        [
            'id'    => 'features-purple',
            'name'  => 'Features grid',
            'icon'  => 'grid-3',
            'color' => '#7c3aed',
            'prompt' => 'Purple three-column features with icons',
            'template' => sh_block_preset_features('#7c3aed'),
        ],
        [
            'id'    => 'newsletter-orange',
            'name'  => 'Newsletter',
            'icon'  => 'paper-plane',
            'color' => '#ea580c',
            'prompt' => 'Orange newsletter signup with email field',
            'template' => sh_block_preset_newsletter('#ea580c'),
        ],
        [
            'id'    => 'trust-teal',
            'name'  => 'Trust badges',
            'icon'  => 'shield-halved',
            'color' => '#0d9488',
            'prompt' => 'Teal trust badges — secure payment, fast delivery, support',
            'template' => sh_block_preset_trust('#0d9488'),
        ],
        [
            'id'    => 'support-red',
            'name'  => 'Support strip',
            'icon'  => 'headset',
            'color' => '#dc2626',
            'prompt' => 'Red support strip with phone and chat icons',
            'template' => sh_block_preset_support('#dc2626'),
        ],
    ];
}

/** @return array{title: array<string, string>, subtitle: array<string, string>, body: array<string, string>} */
function sh_block_preset_contact(string $color): array
{
    $light = sh_block_preset_tint($color, 0.92);
    $titles = ['en' => 'Get in touch', 'uk' => 'Звʼязатися з нами', 'no' => 'Kontakt oss', 'ru' => 'Связаться с нами', 'sv' => 'Kontakta oss', 'lt' => 'Susisiekite'];
    $subs = ['en' => 'We reply within one business day.', 'uk' => 'Відповідаємо протягом одного робочого дня.', 'no' => 'Vi svarer innen én virkedag.', 'ru' => 'Ответим в течение одного рабочего дня.', 'sv' => 'Vi svarar inom en arbetsdag.', 'lt' => 'Atsakome per vieną darbo dieną.'];
    $body = '<div class="sh-tpl-card" style="max-width:520px;margin:0 auto;padding:24px;border:1px solid ' . $color . '33;border-radius:12px;background:linear-gradient(180deg,' . $light . ',#fff);">'
        . '<form class="sh-tpl-form" onsubmit="return false;"><label style="display:block;font-weight:600;margin-bottom:6px;">Name</label>'
        . '<input type="text" style="width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;margin-bottom:12px;">'
        . '<label style="display:block;font-weight:600;margin-bottom:6px;">Email</label>'
        . '<input type="email" style="width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;margin-bottom:12px;">'
        . '<label style="display:block;font-weight:600;margin-bottom:6px;">Message</label>'
        . '<textarea rows="4" style="width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;margin-bottom:14px;"></textarea>'
        . '<button type="button" style="width:100%;padding:12px 16px;border:none;border-radius:8px;background:' . $color . ';color:#fff;font-weight:700;cursor:pointer;"><i class="fas fa-paper-plane"></i> Send</button></form></div>';
    return sh_block_preset_row($titles, $subs, $body);
}

/** @return array{title: array<string, string>, subtitle: array<string, string>, body: array<string, string>} */
function sh_block_preset_cta(string $color): array
{
    $titles = ['en' => 'Ready to shop?', 'uk' => 'Готові до покупок?', 'no' => 'Klar til å handle?', 'ru' => 'Готовы к покупкам?', 'sv' => 'Redo att handla?', 'lt' => 'Pasiruošę pirkti?'];
    $subs = ['en' => 'Browse the catalog and order in a few clicks.', 'uk' => 'Перегляньте каталог і замовте за кілька кліків.', 'no' => 'Utforsk katalogen og bestill på få klikk.', 'ru' => 'Смотрите каталог и заказывайте в пару кликов.', 'sv' => 'Utforska katalogen och beställ med några klick.', 'lt' => 'Peržiūrėkite katalogą ir užsisakykite keliais paspaudimais.'];
    $body = '<div style="text-align:center;padding:28px 20px;border-radius:12px;background:linear-gradient(135deg,' . $color . ',#0f172a);color:#fff;">'
        . '<p style="margin:0 0 16px;font-size:1.05rem;"><i class="fas fa-store"></i> Free shipping on orders over 999 kr</p>'
        . '<a href="#" style="display:inline-block;padding:12px 24px;border-radius:8px;background:#fff;color:' . $color . ';font-weight:700;text-decoration:none;"><i class="fas fa-arrow-right"></i> Shop now</a></div>';
    return sh_block_preset_row($titles, $subs, $body);
}

/** @return array{title: array<string, string>, subtitle: array<string, string>, body: array<string, string>} */
function sh_block_preset_features(string $color): array
{
    $titles = ['en' => 'Why choose us', 'uk' => 'Чому ми', 'no' => 'Hvorfor oss', 'ru' => 'Почему мы', 'sv' => 'Varför vi', 'lt' => 'Kodėl mes'];
    $subs = ['en' => 'Fast delivery, secure checkout, friendly support.', 'uk' => 'Швидка доставка, безпечна оплата, підтримка.', 'no' => 'Rask levering, sikker checkout, god support.', 'ru' => 'Быстрая доставка, безопасная оплата, поддержка.', 'sv' => 'Snabb leverans, säker checkout, support.', 'lt' => 'Greitas pristatymas, saugus checkout, palaikymas.'];
    $body = '<div class="sh-tpl-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:16px;">'
        . '<div style="text-align:center;padding:16px;border:1px solid ' . $color . '33;border-radius:10px;"><i class="fas fa-truck-fast" style="font-size:1.5rem;color:' . $color . ';"></i><h4 style="margin:10px 0 6px;">Fast delivery</h4><p style="margin:0;font-size:13px;color:#64748b;">2–5 business days</p></div>'
        . '<div style="text-align:center;padding:16px;border:1px solid ' . $color . '33;border-radius:10px;"><i class="fas fa-lock" style="font-size:1.5rem;color:' . $color . ';"></i><h4 style="margin:10px 0 6px;">Secure pay</h4><p style="margin:0;font-size:13px;color:#64748b;">Stripe · PayPal · Vipps</p></div>'
        . '<div style="text-align:center;padding:16px;border:1px solid ' . $color . '33;border-radius:10px;"><i class="fas fa-headset" style="font-size:1.5rem;color:' . $color . ';"></i><h4 style="margin:10px 0 6px;">Support</h4><p style="margin:0;font-size:13px;color:#64748b;">Chat & email</p></div></div>';
    return sh_block_preset_row($titles, $subs, $body);
}

/** @return array{title: array<string, string>, subtitle: array<string, string>, body: array<string, string>} */
function sh_block_preset_newsletter(string $color): array
{
    $titles = ['en' => 'Newsletter', 'uk' => 'Розсилка', 'no' => 'Nyhetsbrev', 'ru' => 'Рассылка', 'sv' => 'Nyhetsbrev', 'lt' => 'Naujienlaiškis'];
    $subs = ['en' => 'Get deals and new arrivals — no spam.', 'uk' => 'Акції та новинки — без спаму.', 'no' => 'Tilbud og nyheter — ingen spam.', 'ru' => 'Акции и новинки — без спама.', 'sv' => 'Erbjudanden och nyheter — ingen spam.', 'lt' => 'Pasiūlymai ir naujienos — be spamo.'];
    $body = '<form class="sh-newsletter-form" data-sh-subscribe style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;padding:20px;border-radius:12px;background:' . $color . '14;border:1px solid ' . $color . '44;">'
        . '<i class="fas fa-paper-plane" style="font-size:1.4rem;color:' . $color . ';"></i>'
        . '<input type="email" name="email" required placeholder="your@email.com" style="flex:1;min-width:180px;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;">'
        . '<button type="submit" style="padding:10px 18px;border:none;border-radius:8px;background:' . $color . ';color:#fff;font-weight:700;cursor:pointer;">Subscribe</button>'
        . '<p class="sh-newsletter-msg" hidden style="width:100%;margin:0;font-size:13px;"></p></form>';
    return sh_block_preset_row($titles, $subs, $body);
}

/** @return array{title: array<string, string>, subtitle: array<string, string>, body: array<string, string>} */
function sh_block_preset_trust(string $color): array
{
    $titles = ['en' => 'Shop with confidence', 'uk' => 'Купуйте з впевненістю', 'no' => 'Handle trygt', 'ru' => 'Покупайте уверенно', 'sv' => 'Handla tryggt', 'lt' => 'Pirkite užtikrintai'];
    $subs = ['en' => 'Trusted by thousands of customers.', 'uk' => 'Нам довіряють тисячі клієнтів.', 'no' => 'Betrodd av tusenvis av kunder.', 'ru' => 'Нам доверяют тысячи клиентов.', 'sv' => 'Betrodd av tusentals kunder.', 'lt' => 'Mumis pasitiki tūkstančiai klientų.'];
    $body = '<div style="display:flex;flex-wrap:wrap;gap:12px;justify-content:center;">'
        . '<span style="display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border-radius:999px;background:#fff;border:1px solid ' . $color . '44;font-weight:600;font-size:13px;"><i class="fas fa-shield-halved" style="color:' . $color . ';"></i> Secure checkout</span>'
        . '<span style="display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border-radius:999px;background:#fff;border:1px solid ' . $color . '44;font-weight:600;font-size:13px;"><i class="fas fa-truck" style="color:' . $color . ';"></i> Tracked shipping</span>'
        . '<span style="display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border-radius:999px;background:#fff;border:1px solid ' . $color . '44;font-weight:600;font-size:13px;"><i class="fas fa-rotate-left" style="color:' . $color . ';"></i> Easy returns</span></div>';
    return sh_block_preset_row($titles, $subs, $body);
}

/** @return array{title: array<string, string>, subtitle: array<string, string>, body: array<string, string>} */
function sh_block_preset_support(string $color): array
{
    $titles = ['en' => 'Need help?', 'uk' => 'Потрібна допомога?', 'no' => 'Trenger du hjelp?', 'ru' => 'Нужна помощь?', 'sv' => 'Behöver du hjälp?', 'lt' => 'Reikia pagalbos?'];
    $subs = ['en' => 'Our team is here Mon–Fri 9–17.', 'uk' => 'Команда на звʼязку Пн–Пт 9–17.', 'no' => 'Teamet er her man–fre 9–17.', 'ru' => 'Команда на связи Пн–Пт 9–17.', 'sv' => 'Teamet finns mån–fre 9–17.', 'lt' => 'Komanda pasiekiama Pr–Pn 9–17.'];
    $body = '<div style="display:flex;flex-wrap:wrap;gap:16px;align-items:center;padding:18px 20px;border-radius:12px;background:linear-gradient(90deg,' . $color . '18,#fff);border-left:4px solid ' . $color . ';">'
        . '<div style="flex:1;min-width:200px;"><strong style="color:' . $color . ';"><i class="fas fa-phone"></i> +47 000 00 000</strong><br><span style="font-size:13px;color:#64748b;">Call or chat with us</span></div>'
        . '<a href="#" style="padding:10px 16px;border-radius:8px;background:' . $color . ';color:#fff;font-weight:700;text-decoration:none;"><i class="fas fa-comments"></i> Live chat</a></div>';
    return sh_block_preset_row($titles, $subs, $body);
}

/** @param array<string, string> $titles @param array<string, string> $subs */
function sh_block_preset_row(array $titles, array $subs, string $body): array
{
    $row = ['title' => [], 'subtitle' => [], 'body' => []];
    foreach (sh_langs() as $code => $_info) {
        $row['title'][$code] = $titles[$code] ?? $titles['en'] ?? '';
        $row['subtitle'][$code] = $subs[$code] ?? $subs['en'] ?? '';
        $row['body'][$code] = $body;
    }
    return $row;
}

function sh_block_preset_tint(string $hex, float $mix = 0.9): string
{
    $hex = ltrim($hex, '#');
    if (strlen($hex) !== 6) {
        return '#eff6ff';
    }
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    $r = (int) round($r + (255 - $r) * $mix);
    $g = (int) round($g + (255 - $g) * $mix);
    $b = (int) round($b + (255 - $b) * $mix);
    return sprintf('#%02x%02x%02x', $r, $g, $b);
}

/** Replace primary colour in preset HTML bodies. */
function sh_block_preset_apply_color(array $template, string $color): array
{
    $color = preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? $color : '#2563eb';
    $light = sh_block_preset_tint($color, 0.92);
    foreach ($template['body'] ?? [] as $code => $html) {
        $template['body'][$code] = preg_replace(
            '/#(?:2563eb|059669|7c3aed|ea580c|0d9488|dc2626)\b/i',
            $color,
            (string) $html
        ) ?? (string) $html;
        $template['body'][$code] = str_replace(
            ['#eff6ff', '#ecfdf5', '#f5f3ff', '#fff7ed', '#f0fdfa', '#fef2f2'],
            $light,
            $template['body'][$code]
        );
    }
    return $template;
}