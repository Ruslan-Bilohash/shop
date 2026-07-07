## Shop CMS v1.7.4

### Auto-install (demo-install.php)
- FTP auto-install request form on `/shop/site/demo-install.php` — domain, FTP host/user/pass/path
- Requests saved to ecosystem owner panel (`ecosystem/admin.php`) + email to owner
- CSRF, honeypot and field validation

### Admin — owner & security
- **Owner panel** (`admin/owner.php`) — BILOHASH subscription, domain, license quota for owner account
- **AI Security Scanner** — AJAX console with rule-based scan (no external API required)
- Demo login: one-click `demo` / `demo` autofill on admin login
- MySQL console hidden for demo staff account

### Ecosystem owner panel
- Auto-install requests as separate source type with FTP details column
- Install request counter in stats

### Packages
- `shop-install-v1.7.4.zip` — MySQL commercial package
- `shop-not-mysql-v1.7.4.zip` — JSON storage edition
- `shop-demo-30d-v1.7.4.zip` — 30-day demo package (MySQL install wizard)