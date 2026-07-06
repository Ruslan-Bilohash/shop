<?php
/** @var array $cms_t @var array $cms_values @var string $cms_alert @var string $cms_alert_type @var string $cms_prefix @var string $cms_action */
global $lang;
$cms_prefix = $cms_prefix ?? 'sh';
if (!isset($cms_t) || !is_array($cms_t)) {
    if (!function_exists('cms_contact_texts')) {
        $contactLib = __DIR__ . '/cms-contact.php';
        if (is_file($contactLib)) {
            require_once $contactLib;
        }
    }
    $cms_t = function_exists('cms_contact_texts')
        ? cms_contact_texts('shop', $lang ?? 'en')
        : [
            'h1' => 'Contact', 'subtitle' => '', 'name' => 'Name', 'email' => 'Email',
            'phone' => 'Phone', 'subject' => 'Subject', 'message' => 'Message',
            'privacy_note' => '', 'submit' => 'Send', 'default_subject' => '',
        ];
}
$cms_action = $cms_action ?? (function_exists('sh_url') ? sh_url('contact.php') : '/contact.php');
$cms_csrf = $cms_csrf ?? (function_exists('cms_contact_ensure_csrf') ? cms_contact_ensure_csrf() : '');
$p = preg_replace('/[^a-z0-9]/', '', $cms_prefix ?? 'cms');
$is_fl = ($p === 'fl');
$cms_alert = $cms_alert ?? '';
$cms_alert_type = $cms_alert_type ?? '';
$cms_values = $cms_values ?? ['name' => '', 'email' => '', 'phone' => '', 'subject' => '', 'message' => ''];

if ($is_fl):
    global $t, $lang;
    $tc = $t['contact'] ?? [];
?>
<section class="fl-contact-hero">
    <div class="fl-hero-bg" aria-hidden="true"></div>
    <div class="fl-container fl-contact-hero-inner">
        <nav class="fl-breadcrumb" aria-label="Breadcrumb">
            <a href="<?= htmlspecialchars(fl_url('index.php')) ?>"><?= htmlspecialchars($t['breadcrumb_home'] ?? 'Home') ?></a>
            <span aria-hidden="true">→</span>
            <span><?= htmlspecialchars($cms_t['h1']) ?></span>
        </nav>
        <span class="fl-demo-badge"><i class="fas fa-comments" aria-hidden="true"></i> <?= htmlspecialchars($tc['badge'] ?? 'Custom development') ?></span>
        <h1><?= htmlspecialchars($cms_t['h1']) ?></h1>
        <p><?= htmlspecialchars($cms_t['subtitle']) ?></p>
    </div>
</section>

<div class="fl-container fl-contact-page-wrap">
    <div class="fl-contact-layout">
        <div class="fl-contact-main">
<?php elseif (!empty($cms_embedded)): ?>
<section class="<?= $p ?>-contact-page" id="contact">
    <div class="<?= $p ?>-contact-inner">
<?php else: ?>
<main class="<?= $p ?>-contact-main">
<section class="<?= $p ?>-contact-page">
    <div class="<?= $p ?>-contact-inner">
        <header class="<?= $p ?>-contact-head">
            <h1 class="<?= $p ?>-page-title"><?= htmlspecialchars($cms_t['h1']) ?></h1>
            <p class="<?= $p ?>-contact-sub"><?= htmlspecialchars($cms_t['subtitle']) ?></p>
        </header>
<?php endif; ?>

        <?php if (!empty($cms_alert)): ?>
        <div class="<?= $p ?>-contact-alert <?= $p ?>-contact-alert--<?= htmlspecialchars($cms_alert_type) ?>" role="status">
            <?= htmlspecialchars($cms_alert) ?>
        </div>
        <?php endif; ?>

        <form class="<?= $p ?>-contact-form" method="post" action="<?= htmlspecialchars($cms_action) ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($cms_csrf ?? '') ?>">
            <input type="hidden" name="lang" value="<?= htmlspecialchars($lang ?? 'en') ?>">
            <input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="<?= $p ?>-hp" aria-hidden="true">

            <div class="<?= $p ?>-contact-grid">
                <label class="<?= $p ?>-contact-field">
                    <span><?= htmlspecialchars($cms_t['name']) ?> *</span>
                    <input type="text" name="name" required maxlength="120" value="<?= htmlspecialchars($cms_values['name']) ?>" autocomplete="name">
                </label>
                <label class="<?= $p ?>-contact-field">
                    <span><?= htmlspecialchars($cms_t['email']) ?> *</span>
                    <input type="email" name="email" required maxlength="160" value="<?= htmlspecialchars($cms_values['email']) ?>" autocomplete="email">
                </label>
                <label class="<?= $p ?>-contact-field">
                    <span><?= htmlspecialchars($cms_t['phone']) ?></span>
                    <input type="tel" name="phone" maxlength="24" value="<?= htmlspecialchars($cms_values['phone']) ?>" autocomplete="tel">
                </label>
                <label class="<?= $p ?>-contact-field <?= $p ?>-contact-field--full">
                    <span><?= htmlspecialchars($cms_t['subject']) ?></span>
                    <input type="text" name="subject" maxlength="200" value="<?= htmlspecialchars($cms_values['subject']) ?>" placeholder="<?= htmlspecialchars($cms_t['default_subject'] ?? '') ?>">
                </label>
                <label class="<?= $p ?>-contact-field <?= $p ?>-contact-field--full">
                    <span><?= htmlspecialchars($cms_t['message']) ?> *</span>
                    <textarea name="message" required rows="7" minlength="20" maxlength="5000" placeholder="<?= htmlspecialchars($cms_t['default_subject'] ?? '') ?>"><?= htmlspecialchars($cms_values['message']) ?></textarea>
                </label>
            </div>

            <?php $cms_recap_key = $cms_recaptcha_site_key ?? cms_recaptcha_site_key(); if ($cms_recap_key !== ''): ?>
            <div class="<?= $p ?>-contact-captcha">
                <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($cms_recap_key) ?>"></div>
            </div>
            <?php endif; ?>

            <p class="<?= $p ?>-contact-note"><?= htmlspecialchars($cms_t['privacy_note']) ?></p>

            <button type="submit" class="<?= $p ?>-contact-submit">
                <i class="fas fa-paper-plane" aria-hidden="true"></i> <?= htmlspecialchars($cms_t['submit']) ?>
            </button>
        </form>

