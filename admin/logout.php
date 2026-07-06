<?php
require_once dirname(__DIR__) . '/includes/admin-auth.php';
sh_admin_logout();
header('Location: ' . sh_admin_url('login.php'), true, 302);
exit;