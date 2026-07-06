<?php
require_once __DIR__ . '/init.php';
require_once dirname(__DIR__) . '/includes/news-storage.php';
require_once dirname(__DIR__) . '/includes/payment-settings.php';
require_once dirname(__DIR__) . '/includes/ai.php';
require_once __DIR__ . '/includes/seo-parse.php';
require_once __DIR__ . '/includes/toggle-field.php';
require_once __DIR__ . '/includes/rich-editor.php';
sh_admin_require();

$admin_page = 'news';
$tp = $ta['news_page'] ?? [];
$slug = trim($_GET['slug'] ?? '');
$is_new = $slug === '';
$record = $is_new ? null : sh_news_by_slug($slug, false);
$edit_tab = trim($_GET['tab'] ?? 'general');
if (!in_array($edit_tab, ['general', 'seo'], true)) {
    $edit_tab = 'general';
}
if ($is_new && $edit_tab === 'seo') {
    $edit_tab = 'general';
}

if (!$is_new && $record === null) {
    header('Location: ' . sh_admin_url('news.php'));
    exit;
}

$page_title = $is_new
    ? ($tp['add'] ?? 'Add article')
    : ($tp['edit'] ?? 'Edit article');

$flash = '';
$errors = [];
$aiSettings = sh_ai_settings(sh_load_settings());
$aiSourceLang = (string) ($aiSettings['ai_source_lang'] ?? 'en');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_slug = trim($_POST['slug'] ?? '');
    $orig_slug = trim($_POST['orig_slug'] ?? '');

    if (!sh_news_slug_valid($post_slug)) {
        $errors[] = $tp['slug_invalid'] ?? 'Slug must be 2–49 lowercase letters, numbers, hyphen or underscore.';
    }

    if ($is_new && sh_news_by_slug($post_slug, false) !== null) {
        $errors[] = $tp['slug_exists'] ?? 'This slug already exists.';
    }

    $names = [];
    $excerpts = [];
    $bodies = [];
    foreach (sh_langs() as $code => $_info) {
        $names[$code] = trim($_POST['name_' . $code] ?? '');
        $excerpts[$code] = trim($_POST['excerpt_' . $code] ?? '');
        $bodies[$code] = trim($_POST['body_' . $code] ?? '');
        if (!$is_new && $record !== null) {
            if ($names[$code] === '') {
                $names[$code] = trim((string) ($record['name'][$code] ?? ''));
            }
            if ($excerpts[$code] === '') {
                $excerpts[$code] = trim((string) ($record['excerpt'][$code] ?? ''));
            }
            if ($bodies[$code] === '') {
                $bodies[$code] = trim((string) ($record['body'][$code] ?? ''));
            }
        }
    }

    $defaultLang = sh_site_default_lang();
    $hasTitle = trim($names[$defaultLang] ?? '') !== ''
        || trim($names['en'] ?? '') !== ''
        || trim($names['no'] ?? '') !== '';
    if (!$hasTitle) {
        $errors[] = $tp['names_required'] ?? 'Enter at least one article title (default or English).';
    }

    $publishedAt = trim($_POST['published_at'] ?? '');
    if ($publishedAt !== '') {
        $ts = strtotime($publishedAt);
        if ($ts !== false) {
            $publishedAt = gmdate('Y-m-d\TH:i:s\Z', $ts);
        }
    }

    if ($errors === []) {
        if (!$is_new && $orig_slug !== '' && $orig_slug !== $post_slug) {
            sh_news_delete($orig_slug);
        }

        $ok = sh_news_upsert([
            'slug'         => $post_slug,
            'active'       => !empty($_POST['active']),
            'featured'     => !empty($_POST['featured']),
            'published_at' => $publishedAt,
            'image'        => trim($_POST['image'] ?? ''),
            'name'         => sh_news_normalize_localized($names),
            'excerpt'      => sh_news_normalize_localized($excerpts),
            'body'         => $bodies,
            'seo'          => sh_admin_parse_seo_post($_POST, 'news'),
        ]);

        if ($ok) {
            $_SESSION['sh_admin_flash'] = ['type' => 'success', 'msg' => $tp['saved'] ?? 'Article saved.'];
            $redirectTab = trim($_POST['return_tab'] ?? 'general');
            $url = sh_admin_url('news-edit.php?slug=' . urlencode($post_slug));
            if ($redirectTab === 'seo') {
                $url .= '&tab=seo';
            }
            header('Location: ' . $url);
            exit;
        }
        $errors[] = $tp['save_error'] ?? 'Could not save article.';
    }

    $record = [
        'slug'         => $post_slug,
        'active'       => !empty($_POST['active']),
        'featured'     => !empty($_POST['featured']),
        'published_at' => $publishedAt,
        'image'        => trim($_POST['image'] ?? ''),
        'name'         => $names,
        'excerpt'      => $excerpts,
        'body'         => $bodies,
        'seo'          => sh_admin_parse_seo_post($_POST, 'news'),
    ];
    $flash = 'error';
}

