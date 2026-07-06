# Changelog

All notable changes to the Shop CMS demo repository are documented here.

## [1.3.6] — 2026-07-06

### Added
- **News** — admin list/editor (`admin/news.php`), public `news.php` + `news-article.php`, JSON storage, sitemap URLs
- **AI news generation** — `admin/api/ai-news.php`, per-context model `ai_model_news`, custom `ai_prompt_news`
- **AI models by context** — separate models for product, chat, news and SEO in Settings → AI; setup instructions in admin
- Product page **image gallery** thumbnails and improved Product schema (all images, `mainEntityOfPage`, long description in JSON-LD)

### Changed
- Product **meta description** falls back to `long_desc` when custom SEO field is empty
- Footer and product site cross-links point to in-demo `/shop/news.php` instead of external release HTML
- Version badge on `/shop/site/`, admin topbar and README — **v1.3.6** (single source: `includes/version.php`)

## [1.3.5] — 2026-07-06

### Added
- **Nova Poshta** integration — admin settings tab, API key field, parcel tracking page (`track-np.php`), translations and setup guides
- **SEO analysis** (Marketing) — product table with score filters, issue types, category filter; pages checklist for global SEO, categories and service pages
- Product editor **SEO checklist** reused from analysis (live score in sidebar)

### Changed
- **Product editor** — General, Names and SEO merged into one scrollable page (no tab switching)
- **AI product assistant** — single generate button under fields, SEO tips panel, meta description normalized to 120–160 characters
- **Design generator** (AI block builder) moved from Content to **Design** tab
- **Product site** (`/shop/site/`) — full i18n coverage, meta descriptions 150–160 chars, expanded Schema.org, OG/Twitter, hreflang, internal crosslinks
- Version badge synced: admin, product site, README and GitHub demo — **v1.3.5**

### Security
- `data/settings.json` remains gitignored — no API keys committed; use `data/settings.json.example` as template

## [1.3.0] — 2026-07-06

- Multilingual AI, store currency, GA4/Meta Pixel, quick buy leads
- Service pages, Posten tracking, 21 categories, customer auth
- Header menu CRUD, AI block builder, code editor, product site gallery

Older releases: see [product site changelog](https://bilohash.com/shop/site/#version) or `includes/version.php`.