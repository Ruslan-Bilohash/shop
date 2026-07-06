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
- Kontaktskjema, tjenestesider, SEO-vertikalsider
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
- Landingsside for Shop CMS med skjermbilder og bestillingsskjema

---

## Teknisk stack

- PHP 8+ (uten rammeverk)
- JSON-lagring — MySQL/PostgreSQL på forespørsel for produksjon
- Modulær i18n (`lang/*.php`)
- Apache `.htaccess`, canonical, hreflang, Schema.org, sitemap
- Font Awesome 6, vanlig CSS og JS

---

## Krav

- PHP 8.0 eller nyere
- Apache med `mod_rewrite`
- Skrivbar `data/`-mappe

---

## Installasjon

```bash
git clone https://github.com/Ruslan-Bilohash/shop.git shop
chmod 755 data
```

Åpne `https://ditt-domene.no/shop/` — demoprodukter lastes automatisk.

### Lokal server

```bash
cd shop && php -S localhost:8080
```

---

## Skjermbilder

Se `screenshot/`-mappen — dashboard, katalog, AI-integrasjoner, betalinger, SEO og mer. Full liste i [README.md](README.md#screenshots).

---

## Demo-modus

- Gult demo-banner på alle sider
- Ingen ekte betalinger — kurv kun på sesjon
- Demoprodukter med Unsplash-bilder
- AI bruker lokale maler uten API-nøkkel

---

## Forfatter og lisens

**Ruslan Bilohash** — https://bilohash.com/ · rbilohash@gmail.com

For **kommersiell lisens**, full kildekode eller **nyeste produksjonsversjon**, kontakt forfatteren. Se [LICENSE](LICENSE).