if (is_array($record)) {
    if (isset($record['name']) && is_array($record['name'])) {
        $record['name'] = sh_news_normalize_localized($record['name']);
    }
    if (isset($record['excerpt']) && is_array($record['excerpt'])) {
        $record['excerpt'] = sh_news_normalize_localized($record['excerpt']);
    }
}

$seo_record = $record ?? ['seo' => []];
$seo_ctx = 'news';
$seo_tp = $ta['seo_editor'] ?? [];
$seo_panel_mode = $edit_tab === 'seo';

$edit_tabs = [
    'general' => $tp['tab_general'] ?? 'General',
    'seo'     => $tp['tab_seo'] ?? 'SEO & Schema',
];
$edit_tab_base_url = sh_admin_url('news-edit.php' . ($is_new ? '' : '?slug=' . urlencode($slug)));

$publishedLocal = '';
if (is_array($record) && !empty($record['published_at'])) {
    $ts = strtotime((string) $record['published_at']);
    if ($ts !== false) {
        $publishedLocal = date('Y-m-d\TH:i', $ts);
    }
}

$admin_extra_js = [
    sh_asset('js/admin-service-pages.js') . '?v=1',
    sh_asset('js/admin-news.js') . '?v=1',
];

require __DIR__ . '/includes/layout.php';
?>

<?php if ($flash === 'error' && $errors !== []): ?>
<div class="adm-alert adm-alert-error">
    <i class="fas fa-exclamation-circle"></i>
    <?= htmlspecialchars(implode(' ', $errors)) ?>
</div>
<?php endif; ?>

<?php if (!$is_new): ?>
<?php require __DIR__ . '/includes/edit-tabs.php'; ?>
<?php endif; ?>

