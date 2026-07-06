<?php
require_once __DIR__ . '/init.php';
require_once dirname(__DIR__) . '/includes/news-storage.php';
require_once dirname(__DIR__) . '/includes/seo-checklist.php';
sh_admin_require();

$seoLabels = array_merge(
    $ta['products_page']['seo_checklist'] ?? [],
    [
        'schema' => $ta['news_page']['seo_schema_label'] ?? 'NewsArticle schema',
        'schema_hint' => $ta['news_page']['seo_schema_hint'] ?? '',
    ]
);

$admin_page = 'news';
$tp = $ta['news_page'] ?? [];
$page_title = $ta['news'] ?? 'News';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_slug'])) {
    $slug = trim($_POST['delete_slug'] ?? '');
    if ($slug !== '' && sh_news_delete($slug)) {
        $_SESSION['sh_admin_flash'] = ['type' => 'success', 'msg' => $tp['deleted'] ?? 'Article deleted.'];
    } else {
        $_SESSION['sh_admin_flash'] = ['type' => 'error', 'msg' => $tp['delete_error'] ?? 'Could not delete article.'];
    }
    header('Location: ' . sh_admin_url('news.php'));
    exit;
}

$articles = sh_news_load();
usort($articles, static fn(array $a, array $b): int => strcmp(
    (string) ($b['published_at'] ?? ''),
    (string) ($a['published_at'] ?? '')
));

require __DIR__ . '/includes/layout.php';
?>

<?php
require_once __DIR__ . '/includes/admin-field-help.php';
$newsIntro = sh_admin_settings_intro('news_page', $ta);
if ($newsIntro !== ''): ?>
<div class="adm-alert adm-alert-info">
    <i class="fas fa-info-circle"></i> <?= htmlspecialchars($newsIntro) ?>
</div>
<?php endif; ?>
<?php sh_admin_render_settings_guide('news_page', $ta); ?>

<div class="adm-card">
    <div class="adm-card-head">
        <h2><?= htmlspecialchars($tp['list_title'] ?? 'All articles') ?></h2>
        <a href="<?= sh_admin_url('news-edit.php') ?>" class="adm-btn adm-btn-primary adm-btn-sm">
            <i class="fas fa-plus"></i> <?= htmlspecialchars($tp['add'] ?? 'Add article') ?>
        </a>
    </div>
    <div class="adm-card-body">
        <div class="adm-table-wrap">
        <table class="adm-table adm-table--cards">
            <thead>
                <tr>
                    <th><?= htmlspecialchars($tp['article'] ?? 'Article') ?></th>
                    <th><?= htmlspecialchars($tp['published'] ?? 'Published') ?></th>
                    <th><?= htmlspecialchars($tp['col_seo'] ?? 'SEO') ?></th>
                    <th><?= htmlspecialchars($tp['status'] ?? 'Status') ?></th>
                    <th><?= htmlspecialchars($tp['actions'] ?? 'Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($articles as $article):
                    $slug = (string) ($article['slug'] ?? $article['id'] ?? '');
                    $active = ($article['active'] ?? true) !== false;
                    $seoReport = sh_news_seo_checklist($article, $seoLabels);
                ?>
                <tr>
                    <td data-label="<?= htmlspecialchars($tp['article'] ?? 'Article') ?>">
                        <div class="adm-product-cell">
                            <img src="<?= htmlspecialchars(sh_news_image($article)) ?>" alt="" loading="lazy" width="40" height="40"
                                 onerror="this.onerror=null;this.src='<?= htmlspecialchars(sh_placeholder_image()) ?>';">
                            <div>
                                <a href="<?= htmlspecialchars(sh_url('news-article.php?slug=' . urlencode($slug))) ?>" class="adm-product-name-link" target="_blank" rel="noopener">
                                    <strong><?= htmlspecialchars(sh_localized($article, 'name', $lang)) ?></strong>
                                </a><br>
                                <code class="adm-muted-inline"><?= htmlspecialchars($slug) ?></code>
                                <?php if (!empty($article['featured'])): ?>
                                <span class="adm-badge adm-badge--gold"><i class="fas fa-star"></i></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td data-label="<?= htmlspecialchars($tp['published'] ?? 'Published') ?>">
                        <?= htmlspecialchars(sh_news_published_label($article, $lang)) ?>
                    </td>
                    <td data-label="<?= htmlspecialchars($tp['col_seo'] ?? 'SEO') ?>">
                        <?php sh_admin_render_seo_score_pill((int) $seoReport['score'], $seoReport['grade']); ?>
                    </td>
                    <td data-label="<?= htmlspecialchars($tp['status'] ?? 'Status') ?>">
                        <span class="adm-badge <?= $active ? 'adm-badge--green' : 'adm-badge--muted' ?>">
                            <?= htmlspecialchars($active ? ($tp['active'] ?? 'Active') : ($tp['inactive'] ?? 'Inactive')) ?>
                        </span>
                    </td>
                    <td data-label="<?= htmlspecialchars($tp['actions'] ?? 'Actions') ?>">
                        <div class="adm-actions-row">
                            <a href="<?= sh_admin_url('news-edit.php?slug=' . urlencode($slug)) ?>" class="adm-btn adm-btn-outline adm-btn-sm">
                                <i class="fas fa-pen"></i> <?= htmlspecialchars($tp['edit'] ?? 'Edit') ?>
                            </a>
                            <a href="<?= sh_admin_url('news-edit.php?slug=' . urlencode($slug) . '&tab=seo') ?>" class="adm-btn adm-btn-outline adm-btn-sm" title="<?= htmlspecialchars($tp['tab_seo'] ?? 'SEO & Schema') ?>">
                                <i class="fas fa-chart-line"></i>
                            </a>
                            <a href="<?= sh_url('news-article.php?slug=' . urlencode($slug)) ?>" class="adm-btn adm-btn-outline adm-btn-sm" target="_blank">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                            <form method="post" class="adm-inline-form" onsubmit="return confirm('<?= htmlspecialchars($tp['delete_confirm'] ?? 'Delete this article?') ?>')">
                                <input type="hidden" name="delete_slug" value="<?= htmlspecialchars($slug) ?>">
                                <button type="submit" class="adm-btn adm-btn-danger adm-btn-sm"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/layout-end.php'; ?>