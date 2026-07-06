<?php
require_once __DIR__ . '/init.php';
sh_customer_logout();
header('Location: ' . sh_url('index.php'), true, 302);
exit;