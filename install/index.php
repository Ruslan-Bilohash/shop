<?php
/**
 * Shop CMS install package — not a storefront entry point.
 * Redirect visitors to the MySQL install wizard one level up.
 */
header('Location: ../install.php', true, 302);
exit;