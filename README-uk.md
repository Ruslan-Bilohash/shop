# Shop CMS

Універсальний **PHP e-commerce скрипт** для будь-якого інтернет-магазину — мода, електроніка, їжа, B2B-каталоги, маркетплейси та інше. Багатомовна вітрина, кошик на сесії, Schema.org SEO, **AI-автоматизація з коробки** та повна адмін-панель. Портфоліо-проєкт [Ruslan Bilohash](https://bilohash.com/).

**Версія (цей repo):** 1.3.0 · **Мови readme:** [English](README.md) · [Norsk](README-no.md) · [Svenska](README-sv.md) · [Lietuvių](README-lt.md) · [Українська](README-uk.md)

![PHP](https://img.shields.io/badge/PHP-8%2B-777BB4?logo=php&logoColor=white)
![Version](https://img.shields.io/badge/version-1.3.0-blue)
![License](https://img.shields.io/badge/license-Proprietary-red)
![i18n](https://img.shields.io/badge/languages-NO%20%7C%20EN%20%7C%20UA%20%7C%20RU%20%7C%20SV%20%7C%20LT-green)
![AI](https://img.shields.io/badge/AI-з%20коробки-purple)

---

## Важливо — лише демо-репозиторій

> **На GitHub завантажено лише демо / портфоліо-приклад Shop CMS.**
> Це **не** повна комерційна версія і **не** обовʼязково останній реліз.
>
> **Останню повну версію**, розширені модулі, оновлення та комерційну підтримку можна **придбати у автора**:
> - Сторінка продукту: https://bilohash.com/shop/site/
> - Живе демо: https://bilohash.com/shop/
> - Контакт: **rbilohash@gmail.com**

**Комерційне використання без письмової згоди автора заборонено.** Див. [LICENSE](LICENSE).

---

## Живе демо

| Ресурс | URL |
|--------|-----|
| **Вітрина** | https://bilohash.com/shop/ |
| **Адмін-панель** | https://bilohash.com/shop/admin/ |
| **Замовити / продукт** | https://bilohash.com/shop/site/ |
| **Рішення** | https://bilohash.com/shop/solutions.php |
| **Sitemap** | https://bilohash.com/shop/sitemap.php |

**Вхід в адмінку (демо):** `demo` / `demo2026`

---

## AI з коробки

Shop CMS має **вбудовану AI-автоматизацію** (Grok / OpenAI — API-ключ у адмінці):

| Область | Що робить AI |
|---------|----------------|
| **Товари** | Генерація назв, описів, SEO та переваг з короткого запиту |
| **Категорії** | Пропозиції назв, slug і SEO-текстів |
| **Мови** | Переклад UI-файлів з англійської |
| **SEO** | Meta title, description, keywords |
| **Головна** | Конструктор блоків — форми, CTA, сітки з іконками за описом |
| **Публічний чат** | AI-консультант на вітрині |
| **Новини та контент** | Сервісні сторінки та HTML-блоки з AI-редагуванням |

У демо-режимі **без API-ключа** — застосовуються локальні шаблони для ознайомлення.

---

## Можливості

### Публічна вітрина
- Каталог з пошуком, фільтрами, категоріями та сортуванням
- Картки товарів, Schema.org Product, знижки, супутні товари
- Кошик на PHP-сесії
- Мови: **норвезька** (за замовч.), **англійська**, **українська**, **російська**, **шведська**, **литовська** (`?lang=` + cookie)
- Вхід покупця (демо): телефон, Google і Apple OAuth
- Відстеження посилок (Bring / Posten)
- Контакт, сервісні сторінки, SEO-вертикалі
- Адаптивний світлий дизайн, мобільне меню
- GDPR банер cookies

### Адмін-панель
- **Dashboard** — статистика, діаграми категорій
- **Каталог** — товари, drag-sort категорій, швидкі заявки
- **Контент** — блоки головної, AI-конструктор, сервісні сторінки, футер і шапка
- **Дизайн** — кольори, картка товару, швидка покупка
- **Маркетинг** — SEO, Schema.org, sitemap, аналітика
- **Інтеграції** — AI, чат, reCAPTCHA, вхід покупця, платежі (PayPal, Stripe, Vipps, COD, Google/Apple Pay), Bring
- **Розширені** — режим розробки, GDPR, головна мова, помилки PHP
- **Редактор коду** — HTML/JS з підсвіткою синтаксису
- Багатомовна адмінка

### Маркетинговий сайт (`/site/`)
- Лендінг Shop CMS зі скріншотами та формою замовлення

---

## Стек

- PHP 8+ (без фреймворків)
- JSON — MySQL/PostgreSQL за запитом для продакшену
- i18n (`lang/*.php`)
- Apache, canonical, hreflang, Schema.org, sitemap
- Font Awesome 6, vanilla CSS/JS

---

## Вимоги

- PHP 8.0+
- Apache з `mod_rewrite`
- Запис у `data/`

---

## Встановлення

```bash
git clone https://github.com/Ruslan-Bilohash/shop.git shop
chmod 755 data
```

Відкрийте `https://ваш-домен/shop/` — демо-товари завантажаться автоматично.

### Локальний сервер

```bash
cd shop && php -S localhost:8080
```

---

## Скріншоти

Папка `screenshot/` — dashboard, каталог, AI, платежі, SEO та інше. Повний список у [README.md](README.md#screenshots).

| Екран | Файл |
|-------|------|
| Адмін dashboard | `screenshot/dashboard.jpg` |
| Товари | `screenshot/catalog_product.jpg` |
| Категорії | `screenshot/catalog_categories.jpg` |
| AI-асистент | `screenshot/integrations_ai_assistant.jpg` |
| Конструктор блоків | `screenshot/main_block.jpg` |

---

## Демо-режим

- Жовта смуга «демо» на всіх сторінках
- Без реальних платежів
- Демо-товари з Unsplash
- AI працює з локальними шаблонами без ключа

---

## Автор і ліцензія

**Ruslan Bilohash**
- Сайт: https://bilohash.com/
- GitHub: https://github.com/Ruslan-Bilohash/
- Email: rbilohash@gmail.com

Для **комерційної ліцензії**, повного коду або **останньої версії** — звертайтесь до автора. [LICENSE](LICENSE).