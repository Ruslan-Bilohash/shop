<?php
/**
 * Shared bootstrap for admin JSON API endpoints.
 */
require_once dirname(__DIR__, 2) . '/init.php';
require_once dirname(__DIR__, 2) . '/includes/admin-auth.php';
require_once dirname(__DIR__, 2) . '/includes/ai.php';

@ini_set('display_errors', '0');

sh_admin_require();