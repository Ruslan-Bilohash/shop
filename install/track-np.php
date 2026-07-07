<?php
/** @deprecated Use track.php?c=nova_poshta — kept for backward-compatible links. */
require_once __DIR__ . '/init.php';

$n = trim((string) ($_GET['n'] ?? $_POST['tracking'] ?? ''));
$qs = 'c=nova_poshta' . ($n !== '' ? '&n=' . rawurlencode($n) : '');
$lang = isset($_GET['lang']) ? '?lang=' . rawurlencode((string) $_GET['lang']) . '&' . $qs : '?' . $qs;
header('Location: ' . sh_url('track.php') . $lang, true, 301);
exit;