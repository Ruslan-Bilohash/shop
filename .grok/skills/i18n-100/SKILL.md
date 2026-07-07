---
name: i18n-100
description: >
  Ensure all Shop CMS languages are 100% complete (no, uk, ru, sv, lt) in lang/ and site/lang/.
  Use for any translation task, terminology change, new UI strings, admin labels, or when user asks
  for full multilingual coverage / 100% languages.
---

# i18n 100% — Shop CMS

**Target:** every non-English locale at **100% key coverage** vs `en.php`, with real translations (not empty, not copy-paste English unless intentional).

## Lang trees

| Tree | Path | Locales |
|------|------|---------|
| Storefront + admin | `lang/` | no, uk, ru, sv (+ en base) |
| Product site | `site/lang/` | no, uk, ru, sv, lt |
| Admin guides | `lang/admin-guides.php`, `lang/admin-settings-guides.php` | per-locale sections |

Norwegian shop label: **butik** (not butikk). Ukrainian shop label: **крамниця** (not магазин).

## Audit (run first)

```powershell
cd C:\bilohash\shop
powershell -NoProfile -File scripts/i18n-audit.ps1
```

Exit code 0 required. If any lang &lt; 100%, fix before closing task.

## Workflow when adding/changing strings

1. Add key to `lang/en.php` (and `site/lang/en.php` if product site).
2. Mirror to **all** locales: `no.php`, `uk.php`, `ru.php`, `sv.php`, (`lt.php` for site only).
3. Sync copies: `install/lang/`, `not_mysql/lang/`, `install/site/lang/`, `not_mysql/site/lang/`.
4. Re-run `scripts/i18n-audit.ps1` — must pass 100%.
5. Deploy `-LangOnly` to Hostinger.

## Translation quality rules

- Match grammatical case (UA: крамниця/крамниці/крамницю; NO: butik/butiken).
- Product site placeholders: `{origin}`, `{in_country}`, `{for_country}`, `{currency}`, `{country}` — keep intact.
- `array_replace_recursive($en, [...])` files: only override translated leaves; missing keys inherit EN — **still counts as gap** if EN text shows in UI for that locale.

## PHP runtime check

```powershell
php -r "$l=['no','uk','ru','sv']; foreach($l as $c){ $t=include 'lang/'.$c.'.php'; echo $c.' ok '.count($t, COUNT_RECURSIVE).PHP_EOL; }"
```

## Done criteria

- [ ] `i18n-audit.ps1` → 100% all langs in both trees
- [ ] install + not_mysql mirrors updated
- [ ] Live check: `?lang=uk`, `?lang=no` on /shop/ and /shop/site/