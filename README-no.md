# Shop CMS

Universelt **PHP e-handelsskript** for enhver nettbutikk — mote, elektronikk, mat, B2B-kataloger, markedsplasser og mer. Flerspråklig butikkfront, handlekurv på sesjon, Schema.org SEO, AI-automatisering innebygd, og fullt adminpanel. Porteføljeprosjekt av [Ruslan Bilohash](https://bilohash.com/).

**Versjon (dette repo):** 1.3.0 · **Readme-språk:** [English](README.md) · [Norsk](README-no.md) · [Svenska](README-sv.md) · [Lietuvių](README-lt.md) · [Українська](README-uk.md)

![PHP](https://img.shields.io/badge/PHP-8%2B-777BB4?logo=php&logoColor=white)
![Version](https://img.shields.io/badge/version-1.3.0-blue)
![License](https://img.shields.io/badge/license-Proprietary-red)
![i18n](https://img.shields.io/badge/languages-NO%20%7C%20EN%20%7C%20UA%20%7C%20RU%20%7C%20SV%20%7C%20LT-green)
![AI](https://img.shields.io/badge/AI-innebygd-purple)

---

## Viktig — kun demo-repositorium

> **Dette GitHub-repositoriet inneholder et demo / porteføljeeksempel av Shop CMS.**
> Det er **ikke** det komplette kommersielle produktet og **ikke** nødvendigvis den nyeste versjonen.
>
> **Nyeste fullversjon**, utvidede moduler, oppdateringer og kommersiell støtte er tilgjengelig **kun fra forfatteren**:
> - Produktside: https://bilohash.com/shop/site/
> - Live demo: https://bilohash.com/shop/
> - Kontakt: **rbilohash@gmail.com**

**Kommersiell bruk uten skriftlig samtykke fra forfatteren er forbudt.** Se [LICENSE](LICENSE).

---

## Live demo

| Ressurs | URL |
|---------|-----|
| **Butikkfront** | https://bilohash.com/shop/ |
| **Adminpanel** | https://bilohash.com/shop/admin/ |
| **Produkt / bestilling** | https://bilohash.com/shop/site/ |
| **Løsninger** | https://bilohash.com/shop/solutions.php |
| **Sitemap** | https://bilohash.com/shop/sitemap.php |

**Admin-innlogging (demo):** `demo` / `demo2026`

---

## AI innebygd fra start

Shop CMS har **innebygd AI-automatisering** (Grok / OpenAI — konfigurer API-nøkkel i admin):

| Område | Hva AI gjør |
|--------|-------------|
| **Produkter** | Generer titler, beskrivelser, SEO og høydepunkter fra kort prompt |
| **Kategorier** | Foreslå navn, slug og SEO-tekst |
| **Språk** | Oversett hele UI-språkfiler fra engelsk |
| **SEO** | Generer meta-titler, beskrivelser og nøkkelord |
| **Forside** | AI-blokkbygger — kontaktskjemaer, CTA, ikonruter fra naturlig språk |
| **Offentlig chat** | Flytende AI-salgsassistent på butikken |
| **Nyheter og innhold** | Tjenestesider og HTML-blokker med AI-redigering |

Demomodus fungerer **uten API-nøkkel** — lokale maler brukes slik at du kan teste arbeidsflyten med én gang.

---

## Funksjoner

### Offentlig butikkfront
- Produktkatalog med søk, filtre, kategorier og sortering
- Produktsider med Schema.org Product, salg-merker, relaterte varer
- Handlekurv på sesjon (legg til, endre antall, fjern)
- Flerspråklig UI: **norsk** (standard), **engelsk**, **ukrainsk**, **russisk**, **svensk**, **litauisk** (`?lang=` + informasjonskapsel)
- Demo kundeinnlogging: telefon, Google og Apple OAuth
- Sporingside for pakker (Bring / Posten demo)
- Kontaktskjema, tjenestesider (levering, personvern, informasjonskapsler, egendefinerte sider)
- SEO-vertikalsider (mote, elektronikk, B2B osv.)
- Responsivt lyst tema, mobil burgermeny
- GDPR informasjonskapsel-banner

### Adminpanel
- **Dashboard** — produkter, kategorier, kurvstatistikk, diagrammer
- **Katalog** — produkter, dra-og-slipp kategorier, hurtighenvendelser
- **Innhold** — forsideseksjoner, AI-blokkbygger, tjenestesider, bunntekst og header
- **Design** — farger, produktkort, hurtigkjøp
- **Markedsføring** — global SEO, Schema.org, XML-sitemap, analysepiksler
- **Integrasjoner** — AI-assistent, offentlig chat, reCAPTCHA, kundeinnlogging, betalinger (PayPal, Stripe, Vipps, COD, Google/Apple Pay), Bring-sporing
- **Avansert** — vedlikeholdsmodus, GDPR, standardspråk, utviklerfeil
- **Koderedigerer** — HTML/JS med syntaksutheving
- Flerspråklig admin

### Produktside (`/site/`)
- Landingsside for Shop CMS
- Skjermbilder, versjonsinfo, teknisk stack, bestillingsskjema

---

## Teknisk stack

- PHP 8+ (uten rammeverk)
- JSON-lagring (`data/*.json`, `data/products.php` seed) — MySQL/PostgreSQL på forespørsel for produksjon
- Modulær i18n (`lang/*.php`)
- Apache `.htaccess`, kanoniske URL-er, hreflang, Schema.org, sitemap-indeks
- Font Awesome 6, vanlig CSS og JS
- CodeMirror i admin for HTML/JS-redigering

---

## Krav

- PHP 8.0 eller nyere
- Apache med `mod_rewrite` (eller nginx-tilsvarende)
- Skrivbar `data/`-mappe

---

## Installasjon

```bash
git clone https://github.com/Ruslan-Bilohash/shop.git shop
```

1. Kopier `shop/`-mappen til webroten (f.eks. `/shop/`).
2. Sett skriverettigheter på `data/`:
   ```bash
   chmod 755 data
   ```
3. Åpne `https://ditt-domene.no/shop/` — demoprodukter lastes fra seed-data.
4. Admin: `https://ditt-domene.no/shop/admin/` — endre påloggingsdata før produksjon.

### Lokal PHP-server

```bash
cd shop
php -S localhost:8080
```

Åpne http://localhost:8080/

### Konfigurasjon (`config.php`)

```php
define('SH_BASE_PATH', '/shop');
define('SH_SITE_NAME', 'Shop CMS');
define('SH_CURRENCY', 'NOK');
define('SH_DEMO_MODE', true);
```

---

## Prosjektstruktur

```
shop/
├── index.php              # Forside
├── search.php             # Katalogsøk
├── product.php            # Produktdetalj
├── cart.php / checkout.php
├── login.php              # Demo kundeinnlogging
├── contact.php / page.php
├── config.php / init.php
├── lang/                  # NO, EN, UA, RU, SV butikkfront-UI
├── includes/              # Header, SEO, kurv, AI, betalinger, blokker
├── assets/css|js/
├── data/                  # produkter, kategorier, innstillinger (JSON)
├── admin/                 # Fullt adminpanel
├── site/                  # Markedsføringslanding (NO, EN, UA, RU, SV, LT)
├── screenshot/            # Skjermbilder av admin og butikkfront
├── sitemap*.php
└── LICENSE
```

---

## Skjermbilder

| Skjerm | Fil |
|--------|-----|
| Admin dashboard | `screenshot/dashboard.jpg` |
| Produktkatalog | `screenshot/catalog_product.jpg` |
| Kategorier | `screenshot/catalog_categories.jpg` |
| Butikkinnstillinger | `screenshot/store_setting.jpg` |
| Forsideblokker | `screenshot/main_block.jpg` |
| Tjenesteside-redigerer | `screenshot/servise_page_editor.jpg` |
| Bunntekst-lenker | `screenshot/footer_link_editor.jpg` |
| Header-innstillinger | `screenshot/header_setting.jpg` |
| Utseende / farger | `screenshot/seting_color.jpg` |
| SEO og Schema | `screenshot/seo_schema.jpg` |
| Sitemap-generator | `screenshot/generate_schema_sitemap.jpg` |
| AI-chat-widget | `screenshot/integrations_chat.jpg` |
| AI-assistent | `screenshot/integrations_ai_assistant.jpg` |
| PayPal / Stripe / Vipps | `screenshot/integrations_paypal.jpg` |
| Bring-sporing | `screenshot/integrations_bring_posten_api.jpg` |
| Avanserte innstillinger | `screenshot/advanced_settings.jpg` |

---

## Demo-modus

- Gult demo-banner på alle sider
- Ingen ekte betalinger — kurv kun på sesjon
- Demoprodukter med Unsplash-bilder
- AI bruker lokale maler når ingen API-nøkkel er satt

---

## Forfatter og kommersiell lisens

**Ruslan Bilohash**
- Nettsted: https://bilohash.com/
- GitHub: https://github.com/Ruslan-Bilohash/
- E-post: rbilohash@gmail.com

For **kommersiell lisens**, full kildekode, tilpasset utvikling eller **nyeste produksjonsversjon**, kontakt forfatteren direkte. Se [LICENSE](LICENSE).