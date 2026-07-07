<?php
/**
 * Payment setup guides — 4 languages (no, en, uk, ru)
 */
return [
    'en' => [
        'paypal' => [
            'title' => 'PayPal setup',
            'intro' => 'Connect PayPal REST API for card and PayPal wallet checkout. Use Sandbox first, then switch to Live.',
            'steps' => [
                'Create a Business account at PayPal and open the Developer Dashboard.',
                'Create a REST app (Sandbox) and copy Client ID and Secret.',
                'Set return URL to your shop checkout success page, e.g. /shop/checkout-success.php',
                'Paste Client ID and Secret here, enable the provider and save.',
                'Run a Sandbox test payment, then create a Live app and switch mode to Live.',
            ],
            'links' => [
                ['label' => 'PayPal Developer Dashboard', 'url' => 'https://developer.paypal.com/dashboard/'],
                ['label' => 'REST API docs', 'url' => 'https://developer.paypal.com/api/rest/'],
            ],
            'note' => 'Store secrets only on the server. Never expose Client Secret in frontend JavaScript.',
        ],
        'paysera' => [
            'title' => 'Paysera setup',
            'intro' => 'Paysera WebToPay redirects customers to paysera.com for cards and online banking (popular in Lithuania, Latvia, Estonia and EU).',
            'steps' => [
                'Create a Paysera account and open Projects → your checkout project.',
                'Copy Project ID and Sign password from project settings.',
                'Set callback URL to https://yourdomain.com/shop/api/paysera-callback.php',
                'Use test mode (sandbox.paysera.com) until checkout flow is verified.',
                'Enable Paysera here, save, and test with a small amount in sandbox.',
            ],
            'links' => [
                ['label' => 'Paysera Checkout', 'url' => 'https://www.paysera.com/en/checkout/'],
                ['label' => 'Paysera Developers', 'url' => 'https://developers.paysera.com/'],
            ],
            'note' => 'Amount is sent in cents (×100). Currency must match your Paysera project (EUR typical for Baltics).',
        ],
        'revolut' => [
            'title' => 'Revolut Merchant setup',
            'intro' => 'Revolut Merchant API creates a hosted checkout for cards and Revolut Pay. Ideal for EU / UK merchants.',
            'steps' => [
                'Open Revolut Business → Merchant and enable Merchant API.',
                'Generate Sandbox secret key (and optional public key).',
                'Add webhook https://yourdomain.com/shop/api/revolut-webhook.php with ORDER_COMPLETED event.',
                'Paste secret key and webhook signing secret here, enable Revolut.',
                'Run a sandbox payment, then switch mode to Production for go-live.',
            ],
            'links' => [
                ['label' => 'Revolut Merchant API docs', 'url' => 'https://developer.revolut.com/docs/merchant/merchant-api/'],
                ['label' => 'Revolut Business', 'url' => 'https://business.revolut.com/merchant'],
            ],
            'note' => 'Checkout redirects to Revolut hosted page. Set SH_DEMO_MODE to false to enable live payments.',
        ],
        'stripe' => [
            'title' => 'Stripe setup',
            'intro' => 'Stripe handles cards, Klarna and wallets. Google Pay and Apple Pay are enabled through Stripe when configured.',
            'steps' => [
                'Sign up at Stripe and complete business verification.',
                'In Developers → API keys copy Publishable key and Secret key (Test mode first).',
                'Create a webhook endpoint pointing to /shop/api/stripe-webhook.php and select checkout events.',
                'Copy the webhook signing secret and save all keys here.',
                'Enable Apple Pay / Google Pay in Stripe Dashboard → Payment methods after domain verification.',
            ],
            'links' => [
                ['label' => 'Stripe Dashboard', 'url' => 'https://dashboard.stripe.com/'],
                ['label' => 'API keys', 'url' => 'https://dashboard.stripe.com/apikeys'],
                ['label' => 'Webhooks', 'url' => 'https://dashboard.stripe.com/webhooks'],
            ],
            'note' => 'Use Test keys until checkout flow is verified. NOK is supported for Norwegian stores.',
        ],
        'vipps' => [
            'title' => 'Vipps MobilePay setup',
            'intro' => 'Vipps eCom API is the standard mobile payment method in Norway. Requires a Vipps MobilePay merchant agreement.',
            'steps' => [
                'Register at Vipps MobilePay Portal and order eCom API access for your business.',
                'Create API keys: Client ID, Client Secret, Subscription Key and MSN (Merchant Serial Number).',
                'Set callback URL to https://yourdomain.com/shop/api/vipps-callback.php',
                'Use test environment (apitest.vipps.no) for development, production for go-live.',
                'Paste all keys here, enable Vipps and test with the Vipps test app.',
            ],
            'links' => [
                ['label' => 'Vipps MobilePay Portal', 'url' => 'https://portal.vippsmobilepay.com/'],
                ['label' => 'eCom API documentation', 'url' => 'https://developer.vippsmobilepay.com/docs/APIs/epayment-api/'],
            ],
            'note' => 'Callback auth token should be a long random string shared between your server and Vipps configuration.',
        ],
        'google_pay' => [
            'title' => 'Google Pay setup',
            'intro' => 'Google Pay is usually enabled through your payment gateway (Stripe or PayPal). Requires a Google Pay Business Console merchant ID.',
            'steps' => [
                'Complete Stripe or PayPal setup first — Google Pay uses that gateway for charges.',
                'Register in Google Pay Business Console and create a merchant profile.',
                'Enable Google Pay in Stripe Dashboard (Payment methods) or PayPal wallet settings.',
                'Add your domain to allowed origins and verify HTTPS.',
                'Enter Google Pay Merchant ID here and enable the method on checkout.',
            ],
            'links' => [
                ['label' => 'Google Pay Business Console', 'url' => 'https://pay.google.com/business/console/'],
                ['label' => 'Stripe Google Pay', 'url' => 'https://stripe.com/docs/google-pay'],
            ],
            'note' => 'Google Pay button appears only on supported browsers (Chrome, Android) with a saved card.',
        ],
        'apple_pay' => [
            'title' => 'Apple Pay setup',
            'intro' => 'Apple Pay on the web requires domain verification, a Merchant ID and a payment processor (typically Stripe).',
            'steps' => [
                'Create a Merchant ID in Apple Developer account (Certificates, Identifiers & Profiles).',
                'Verify your shop domain in Stripe Dashboard → Apple Pay (download verification file to /.well-known/).',
                'Generate Apple Pay Payment Processing certificate and upload to Stripe.',
                'Enter Merchant ID (merchant.com.yourbrand) and domain here.',
                'Test on Safari (macOS/iOS) with a card in Wallet — not available on Windows/Android browsers.',
            ],
            'links' => [
                ['label' => 'Apple Developer', 'url' => 'https://developer.apple.com/account/'],
                ['label' => 'Stripe Apple Pay', 'url' => 'https://stripe.com/docs/apple-pay'],
            ],
            'note' => 'Apple Pay requires HTTPS and Safari. Pair with Stripe for full production checkout.',
        ],
    ],
    'no' => [
        'paypal' => [
            'title' => 'PayPal-oppsett',
            'intro' => 'Koble til PayPal REST API for kort og PayPal-lommebok. Bruk Sandbox først, deretter Live.',
            'steps' => [
                'Opprett Business-konto hos PayPal og åpne Developer Dashboard.',
                'Opprett REST-app (Sandbox) og kopier Client ID og Secret.',
                'Sett return URL til checkout success-side, f.eks. /shop/checkout-success.php',
                'Lim inn Client ID og Secret her, aktiver og lagre.',
                'Test i Sandbox, opprett Live-app og bytt modus til Live.',
            ],
            'links' => [
                ['label' => 'PayPal Developer Dashboard', 'url' => 'https://developer.paypal.com/dashboard/'],
                ['label' => 'REST API-dokumentasjon', 'url' => 'https://developer.paypal.com/api/rest/'],
            ],
            'note' => 'Lagre hemmeligheter kun på serveren. Aldri eksponer Client Secret i frontend.',
        ],
        'paysera' => [
            'title' => 'Paysera-oppsett',
            'intro' => 'Paysera WebToPay sender kunden til paysera.com for kort og nettbank (Baltikum og EU).',
            'steps' => [
                'Opprett Paysera-konto og åpne Prosjekter → checkout-prosjektet.',
                'Kopier Project ID og Sign password fra prosjektinnstillinger.',
                'Sett callback URL til https://dittdomene.no/shop/api/paysera-callback.php',
                'Bruk testmodus (sandbox.paysera.com) til checkout er verifisert.',
                'Aktiver Paysera her, lagre og test med lite beløp i sandbox.',
            ],
            'links' => [
                ['label' => 'Paysera Checkout', 'url' => 'https://www.paysera.com/en/checkout/'],
                ['label' => 'Paysera Developers', 'url' => 'https://developers.paysera.com/'],
            ],
            'note' => 'Beløp sendes i øre/cent (×100). Valuta må matche Paysera-prosjektet (ofte EUR).',
        ],
        'revolut' => [
            'title' => 'Revolut Merchant-oppsett',
            'intro' => 'Revolut Merchant API oppretter hosted checkout for kort og Revolut Pay. Passer for EU / UK.',
            'steps' => [
                'Åpne Revolut Business → Merchant og aktiver Merchant API.',
                'Generer Sandbox secret key (og valgfri public key).',
                'Legg til webhook https://dittdomene.no/shop/api/revolut-webhook.php med ORDER_COMPLETED.',
                'Lim inn secret key og webhook secret her, aktiver Revolut.',
                'Kjør sandbox-betaling, bytt til Production ved lansering.',
            ],
            'links' => [
                ['label' => 'Revolut Merchant API', 'url' => 'https://developer.revolut.com/docs/merchant/merchant-api/'],
                ['label' => 'Revolut Business', 'url' => 'https://business.revolut.com/merchant'],
            ],
            'note' => 'Checkout omdirigerer til Revolut. Sett SH_DEMO_MODE til false for live betaling.',
        ],
        'stripe' => [
            'title' => 'Stripe-oppsett',
            'intro' => 'Stripe håndterer kort, Klarna og lommebøker. Google Pay og Apple Pay aktiveres via Stripe.',
            'steps' => [
                'Registrer deg hos Stripe og fullfør bedriftsverifisering.',
                'Under Developers → API keys kopier Publishable key og Secret key (Test først).',
                'Opprett webhook til /shop/api/stripe-webhook.php med checkout-hendelser.',
                'Kopier webhook signing secret og lagre alle nøkler her.',
                'Aktiver Apple Pay / Google Pay under Payment methods etter domeneverifisering.',
            ],
            'links' => [
                ['label' => 'Stripe Dashboard', 'url' => 'https://dashboard.stripe.com/'],
                ['label' => 'API-nøkler', 'url' => 'https://dashboard.stripe.com/apikeys'],
                ['label' => 'Webhooks', 'url' => 'https://dashboard.stripe.com/webhooks'],
            ],
            'note' => 'Bruk Test-nøkler til checkout er verifisert. NOK støttes for norske butikker.',
        ],
        'vipps' => [
            'title' => 'Vipps MobilePay-oppsett',
            'intro' => 'Vipps eCom API er standard mobilbetaling i Norge. Krever avtale med Vipps MobilePay.',
            'steps' => [
                'Registrer deg i Vipps MobilePay Portal og bestill eCom API-tilgang.',
                'Opprett API-nøkler: Client ID, Client Secret, Subscription Key og MSN.',
                'Sett callback URL til https://dittdomene.no/shop/api/vipps-callback.php',
                'Bruk testmiljø (apitest.vipps.no) under utvikling, production ved lansering.',
                'Lim inn nøkler her, aktiver Vipps og test med Vipps test-app.',
            ],
            'links' => [
                ['label' => 'Vipps MobilePay Portal', 'url' => 'https://portal.vippsmobilepay.com/'],
                ['label' => 'eCom API-dokumentasjon', 'url' => 'https://developer.vippsmobilepay.com/docs/APIs/epayment-api/'],
            ],
            'note' => 'Callback auth token bør være en lang tilfeldig streng mellom server og Vipps.',
        ],
        'google_pay' => [
            'title' => 'Google Pay-oppsett',
            'intro' => 'Google Pay aktiveres vanligvis via betalingsgateway (Stripe eller PayPal). Krever Merchant ID fra Google Pay Business Console.',
            'steps' => [
                'Fullfør Stripe- eller PayPal-oppsett først.',
                'Registrer deg i Google Pay Business Console og opprett merchant-profil.',
                'Aktiver Google Pay i Stripe Dashboard eller PayPal-innstillinger.',
                'Legg til domenet i tillatte origins og verifiser HTTPS.',
                'Skriv inn Google Pay Merchant ID her og aktiver på checkout.',
            ],
            'links' => [
                ['label' => 'Google Pay Business Console', 'url' => 'https://pay.google.com/business/console/'],
                ['label' => 'Stripe Google Pay', 'url' => 'https://stripe.com/docs/google-pay'],
            ],
            'note' => 'Google Pay-knapp vises kun i støttede nettlesere (Chrome, Android) med lagret kort.',
        ],
        'apple_pay' => [
            'title' => 'Apple Pay-oppsett',
            'intro' => 'Apple Pay på web krever domeneverifisering, Merchant ID og betalingsprocessor (vanligvis Stripe).',
            'steps' => [
                'Opprett Merchant ID i Apple Developer (Certificates, Identifiers & Profiles).',
                'Verifiser butikkdomene i Stripe Dashboard → Apple Pay (fil i /.well-known/).',
                'Generer Apple Pay-sertifikat og last opp til Stripe.',
                'Skriv inn Merchant ID (merchant.com.merke) og domene her.',
                'Test i Safari (macOS/iOS) med kort i Wallet — ikke på Windows/Android.',
            ],
            'links' => [
                ['label' => 'Apple Developer', 'url' => 'https://developer.apple.com/account/'],
                ['label' => 'Stripe Apple Pay', 'url' => 'https://stripe.com/docs/apple-pay'],
            ],
            'note' => 'Apple Pay krever HTTPS og Safari. Kombiner med Stripe for produksjon.',
        ],
    ],
    'uk' => [
        'paypal' => [
            'title' => 'Налаштування PayPal',
            'intro' => 'Підключіть PayPal REST API для оплати карткою та через гаманець PayPal. Спочатку Sandbox, потім Live.',
            'steps' => [
                'Створіть Business-акаунт у PayPal і відкрийте Developer Dashboard.',
                'Створіть REST-додаток (Sandbox) і скопіюйте Client ID та Secret.',
                'Вкажіть return URL на сторінку успіху, напр. /shop/checkout-success.php',
                'Вставте Client ID і Secret тут, увімкніть провайдера та збережіть.',
                'Проведіть тест у Sandbox, створіть Live-додаток і перемкніть режим на Live.',
            ],
            'links' => [
                ['label' => 'PayPal Developer Dashboard', 'url' => 'https://developer.paypal.com/dashboard/'],
                ['label' => 'Документація REST API', 'url' => 'https://developer.paypal.com/api/rest/'],
            ],
            'note' => 'Секрети зберігайте лише на сервері. Ніколи не показуйте Client Secret у frontend.',
        ],
        'paysera' => [
            'title' => 'Налаштування Paysera',
            'intro' => 'Paysera WebToPay перенаправляє клієнта на paysera.com для карток та інтернет-банкінгу (Балтія та ЄС).',
            'steps' => [
                'Створіть акаунт Paysera і відкрийте Проєкти → checkout-проєкт.',
                'Скопіюйте Project ID та Sign password з налаштувань проєкту.',
                'Вкажіть callback URL: https://вашдомен/shop/api/paysera-callback.php',
                'Використовуйте тестовий режим (sandbox.paysera.com) до перевірки checkout.',
                'Увімкніть Paysera тут, збережіть і протестуйте в sandbox.',
            ],
            'links' => [
                ['label' => 'Paysera Checkout', 'url' => 'https://www.paysera.com/en/checkout/'],
                ['label' => 'Paysera Developers', 'url' => 'https://developers.paysera.com/'],
            ],
            'note' => 'Сума передається в копійках (×100). Валюта має збігатися з проєктом Paysera (зазвичай EUR).',
        ],
        'revolut' => [
            'title' => 'Налаштування Revolut Merchant',
            'intro' => 'Revolut Merchant API створює hosted checkout для карток і Revolut Pay. Підходить для ЄС / UK.',
            'steps' => [
                'Відкрийте Revolut Business → Merchant і увімкніть Merchant API.',
                'Згенеруйте Sandbox secret key (і опційно public key).',
                'Додайте webhook https://вашдомен/shop/api/revolut-webhook.php з подією ORDER_COMPLETED.',
                'Вставте secret key і webhook secret тут, увімкніть Revolut.',
                'Проведіть sandbox-платіж, потім перемкніть на Production.',
            ],
            'links' => [
                ['label' => 'Revolut Merchant API', 'url' => 'https://developer.revolut.com/docs/merchant/merchant-api/'],
                ['label' => 'Revolut Business', 'url' => 'https://business.revolut.com/merchant'],
            ],
            'note' => 'Checkout перенаправляє на сторінку Revolut. Вимкніть SH_DEMO_MODE для живої оплати.',
        ],
        'stripe' => [
            'title' => 'Налаштування Stripe',
            'intro' => 'Stripe обробляє картки, Klarna та гаманці. Google Pay і Apple Pay підключаються через Stripe.',
            'steps' => [
                'Зареєструйтесь у Stripe і пройдіть верифікацію бізнесу.',
                'У Developers → API keys скопіюйте Publishable key і Secret key (спочатку Test).',
                'Створіть webhook на /shop/api/stripe-webhook.php з подіями checkout.',
                'Скопіюйте webhook signing secret і збережіть усі ключі тут.',
                'Увімкніть Apple Pay / Google Pay у Stripe Dashboard після верифікації домену.',
            ],
            'links' => [
                ['label' => 'Stripe Dashboard', 'url' => 'https://dashboard.stripe.com/'],
                ['label' => 'API-ключі', 'url' => 'https://dashboard.stripe.com/apikeys'],
                ['label' => 'Webhooks', 'url' => 'https://dashboard.stripe.com/webhooks'],
            ],
            'note' => 'Використовуйте Test-ключі до перевірки checkout. NOK підтримується для норвезьких магазинів.',
        ],
        'vipps' => [
            'title' => 'Налаштування Vipps MobilePay',
            'intro' => 'Vipps eCom API — стандарт мобільних платежів у Норвегії. Потрібен договір з Vipps MobilePay.',
            'steps' => [
                'Зареєструйтесь у Vipps MobilePay Portal і замовте доступ до eCom API.',
                'Створіть ключі: Client ID, Client Secret, Subscription Key та MSN.',
                'Вкажіть callback URL: https://вашдомен/shop/api/vipps-callback.php',
                'Для розробки — test (apitest.vipps.no), для продакшену — production.',
                'Вставте ключі, увімкніть Vipps і протестуйте з тестовим додатком Vipps.',
            ],
            'links' => [
                ['label' => 'Vipps MobilePay Portal', 'url' => 'https://portal.vippsmobilepay.com/'],
                ['label' => 'Документація eCom API', 'url' => 'https://developer.vippsmobilepay.com/docs/APIs/epayment-api/'],
            ],
            'note' => 'Callback auth token — довгий випадковий рядок між вашим сервером і Vipps.',
        ],
        'google_pay' => [
            'title' => 'Налаштування Google Pay',
            'intro' => 'Google Pay зазвичай підключається через платіжний шлюз (Stripe або PayPal). Потрібен Merchant ID з Google Pay Business Console.',
            'steps' => [
                'Спочатку налаштуйте Stripe або PayPal.',
                'Зареєструйтесь у Google Pay Business Console і створіть merchant-профіль.',
                'Увімкніть Google Pay у Stripe Dashboard або налаштуваннях PayPal.',
                'Додайте домен до дозволених origins і перевірте HTTPS.',
                'Введіть Google Pay Merchant ID тут і увімкніть на checkout.',
            ],
            'links' => [
                ['label' => 'Google Pay Business Console', 'url' => 'https://pay.google.com/business/console/'],
                ['label' => 'Stripe Google Pay', 'url' => 'https://stripe.com/docs/google-pay'],
            ],
            'note' => 'Кнопка Google Pay з’являється лише у підтримуваних браузерах (Chrome, Android).',
        ],
        'apple_pay' => [
            'title' => 'Налаштування Apple Pay',
            'intro' => 'Apple Pay на веб потребує верифікації домену, Merchant ID і процесора (зазвичай Stripe).',
            'steps' => [
                'Створіть Merchant ID в Apple Developer (Certificates, Identifiers & Profiles).',
                'Верифікуйте домен у Stripe Dashboard → Apple Pay (файл у /.well-known/).',
                'Згенеруйте сертифікат Apple Pay і завантажте в Stripe.',
                'Введіть Merchant ID (merchant.com.brand) і домен тут.',
                'Тестуйте в Safari (macOS/iOS) з карткою в Wallet.',
            ],
            'links' => [
                ['label' => 'Apple Developer', 'url' => 'https://developer.apple.com/account/'],
                ['label' => 'Stripe Apple Pay', 'url' => 'https://stripe.com/docs/apple-pay'],
            ],
            'note' => 'Apple Pay потребує HTTPS і Safari. Для продакшену поєднуйте зі Stripe.',
        ],
    ],
    'ru' => [
        'paypal' => [
            'title' => 'Настройка PayPal',
            'intro' => 'Подключите PayPal REST API для оплаты картой и через кошелёк PayPal. Сначала Sandbox, затем Live.',
            'steps' => [
                'Создайте Business-аккаунт в PayPal и откройте Developer Dashboard.',
                'Создайте REST-приложение (Sandbox) и скопируйте Client ID и Secret.',
                'Укажите return URL на страницу успеха, напр. /shop/checkout-success.php',
                'Вставьте Client ID и Secret здесь, включите провайдера и сохраните.',
                'Проведите тест в Sandbox, создайте Live-приложение и переключите режим на Live.',
            ],
            'links' => [
                ['label' => 'PayPal Developer Dashboard', 'url' => 'https://developer.paypal.com/dashboard/'],
                ['label' => 'Документация REST API', 'url' => 'https://developer.paypal.com/api/rest/'],
            ],
            'note' => 'Секреты храните только на сервере. Никогда не показывайте Client Secret во frontend.',
        ],
        'paysera' => [
            'title' => 'Настройка Paysera',
            'intro' => 'Paysera WebToPay перенаправляет клиента на paysera.com для карт и интернет-банкинга (Балтия и ЕС).',
            'steps' => [
                'Создайте аккаунт Paysera и откройте Проекты → checkout-проект.',
                'Скопируйте Project ID и Sign password из настроек проекта.',
                'Укажите callback URL: https://вашдомен/shop/api/paysera-callback.php',
                'Используйте тестовый режим (sandbox.paysera.com) до проверки checkout.',
                'Включите Paysera здесь, сохраните и протестируйте в sandbox.',
            ],
            'links' => [
                ['label' => 'Paysera Checkout', 'url' => 'https://www.paysera.com/en/checkout/'],
                ['label' => 'Paysera Developers', 'url' => 'https://developers.paysera.com/'],
            ],
            'note' => 'Сумма передаётся в копейках (×100). Валюта должна совпадать с проектом Paysera (обычно EUR).',
        ],
        'revolut' => [
            'title' => 'Настройка Revolut Merchant',
            'intro' => 'Revolut Merchant API создаёт hosted checkout для карт и Revolut Pay. Подходит для ЕС / UK.',
            'steps' => [
                'Откройте Revolut Business → Merchant и включите Merchant API.',
                'Сгенерируйте Sandbox secret key (и опционально public key).',
                'Добавьте webhook https://вашдомен/shop/api/revolut-webhook.php с событием ORDER_COMPLETED.',
                'Вставьте secret key и webhook secret здесь, включите Revolut.',
                'Проведите sandbox-платёж, затем переключите на Production.',
            ],
            'links' => [
                ['label' => 'Revolut Merchant API', 'url' => 'https://developer.revolut.com/docs/merchant/merchant-api/'],
                ['label' => 'Revolut Business', 'url' => 'https://business.revolut.com/merchant'],
            ],
            'note' => 'Checkout перенаправляет на страницу Revolut. Отключите SH_DEMO_MODE для живой оплаты.',
        ],
        'stripe' => [
            'title' => 'Настройка Stripe',
            'intro' => 'Stripe обрабатывает карты, Klarna и кошельки. Google Pay и Apple Pay подключаются через Stripe.',
            'steps' => [
                'Зарегистрируйтесь в Stripe и пройдите верификацию бизнеса.',
                'В Developers → API keys скопируйте Publishable key и Secret key (сначала Test).',
                'Создайте webhook на /shop/api/stripe-webhook.php с событиями checkout.',
                'Скопируйте webhook signing secret и сохраните все ключи здесь.',
                'Включите Apple Pay / Google Pay в Stripe Dashboard после верификации домена.',
            ],
            'links' => [
                ['label' => 'Stripe Dashboard', 'url' => 'https://dashboard.stripe.com/'],
                ['label' => 'API-ключи', 'url' => 'https://dashboard.stripe.com/apikeys'],
                ['label' => 'Webhooks', 'url' => 'https://dashboard.stripe.com/webhooks'],
            ],
            'note' => 'Используйте Test-ключи до проверки checkout. NOK поддерживается для норвежских магазинов.',
        ],
        'vipps' => [
            'title' => 'Настройка Vipps MobilePay',
            'intro' => 'Vipps eCom API — стандарт мобильных платежей в Норвегии. Нужен договор с Vipps MobilePay.',
            'steps' => [
                'Зарегистрируйтесь в Vipps MobilePay Portal и закажите доступ к eCom API.',
                'Создайте ключи: Client ID, Client Secret, Subscription Key и MSN.',
                'Укажите callback URL: https://вашдомен/shop/api/vipps-callback.php',
                'Для разработки — test (apitest.vipps.no), для продакшена — production.',
                'Вставьте ключи, включите Vipps и протестируйте с тестовым приложением Vipps.',
            ],
            'links' => [
                ['label' => 'Vipps MobilePay Portal', 'url' => 'https://portal.vippsmobilepay.com/'],
                ['label' => 'Документация eCom API', 'url' => 'https://developer.vippsmobilepay.com/docs/APIs/epayment-api/'],
            ],
            'note' => 'Callback auth token — длинная случайная строка между вашим сервером и Vipps.',
        ],
        'google_pay' => [
            'title' => 'Настройка Google Pay',
            'intro' => 'Google Pay обычно подключается через платёжный шлюз (Stripe или PayPal). Нужен Merchant ID из Google Pay Business Console.',
            'steps' => [
                'Сначала настройте Stripe или PayPal.',
                'Зарегистрируйтесь в Google Pay Business Console и создайте merchant-профиль.',
                'Включите Google Pay в Stripe Dashboard или настройках PayPal.',
                'Добавьте домен в разрешённые origins и проверьте HTTPS.',
                'Введите Google Pay Merchant ID здесь и включите на checkout.',
            ],
            'links' => [
                ['label' => 'Google Pay Business Console', 'url' => 'https://pay.google.com/business/console/'],
                ['label' => 'Stripe Google Pay', 'url' => 'https://stripe.com/docs/google-pay'],
            ],
            'note' => 'Кнопка Google Pay появляется только в поддерживаемых браузерах (Chrome, Android).',
        ],
        'apple_pay' => [
            'title' => 'Настройка Apple Pay',
            'intro' => 'Apple Pay на веб требует верификации домена, Merchant ID и процессора (обычно Stripe).',
            'steps' => [
                'Создайте Merchant ID в Apple Developer (Certificates, Identifiers & Profiles).',
                'Верифицируйте домен в Stripe Dashboard → Apple Pay (файл в /.well-known/).',
                'Сгенерируйте сертификат Apple Pay и загрузите в Stripe.',
                'Введите Merchant ID (merchant.com.brand) и домен здесь.',
                'Тестируйте в Safari (macOS/iOS) с картой в Wallet.',
            ],
            'links' => [
                ['label' => 'Apple Developer', 'url' => 'https://developer.apple.com/account/'],
                ['label' => 'Stripe Apple Pay', 'url' => 'https://stripe.com/docs/apple-pay'],
            ],
            'note' => 'Apple Pay требует HTTPS и Safari. Для продакшена сочетайте со Stripe.',
        ],
    ],
];