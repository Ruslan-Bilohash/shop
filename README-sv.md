# Shop CMS

Universellt **PHP e-handelsskript** för alla typer av webbutiker — mode, elektronik, mat, B2B-kataloger, marknadsplatser med mera. Flerspråkig butik, sessionskundvagn, Schema.org SEO, inbyggd AI-automatisering och fullt adminpanel. Portfolio-projekt av [Ruslan Bilohash](https://bilohash.com/).

**Version (detta repo):** 1.3.0 · **Readme-språk:** [English](README.md) · [Norsk](README-no.md) · [Svenska](README-sv.md) · [Lietuvių](README-lt.md) · [Українська](README-uk.md)

![PHP](https://img.shields.io/badge/PHP-8%2B-777BB4?logo=php&logoColor=white)
![Version](https://img.shields.io/badge/version-1.3.0-blue)
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
- Kontaktformulär, servicesidor, SEO-vertikalsidor
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
- Landningssida för Shop CMS med skärmdumpar och beställningsformulär

---

## Teknisk stack

- PHP 8+ (utan ramverk)
- JSON-lagring — MySQL/PostgreSQL på begäran för produktion
- Modulär i18n (`lang/*.php`)
- Apache `.htaccess`, canonical, hreflang, Schema.org, sitemap
- Font Awesome 6, vanlig CSS och JS

---

## Krav

- PHP 8.0 eller nyare
- Apache med `mod_rewrite`
- Skrivbar `data/`-katalog

---

## Installation

```bash
git clone https://github.com/Ruslan-Bilohash/shop.git shop
chmod 755 data
```

Öppna `https://din-domän.se/shop/` — demoprodukter laddas automatiskt.

### Lokal server

```bash
cd shop && php -S localhost:8080
```

---

## Skärmdumpar

Se mappen `screenshot/` — dashboard, katalog, AI-integrationer, betalningar, SEO med mera. Full lista i [README.md](README.md#screenshots).

---

## Demoläge

- Gult demo-band på alla sidor
- Inga riktiga betalningar — kundvagn endast i session
- Demoprodukter med Unsplash-bilder
- AI använder lokala mallar utan API-nyckel

---

## Författare och licens

**Ruslan Bilohash** — https://bilohash.com/ · rbilohash@gmail.com

För **kommersiell licens**, full källkod eller **senaste produktionsversionen**, kontakta författaren. Se [LICENSE](LICENSE).