# Shop CMS

Universal **PHP e-commerce script** for any online store — fashion, electronics, food, B2B catalogues, marketplaces and more. Multilingual storefront, session cart, Schema.org SEO, AI automation out of the box, and a full admin panel. Portfolio project by [Ruslan Bilohash](https://bilohash.com/).

**Version (this repo):** 1.3.6 · **Readme languages:** [English](README.md) · [Norsk](README-no.md) · [Svenska](README-sv.md) · [Lietuvių](README-lt.md) · [Українська](README-uk.md)

![PHP](https://img.shields.io/badge/PHP-8%2B-777BB4?logo=php&logoColor=white)
![Version](https://img.shields.io/badge/version-1.3.6-blue)
![License](https://img.shields.io/badge/license-Proprietary-red)
![i18n](https://img.shields.io/badge/languages-NO%20%7C%20EN%20%7C%20UA%20%7C%20RU%20%7C%20SV%20%7C%20LT-green)
![AI](https://img.shields.io/badge/AI-built--in-purple)

---

## Important — demo repository only

> **This GitHub repository contains a demo / portfolio example of Shop CMS.**
> It is **not** the complete commercial product and **not** guaranteed to be the latest version.
>
> The **latest full version**, extended modules, updates and commercial support are available **only from the author**:
> - Product page: https://bilohash.com/shop/site/
> - Live demo: https://bilohash.com/shop/
> - Contact: **rbilohash@gmail.com**

**Commercial use without written consent from the author is prohibited.** See [LICENSE](LICENSE).

**Changelog:** [CHANGELOG.md](CHANGELOG.md) · **Latest:** v1.3.6 (2026-07-06)

---

## Live demo

| Resource | URL |
|----------|-----|
| **Storefront demo** | https://bilohash.com/shop/ |
| **Admin panel** | https://bilohash.com/shop/admin/ |
| **Product / order page** | https://bilohash.com/shop/site/ |
| **Solutions hub** | https://bilohash.com/shop/solutions.php |
| **Sitemap** | https://bilohash.com/shop/sitemap.php |

**Admin login (demo):** `demo` / `demo2026`

---

## AI out of the box

Shop CMS ships with **built-in AI automation** (Grok / OpenAI — configure API key in admin):

| Area | What AI does |
|------|----------------|
| **Products** | Generate titles, descriptions, SEO meta and highlights from a short prompt |
| **Categories** | Suggest names, slugs and SEO copy |
| **Languages** | Translate entire UI language files from English |
| **SEO** | Generate meta titles, descriptions and keywords |
| **Homepage** | AI block builder — contact forms, CTAs, icon grids from plain-language prompts |
| **Public chat** | Floating AI sales assistant on the storefront |
| **News & content** | Service pages and rich HTML blocks with AI-assisted editing |

Demo mode works **without an API key** — sample templates are applied locally so you can explore the workflow immediately.

---

## Features

### Public storefront
- Product catalog with search, filters, categories and sorting
- Product detail pages with Schema.org Product, sale badges, related items
- Session-based shopping cart (add, update quantity, remove)
- Multilingual UI: **Norwegian** (default), **English**, **Ukrainian**, **Russian**, **Swedish**, **Lithuanian** (`?lang=` + cookie)
- Customer sign-in demo: phone, Google & Apple OAuth shortcuts
- Parcel tracking page (Bring / Posten integration demo)
- Contact form, service pages (delivery, privacy, cookies, custom pages)
- SEO vertical landing pages (fashion, electronics, B2B, etc.)
- Responsive light theme, mobile burger menu
- GDPR cookie consent banner

### Admin panel
- **Dashboard** — products, categories, cart stats, charts
- **Catalog** — products, drag-sort categories, quick leads
- **Content** — homepage blocks, service pages, footer & header links
- **Design** — appearance, AI design generator (block builder), product card layout
- **Marketing** — global SEO, SEO analysis (product filters & pages checklist), Schema.org, XML sitemap, analytics pixels
- **Integrations** — AI assistant, public chat, reCAPTCHA, customer auth, payments (PayPal, Stripe, Vipps, COD, Google/Apple Pay), Bring & Nova Poshta tracking
- **Advanced** — maintenance mode, GDPR, default language, developer errors toggle
- **Code editor** — syntax-highlighted HTML/JS editor
- Multilingual admin UI

### Marketing site (`/site/`)
- Product landing for Shop CMS
- Screenshots, version info, tech stack, order form

---

## Tech stack

- PHP 8+ (no framework)
- JSON storage (`data/*.json`, `data/products.php` seed) — MySQL/PostgreSQL on request for production
- Modular i18n (`lang/*.php`)
- Apache `.htaccess`, canonical URLs, hreflang, Schema.org, sitemap index
- Font Awesome 6, vanilla CSS & JS
- CodeMirror in admin for HTML/JS editing

---

## Requirements

- PHP 8.0 or newer
- Apache with `mod_rewrite` (or nginx equivalent)
- Writable `data/` directory

---

## Installation

```bash
git clone https://github.com/Ruslan-Bilohash/shop.git shop
```

1. Copy the `shop/` folder to your web root (e.g. `/shop/`).
2. Set write permissions on `data/`:
   ```bash
   chmod 755 data
   ```
3. Open `https://your-domain.com/shop/` — demo products load from seed data.
4. Admin: `https://your-domain.com/shop/admin/` — change credentials before production.

### Local PHP server

```bash
cd shop
php -S localhost:8080
```

Open http://localhost:8080/

### Configuration (`config.php`)

```php
define('SH_BASE_PATH', '/shop');
define('SH_SITE_NAME', 'Shop CMS');
define('SH_CURRENCY', 'NOK');
define('SH_DEMO_MODE', true);
```

---

## Project structure

```
shop/
├── index.php              # Homepage
├── search.php             # Catalog search
├── product.php            # Product detail
├── cart.php / checkout.php
├── login.php              # Customer sign-in demo
├── contact.php / page.php
├── config.php / init.php
├── lang/                  # NO, EN, UA, RU, SV storefront UI
├── includes/              # Header, SEO, cart, AI, payments, blocks
├── assets/css|js/
├── data/                  # products, categories, settings (JSON)
├── admin/                 # Full admin panel
├── site/                  # Marketing landing (NO, EN, UA, RU, SV, LT)
├── screenshot/            # Admin & storefront screenshots
├── sitemap*.php
└── LICENSE
```

---

## Screenshots

### Dashboard & catalog

**Admin dashboard**

![Admin dashboard](screenshot/dashboard.jpg)

**Products catalog**

![Products catalog](screenshot/catalog_product.jpg)

**Categories**

![Categories](screenshot/catalog_categories.jpg)

### Store & design

**Store settings**

![Store settings](screenshot/store_setting.jpg)

**Shop settings**

![Shop settings](screenshot/setting_shop.jpg)

**Appearance / colours**

![Appearance and colours](screenshot/seting_color.jpg)

**Header settings**

![Header settings](screenshot/header_setting.jpg)

**Footer link editor**

![Footer link editor](screenshot/footer_link_editor.jpg)

### Content

**Homepage blocks**

![Homepage blocks](screenshot/main_block.jpg)

**Service page editor**

![Service page editor](screenshot/servise_page_editor.jpg)

![Service page editor — blocks](screenshot/servise_page_editor_2.jpg)

### SEO & marketing

**SEO & Schema.org**

![SEO and Schema.org](screenshot/seo_schema.jpg)

**Sitemap generator**

![Sitemap generator](screenshot/generate_schema_sitemap.jpg)

### Integrations — AI

**AI assistant**

![AI assistant](screenshot/integrations_ai_assistant.jpg)

![AI assistant — settings](screenshot/integrations_ai_assistant2.jpg)

**Public AI chat**

![Public AI chat widget](screenshot/integrations_chat.jpg)

![AI chat design](screenshot/integrations_chat_design.jpg)

**reCAPTCHA**

![reCAPTCHA integration](screenshot/integrations_recapcha.jpg)

### Integrations — payments

**PayPal**

![PayPal integration](screenshot/integrations_paypal.jpg)

**Stripe**

![Stripe integration](screenshot/integrations_stripe.jpg)

**Vipps**

![Vipps integration](screenshot/integrations_vipps.jpg)

**Cash on delivery**

![Cash on delivery](screenshot/integrations_cash_on_delivery.jpg)

**Google Pay**

![Google Pay](screenshot/integrations_google_pay.jpg)

**Apple Pay**

![Apple Pay](screenshot/integrations_apple_pay.jpg)

### Integrations — shipping

**Bring / Posten tracking**

![Bring and Posten API](screenshot/integrations_bring_posten_api.jpg)

### Advanced

**Advanced settings**

![Advanced settings](screenshot/advanced_settings.jpg)

---

## Demo mode

- Yellow demo strip on every page
- No real payments — cart is session-only
- Sample products with Unsplash images
- AI features use local fallbacks when no API key is set

---

## Author & commercial license

**Ruslan Bilohash**
- Website: https://bilohash.com/
- GitHub: https://github.com/Ruslan-Bilohash/
- Email: rbilohash@gmail.com

For **commercial licensing**, full source, custom development or the **latest production version**, contact the author directly. See [LICENSE](LICENSE).