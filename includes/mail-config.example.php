<?php
/**
 * SMTP mail config — copy to mail-config.php and set your Hostinger password.
 * mail-config.php should NOT be committed to public repos.
 */
return [
    'host'       => 'smtp.hostinger.com',
    'port'       => 465,
    'secure'     => 'ssl',
    'username'   => 'email@bilohash.com',
    'password'   => 'YOUR_SMTP_PASSWORD',
    'from_email' => 'email@bilohash.com',
    'from_name'  => 'BILOHASH',
];