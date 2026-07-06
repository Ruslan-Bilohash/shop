<?php
require_once __DIR__ . '/init.php';
header('Location: ' . sh_admin_url('settings-header.php'), true, 301);
exit;