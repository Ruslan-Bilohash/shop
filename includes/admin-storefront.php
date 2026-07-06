<?php
/**
 * Admin tools on public storefront (edit product, admin link).
 */
require_once __DIR__ . '/admin-auth.php';

function sh_admin_storefront_active(): bool
{
    return sh_admin_logged();
}

function sh_admin_product_edit_url(string $productId): string
{
    return sh_admin_url('product-edit.php?id=' . urlencode($productId));
}

function sh_render_admin_storefront_bar(?string $productId = null): void
{
    if (!sh_admin_storefront_active()) {
        return;
    }
    global $t;
    $ab = $t['admin_bar'] ?? [];
    $editUrl = $productId !== null && $productId !== '' ? sh_admin_product_edit_url($productId) : '';
    ?>
    <div class="sh-admin-bar" role="complementary" aria-label="<?= htmlspecialchars($ab['label'] ?? 'Admin tools') ?>">
        <div class="sh-admin-bar-inner">
            <span class="sh-admin-bar-badge"><i class="fas fa-user-shield" aria-hidden="true"></i> <?= htmlspecialchars($ab['logged_in'] ?? 'Admin') ?></span>
            <?php if ($editUrl !== ''): ?>
            <a href="<?= htmlspecialchars($editUrl) ?>" class="sh-admin-bar-btn sh-admin-bar-btn--primary">
                <i class="fas fa-pen" aria-hidden="true"></i> <?= htmlspecialchars($ab['edit_product'] ?? 'Edit product') ?>
            </a>
            <?php endif; ?>
            <a href="<?= htmlspecialchars(sh_admin_url('index.php')) ?>" class="sh-admin-bar-btn">
                <i class="fas fa-gauge" aria-hidden="true"></i> <?= htmlspecialchars($ab['dashboard'] ?? 'Dashboard') ?>
            </a>
            <a href="<?= htmlspecialchars(sh_admin_url('logout.php')) ?>" class="sh-admin-bar-btn sh-admin-bar-btn--muted">
                <i class="fas fa-right-from-bracket" aria-hidden="true"></i> <?= htmlspecialchars($ab['logout'] ?? 'Logout') ?>
            </a>
        </div>
    </div>
    <?php
}

function sh_render_admin_product_edit_link(string $productId): void
{
    if (!sh_admin_storefront_active() || $productId === '') {
        return;
    }
    global $t;
    $ab = $t['admin_bar'] ?? [];
    ?>
    <a href="<?= htmlspecialchars(sh_admin_product_edit_url($productId)) ?>"
       class="sh-admin-edit-card"
       title="<?= htmlspecialchars($ab['edit_product'] ?? 'Edit product') ?>"
       aria-label="<?= htmlspecialchars($ab['edit_product'] ?? 'Edit product') ?>">
        <i class="fas fa-pen" aria-hidden="true"></i>
    </a>
    <?php
}