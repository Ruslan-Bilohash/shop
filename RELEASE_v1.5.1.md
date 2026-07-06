## Shop CMS v1.5.1

### New: JSON edition (`not_mysql/`)
- Standalone package without MySQL — upload and run with demo JSON data
- Demo admin: `demo` / `demo2026`

### Fixes
- Admin AI buttons: correct API base path when app is not at `/shop`
- Admin API returns JSON `401` instead of HTML redirect (fixes fetch errors)
- Category sort API: missing `admin-auth.php` include fixed

### Packages
- `install/` — MySQL commercial package (install wizard)
- `not_mysql/` — JSON storage edition (no database)