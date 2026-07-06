# Shop CMS

Universali **PHP e. prekybos sistema** bet kuriai internetinei parduotuvei — mada, elektronika, maistas, B2B katalogai, marketplaces ir kt. Daugiakalbis vitrinos frontendas, sesijos krepšelis, Schema.org SEO, įmontuota AI automatizacija ir pilna administravimo panelė. Portfelio projektas — [Ruslan Bilohash](https://bilohash.com/).

**Versija (šis repo):** 1.3.0 · **Readme kalbos:** [English](README.md) · [Norsk](README-no.md) · [Svenska](README-sv.md) · [Lietuvių](README-lt.md) · [Українська](README-uk.md)

![PHP](https://img.shields.io/badge/PHP-8%2B-777BB4?logo=php&logoColor=white)
![Version](https://img.shields.io/badge/version-1.3.0-blue)
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
| **Pagrindinis puslapis** | AI blokų konstruktorius — kontaktų formos, CTA, ikonų tinkleliai |
| **Viešas pokalbis** | Plaukiojantis AI pardavimų asistentas vitrinoje |
| **Naujienos ir turinys** | Paslaugų puslapiai ir HTML blokai su AI redagavimu |

Demo režimas veikia **be API rakto** — naudojami vietiniai šablonai.

---

## Funkcijos

### Vieša vitrina
- Produktų katalogas su paieška, filtrais, kategorijomis ir rūšiavimu
- Produktų puslapiai su Schema.org Product, išpardavimo ženklais
- Sesijos krepšelis (pridėti, keisti kiekį, pašalinti)
- Daugiakalbis UI: **norvegų** (numatyta), **anglų**, **ukrainiečių**, **rusų**, **švedų**, **lietuvių** (`?lang=` + slapukas)
- Demo kliento prisijungimas: telefonas, Google ir Apple OAuth
- Siuntos sekimas (Bring / Posten demo)
- Kontaktų forma, paslaugų puslapiai, SEO vertikalės
- Adaptyvus šviesus dizainas, mobilus meniu
- GDPR slapukų banneris

### Administravimo panelė
- **Dashboard** — produktai, kategorijos, krepšelio statistika
- **Katalogas** — produktai, kategorijų rikiavimas, greitos užklausos
- **Turinys** — pagrindinio puslapio blokai, AI konstruktorius, paslaugų puslapiai
- **Dizainas** — spalvos, produktų kortelės, greitas pirkimas
- **Rinkodara** — globalus SEO, Schema.org, XML sitemap, analitika
- **Integracijos** — AI asistentas, viešas chat, reCAPTCHA, klientų prisijungimas, mokėjimai (PayPal, Stripe, Vipps, COD, Google/Apple Pay), Bring sekimas
- **Išplėstiniai** — priežiūros režimas, GDPR, numatyta kalba, kūrėjo klaidos
- **Kodo redaktorius** — HTML/JS su sintaksės paryškinimu
- Daugiakalbis admin

### Produkto svetainė (`/site/`)
- Shop CMS pristatymo puslapis su ekrano nuotraukomis

---

## Technologijos

- PHP 8+ (be framework)
- JSON saugykla — MySQL/PostgreSQL pagal užklausą
- Modulinis i18n (`lang/*.php`)
- Apache `.htaccess`, canonical, hreflang, Schema.org, sitemap
- Font Awesome 6, paprastas CSS ir JS

---

## Reikalavimai

- PHP 8.0 ar naujesnė
- Apache su `mod_rewrite`
- Rašomas `data/` katalogas

---

## Diegimas

```bash
git clone https://github.com/Ruslan-Bilohash/shop.git shop
chmod 755 data
```

Atidarykite `https://jusu-domenas.lt/shop/` — demo produktai įkeliami automatiškai.

### Vietinis serveris

```bash
cd shop && php -S localhost:8080
```

---

## Ekrano nuotraukos

Žr. aplanką `screenshot/` — dashboard, katalogas, AI integracijos, mokėjimai, SEO ir kt. Pilnas sąrašas [README.md](README.md#screenshots).

---

## Demo režimas

- Geltona demo juosta visuose puslapiuose
- Jokių tikrų mokėjimų — krepšelis tik sesijoje
- Demo produktai su Unsplash vaizdais
- AI naudoja vietinius šablonus be API rakto

---

## Autorius ir licencija

**Ruslan Bilohash** — https://bilohash.com/ · rbilohash@gmail.com

**Komercinei licencijai**, pilnam kodui ar **naujausiai produkcinei versijai** kreipkitės į autorių. Žr. [LICENSE](LICENSE).