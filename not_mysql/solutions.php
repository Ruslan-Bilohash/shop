<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/includes/vertical-lib.php';

if (!function_exists('bh_str_sub')) {
    function bh_str_sub(string $str, int $start, ?int $length = null): string
    {
        if (function_exists('mb_substr')) {
            return $length === null ? mb_substr($str, $start) : mb_substr($str, $start, $length);
        }
        return $length === null ? substr($str, $start) : substr($str, $start, $length);
    }
}

$current_page = 'solutions';
$all = sh_verticals_all();

$langCopy = [
    'page_title' => [
        'no' => 'Nettbutikkstyringssystem Norge | Shop CMS',
        'en' => 'Online Store Management System | Shop CMS',
        'uk' => 'Система управління інтернет-магазином | Shop CMS',
        'ru' => 'Система управления интернет-магазином | Shop CMS',
    ],
    'page_desc' => [
        'no' => 'PHP nettbutikk for motebutikker, elektronikk, hjem & interiør, sport, skjønnhet, mat, B2B engros og markedsplass. Flerspråklig SEO og live demo.',
        'en' => 'PHP online store for fashion boutiques, electronics, home & interior, sports, beauty, food, B2B wholesale and marketplace. Multilingual SEO and live demo.',
        'uk' => 'PHP інтернет-магазин для крамниць моди, електроніки, дому, спорту, краси, їжі, B2B опту та маркетплейсу. Багатомовне SEO та live demo.',
        'ru' => 'PHP интернет-магазин для бутиков моды, электроники, дома, спорта, красоты, еды, B2B опта и маркетплейса. Многоязычное SEO и live demo.',
    ],
    'breadcrumb' => [
        'no' => 'Løsninger',
        'en' => 'Solutions',
        'uk' => 'Рішення',
        'ru' => 'Решения',
    ],
    'hub_h1' => [
        'no' => 'Nettbutikkløsninger for Norge & Europa',
        'en' => 'E-commerce solutions for Norway & Europe',
        'uk' => 'Рішення для інтернет-магазину в Норвегії та Європі',
        'ru' => 'Решения для интернет-магазина в Норвегии и Европе',
    ],
    'hub_sub' => [
        'no' => 'Velg din bransje — hver side er SEO-optimalisert på norsk, engelsk, ukrainsk og russisk.',
        'en' => 'Choose your industry — each page is SEO-optimized in Norwegian, English, Ukrainian and Russian.',
        'uk' => 'Оберіть нішу — кожна сторінка оптимізована для SEO чотирма мовами.',
        'ru' => 'Выберите нишу — каждая страница оптимизирована для SEO на четырёх языках.',
    ],
];

$page_title = $langCopy['page_title'][$lang] ?? $langCopy['page_title']['en'];
$page_desc  = $langCopy['page_desc'][$lang] ?? $langCopy['page_desc']['en'];
$canonical  = $site_url . '/solutions.php' . ($lang !== 'no' ? '?lang=' . $lang : '');
$canon_abs  = sh_absolute_url($canonical);
$seo_schemas = [
    sh_seo_organization(),
    sh_seo_webpage($canon_abs, $page_title, $page_desc),
    sh_seo_breadcrumbs([
        ['name' => $t['breadcrumb_home'], 'url' => sh_absolute_url(sh_url('index.php'))],
        ['name' => $langCopy['breadcrumb'][$lang] ?? $langCopy['breadcrumb']['en'], 'url' => $canon_abs],
    ]),
    sh_seo_software_app($canon_abs, $page_desc),
];
require __DIR__ . '/includes/header.php';
$hub_h1 = $langCopy['hub_h1'][$lang] ?? $langCopy['hub_h1']['en'];
$hub_sub = $langCopy['hub_sub'][$lang] ?? $langCopy['hub_sub']['en'];
?>

<div class="sh-container sh-solutions-hub">
    <h1 class="sh-page-title"><?= htmlspecialchars($hub_h1) ?></h1>
    <p class="sh-results-meta"><?= htmlspecialchars($hub_sub) ?></p>
    <div class="sh-vertical-links sh-solutions-grid">
        <?php
        $vdefs = sh_vertical_defs();
        foreach ($all as $slug => $item):
            $lv = sh_vertical_lang($item, $lang);
            $short = $vdefs[$slug][$lang] ?? $vdefs[$slug]['en'] ?? ($lv['h1'] ?? $slug);
        ?>
        <a href="<?= htmlspecialchars(sh_vertical_url($slug)) ?>" class="sh-vertical-link-card">
            <i class="fas fa-<?= htmlspecialchars($item['icon'] ?? 'store') ?>"></i>
            <strong><?= htmlspecialchars($short) ?></strong>
            <span><?= htmlspecialchars(bh_str_sub($lv['subtitle'] ?? '', 0, 90)) ?>…</span>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>