Shop CMS — Commercial Install Package
Copyright (c) 2024–2026 Ruslan Bilohash
https://bilohash.com | rbilohash@gmail.com

INSTALLATION
============
1. Upload the entire contents of this folder to your hosting (e.g. public_html/shop/)
2. Create a MySQL database and user in your hosting panel
3. Open https://your-domain.com/shop/install.php in your browser
4. Enter database credentials and admin login/password
5. Click "Install" — demo products, categories, news and settings are imported automatically
6. Delete or rename install.php after successful setup

REQUIREMENTS
============
- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- PDO MySQL extension
- Writable data/ and uploads/ folders

DATA STORAGE
============
All shop data is stored in MySQL only (8 tables). No JSON runtime files after install.

Tables: products, categories, news, leads, orders, subscribers, customer_profiles, settings.

MIGRATE FROM JSON EDITION (not_mysql)
===================================
If you have the JSON package with data/*.json files:

1. Upload this MySQL package OR add migrate-to-mysql.php to your site root
2. Open https://your-domain.com/shop/migrate-to-mysql.php
3. Enter MySQL credentials — all JSON files are imported automatically
4. JSON files are backed up to data/archive/ and renamed to *.json.migrated
5. init.php switches to MySQL runtime automatically
6. Delete migrate-to-mysql.php and install.php after verification

FRESH INSTALL
=============
Open install.php — demo seed is imported into MySQL.

LICENSE
=======
See LICENSE.txt — commercial use requires written permission from Ruslan Bilohash.