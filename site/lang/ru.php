<?php
$en = require __DIR__ . '/en.php';
return array_replace_recursive($en, [
    'meta' => [
        'title'       => 'Shop CMS — Заказать интернет-магазин {for_country} | PHP e-commerce',
        'description' => 'Заказать разработку интернет-магазина {in_country}. PHP скрипт: каталог, корзина, Stripe PayPal Vipps наложенный платёж, категории, админ, Schema.org SEO. Демо в {currency}.',
    ],
    'nav' => ['order' => 'Заказать разработку', 'features' => 'Возможности', 'tech' => 'Технологии', 'demo' => 'Живое демо', 'seo' => 'SEO', 'version' => 'Версия', 'contact' => 'Контакт', 'menu' => 'Меню', 'main_nav' => 'Главная навигация'],
    'a11y' => ['menu' => 'Меню'],
    'hero' => [
        'badge' => 'PHP e-commerce · Shop CMS · %s',
        'title' => 'Заказать разработку интернет-магазина',
        'subtitle' => 'Универсальный PHP e-commerce из {origin} {for_market} — мода, электроника, еда, B2B. Stripe · PayPal · Vipps · наложенный платёж. Демо-цены в {currency}.',
        'cta_order' => 'Заказать магазин',
        'cta_demo' => 'Живое демо', 'cta_admin' => 'Демо админки', 'cta_contact' => 'Связаться с разработчиком',
    ],
    'pitch' => [
        'title' => 'Разработка интернет-магазина на Shop CMS',
        'text' => 'Shop CMS — проверенная PHP e-commerce основа. Настраиваем демо-витрину, checkout, категории, дизайн и SEO под вашу нишу {in_country}.',
        'cta_order' => 'Заказать разработку', 'cta_demo' => 'Попробовать демо',
        'items' => [
            ['icon' => 'paint-brush', 'title' => 'Брендинг и UX', 'desc' => 'Ваш логотип, цвета, поля товаров и категории — не шаблон.'],
            ['icon' => 'credit-card', 'title' => 'Оплата и доставка', 'desc' => 'Stripe, PayPal, Vipps, наложенный платёж — для России и Европы.'],
            ['icon' => 'language', 'title' => 'Мультиязычное SEO', 'desc' => 'hreflang, Schema.org Product, sitemap, PageSpeed 99+ и AI-перевод в админке.'],
            ['icon' => 'server', 'title' => 'Деплой и поддержка', 'desc' => 'JSON-демо или MySQL на shared hosting / VPS — white-label по запросу.'],
        ],
    ],
    'intro' => ['title' => 'Один скрипт — много моделей магазина', 'text' => 'Изучите live demo, выберите нишу и закажите PHP-магазин под ваш рынок.', 'use_label' => 'Идеально для:'],
    'faq' => [
        'title' => 'FAQ — заказ Shop CMS',
        'items' => [
            ['q' => 'Как заказать интернет-магазин {in_country}?', 'a' => 'Откройте страницу заказа или форму контакта с нишей, языками и платёжными системами.'],
            ['q' => 'Shop CMS только для Норвегии?', 'a' => 'Скрипт создан в Норвегии, но мы разворачиваем для России, Украины и Европы.'],
            ['q' => 'Можно начать с live demo?', 'a' => 'Да. Демо на /shop/ показывает каталог, корзину, админ и checkout.'],
            ['q' => 'Какая версия в продакшене?', 'a' => 'Текущий релиз — v1.3.0 в админке, на продуктовом сайте и в демо-пакете.'],
        ],
    ],
    'cta' => ['title' => 'Готовы заказать интернет-магазин?', 'text' => 'Опишите нишу {in_country} — мода, электроника, еда, B2B. Адаптируем Shop CMS и запустим на вашем домене.', 'btn' => 'Заказать разработку'],
    'tech' => ['items' => [
        'PHP 8+ без фреймворков — shared hosting или VPS в Норвегии/ЕС',
        'Продакшен-скрипт демо — JSON без MySQL. Реальные проекты клиентов — MySQL/PostgreSQL по желанию',
        'Модульный i18n с файлами локалей и переключением через cookie',
        'SEO: canonical, hreflang, sitemap, robots.txt, Schema.org Product',
        'Платежи: Stripe, PayPal, Vipps, наложенный платёж — в админке',
        'Лёгкий адаптивный UI с Font Awesome 6 — без сборки',
    ]],
    'version' => ['older_versions' => 'Предыдущие версии (%d)', 'script_note' => 'Та же версия в админке и на продуктовом сайте. Демо на JSON без MySQL. Проекты клиентов — MySQL/PostgreSQL по запросу.'],
    'cookies_banner' => [
        'title' => 'Мы используем cookies',
        'text' => 'Shop CMS использует cookies сессии, языка и опциональной аналитики.',
        'privacy' => 'Политика конфиденциальности', 'more' => 'Политика cookies',
        'accept' => 'Принять все', 'reject' => 'Отклонить', 'settings' => 'Настройки',
        'warning' => 'Без cookies часть функций может не работать.',
        'accept_again' => 'Принять cookies',
        'modal_title' => 'Настройки cookies', 'modal_text' => 'Необходимые cookies (корзина, язык) нужны для демо.',
        'modal_cancel' => 'Отмена', 'modal_save' => 'Сохранить',
    ],
    'footer' => [
        'demo_link' => 'Живое демо магазина', 'order' => 'Заказать разработку', 'order_page' => 'Заказать магазин', 'news' => 'Новости запуска',
        'tagline' => 'White-label PHP e-commerce платформа из Норвегии. Запускайте магазины моды, электроники, еды и B2B для России и Европы.',
        'copyright' => '© %s Shop CMS · %s',
    ],
]);