<?php

function sh_admin_flash_set(string $type, string $msg): void
{
    $_SESSION['sh_admin_flash'] = ['type' => $type, 'msg' => $msg];
}

/** @param mixed $raw */
function sh_admin_flash_normalize($raw, array $ta = []): ?array
{
    if ($raw === null || $raw === '' || $raw === false) {
        return null;
    }
    if (is_array($raw)) {
        $type = (string) ($raw['type'] ?? 'info');
        if (!in_array($type, ['success', 'error', 'info', 'warning'], true)) {
            $type = 'info';
        }
        $msg = trim((string) ($raw['msg'] ?? ''));
        if ($msg === '') {
            $msg = sh_admin_flash_default_msg($type, $ta);
        }
        return $msg === '' ? null : ['type' => $type, 'msg' => $msg];
    }
    if ($raw === 'success') {
        return ['type' => 'success', 'msg' => sh_admin_flash_default_msg('success', $ta)];
    }
    if ($raw === 'sitemap_ok') {
        $msg = function_exists('sh_settings_admin_label')
            ? sh_settings_admin_label('sitemap_regenerated', $ta)
            : 'Sitemap regenerated.';
        return ['type' => 'success', 'msg' => $msg];
    }
    if ($raw === 'ai_key_ok') {
        $msg = function_exists('sh_settings_admin_label')
            ? sh_settings_admin_label('ai_key_saved', $ta)
            : 'API key saved.';
        return ['type' => 'success', 'msg' => $msg];
    }
    if ($raw === 'error') {
        return ['type' => 'error', 'msg' => sh_admin_flash_default_msg('error', $ta)];
    }
    if (is_string($raw)) {
        return ['type' => 'info', 'msg' => $raw];
    }
    return null;
}

function sh_admin_flash_default_msg(string $type, array $ta = []): string
{
    if ($type === 'success') {
        if (function_exists('sh_settings_admin_label')) {
            $label = sh_settings_admin_label('settings_saved', $ta);
            if ($label !== '') {
                return $label;
            }
        }
        return $ta['flash_saved'] ?? 'Saved successfully.';
    }
    if ($type === 'error') {
        if (function_exists('sh_settings_admin_label')) {
            $label = sh_settings_admin_label('error', $ta);
            if ($label !== '') {
                return $label;
            }
        }
        return $ta['flash_error'] ?? 'Could not save. Try again.';
    }
    return '';
}

function sh_admin_flash_consume(array $ta = []): ?array
{
    $raw = $_SESSION['sh_admin_flash'] ?? null;
    unset($_SESSION['sh_admin_flash']);
    return $raw === null ? null : sh_admin_flash_normalize($raw, $ta);
}

function sh_admin_flash_resolve(array $ta = []): ?array
{
    $session = sh_admin_flash_consume($ta);
    if ($session !== null) {
        return $session;
    }
    if (isset($admin_flash)) {
        return sh_admin_flash_normalize($admin_flash, $ta);
    }
    return null;
}

function sh_admin_render_flash_toast(?array $flash, array $ta = []): void
{
    if ($flash === null || trim((string) ($flash['msg'] ?? '')) === '') {
        return;
    }
    $type = (string) ($flash['type'] ?? 'info');
    if (!in_array($type, ['success', 'error', 'info', 'warning'], true)) {
        $type = 'info';
    }
    $icons = [
        'success' => 'check-circle',
        'error'   => 'exclamation-circle',
        'warning' => 'triangle-exclamation',
        'info'    => 'info-circle',
    ];
    $icon = $icons[$type] ?? 'info-circle';
    $dismiss = $ta['flash_dismiss'] ?? 'Close';
    ?>
    <div class="adm-flash-stack" id="admFlashStack" aria-live="polite" aria-atomic="true">
        <div class="adm-flash-toast adm-flash-toast--<?= htmlspecialchars($type) ?>" role="status" data-adm-flash-toast>
            <i class="fas fa-<?= htmlspecialchars($icon) ?>" aria-hidden="true"></i>
            <span class="adm-flash-toast-msg"><?= htmlspecialchars((string) $flash['msg']) ?></span>
            <button type="button" class="adm-flash-toast-close" data-adm-flash-dismiss aria-label="<?= htmlspecialchars($dismiss) ?>">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        </div>
    </div>
    <?php
}