<form method="post" class="adm-settings-form" id="shNewsForm"
      data-ai-url="<?= htmlspecialchars(sh_admin_url('api/ai-news.php')) ?>"
      data-ai-source-lang="<?= htmlspecialchars($aiSourceLang) ?>">
    <input type="hidden" name="orig_slug" value="<?= htmlspecialchars($record['slug'] ?? $slug) ?>">
    <input type="hidden" name="return_tab" value="<?= htmlspecialchars($edit_tab) ?>">

    <div class="adm-edit-panel <?= $edit_tab === 'general' ? 'is-active' : '' ?>" data-panel="general">
        <div class="adm-card">
            <div class="adm-card-head adm-card-head--stack">
                <h2><?= htmlspecialchars($page_title) ?></h2>
                <a href="<?= sh_admin_url('news.php') ?>" class="adm-btn adm-btn-outline adm-btn-sm">
                    <i class="fas fa-arrow-left"></i> <?= htmlspecialchars($tp['back'] ?? 'Back') ?>
                </a>
            </div>
            <div class="adm-card-body padded">
                <div class="adm-form-grid">
                    <div class="adm-field">
                        <label><?= htmlspecialchars($tp['slug'] ?? 'Slug') ?> *</label>
                        <input type="text" name="slug" value="<?= htmlspecialchars($record['slug'] ?? '') ?>"
                               pattern="[a-z][a-z0-9_-]{1,48}" <?= $is_new ? '' : 'readonly' ?>
                               placeholder="shop-cms-v1-3-5-release" required>
                        <small class="adm-field-hint"><?= htmlspecialchars($tp['slug_hint'] ?? 'Used in URLs. Lowercase only.') ?></small>
                    </div>
                    <div class="adm-field">
                        <label><?= htmlspecialchars($tp['published'] ?? 'Published at') ?></label>
                        <input type="datetime-local" name="published_at" value="<?= htmlspecialchars($publishedLocal) ?>">
                    </div>
                    <div class="adm-field adm-field--wide">
                        <label><?= htmlspecialchars($tp['image'] ?? 'Cover image URL') ?></label>
                        <input type="url" name="image" value="<?= htmlspecialchars($record['image'] ?? '') ?>" placeholder="https://…" inputmode="url">
                    </div>
                </div>

                <?php
                sh_admin_toggle_section(
                    $tp['status_section'] ?? 'Status',
                    [
                        ['name' => 'active', 'label' => $tp['active'] ?? 'Active', 'checked' => ($record['active'] ?? true) !== false],
                        ['name' => 'featured', 'label' => $tp['featured'] ?? 'Featured', 'checked' => !empty($record['featured'])],
                    ],
                    'eye'
                );
                ?>
            </div>
        </div>

        <div class="adm-card">
            <div class="adm-card-head adm-card-head--stack">
                <h2><i class="fas fa-wand-magic-sparkles"></i> <?= htmlspecialchars($tp['ai_section'] ?? 'AI assistant') ?></h2>
            </div>
            <div class="adm-card-body padded">
                <p class="adm-help"><?= htmlspecialchars($tp['ai_help'] ?? 'Enter a headline and optional brief — AI fills title, excerpt, body and SEO for all languages.') ?></p>
                <div class="adm-form-grid">
                    <div class="adm-field adm-field--wide">
                        <label><?= htmlspecialchars($tp['ai_title_label'] ?? 'Article title for AI') ?> *</label>
                        <input type="text" id="shAiNewsTitle" placeholder="<?= htmlspecialchars($tp['ai_title_ph'] ?? 'e.g. Shop CMS v1.3.5 release notes') ?>">
                    </div>
                    <div class="adm-field adm-field--wide">
                        <label><?= htmlspecialchars($tp['ai_brief_label'] ?? 'Brief / angle (optional)') ?></label>
                        <input type="text" id="shAiNewsBrief" placeholder="<?= htmlspecialchars($tp['ai_brief_ph'] ?? 'e.g. Nova Poshta, SEO analysis, product editor') ?>">
                    </div>
                </div>
                <div class="adm-ai-toolbar adm-ai-toolbar--news">
                    <button type="button" class="adm-btn adm-btn-primary adm-btn-ai-generate" id="shAiNewsGenerate"
                            data-need-title="<?= htmlspecialchars($tp['ai_need_title'] ?? 'Enter article title first.') ?>"
                            data-loading="<?= htmlspecialchars($tp['ai_generating'] ?? 'Generating full article…') ?>"
                            data-ok="<?= htmlspecialchars($tp['ai_ok'] ?? 'Article generated for all languages.') ?>"
                            data-demo-ok="<?= htmlspecialchars($tp['ai_demo_ok'] ?? 'Demo templates applied.') ?>">
                        <span class="adm-btn-ai-icon"><i class="fas fa-wand-magic-sparkles"></i></span>
                        <span class="adm-btn-ai-label"><?= htmlspecialchars($tp['ai_generate'] ?? 'Generate with AI') ?></span>
                        <span class="adm-btn-ai-spinner" hidden aria-hidden="true"></span>
                    </button>
                    <span id="shAiNewsStatus" class="adm-ai-status" hidden></span>
                </div>
            </div>
        </div>

        <div class="adm-card">
            <div class="adm-card-head"><h2><?= htmlspecialchars($tp['content_title'] ?? 'Content') ?> (<?= count(sh_langs()) ?>)</h2></div>
            <div class="adm-card-body padded">
                <?php foreach (sh_langs() as $code => $info): ?>
                <details class="adm-spoiler adm-spoiler-nested" <?= $code === $aiSourceLang ? 'open' : '' ?>>
                    <summary><?= htmlspecialchars($info['name']) ?> (<?= htmlspecialchars($info['label']) ?>)</summary>
                    <div class="adm-spoiler-body">
                        <div class="adm-form-grid">
                            <div class="adm-field adm-field--wide">
                                <label><?= htmlspecialchars($tp['title'] ?? 'Title') ?></label>
                                <input type="text" name="name_<?= htmlspecialchars($code) ?>"
                                       value="<?= htmlspecialchars($record['name'][$code] ?? '') ?>">
                            </div>
                            <div class="adm-field adm-field--wide">
                                <label><?= htmlspecialchars($tp['excerpt'] ?? 'Excerpt') ?></label>
                                <textarea name="excerpt_<?= htmlspecialchars($code) ?>" rows="2"><?= htmlspecialchars($record['excerpt'][$code] ?? '') ?></textarea>
                            </div>
                            <div class="adm-field adm-field--wide">
                                <label><?= htmlspecialchars($tp['body'] ?? 'Body') ?></label>
                                <?php sh_admin_rich_editor(
                                    'body_' . $code,
                                    (string) ($record['body'][$code] ?? ''),
                                    'shNewsBody' . $code,
                                    $ta
                                ); ?>
                            </div>
                        </div>
                    </div>
                </details>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php if (!$is_new): ?>
    <div class="adm-edit-panel <?= $edit_tab === 'seo' ? 'is-active' : '' ?>" data-panel="seo">
        <div class="adm-card">
            <div class="adm-card-head">
                <h2><i class="fas fa-chart-line"></i> <?= htmlspecialchars($tp['tab_seo'] ?? 'SEO & Schema') ?></h2>
            </div>
            <?php $seo_panel_mode = true; require __DIR__ . '/includes/seo-spoiler.php'; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="adm-alert adm-alert-info">
        <i class="fas fa-info-circle"></i> <?= htmlspecialchars($tp['seo_after_save'] ?? 'Save the article first, then open the SEO & Schema tab.') ?>
    </div>
    <?php endif; ?>

    <div class="adm-form-actions adm-form-actions-sticky">
        <button type="submit" class="adm-btn adm-btn-primary"><i class="fas fa-save"></i> <?= htmlspecialchars($tp['save'] ?? 'Save') ?></button>
        <a href="<?= sh_admin_url('news.php') ?>" class="adm-btn adm-btn-outline"><?= htmlspecialchars($tp['cancel'] ?? 'Cancel') ?></a>
    </div>
</form>

<?php require __DIR__ . '/includes/layout-end.php'; ?>