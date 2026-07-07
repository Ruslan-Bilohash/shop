<?php
require_once dirname(__DIR__) . '/includes/admin-auth.php';
sh_admin_logout();
header('Location: ' . sh_url('site/'), true, 302);
exit;