<?php if ($is_fl): ?>
        </div>
        <aside class="fl-contact-aside" aria-label="<?= htmlspecialchars($tc['markets_label'] ?? 'Markets') ?>">
            <?php
            $solution_lib = dirname(__DIR__) . '/freelance/includes/solution-lib.php';
            if (is_file($solution_lib)) {
                require_once $solution_lib;
            }
            if (function_exists('fl_render_solution_tags')): ?>
            <div class="fl-contact-aside-block">
                <h3 class="fl-contact-aside-title"><i class="fas fa-layer-group" aria-hidden="true"></i> <?= htmlspecialchars($tc['solutions_label'] ?? ($t['solutions']['hub_title'] ?? 'Use cases')) ?></h3>
                <p class="fl-contact-aside-hint"><?= htmlspecialchars($tc['solutions_hint'] ?? '') ?></p>
                <div class="fl-skills fl-contact-tags">
                    <?php fl_render_solution_tags(); ?>
                </div>
                <a href="<?= fl_url('solutions.php') ?>" class="fl-contact-aside-link"><?= htmlspecialchars($tc['all_solutions'] ?? 'All use cases') ?> →</a>
            </div>
            <?php endif;
            $region_tags = dirname(__DIR__) . '/freelance/includes/region-tags.php';
            if (is_file($region_tags)) {
                require_once $region_tags;
                fl_render_contact_region_tags();
            }
            ?>
            <div class="fl-contact-aside-block">
                <h3 class="fl-contact-aside-title"><i class="fas fa-link" aria-hidden="true"></i> <?= htmlspecialchars($tc['quick_label'] ?? 'Quick links') ?></h3>
                <ul class="fl-contact-quick-links">
                    <li><a href="<?= fl_url('index.php') ?>"><i class="fas fa-play-circle" aria-hidden="true"></i> <?= htmlspecialchars($tc['quick_demo'] ?? 'Live demo') ?></a></li>
                    <li><a href="https://bilohash.com/freelance/site/"><i class="fas fa-book" aria-hidden="true"></i> <?= htmlspecialchars($tc['quick_product'] ?? 'Product page') ?></a></li>
                    <li><a href="<?= fl_url('become-freelancer.php') ?>"><i class="fas fa-user-plus" aria-hidden="true"></i> <?= htmlspecialchars($tc['quick_become'] ?? 'Become a freelancer') ?></a></li>
                    <li><a href="<?= fl_url('search.php') ?>"><i class="fas fa-search" aria-hidden="true"></i> <?= htmlspecialchars($tc['quick_projects'] ?? 'Browse projects') ?></a></li>
                </ul>
            </div>
            <?php if (!empty($t['ecosystem']['items'])): ?>
            <div class="fl-contact-aside-block">
                <h3 class="fl-contact-aside-title"><i class="fas fa-cubes" aria-hidden="true"></i> <?= htmlspecialchars($tc['ecosystem_label'] ?? ($t['ecosystem']['title'] ?? 'Other products')) ?></h3>
                <p class="fl-contact-aside-hint"><?= htmlspecialchars($tc['ecosystem_hint'] ?? '') ?></p>
                <ul class="fl-contact-quick-links">
                    <?php foreach ($t['ecosystem']['items'] as $eco): ?>
                    <li>
                        <a href="<?= htmlspecialchars($eco['demo']) ?>" rel="related">
                            <?php if (($eco['icon'] ?? '') === 'wordpress'): ?>
                            <i class="fab fa-wordpress" aria-hidden="true"></i>
                            <?php else: ?>
                            <i class="fas fa-<?= htmlspecialchars($eco['icon']) ?>" aria-hidden="true"></i>
                            <?php endif; ?>
                            <?= htmlspecialchars($eco['name']) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </aside>
    </div>
</div>
<?php elseif (!empty($cms_embedded)): ?>
    </div>
</section>
<?php else: ?>
    </div>
</section>
</main>
<?php endif; ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>