# Shop CMS

Universali **PHP e. prekybos sistema** bet kuriai internetinei parduotuvei — mada, elektronika, maistas, B2B katalogai, marketplaces ir kt. Daugiakalbis vitrinos frontendas, sesijos krepšelis, Schema.org SEO, įmontuota AI automatizacija ir pilna administravimo panelė. Portfelio projektas — [Ruslan Bilohash](https://bilohash.com/).

**Versija (šis repo):** 1.7.6 · **Readme kalbos:** [English](README.md) · [Norsk](README-no.md) · [Svenska](README-sv.md) · [Lietuvių](README-lt.md) · [Українська](README-uk.md)

![PHP](https://img.shields.io/badge/PHP-8%2B-777BB4?logo=php&logoColor=white)
![Version](https://img.shields.io/badge/version-1.7.6-blue)
![License](https://img.shields.io/badge/license-Proprietary-red)
![i18n](https://img.shields.io/badge/languages-NO%20%7C%20EN%20%7C%20UA%20%7C%20RU%20%7C%20SV%20%7C%20LT-green)
![AI](https://img.shields.io/badge/AI-įmontuota-purple)

---

## Svarbu — tik demo saugykla

> **Šiame GitHub saugykloje pateiktas Shop CMS demo / portfelio pavyzdys.**
> Tai **ne** pilnas komercinis produktas ir **ne** būtinai naujausia versija.
>
> **Naujausia pilna versija**, papildomi moduliai, atnaujinimai ir komercinė pagalba prieinami **tik pas autorių**:
> - Produkto puslapis: https://bilohash.com/shop/site/
> - Gyva demonstracija: https://bilohash.com/shop/
> - Kontaktas: **rbilohash@gmail.com**

**Komercinis naudojimas be raštiško autoriaus sutikimo draudžiamas.** Žr. [LICENSE](LICENSE).

---

## Gyva demonstracija

| Išteklius | URL |
|-----------|-----|
| **Parduotuvė** | https://bilohash.com/shop/ |
| **Administravimas** | https://bilohash.com/shop/admin/ |
| **Produktas / užsakymas** | https://bilohash.com/shop/site/ |
| **Sprendimai** | https://bilohash.com/shop/solutions.php |
| **Svetainės žemėlapis** | https://bilohash.com/shop/sitemap.php |

**Admin prisijungimas (demo):** `demo` / `demo2026`

---

## AI įmontuota iš karto

Shop CMS turi **įmontuotą AI automatizaciją** (Grok / OpenAI — API raktas admin panelėje):

| Sritis | Ką daro AI |
|--------|------------|
| **Produktai** | Generuoja pavadinimus, aprašymus, SEO ir akcentus iš trumpo prompt |
| **Kategorijos** | Siūlo pavadinimus, slug ir SEO tekstą |
| **Kalbos** | Verčia visus UI kalbų failus iš anglų |
| **SEO** | Generuoja meta pavadinimus, aprašymus ir raktažodžius |
| **Pagrindinis puslapis** | AI blokų konstruktorius — kontaktų formos, CTA, ikonų tinkleliai iš natūralios kalbos |
| **Viešas pokalbis** | Plaukiojantis AI pardavimų asistentas vitrinoje |
| **Naujienos ir turinys** | Paslaugų puslapiai ir HTML blokai su AI redagavimu |

Demo režimas veikia **be API rakto** — naudojami vietiniai šablonai, kad galėtumėte iš karto išbandyti darbo eigą.

---

## Funkcijos

### Vieša vitrina
- Produktų katalogas su paieška, filtrais, kategorijomis ir rūšiavimu
- Produktų puslapiai su Schema.org Product, išpardavimo ženklais, susijusiais produktais
- Sesijos krepšelis (pridėti, keisti kiekį, pašalinti)
- Daugiakalbis UI: **norvegų** (numatyta), **anglų**, **ukrainiečių**, **rusų**, **švedų**, **lietuvių** (`?lang=` + slapukas)
- Demo kliento prisijungimas: telefonas, Google ir Apple OAuth
- Siuntos sekimas (Bring / Posten demo)
- Kontaktų forma, paslaugų puslapiai (pristatymas, privatumas, slapukai, pasirinktiniai puslapiai)
- SEO vertikalės (mada, elektronika, B2B ir kt.)
- Adaptyvus šviesus dizainas, mobilus meniu
- GDPR slapukų banneris

### Administravimo panelė
- **Dashboard** — produktai, kategorijos, krepšelio statistika, diagramos
- **Katalogas** — produktai, kategorijų rikiavimas, greitos užklausos
- **Turinys** — pagrindinio puslapio blokai, AI konstruktorius, paslaugų puslapiai, poraštė ir antraštė
- **Dizainas** — spalvos, produktų kortelės, greitas pirkimas
- **Rinkodara** — globalus SEO, Schema.org, XML sitemap, analitika
- **Integracijos** — AI asistentas, viešas chat, reCAPTCHA, klientų prisijungimas, mokėjimai (PayPal, Stripe, Vipps, COD, Google/Apple Pay), Bring sekimas
- **Išplėstiniai** — priežiūros režimas, GDPR, numatyta kalba, kūrėjo klaidos
- **Kodo redaktorius** — HTML/JS su sintaksės paryškinimu
- Daugiakalbis admin

### Produkto svetainė (`/site/`)
- Shop CMS pristatymo puslapis
- Ekrano nuotraukos, versijos informacija, technologijų stack, užsakymo forma

---

## Technologijos

- PHP 8+ (be framework)
- JSON saugykla (`data/*.json`, `data/products.php` seed) — MySQL/PostgreSQL pagal užklausą produkcijai
- Modulinis i18n (`lang/*.php`)
- Apache `.htaccess`, kanoniniai URL, hreflang, Schema.org, sitemap indeksas
- Font Awesome 6, paprastas CSS ir JS
- CodeMirror admin panelėje HTML/JS redagavimui

---

## Reikalavimai

- PHP 8.0 ar naujesnė
- Apache su `mod_rewrite` (arba nginx atitikmuo)
- Rašomas `data/` katalogas

---

## Diegimas

```bash
git clone https://github.com/Ruslan-Bilohash/shop.git shop
```

1. Nukopijuokite `shop/` aplanką į web root (pvz. `/shop/`).
2. Nustatykite rašymo teises `data/`:
   ```bash
   chmod 755 data
   ```
3. Atidarykite `https://jusu-domenas.lt/shop/` — demo produktai įkeliami iš seed duomenų.
4. Admin: `https://jusu-domenas.lt/shop/admin/` — pakeiskite prisijungimo duomenis prieš produkciją.

### Vietinis PHP serveris

```bash
cd shop
php -S localhost:8080
```

Atidarykite http://localhost:8080/

### Konfigūracija (`config.php`)

```php
define('SH_BASE_PATH', '/shop');
define('SH_SITE_NAME', 'Shop CMS');
define('SH_CURRENCY', 'NOK');
define('SH_DEMO_MODE', true);
```

---

## Projekto struktūra

```
shop/
├── index.php              # Pagrindinis puslapis
├── search.php             # Katalogo paieška
├── product.php            # Produkto detalė
├── cart.php / checkout.php
├── login.php              # Demo kliento prisijungimas
├── contact.php / page.php
├── config.php / init.php
├── lang/                  # NO, EN, UA, RU, SV vitrinos UI
├── includes/              # Header, SEO, krepšelis, AI, mokėjimai, blokai
├── assets/css|js/
├── data/                  # produktai, kategorijos, nustatymai (JSON)
├── admin/                 # Pilna admin panelė
├── site/                  # Rinkodaros landing (NO, EN, UA, RU, SV, LT)
├── screenshot/            # Admin ir vitrinos ekrano nuotraukos
├── sitemap*.php
└── LICENSE
```

---

## Ekrano nuotraukos

### Dashboard ir katalogas

**Admin dashboard**

![Admin dashboard](screenshot/dashboard.jpg)

**Produktų katalogas**

![Produktų katalogas](screenshot/catalog_product.jpg)

**Kategorijos**

![Kategorijos](screenshot/catalog_categories.jpg)

### Parduotuvė ir dizainas

**Parduotuvės nustatymai**

![Parduotuvės nustatymai](screenshot/store_setting.jpg)

**Parduotuvės konfigūracija**

![Parduotuvės konfigūracija](screenshot/setting_shop.jpg)

**Išvaizda / spalvos**

![Išvaizda ir spalvos](screenshot/seting_color.jpg)

**Antraštės nustatymai**

![Antraštės nustatymai](screenshot/header_setting.jpg)

**Poraštės nuorodos**

![Poraštės nuorodos](screenshot/footer_link_editor.jpg)

### Turinys

**Pagrindinio puslapio blokai**

![Pagrindinio puslapio blokai](screenshot/main_block.jpg)

**Paslaugų puslapio redaktorius**

![Paslaugų puslapio redaktorius](screenshot/servise_page_editor.jpg)

![Paslaugų puslapio redaktorius — blokai](screenshot/servise_page_editor_2.jpg)

### SEO ir rinkodara

**SEO ir Schema.org**

![SEO ir Schema.org](screenshot/seo_schema.jpg)

**Sitemap generatorius**

![Sitemap generatorius](screenshot/generate_schema_sitemap.jpg)

### Integracijos — AI

**AI asistentas**

![AI asistentas](screenshot/integrations_ai_assistant.jpg)

![AI asistentas — nustatymai](screenshot/integrations_ai_assistant2.jpg)

**Viešas AI pokalbis**

![Viešas AI pokalbis](screenshot/integrations_chat.jpg)

![AI pokalbio dizainas](screenshot/integrations_chat_design.jpg)

**reCAPTCHA**

![reCAPTCHA integracija](screenshot/integrations_recapcha.jpg)

### Integracijos — mokėjimai

**PayPal**

![PayPal integracija](screenshot/integrations_paypal.jpg)

**Stripe**

![Stripe integracija](screenshot/integrations_stripe.jpg)

**Vipps**

![Vipps integracija](screenshot/integrations_vipps.jpg)

**Atsiskaitymas pristatymo metu**

![Atsiskaitymas pristatymo metu](screenshot/integrations_cash_on_delivery.jpg)

**Google Pay**

![Google Pay](screenshot/integrations_google_pay.jpg)

**Apple Pay**

![Apple Pay](screenshot/integrations_apple_pay.jpg)

### Integracijos — pristatymas

**Bring / Posten sekimas**

![Bring ir Posten API](screenshot/integrations_bring_posten_api.jpg)

### Išplėstiniai

**Išplėstiniai nustatymai**

![Išplėstiniai nustatymai](screenshot/advanced_settings.jpg)

---

## Demo režimas

- Geltona demo juosta visuose puslapiuose
- Jokių tikrų mokėjimų — krepšelis tik sesijoje
- Demo produktai su Unsplash vaizdais
- AI naudoja vietinius šablonus, kai API raktas nenustatytas

---

## Autorius ir komercinė licencija

**Ruslan Bilohash**
- Svetainė: https://bilohash.com/
- GitHub: https://github.com/Ruslan-Bilohash/
- El. paštas: rbilohash@gmail.com

**Komercinei licencijai**, pilnam kodui, individualiai plėtrai ar **naujausiai produkcinei versijai** kreipkitės tiesiogiai į autorių. Žr. [LICENSE](LICENSE).