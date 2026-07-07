---
name: security-audit
description: >
  Actively audit Shop CMS for PHP errors, security issues, exposed secrets, and fatal-risk patterns.
  Use proactively after every code change, before deploy, when user mentions security/errors/audit,
  or when fixing production fatals on bilohash.com/shop.
---

# Security & error audit — Shop CMS

Run **before every deploy** and after touching `includes/`, `admin/`, `api/`, or payment code.

## Automated scan

```powershell
cd C:\bilohash\shop
powershell -NoProfile -File scripts/security-audit.ps1
php -l init.php
php -l index.php
```

Read `scripts/security-audit-last.txt` if exit code ≠ 0.

## Manual checklist (agent)

### PHP fatals (seen on production)

- Duplicate `function sh_*()` in `includes/*.php` — only one definition per function.
- `require_once` order in `init.php`: storage helpers before files that call them (`site-integrations.php`, `orders-storage.php`).
- Undefined functions called from `index.php` before `init.php` loads their file.

### Security

| Check | Action |
|-------|--------|
| `eval`, `shell_exec`, `passthru`, `system` | Remove or justify; never on user input |
| `$_GET`/`$_POST` in `include`/`require` | Block — path traversal |
| SQL queries | Prepared statements only (`includes/database.php`) |
| File upload | Extension whitelist, no PHP in `uploads/` |
| Admin routes | Session auth via `includes/admin-auth.php` |
| API keys in repo | Only in `data/settings.json` / env — never commit live keys |
| `mail-config.php`, `db.config.php` | Must not be web-accessible; `.htaccess` in `data/` |

### Production smoke (after deploy)

Fetch these URLs — must return 200, no Fatal/Warning in body:

- https://bilohash.com/shop/
- https://bilohash.com/shop/admin/login.php
- https://bilohash.com/shop/site/

## On failure

Fix issues in `C:\bilohash\shop\`, re-run audit, deploy via `deploy-hostinger` skill, verify live again.