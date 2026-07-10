#!/usr/bin/env bash
# 30-day demo ZIP for GitHub Release / Packages (MySQL install wizard).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
VER="${1:-dev}"
OUT="dist/shop-cms-demo-30d-${VER}.zip"
mkdir -p dist
rm -f "$OUT"
zip -rq "$OUT" . \
  -x ".git/*" \
  -x "screenshot/*" \
  -x "code_canyon/*" \
  -x "prompt/*" \
  -x "dist/*" \
  -x "*.zip" \
  -x "data/db.config.php" \
  -x "data/admin.config.php" \
  -x "data/installed.lock" \
  -x "data/settings.json" \
  -x "data/leads.json" \
  -x "scripts/deploy.config.local.ps1" \
  -x "**/mail-config.php" \
  -x "uploads/*" \
  -x "**/.env" \
  -x "**/.env.*"
echo "Created $OUT ($(du -h "$OUT" | cut -f1))"