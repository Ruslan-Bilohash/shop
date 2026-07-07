---
name: deploy-hostinger
description: >
  Deploy Shop CMS files directly to Hostinger production (bilohash.com/shop/) via SCP/SSH.
  Use after any code change, when user says deploy/upload/hostinger/production, or at end of every task.
  Never tell the user to upload manually — run scripts/deploy-to-hostinger.ps1 yourself.
---

# Deploy to Hostinger (bilohash.com/shop)

## Production paths

| Role | Path |
|------|------|
| Local source | `C:\bilohash\shop\` |
| Remote root | `/home/u762384583/domains/bilohash.com/public_html/shop/` |
| Live URL | https://bilohash.com/shop/ |

## One-time setup (user)

1. hPanel → **SSH Access** → enable SSH, note host, port (often `65002`), user `u762384583`.
2. Copy `scripts/deploy.config.example.ps1` → `scripts/deploy.config.local.ps1` (gitignored).
3. Either set `Password` in that file, or add your public key to Hostinger and leave `Password` empty.

Optional SSH alias in `~/.ssh/config`:

```
Host bilohash
    HostName 187.124.26.9
    User u762384583
    Port 22
    IdentityFile ~/.ssh/id_ed25519
```

## Deploy commands (agent runs these)

```powershell
cd C:\bilohash\shop

# After lang/i18n work
powershell -NoProfile -File scripts/deploy-to-hostinger.ps1 -LangOnly

# Specific files
powershell -NoProfile -File scripts/deploy-to-hostinger.ps1 -Files lang/no.php,lang/uk.php,site/lang/no.php

# All files changed in last commit
powershell -NoProfile -File scripts/deploy-to-hostinger.ps1 -ChangedSinceGit
```

## Rules

- Deploy **immediately** after fixing production bugs or updating translations — do not end the task with a file list for the user.
- **Never overwrite** on server: `data/db.config.php`, `data/admin.config.php`, `data/*.json` (unless task explicitly migrates data).
- After deploy, verify live page loads (fetch `https://bilohash.com/shop/?lang=uk` and `?lang=no`).
- Sync `install/` and `not_mysql/` copies when changing `lang/` (mirror from main tree).

## Fallback

If SSH fails (no key/password): create hotfix zip via `scripts/pack-production-hotfix.ps1` and report that `deploy.config.local.ps1` must be configured — but always attempt SSH first.