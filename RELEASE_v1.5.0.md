# Shop CMS v1.5.0 — 2026-07-06

## Highlights

**Full MySQL migration** — all shop data (products, categories, news, leads, customer profiles, settings) is stored in MySQL only. No JSON runtime files.

**Commercial install package** — the `install/` folder is a complete standalone shop. Buyers upload it to hosting, open `install.php`, enter MySQL credentials and admin login — the store is ready with demo catalog.

## Added

- `includes/database.php` — MySQL storage layer with install detection and redirect
- `install/install.php` — installation wizard (DB + admin account)
- `install/schema.sql` — 6 MySQL tables
- `install/seed/` — demo data imported on first install
- `install/README.txt`, `install/LICENSE.txt` — buyer documentation
- `scripts/build-install.ps1` — sync dev tree → commercial package

## Changed

- Removed `includes/json-store.php` and runtime `data/*.json`
- Storage modules rewritten for MySQL (`storage.php`, `category-storage.php`, `news-storage.php`, `leads-storage.php`, `payment-settings.php`, `shop-mode.php`)
- Admin UI texts updated: settings stored in database, not JSON files
- Install package admin API paths fixed for standalone deployment

## Installation (buyers)

1. Download **shop-install-v1.5.0.zip** from this release
2. Upload contents to your hosting (e.g. `public_html/shop/`)
3. Create a MySQL database in your hosting panel
4. Open `install.php` in the browser
5. Enter DB credentials and admin password → done
6. Delete or rename `install.php` after setup

## Requirements

- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- PDO MySQL extension
- Writable `data/` and `uploads/` folders

---
© 2024–2026 Ruslan Bilohash — https://bilohash.com