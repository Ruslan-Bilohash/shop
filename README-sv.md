# Shop CMS

Universellt **PHP e-handelsskript** för alla typer av webbutiker — mode, elektronik, mat, B2B-kataloger, marknadsplatser med mera. Flerspråkig butik, sessionskundvagn, Schema.org SEO, inbyggd AI-automatisering och fullt adminpanel. Portfolio-projekt av [Ruslan Bilohash](https://bilohash.com/).

**Version (detta repo):** 1.7.6 · **Readme-språk:** [English](README.md) · [Norsk](README-no.md) · [Svenska](README-sv.md) · [Lietuvių](README-lt.md) · [Українська](README-uk.md)

![PHP](https://img.shields.io/badge/PHP-8%2B-777BB4?logo=php&logoColor=white)
![Version](https://img.shields.io/badge/version-1.7.6-blue)
![License](https://img.shields.io/badge/license-Proprietary-red)
![i18n](https://img.shields.io/badge/languages-NO%20%7C%20EN%20%7C%20UA%20%7C%20RU%20%7C%20SV%20%7C%20LT-green)
![AI](https://img.shields.io/badge/AI-inbyggd-purple)

---

## Viktigt — endast demo-repository

> **Detta GitHub-repository innehåller ett demo / portfolio-exempel av Shop CMS.**
> Det är **inte** den kompletta kommersiella produkten och **inte** nödvändigtvis den senaste versionen.
>
> **Senaste fullversionen**, utökade moduler, uppdateringar och kommersiell support finns **endast hos författaren**:
> - Produktsida: https://bilohash.com/shop/site/
> - Live demo: https://bilohash.com/shop/
> - Kontakt: **rbilohash@gmail.com**

**Kommersiell användning utan skriftligt medgivande från författaren är förbjuden.** Se [LICENSE](LICENSE).

---

## Live demo

| Resurs | URL |
|--------|-----|
| **Butik** | https://bilohash.com/shop/ |
| **Adminpanel** | https://bilohash.com/shop/admin/ |
| **Produkt / beställning** | https://bilohash.com/shop/site/ |
| **Lösningar** | https://bilohash.com/shop/solutions.php |
| **Sitemap** | https://bilohash.com/shop/sitemap.php |

**Admin-inloggning (demo):** `demo` / `demo2026`

---

## AI inbyggd från start

Shop CMS har **inbyggd AI-automatisering** (Grok / OpenAI — konfigurera API-nyckel i admin):

| Område | Vad AI gör |
|--------|------------|
| **Produkter** | Generera titlar, beskrivningar, SEO och höjdpunkter från kort prompt |
| **Kategorier** | Föreslå namn, slug och SEO-text |
| **Språk** | Översätt hela UI-språkfiler från engelska |
| **SEO** | Generera meta-titlar, beskrivningar och nyckelord |
| **Startsida** | AI-blockbyggare — kontaktformulär, CTA, ikonrutnät från naturligt språk |
| **Offentlig chat** | Flytande AI-försäljningsassistent i butiken |
| **Nyheter och innehåll** | Servicesidor och HTML-block med AI-redigering |

Demoläge fungerar **utan API-nyckel** — lokala mallar används så att du kan testa arbetsflödet direkt.

---

## Funktioner

### Offentlig butik
- Produktkatalog med sökning, filter, kategorier och sortering
- Produktsidor med Schema.org Product, rea-märken, relaterade varor
- Sessionskundvagn (lägg till, ändra antal, ta bort)
- Flerspråkigt UI: **norska** (standard), **engelska**, **ukrainska**, **ryska**, **svenska**, **litauiska** (`?lang=` + cookie)
- Demo kundinloggning: telefon, Google och Apple OAuth
- Spårningssida för paket (Bring / Posten demo)
- Kontaktformulär, servicesidor (leverans, integritet, cookies, anpassade sidor)
- SEO-vertikalsidor (mode, elektronik, B2B m.m.)
- Responsivt ljust tema, mobil burgermeny
- GDPR cookie-banner

### Adminpanel
- **Dashboard** — produkter, kategorier, kundvagnsstatistik, diagram
- **Katalog** — produkter, dra-och-släpp kategorier, snabbleads
- **Innehåll** — startsidesblock, AI-blockbyggare, servicesidor, sidfot och header
- **Design** — färger, produktkort, snabbköp
- **Marknadsföring** — global SEO, Schema.org, XML-sitemap, analyspixlar
- **Integrationer** — AI-assistent, offentlig chat, reCAPTCHA, kundinloggning, betalningar (PayPal, Stripe, Vipps, COD, Google/Apple Pay), Bring-spårning
- **Avancerat** — underhållsläge, GDPR, standardspråk, utvecklarfel
- **Kodredigerare** — HTML/JS med syntaxmarkering
- Flerspråkig admin

### Produktsida (`/site/`)
- Landningssida för Shop CMS
- Skärmdumpar, versionsinfo, teknisk stack, beställningsformulär

---

## Teknisk stack

- PHP 8+ (utan ramverk)
- JSON-lagring (`data/*.json`, `data/products.php` seed) — MySQL/PostgreSQL på begäran för produktion
- Modulär i18n (`lang/*.php`)
- Apache `.htaccess`, kanoniska URL:er, hreflang, Schema.org, sitemap-index
- Font Awesome 6, vanlig CSS och JS
- CodeMirror i admin för HTML/JS-redigering

---

## Krav

- PHP 8.0 eller nyare
- Apache med `mod_rewrite` (eller nginx-motsvarighet)
- Skrivbar `data/`-katalog

---

## Installation

```bash
git clone https://github.com/Ruslan-Bilohash/shop.git shop
```

1. Kopiera `shop/`-mappen till webbrot (t.ex. `/shop/`).
2. Sätt skrivrättigheter på `data/`:
   ```bash
   chmod 755 data
   ```
3. Öppna `https://din-domän.se/shop/` — demoprodukter laddas från seed-data.
4. Admin: `https://din-domän.se/shop/admin/` — ändra inloggningsuppgifter före produktion.

### Lokal PHP-server

```bash
cd shop
php -S localhost:8080
```

Öppna http://localhost:8080/

### Konfiguration (`config.php`)

```php
define('SH_BASE_PATH', '/shop');
define('SH_SITE_NAME', 'Shop CMS');
define('SH_CURRENCY', 'NOK');
define('SH_DEMO_MODE', true);
```

---

## Projektstruktur

```
shop/
├── index.php              # Startsida
├── search.php             # Katalogsökning
├── product.php            # Produktdetalj
├── cart.php / checkout.php
├── login.php              # Demo kundinloggning
├── contact.php / page.php
├── config.php / init.php
├── lang/                  # NO, EN, UA, RU, SV butiks-UI
├── includes/              # Header, SEO, kundvagn, AI, betalningar, block
├── assets/css|js/
├── data/                  # produkter, kategorier, inställningar (JSON)
├── admin/                 # Fullt adminpanel
├── site/                  # Marknadsföringslanding (NO, EN, UA, RU, SV, LT)
├── screenshot/            # Skärmdumpar av admin och butik
├── sitemap*.php
└── LICENSE
```

---

## Skärmdumpar

### Dashboard och katalog

**Admin dashboard**

![Admin dashboard](screenshot/dashboard.jpg)

**Produktkatalog**

![Produktkatalog](screenshot/catalog_product.jpg)

**Kategorier**

![Kategorier](screenshot/catalog_categories.jpg)

### Butik och design

**Butiksinställningar**

![Butiksinställningar](screenshot/store_setting.jpg)

**Butiksinställningar (detalj)**

![Butiksinställningar detalj](screenshot/setting_shop.jpg)

**Utseende / färger**

![Utseende och färger](screenshot/seting_color.jpg)

**Header-inställningar**

![Header-inställningar](screenshot/header_setting.jpg)

**Sidfotslänkar**

![Sidfotslänkar](screenshot/footer_link_editor.jpg)

### Innehåll

**Startsidesblock**

![Startsidesblock](screenshot/main_block.jpg)

**Serviceside-redigerare**

![Serviceside-redigerare](screenshot/servise_page_editor.jpg)

![Serviceside-redigerare — block](screenshot/servise_page_editor_2.jpg)

### SEO och marknadsföring

**SEO och Schema.org**

![SEO och Schema.org](screenshot/seo_schema.jpg)

**Sitemap-generator**

![Sitemap-generator](screenshot/generate_schema_sitemap.jpg)

### Integrationer — AI

**AI-assistent**

![AI-assistent](screenshot/integrations_ai_assistant.jpg)

![AI-assistent — inställningar](screenshot/integrations_ai_assistant2.jpg)

**Offentlig AI-chat**

![Offentlig AI-chat](screenshot/integrations_chat.jpg)

![AI-chat design](screenshot/integrations_chat_design.jpg)

**reCAPTCHA**

![reCAPTCHA-integration](screenshot/integrations_recapcha.jpg)

### Integrationer — betalningar

**PayPal**

![PayPal-integration](screenshot/integrations_paypal.jpg)

**Stripe**

![Stripe-integration](screenshot/integrations_stripe.jpg)

**Vipps**

![Vipps-integration](screenshot/integrations_vipps.jpg)

**Postförskott**

![Postförskott](screenshot/integrations_cash_on_delivery.jpg)

**Google Pay**

![Google Pay](screenshot/integrations_google_pay.jpg)

**Apple Pay**

![Apple Pay](screenshot/integrations_apple_pay.jpg)

### Integrationer — frakt

**Bring / Posten-spårning**

![Bring och Posten API](screenshot/integrations_bring_posten_api.jpg)

### Avancerat

**Avancerade inställningar**

![Avancerade inställningar](screenshot/advanced_settings.jpg)

---

## Demoläge

- Gult demo-band på alla sidor
- Inga riktiga betalningar — kundvagn endast i session
- Demoprodukter med Unsplash-bilder
- AI använder lokala mallar när ingen API-nyckel är angiven

---

## Författare och kommersiell licens

**Ruslan Bilohash**
- Webbplats: https://bilohash.com/
- GitHub: https://github.com/Ruslan-Bilohash/
- E-post: rbilohash@gmail.com

För **kommersiell licens**, full källkod, anpassad utveckling eller **senaste produktionsversionen**, kontakta författaren direkt. Se [LICENSE](LICENSE).