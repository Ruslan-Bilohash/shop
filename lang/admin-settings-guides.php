<?php
/**
 * Client-friendly setup guides for Shop CMS settings tabs.
 */
$en = [
    'store' => [
        'intro' => 'Core storefront settings: open or close the shop and currency.',
        'guide' => [
            'title' => 'Store setup guide',
            'intro' => 'Configure store availability and how prices are displayed.',
            'steps' => [
                'Turn on «Store open» when the catalog is ready for visitors.',
                'Pick the primary currency (NOK, UAH, EUR, etc.) — prices in products use this format.',
                'Product cards and quick purchase are under Design → Appearance.',
                'Parcel tracking is under Integrations → Parcel tracking.',
            ],
            'links' => [],
            'note' => 'While the store is closed, visitors see a maintenance page — admin panel stays available.',
        ],
        'sections' => [
            'store-status' => 'Store availability',
            'store-locale' => 'Default language',
            'store-developer' => 'Developer mode',
            'store-currency' => 'Currency & prices',
        ],
        'hints' => [
            'site_currency' => 'ISO 4217 code shown in Schema.org and checkout (e.g. NOK, UAH, EUR).',
            'currency_symbol' => 'Displayed after the amount: kr, ₴, € — match local convention.',
            'card_excerpt_len' => 'Short description length on category and search pages.',
        ],
    ],
    'smtp' => [
        'intro' => 'Outgoing mail (Hostinger SMTP) and newsletter subscribe form on the storefront.',
        'guide' => [
            'title' => 'SMTP & newsletter setup',
            'intro' => 'Configure SMTP so subscribe confirmations and admin notifications are delivered.',
            'steps' => [
                'Enable SMTP and enter host, port, username and password from your hosting panel.',
                'Set From email and name — usually the same mailbox as SMTP username.',
                'Enable newsletter and set admin notify email for new subscribers.',
                'Optional: welcome subject and body sent to new subscribers.',
                'View subscribers under Catalog → Subscribers.',
            ],
            'links' => [],
            'note' => 'If SMTP is off, subscribe still saves emails — welcome mail is skipped.',
        ],
        'sections' => [
            'smtp-connection' => 'SMTP server',
            'newsletter' => 'Newsletter',
        ],
        'hints' => [
            'smtp_host' => 'e.g. smtp.hostinger.com — from your hosting email settings.',
            'smtp_password' => 'Leave blank to keep the current password.',
            'newsletter_notify_email' => 'You receive an email when someone clicks Subscribe in the footer.',
        ],
    ],
    'taxes' => [
        'intro' => 'VAT and sales tax for Norway, Lithuania, Ukraine, Sweden, Poland and UK.',
        'guide' => [
            'title' => 'Tax setup guide',
            'intro' => 'Pick your country preset, set the standard rate and whether catalog prices include tax.',
            'steps' => [
                'Enable tax and choose country (NO, LT, UA, SE, PL, GB).',
                'Set inclusive prices if your product prices already contain VAT (typical B2C).',
                'Use exclusive if prices are net — tax is added in cart and checkout.',
                'Optional: enter VAT/org number shown on checkout summary.',
            ],
            'links' => [],
            'note' => 'Standard rate only — reduced rates per product category can be added in a future version.',
        ],
        'sections' => [
            'taxes-enable' => 'Tax display',
            'taxes-country' => 'Country & rate',
        ],
        'hints' => [
            'tax_country' => 'Preset with default standard VAT rate for the selected country.',
            'tax_rate' => 'Override the preset if you use a reduced rate or local exception.',
            'tax_mode' => 'Inclusive = prices in admin already include tax. Exclusive = tax added at checkout.',
            'tax_custom_label' => 'Leave empty to use MVA / VAT / ПДВ / Moms automatically.',
            'tax_business_id' => 'Shown on checkout for invoices (org.nr, NIP, VAT number, etc.).',
        ],
    ],
    'analytics' => [
        'intro' => 'Google Analytics 4 and Meta Pixel for marketing attribution on the public storefront.',
        'guide' => [
            'title' => 'Analytics setup',
            'intro' => 'Paste tracking IDs — snippets load automatically in the site footer after save.',
            'steps' => [
                'Create a GA4 property and copy the G-XXXXXXXX measurement ID.',
                'Create a Meta Pixel in Events Manager and copy the numeric Pixel ID.',
                'Paste both IDs here and save.',
                'Verify events in GA Realtime and Meta Test Events after publishing.',
            ],
            'links' => [
                ['label' => 'Google Analytics', 'url' => 'https://analytics.google.com/'],
                ['label' => 'Meta Business Suite', 'url' => 'https://business.facebook.com/'],
            ],
            'note' => 'Tracking loads only on the public storefront — not in the admin panel.',
        ],
        'sections' => ['analytics-tracking' => 'Analytics & pixels'],
        'hints' => [
            'tracking_gtag_id' => 'Paste G-XXXXXXXX from Google Analytics 4 property.',
            'tracking_meta_pixel' => 'Numeric Pixel ID from Meta Events Manager.',
        ],
    ],
    'advanced' => [
        'intro' => 'Development mode, GDPR cookies and custom HTML/JS for the storefront.',
        'guide' => [
            'title' => 'Advanced setup',
            'intro' => 'Fine-tune maintenance behaviour and inject your own verification tags or scripts.',
            'steps' => [
                'Keep cookie consent enabled for EU/Norway GDPR compliance.',
                'Use Developer → Show PHP errors only during development.',
                'Edit custom <head> HTML and footer JS in the section below (syntax highlighting).',
                'Edit llms.txt, robots.txt and data/custom.php in Admin → Code editor.',
            ],
            'links' => [],
            'note' => 'Maintenance mode shows a “under development” page — admin stays available when allowed.',
        ],
        'sections' => [
            'advanced-maintenance' => 'Development & GDPR',
            'advanced-custom-code' => 'Custom HTML & JavaScript',
        ],
        'hints' => [],
    ],
    'seo' => [
        'intro' => 'Help Google and social networks understand your shop. Global meta tags, Open Graph image and structured data (Schema.org) for products and categories.',
        'guide' => [
            'title' => 'SEO & Schema guide',
            'intro' => 'Good SEO brings organic traffic. Product-level SEO is edited per product; here you set site-wide defaults.',
            'steps' => [
                'Set Site name and Organization — they appear in titles and JSON-LD.',
                'Upload a default OG image (1200×630 px) for link previews on Facebook, LinkedIn, Telegram.',
                'Enable Product, Breadcrumb and ItemList schema — recommended for e-commerce.',
                'Turn on XML sitemap and click Regenerate after adding many products.',
                'Submit sitemap URL in Google Search Console.',
            ],
            'links' => [
                ['label' => 'Google Search Console', 'url' => 'https://search.google.com/search-console'],
                ['label' => 'Schema.org Product', 'url' => 'https://schema.org/Product'],
                ['label' => 'Open Graph protocol', 'url' => 'https://ogp.me/'],
            ],
            'note' => 'Per-product meta titles and descriptions are in Products → Edit → SEO tab.',
        ],
        'sections' => [
            'seo-global' => 'Global meta & brand',
            'seo-schema' => 'Structured data (Schema.org)',
            'seo-sitemap' => 'XML sitemap',
        ],
        'hints' => [
            'seo_site_name' => 'Brand name in <title> suffix and WebSite schema.',
            'seo_default_og_image' => 'Fallback image when a product has no photo. Use HTTPS absolute URL.',
            'seo_geo_region' => 'ISO region for geo meta (NO, UA, SE…).',
            'seo_twitter_site' => '@handle for Twitter/X cards (optional).',
            'sitemap_priority_product' => '0.0–1.0 — relative importance for product URLs in sitemap.',
        ],
    ],
    'seo_analysis' => [
        'intro' => 'Audit product SEO scores and page-level meta in one place. Filter weak listings and open the editor with one click.',
        'guide' => [
            'title' => 'SEO analysis',
            'intro' => 'Use filters to find products missing meta titles, descriptions or schema. The pages checklist covers global settings, categories and service pages.',
            'steps' => [
                'Start with products scored below 75 — fix meta title (30–60 chars) and description (120–160 chars) per language.',
                'Filter by issue type (missing title, description length, schema) to batch similar fixes.',
                'Review the pages checklist — global SEO, categories and service pages.',
                'After bulk fixes, regenerate the XML sitemap under SEO & Schema.',
            ],
            'links' => [
                ['label' => 'Google Search Console', 'url' => 'https://search.google.com/search-console'],
            ],
            'note' => 'Product SEO checklist also appears in Products → Edit on the right sidebar.',
        ],
        'sections' => [
            'seo-analysis-products' => 'Products to optimize',
            'seo-analysis-pages' => 'SEO pages checklist',
        ],
        'hints' => [],
    ],
    'recaptcha' => [
        'intro' => 'Protect contact and lead forms from bots with Google reCAPTCHA v2 checkbox.',
        'guide' => [
            'title' => 'reCAPTCHA setup',
            'intro' => 'Adds «I\'m not a robot» to public forms without annoying real customers.',
            'steps' => [
                'Open Google reCAPTCHA Admin and register your domain.',
                'Choose reCAPTCHA v2 → «I\'m not a robot» Checkbox.',
                'Copy Site key (public) and Secret key (server only).',
                'Paste keys here, enable reCAPTCHA and save.',
                'Test the contact form on mobile and desktop.',
            ],
            'links' => [
                ['label' => 'reCAPTCHA Admin', 'url' => 'https://www.google.com/recaptcha/admin'],
                ['label' => 'reCAPTCHA docs', 'url' => 'https://developers.google.com/recaptcha/docs/display'],
            ],
            'note' => 'Never put the Secret key in frontend code — it stays in the database on the server.',
        ],
        'sections' => ['recaptcha-main' => 'reCAPTCHA keys'],
        'hints' => [
            'recaptcha_site_key' => 'Public key — safe to use in HTML widget.',
            'recaptcha_secret_key' => 'Private key — used only when verifying submissions on the server.',
        ],
    ],
    'customer_auth' => [
        'intro' => 'Buyer sign-in on the storefront: phone number, Google and Apple OAuth — with demo shortcuts when demo mode is on.',
        'guide' => [
            'title' => 'Customer sign-in',
            'intro' => 'Enable login.php for buyers. In demo mode, Google and Apple buttons work without OAuth keys.',
            'steps' => [
                'Enable customer sign-in and pick phone / Google / Apple methods.',
                'Paste Google OAuth Client ID and Apple Services ID for production.',
                'Open login.php and test phone and demo OAuth buttons.',
                'Link appears in the header when sign-in is enabled.',
            ],
            'links' => [
                ['label' => 'Google Cloud Console', 'url' => 'https://console.cloud.google.com/apis/credentials'],
                ['label' => 'Apple Developer', 'url' => 'https://developer.apple.com/account/resources/identifiers/list/serviceId'],
            ],
            'note' => 'Demo mode shows Google/Apple sign-in even when toggles are off — no real OAuth redirect.',
        ],
        'sections' => [
            'customer-auth-main' => 'Sign-in methods',
            'customer-auth-google' => 'Google API',
            'customer-auth-apple' => 'Apple API',
        ],
        'hints' => [
            'customer_google_client_id' => 'OAuth 2.0 Client ID — Web application type in Google Cloud.',
            'customer_google_client_secret' => 'Client secret from the same OAuth client. Leave blank to keep current value.',
            'customer_google_redirect_uri' => 'Copy into Google Console → Authorized redirect URIs.',
            'customer_apple_client_id' => 'Services ID identifier (Sign in with Apple).',
            'customer_apple_private_key' => 'Contents of the .p8 key file. Leave blank to keep current key.',
            'customer_apple_redirect_uri' => 'Add as Return URL in Apple Developer → Services ID.',
        ],
    ],
    'block_builder' => [
        'intro' => 'AI template constructor for small homepage or service-page blocks — forms, CTAs, icon grids.',
        'guide' => [
            'title' => 'Design generator',
            'intro' => 'Describe a block in plain language, preview the HTML, edit and attach to the homepage or a service page.',
            'steps' => [
                'Write a prompt (e.g. blue contact form with icons) and click Generate.',
                'Review the live preview and tweak title, subtitle and HTML per language.',
                'Choose attachment: Homepage or a service page, then Save templates.',
                'Reorder built-in homepage sections under Content → Homepage if needed.',
            ],
            'links' => [],
            'note' => 'Templates sync to homepage blocks automatically when attached to Homepage.',
        ],
        'sections' => [
            'block-builder-generate' => 'Design generator',
            'block-builder-new' => 'Block editor',
            'block-builder-saved' => 'Saved templates',
        ],
        'hints' => [],
    ],
    'chat' => [
        'intro' => 'Floating AI chat on the public storefront — separate from the admin product AI assistant.',
        'guide' => [
            'title' => 'Public chat widget',
            'intro' => 'Enable the bubble visitors see on every page. Configure provider, instructions and button appearance.',
            'steps' => [
                'Turn on «Enable chat widget on public site».',
                'Pick Grok or OpenAI and paste the API key.',
                'Write system instructions: languages, shipping, payments, demo notice.',
                'Set button colour and Font Awesome icon (e.g. comments, headset).',
                'Open the storefront and test the bubble after saving.',
            ],
            'links' => [
                ['label' => 'xAI Docs', 'url' => 'https://docs.x.ai/'],
                ['label' => 'Font Awesome icons', 'url' => 'https://fontawesome.com/search?o=r&m=free&f=classic'],
            ],
            'note' => 'Independent from Settings → AI assistant (product descriptions in the editor).',
        ],
        'sections' => [
            'chat-main' => 'Chat connection',
            'chat-widget' => 'Widget appearance',
        ],
        'hints' => [
            'chat_api_key' => 'xai-… for Grok or sk-… for OpenAI. Leave blank to keep current key.',
            'chat_instructions' => 'System prompt: languages, delivery countries, payment methods, support email.',
            'chat_model' => 'Override chat model here, or leave empty to use Settings → AI → Chat model, then default.',
            'chat_widget_color' => 'Accent colour for the floating button. Empty uses the primary theme colour.',
            'chat_widget_icon' => 'Font Awesome icon name without fa- prefix: comments, headset, robot, message.',
        ],
    ],
    'ai' => [
        'intro' => 'AI assistant for shop owners: per-context models and prompts for products, chat, news, SEO and UI translation.',
        'guide' => [
            'title' => 'Admin AI assistant',
            'steps' => [
                'Enable AI and select provider (OpenAI or xAI Grok).',
                'Paste API key and set the default model.',
                'Optionally assign models per context: product, chat, news, SEO.',
                'Customize prompts for product, news and site/category SEO.',
                'Products → Edit → «Generate with AI»; Languages → «Translate UI with AI».',
            ],
            'links' => [
                ['label' => 'xAI Docs', 'url' => 'https://docs.x.ai/'],
            ],
            'note' => 'Review AI text before publishing. Empty context model = default model fallback.',
        ],
        'sections' => [
            'ai-connection' => 'API connection',
            'ai-models' => 'Models by context',
            'ai-prompts' => 'Prompts',
            'ai-instructions' => 'Setup guide',
        ],
        'hints' => [
            'ai_api_key' => 'Stored server-side. Leave empty to keep the current key.',
            'ai_source_lang' => 'Language of the product name you type before clicking Generate.',
            'ai_prompt_product' => 'Optional custom prompt. Placeholders: {product_name}, {category}, {source_lang}.',
            'ai_prompt_news' => 'News/article JSON prompt. Placeholders: {topic}, {source_lang}.',
            'ai_prompt_seo' => 'Site or category SEO prompt. Placeholders: {task_type}, {target_name}, {slug}, {country_code}, {source_lang}.',
        ],
    ],
    'news_page' => [
        'intro' => 'Store news and release notes — multilingual articles with SEO, cover image and NewsArticle schema on the public storefront.',
        'guide' => [
            'title' => 'News articles guide',
            'steps' => [
                'Catalog → News → Add article — set slug, publish date and cover image URL.',
                'Fill title, excerpt and HTML body for each enabled language (or use «Generate with AI»).',
                'Enable Active and optionally Featured for the listing page.',
                'Open SEO & Schema tab — meta title, description 120–160 chars, keywords, OG image.',
                'Preview on /news.php and share news-article.php?slug=… links in footer or marketing.',
            ],
            'links' => [
                ['label' => 'Public news listing', 'url' => 'https://bilohash.com/shop/news.php'],
            ],
            'note' => 'AI news uses the model from Settings → AI → News. Empty per-context model falls back to the default model.',
        ],
        'sections' => [
            'news-content' => 'Article content',
            'news-status' => 'Publication',
            'news-seo' => 'SEO & Schema',
        ],
        'hints' => [
            'slug' => 'Lowercase URL segment — e.g. shop-cms-v1-3-6-release.',
            'published_at' => 'ISO date shown in schema datePublished and on the listing.',
            'image' => '1200×630 recommended for Open Graph and NewsArticle image.',
        ],
    ],
    'footer' => [
        'intro' => 'Footer columns «Shop» and «Legal» — links to delivery, payment, privacy, cookies and custom pages.',
        'guide' => [
            'title' => 'Footer links guide',
            'intro' => 'E-commerce sites need clear legal and service links in every language.',
            'steps' => [
                'Edit service page content under Settings → Service pages first.',
                'Add footer links with relative URLs: page.php?slug=delivery.',
                'Fill link labels for NO, EN, UA, RU, SV.',
                'Mark external links (https://…) when pointing outside the shop.',
                'Preview the storefront footer on mobile — links should wrap cleanly.',
            ],
            'links' => [
                ['label' => 'GDPR footer checklist', 'url' => 'https://gdpr.eu/checklist/'],
            ],
            'note' => 'Recommended legal links: Delivery, Payment, Privacy policy, Cookies.',
        ],
        'sections' => [
            'footer-shop' => 'Shop column',
            'footer-legal' => 'Legal column',
        ],
        'hints' => [
            'footer_link_url' => 'Relative: search.php, page.php?slug=privacy — or full https:// URL if external.',
            'footer_link_id' => 'Internal ID for analytics — lowercase, no spaces.',
        ],
    ],
    'appearance' => [
        'intro' => 'Brand colors, typography, surfaces and background for the public storefront.',
        'guide' => [
            'title' => 'Appearance guide',
            'steps' => [
                'Set primary and button colors — used for CTAs, links and accents.',
                'Pick text, card and header/footer surface colors for a cohesive look.',
                'Adjust border radius and font family to match your brand.',
                'Optional: background color or full-page background image URL.',
                'Preview on mobile — contrast should stay readable.',
            ],
            'links' => [],
            'note' => 'Changes apply after save — hard-refresh the storefront (Ctrl+F5).',
        ],
        'sections' => [
            'appearance-colors' => 'Colors & background',
            'appearance-buttons' => 'Button colors',
            'appearance-typography' => 'Typography & radius',
            'appearance-surfaces' => 'Text & surfaces',
            'appearance-product-card' => 'Product cards',
            'appearance-quick-buy' => 'Quick purchase (hot leads)',
        ],
        'hints' => [
            'color_primary' => 'Main brand color — WCAG contrast 4.5:1 with white text is recommended.',
            'color_btn_search' => 'Search form submit button on homepage and catalog.',
            'color_btn_cart' => 'Cart link hover/active color in the header.',
            'color_btn_outline' => 'Sign-in, admin and other outline buttons in the header.',
            'bg_image' => 'Optional full-page background image URL (HTTPS).',
            'design_font_family' => 'CSS font-family stack, e.g. \'Inter\', system-ui, sans-serif.',
            'design_border_radius' => 'Corner radius in pixels for cards, buttons and inputs (0–24).',
            'design_text_color' => 'Main body text color on the storefront.',
            'design_sale_color' => 'Sale badge and promo accent color.',
        ],
    ],
    'pages' => [
        'intro' => 'Multilingual service pages: delivery, payment, privacy, cookies and custom legal pages — with built-in rich text editor.',
        'guide' => [
            'title' => 'Service pages guide',
            'steps' => [
                'Edit built-in pages or add a new slug (e.g. returns, warranty).',
                'Use the rich editor for headings, lists and links — no external API.',
                'Fill all active languages and set page visibility.',
                'Add footer links to page.php?slug=your-page.',
            ],
            'links' => [],
            'note' => 'URL format: /shop/page.php?slug=privacy&lang=uk',
        ],
        'sections' => ['pages-list' => 'All service pages'],
        'hints' => [
            'page_content_editor_hint' => 'Bold, lists, links and quotes — saved as safe HTML.',
        ],
    ],
    'header' => [
        'intro' => 'Manage storefront header menu items — add, edit, delete or hide links.',
        'guide' => [
            'title' => 'Menu items',
            'steps' => [
                'Add a row with URL and labels, or edit existing items.',
                'Use the trash icon to remove a menu item.',
                'Toggle Customer sign-in in the Header actions section.',
                'Save and preview the storefront header.',
            ],
            'links' => [],
            'note' => 'Shop and Categories are fixed. Cart and language switcher stay in the header bar.',
        ],
        'sections' => ['header-nav' => 'Menu items', 'header-actions' => 'Header actions'],
        'hints' => [],
    ],
    'posten' => [
        'intro' => 'Bring/Posten parcel tracking API for track.php on the storefront.',
        'guide' => [
            'title' => 'Parcel tracking',
            'steps' => [
                'Enable tracking and demo mode for development.',
                'Paste Bring Client ID and API key for production.',
                'Open track.php and test with a demo tracking number.',
            ],
            'links' => [
                ['label' => 'Bring Developer', 'url' => 'https://developer.bring.com/'],
            ],
            'note' => 'Demo mode shows a sample timeline without a real API call.',
        ],
        'sections' => ['posten-main' => 'Bring / Posten API'],
        'hints' => [
            'posten_client_id' => 'Bring Tracking API Client ID.',
            'posten_api_key' => 'API key from Bring — leave blank to keep current value.',
        ],
    ],
    'nova_poshta' => [
        'intro' => 'Nova Poshta (Ukraine) API: parcel tracking, checkout warehouse picker, and sender branch.',
        'guide' => [
            'title' => 'Nova Poshta setup',
            'steps' => [
                'Enable integration and demo mode while developing.',
                'Get an API key from novaposhta.ua → Business → API and click «Test API connection».',
                'Pick sender city and warehouse — saved for checkout and future label API.',
                'Enable tracking and open track-np.php with a TTN number (11–14 digits).',
                'Optional: enable warehouse picker on checkout for Ukrainian delivery.',
            ],
            'links' => [
                ['label' => 'Nova Poshta API docs', 'url' => 'https://developers.novaposhta.ua/'],
            ],
            'note' => 'Without an API key, demo mode returns sample cities, warehouses and tracking timeline.',
        ],
        'sections' => [
            'nova-poshta-main' => 'Nova Poshta API',
            'nova-poshta-sender' => 'Sender warehouse',
        ],
        'hints' => [
            'nova_poshta_api_key' => 'API key from business account — leave blank to keep current value.',
            'nova_poshta_sender_phone' => 'Sender phone helps TrackingDocument API return full status.',
        ],
    ],
    'languages' => [
        'intro' => 'Add or disable storefront languages. Use AI translate to generate lang/*.php from English.',
        'guide' => [
            'title' => 'Multilingual setup',
            'steps' => [
                'Enable languages your customers need (NO, EN, UA, RU, SV…).',
                'Fill locale and HTML lang code for SEO hreflang.',
                'Translate products per language in the product editor.',
                'Languages → «Translate UI with AI» after adding a new language code.',
            ],
            'links' => [],
            'note' => 'At least one language must stay active.',
        ],
        'sections' => ['languages-list' => 'Active languages'],
        'hints' => [
            'lang_locale' => 'e.g. uk-UA, nb-NO — used in Open Graph locale meta.',
        ],
    ],
];

$uk = [
    'store' => [
        'intro' => 'Основні налаштування вітрини: відкрити/закрити магазин та валюта.',
        'guide' => [
            'title' => 'Налаштування магазину',
            'intro' => 'Доступність магазину та формат цін.',
            'steps' => [
                'Увімкніть «Магазин увімкнено», коли каталог готовий для відвідувачів.',
                'Оберіть основну валюту (NOK, UAH, EUR тощо).',
                'Картка товару та швидка покупка — у Дизайн → Оформлення.',
                'Відстеження посилок — у Інтеграції → Відстеження.',
            ],
            'links' => [],
            'note' => 'Поки магазин закритий, відвідувачі бачать сторінку «на розробці».',
        ],
        'sections' => [
            'store-status' => 'Доступність магазину',
            'store-locale' => 'Головна мова',
            'store-developer' => 'Режим розробника',
            'store-currency' => 'Валюта та ціни',
        ],
        'hints' => [
            'site_currency' => 'Код ISO 4217 для Schema.org та checkout (NOK, UAH, EUR).',
            'currency_symbol' => 'Символ після суми: kr, ₴, €.',
            'card_excerpt_len' => 'Довжина короткого опису в каталозі.',
        ],
    ],
    'smtp' => [
        'intro' => 'Вихідна пошта (SMTP Hostinger) та форма «Підписатись» на вітрині.',
        'guide' => [
            'title' => 'SMTP та розсилка',
            'intro' => 'Налаштуйте SMTP для підтверджень підписки та сповіщень адміну.',
            'steps' => [
                'Увімкніть SMTP і вкажіть host, port, логін і пароль з панелі хостингу.',
                'From email та імʼя — зазвичай той самий поштовий скринька.',
                'Увімкніть розсилку та email для сповіщень про нових підписників.',
                'За бажанням: тема та текст вітального листа.',
                'Підписники — Каталог → Підписники.',
            ],
            'links' => [],
            'note' => 'Без SMTP підписка все одно зберігається — вітальний лист не надсилається.',
        ],
        'sections' => [
            'smtp-connection' => 'SMTP-сервер',
            'newsletter' => 'Розсилка',
        ],
        'hints' => [
            'smtp_host' => 'напр. smtp.hostinger.com — з налаштувань пошти хостингу.',
            'smtp_password' => 'Порожньо — залишити поточний пароль.',
            'newsletter_notify_email' => 'Лист при натисканні «Підписатись» у футері.',
        ],
    ],
    'taxes' => [
        'intro' => 'ПДВ та податки для Норвегії, Литви, України, Швеції, Польщі та Великобританії.',
        'guide' => [
            'title' => 'Налаштування податків',
            'intro' => 'Оберіть країну, стандартну ставку та чи ціни в каталозі вже включають податок.',
            'steps' => [
                'Увімкніть податок і оберіть країну (NO, LT, UA, SE, PL, GB).',
                'Inclusive — ціни в адмінці вже з ПДВ (типово B2C).',
                'Exclusive — ціни без податку, ПДВ додається в кошику.',
                'За бажанням: ЄДРПОУ / VAT / org.nr для підсумку замовлення.',
            ],
            'links' => [],
            'note' => 'Поки лише стандартна ставка — знижені ставки по категоріях можна додати пізніше.',
        ],
        'sections' => [
            'taxes-enable' => 'Відображення податку',
            'taxes-country' => 'Країна та ставка',
        ],
        'hints' => [
            'tax_country' => 'Пресет зі стандартною ставкою ПДВ для обраної країни.',
            'tax_rate' => 'Змініть пресет, якщо потрібна інша ставка.',
            'tax_mode' => 'Inclusive = ціни з податком. Exclusive = податок додається при оформленні.',
            'tax_custom_label' => 'Порожньо — автоматично MVA / VAT / ПДВ / Moms.',
            'tax_business_id' => 'Показується при checkout (org.nr, NIP, VAT тощо).',
        ],
    ],
    'analytics' => [
        'intro' => 'Google Analytics 4 та Meta Pixel для маркетингової аналітики на публічній вітрині.',
        'guide' => [
            'title' => 'Налаштування аналітики',
            'intro' => 'Вставте ID відстеження — скрипти підключаться автоматично у футері після збереження.',
            'steps' => [
                'Створіть властивість GA4 і скопіюйте G-XXXXXXXX.',
                'Створіть Meta Pixel у Events Manager і скопіюйте числовий ID.',
                'Вставте обидва ID сюди та збережіть.',
                'Перевірте події в GA Realtime та Meta Test Events.',
            ],
            'links' => [
                ['label' => 'Google Analytics', 'url' => 'https://analytics.google.com/'],
                ['label' => 'Meta Business Suite', 'url' => 'https://business.facebook.com/'],
            ],
            'note' => 'Аналітика працює лише на публічній вітрині — не в адмін-панелі.',
        ],
        'sections' => ['analytics-tracking' => 'Аналітика та пікселі'],
        'hints' => [
            'tracking_gtag_id' => 'G-XXXXXXXX з Google Analytics 4.',
            'tracking_meta_pixel' => 'ID пікселя з Meta Events Manager.',
        ],
    ],
    'advanced' => [
        'intro' => 'Режим розробки, GDPR cookies та власний HTML/JS для вітрини.',
        'guide' => [
            'title' => 'Загальні налаштування',
            'intro' => 'Тонке налаштування доступності магазину та вставки коду.',
            'steps' => [
                'Залиште банер cookies увімкненим для GDPR.',
                'Увімкніть показ PHP-помилок лише під час розробки.',
                'HTML у <head> та JS у футері — у секції нижче з підсвіткою синтаксису.',
                'llms.txt, robots.txt та data/custom.php — у меню «Редактор коду».',
            ],
            'links' => [],
            'note' => 'Режим «на розробці» показує заглушку — адмінка доступна за потреби.',
        ],
        'sections' => [
            'advanced-maintenance' => 'Розробка та GDPR',
            'advanced-custom-code' => 'Власний HTML та JavaScript',
        ],
        'hints' => [],
    ],
    'seo' => [
        'intro' => 'Допоможіть Google і соцмережам зрозуміти ваш магазин: глобальні meta-теги, OG-зображення та Schema.org для товарів.',
        'guide' => [
            'title' => 'SEO та Schema',
            'intro' => 'SEO приносить органічний трафік. Для кожного товару — окрема вкладка SEO в редакторі.',
            'steps' => [
                'Вкажіть назву сайту та організації.',
                'Додайте OG-зображення 1200×630 px для превʼю посилань.',
                'Увімкніть Schema Product, Breadcrumbs, ItemList.',
                'Увімкніть sitemap і натисніть «Оновити» після змін каталогу.',
                'Додайте sitemap у Google Search Console.',
            ],
            'links' => [
                ['label' => 'Google Search Console', 'url' => 'https://search.google.com/search-console'],
                ['label' => 'Schema.org Product', 'url' => 'https://schema.org/Product'],
            ],
            'note' => 'Meta title/description для товарів — в Товари → Редагувати → SEO.',
        ],
        'sections' => [
            'seo-global' => 'Глобальні meta та бренд',
            'seo-schema' => 'Структуровані дані',
            'seo-sitemap' => 'XML sitemap',
        ],
        'hints' => [
            'seo_site_name' => 'Назва бренду в <title> та WebSite schema.',
            'seo_default_og_image' => 'Зображення за замовчуванням для соцмереж (HTTPS URL).',
            'seo_geo_region' => 'Регіон ISO: NO, UA, SE…',
            'seo_twitter_site' => '@нік для Twitter/X (необовʼязково).',
        ],
    ],
    'seo_analysis' => [
        'intro' => 'Аудит SEO товарів і meta сторінок в одному місці. Фільтруйте слабкі позиції та відкривайте редактор одним кліком.',
        'guide' => [
            'title' => 'Аналіз SEO',
            'intro' => 'Фільтри показують товари без meta title, description або schema. Чеклист сторінок охоплює глобальні налаштування, категорії та сервісні сторінки.',
            'steps' => [
                'Почніть з товарів з балом нижче 75 — meta title 30–60 символів, description 120–160 для кожної мови.',
                'Фільтр за типом проблеми — групуйте однакові правки.',
                'Перегляньте чеклист сторінок: глобальні SEO, категорії, сервісні сторінки.',
                'Після масових змін оновіть XML sitemap у SEO та Schema.',
            ],
            'links' => [
                ['label' => 'Google Search Console', 'url' => 'https://search.google.com/search-console'],
            ],
            'note' => 'Чеклист SEO товару також є в Товари → Редагувати (панель справа).',
        ],
        'sections' => [
            'seo-analysis-products' => 'Товари для оптимізації',
            'seo-analysis-pages' => 'Чеклист SEO сторінок',
        ],
        'hints' => [],
    ],
    'recaptcha' => [
        'intro' => 'Захист контактних форм від ботів через Google reCAPTCHA v2.',
        'guide' => [
            'title' => 'Налаштування reCAPTCHA',
            'intro' => 'Чекбокс «Я не робот» на публічних формах.',
            'steps' => [
                'Зареєструйте домен у Google reCAPTCHA Admin.',
                'Оберіть v2 → Checkbox.',
                'Скопіюйте Site key та Secret key.',
                'Увімкніть і збережіть.',
                'Перевірте форму контакту на телефоні.',
            ],
            'links' => [
                ['label' => 'reCAPTCHA Admin', 'url' => 'https://www.google.com/recaptcha/admin'],
            ],
            'note' => 'Secret key лише на сервері — ніколи у фронтенді.',
        ],
        'sections' => ['recaptcha-main' => 'Ключі reCAPTCHA'],
        'hints' => [
            'recaptcha_site_key' => 'Публічний ключ для HTML-віджета.',
            'recaptcha_secret_key' => 'Приватний ключ для перевірки на сервері.',
        ],
    ],
    'customer_auth' => [
        'intro' => 'Вхід покупця на вітрині: телефон, Google і Apple OAuth — з демо-кнопками у демо-режимі.',
        'guide' => [
            'title' => 'Вхід покупця',
            'intro' => 'Увімкніть login.php для покупців. У демо-режимі Google і Apple працюють без OAuth-ключів.',
            'steps' => [
                'Увімкніть вхід покупця та оберіть телефон / Google / Apple.',
                'Вставте Google OAuth Client ID та Apple Services ID для продакшену.',
                'Відкрийте login.php і перевірте телефон та демо OAuth.',
                'Посилання зʼявляється в шапці, коли вхід увімкнено.',
            ],
            'links' => [
                ['label' => 'Google Cloud Console', 'url' => 'https://console.cloud.google.com/apis/credentials'],
                ['label' => 'Apple Developer', 'url' => 'https://developer.apple.com/account/resources/identifiers/list/serviceId'],
            ],
            'note' => 'У демо-режимі Google/Apple показуються навіть без перемикачів — без реального OAuth.',
        ],
        'sections' => [
            'customer-auth-main' => 'Способи входу',
            'customer-auth-google' => 'Google API',
            'customer-auth-apple' => 'Apple API',
        ],
        'hints' => [
            'customer_google_client_id' => 'OAuth 2.0 Client ID — тип Web application у Google Cloud.',
            'customer_google_client_secret' => 'Client secret того ж OAuth-клієнта. Залиште порожнім, щоб зберегти.',
            'customer_google_redirect_uri' => 'Скопіюйте в Google Console → Authorized redirect URIs.',
            'customer_apple_client_id' => 'Ідентифікатор Services ID (Sign in with Apple).',
            'customer_apple_private_key' => 'Вміст .p8-ключа. Залиште порожнім, щоб зберегти поточний.',
            'customer_apple_redirect_uri' => 'Додайте як Return URL у Apple Developer → Services ID.',
        ],
    ],
    'block_builder' => [
        'intro' => 'AI-конструктор невеликих блоків для головної або сервісних сторінок — форми, CTA, сітки з іконками.',
        'guide' => [
            'title' => 'Генератор дизайну',
            'intro' => 'Опишіть блок простою мовою, перегляньте HTML, відредагуйте та підключіть на головну або сторінку.',
            'steps' => [
                'Напишіть запит (напр. синя форма зворотного звʼязку з іконками) і натисніть Згенерувати.',
                'Перевірте превʼю та змініть заголовок, підзаголовок і HTML для кожної мови.',
                'Оберіть підключення: Головна або сервісна сторінка, потім Зберегти шаблони.',
                'Порядок стандартних секцій — у Контент → Головна сторінка.',
            ],
            'links' => [],
            'note' => 'Шаблони з підключенням «Головна» автоматично синхронізуються з блоками головної.',
        ],
        'sections' => [
            'block-builder-generate' => 'Генератор дизайну',
            'block-builder-new' => 'Редактор блоку',
            'block-builder-saved' => 'Збережені шаблони',
        ],
        'hints' => [],
    ],
    'chat' => [
        'intro' => 'Плаваючий AI-чат на вітрині — окремо від AI-асистента для товарів у адмінці.',
        'guide' => [
            'title' => 'Публічний віджет чату',
            'intro' => 'Увімкніть бульбашку, яку бачать відвідувачі. Налаштуйте провайдера, інструкції та вигляд кнопки.',
            'steps' => [
                'Увімкніть «Увімкнути чат на вітрині».',
                'Оберіть Grok або OpenAI та вставте API-ключ.',
                'Напишіть системні інструкції: мови, доставка, оплата, демо-повідомлення.',
                'Задайте колір кнопки та іконку Font Awesome (comments, headset тощо).',
                'Відкрийте вітрину та перевірте бульбашку після збереження.',
            ],
            'links' => [
                ['label' => 'xAI Docs', 'url' => 'https://docs.x.ai/'],
                ['label' => 'Іконки Font Awesome', 'url' => 'https://fontawesome.com/search?o=r&m=free&f=classic'],
            ],
            'note' => 'Незалежно від Налаштування → AI-асистент (описи товарів у редакторі).',
        ],
        'sections' => [
            'chat-main' => 'Підключення чату',
            'chat-widget' => 'Вигляд віджета',
        ],
        'hints' => [
            'chat_api_key' => 'xai-… для Grok або sk-… для OpenAI. Залиште порожнім, щоб зберегти ключ.',
            'chat_instructions' => 'Системний промпт: мови, країни доставки, способи оплати.',
            'chat_model' => 'Модель чату тут або порожнє — з Налаштування → AI → Модель для чату, далі default.',
            'chat_widget_color' => 'Акцентний колір плаваючої кнопки. Порожнє — основний колір теми.',
            'chat_widget_icon' => 'Назва іконки Font Awesome без префікса fa-: comments, headset, robot, message.',
        ],
    ],
    'ai' => [
        'intro' => 'AI-асистент для власника магазину: моделі за контекстом і промпти для товарів, чату, новин, SEO та перекладу UI.',
        'guide' => [
            'title' => 'AI-асистент адмінки',
            'steps' => [
                'Увімкніть AI та оберіть провайдера (OpenAI або xAI Grok).',
                'Вставте API-ключ і задайте модель за замовчуванням.',
                'За потреби оберіть окрему модель для контекстів: товар, чат, новини, SEO.',
                'Налаштуйте промпти для товарів, новин і SEO сайту/категорій.',
                'Товари → Редагувати → «Згенерувати з AI»; Мультимовність → «Перекласти UI через AI».',
            ],
            'links' => [
                ['label' => 'xAI Docs', 'url' => 'https://docs.x.ai/'],
            ],
            'note' => 'Перевіряйте текст AI перед публікацією. Порожня модель контексту = fallback на default.',
        ],
        'sections' => [
            'ai-connection' => 'Підключення API',
            'ai-models' => 'Моделі за контекстом',
            'ai-prompts' => 'Промпти',
            'ai-instructions' => 'Інструкція з налаштування',
        ],
        'hints' => [
            'ai_source_lang' => 'Мова, якою ви вводите назву товару перед генерацією.',
            'ai_prompt_product' => 'Промпт товару. Плейсхолдери: {product_name}, {category}, {source_lang}.',
            'ai_prompt_news' => 'Промпт новин. Плейсхолдери: {topic}, {source_lang}.',
            'ai_prompt_seo' => 'Промпт SEO сайту/категорії. Плейсхолдери: {task_type}, {target_name}, {slug}, {country_code}, {source_lang}.',
        ],
    ],
    'news_page' => [
        'intro' => 'Новини та релізи магазину — багатомовні статті з SEO, обкладинкою та NewsArticle schema на вітрині.',
        'guide' => [
            'title' => 'Інструкція з новин',
            'steps' => [
                'Каталог → Новини → Додати статтю — slug, дата публікації та URL обкладинки.',
                'Заповніть заголовок, уривок і HTML-тіло для кожної мови (або «Згенерувати з AI»).',
                'Увімкніть Активна та за потреби Рекомендована для списку.',
                'Вкладка SEO & Schema — meta title, description 120–160 символів, keywords, OG image.',
                'Перегляньте на /news.php та діліться посиланнями news-article.php?slug=…',
            ],
            'links' => [
                ['label' => 'Публічний список новин', 'url' => 'https://bilohash.com/shop/news.php'],
            ],
            'note' => 'AI для новин використовує модель з Налаштування → AI → Новини. Порожня модель контексту = default.',
        ],
        'sections' => [
            'news-content' => 'Контент статті',
            'news-status' => 'Публікація',
            'news-seo' => 'SEO та Schema',
        ],
        'hints' => [
            'slug' => 'Сегмент URL латиницею — напр. shop-cms-v1-3-6-release.',
            'published_at' => 'Дата для schema datePublished та списку новин.',
            'image' => 'Рекомендовано 1200×630 для Open Graph та NewsArticle image.',
        ],
    ],
    'footer' => [
        'intro' => 'Колонки футера «Магазин» та «Правове» — доставка, оплата, конфіденційність, cookies.',
        'guide' => [
            'title' => 'Посилання у футері',
            'steps' => [
                'Спочатку відредагуйте сервісні сторінки.',
                'Додайте посилання: page.php?slug=delivery.',
                'Заповніть підписи для всіх мов.',
                'Позначте зовнішні посилання (https://).',
            ],
            'links' => [],
            'note' => 'Рекомендовано: Доставка, Оплата, Політика конфіденційності, Cookies.',
        ],
        'sections' => [
            'footer-shop' => 'Колонка «Магазин»',
            'footer-legal' => 'Колонка «Правове»',
        ],
        'hints' => [
            'footer_link_url' => 'Відносний шлях або повний https:// URL.',
        ],
    ],
    'appearance' => [
        'intro' => 'Кольори бренду, типографіка, поверхні та фон публічної вітрини.',
        'guide' => [
            'title' => 'Зовнішній вигляд',
            'steps' => [
                'Оберіть основний колір кнопок і посилань.',
                'Налаштуйте кольори тексту, карток, шапки та футера.',
                'Задайте радіус кутів і шрифт під ваш бренд.',
                'За потреби — фон сторінки або URL фонового зображення.',
                'Перевірте контраст на мобільному.',
            ],
            'links' => [],
            'note' => 'Зміни застосовуються після збереження — оновіть вітрину (Ctrl+F5).',
        ],
        'sections' => [
            'appearance-colors' => 'Кольори та фон',
            'appearance-buttons' => 'Кольори кнопок',
            'appearance-typography' => 'Типографіка та радіус',
            'appearance-surfaces' => 'Текст та поверхні',
            'appearance-product-card' => 'Картка товару',
            'appearance-quick-buy' => 'Швидка покупка (гарячі клієнти)',
        ],
        'hints' => [
            'color_primary' => 'Основний колір бренду — рекомендований контраст WCAG 4.5:1.',
            'color_btn_search' => 'Кнопка «Шукати» на головній та в каталозі.',
            'color_btn_cart' => 'Колір кошика при наведенні/активному стані в шапці.',
            'color_btn_outline' => 'Outline-кнопки: вхід, адмін та інші в шапці.',
            'bg_image' => 'Необовʼязкове фонове зображення (HTTPS URL).',
            'design_font_family' => 'CSS font-family, напр. \'Inter\', system-ui, sans-serif.',
            'design_border_radius' => 'Радіус кутів у пікселях (0–24).',
            'design_text_color' => 'Основний колір тексту на вітрині.',
            'design_sale_color' => 'Колір бейджа розпродажу.',
        ],
    ],
    'pages' => [
        'intro' => 'Багатомовні сервісні сторінки: доставка, оплата, cookies та власні legal-сторінки з редактором.',
        'guide' => [
            'title' => 'Сервісні сторінки',
            'steps' => [
                'Редагуйте вбудовані сторінки або додайте slug (returns, warranty…).',
                'Використовуйте редактор: заголовки, списки, посилання — без API.',
                'Заповніть усі мови та увімкніть видимість.',
                'Додайте посилання у футер: page.php?slug=…',
            ],
            'links' => [],
            'note' => 'URL: /shop/page.php?slug=privacy&lang=uk',
        ],
        'sections' => ['pages-list' => 'Усі сервісні сторінки'],
        'hints' => [
            'page_content_editor_hint' => 'Жирний, списки, посилання — зберігається як безпечний HTML.',
        ],
    ],
    'header' => [
        'intro' => 'Керуйте пунктами меню в шапці вітрини — додавайте, редагуйте, видаляйте або приховуйте.',
        'guide' => [
            'title' => 'Пункти меню',
            'steps' => [
                'Додайте рядок з URL і підписами або відредагуйте наявні пункти.',
                'Кнопка з іконкою кошика видаляє пункт меню.',
                'Вхід покупця — у блоці «Дії в шапці».',
                'Збережіть і перевірте шапку на вітрині.',
            ],
            'links' => [],
            'note' => '«Магазин» і «Категорії» завжди на місці. Кошик і мови — у панелі дій шапки.',
        ],
        'sections' => ['header-nav' => 'Пункти меню', 'header-actions' => 'Дії в шапці'],
        'hints' => [],
    ],
    'posten' => [
        'intro' => 'API Bring/Posten для сторінки відстеження track.php.',
        'guide' => [
            'title' => 'Відстеження посилок',
            'steps' => [
                'Увімкніть відстеження та демо-режим для розробки.',
                'Вставте Bring Client ID та API key для продакшену.',
                'Відкрийте track.php і перевірте демо-номер.',
            ],
            'links' => [
                ['label' => 'Bring Developer', 'url' => 'https://developer.bring.com/'],
            ],
            'note' => 'У демо-режимі показується зразковий таймлайн без реального API.',
        ],
        'sections' => ['posten-main' => 'Bring / Posten API'],
        'hints' => [
            'posten_client_id' => 'Bring Tracking Client ID.',
            'posten_api_key' => 'API key — залиште порожнім, щоб зберегти поточний.',
        ],
    ],
    'nova_poshta' => [
        'intro' => 'API Нова пошта (Україна): відстеження, вибір відділення на checkout і склад відправника.',
        'guide' => [
            'title' => 'Налаштування Нова пошта',
            'steps' => [
                'Увімкніть інтеграцію та демо-режим на час розробки.',
                'Отримайте API-ключ на novaposhta.ua → Бізнес → API і натисніть «Перевірити API».',
                'Оберіть місто та відділення відправника — зберігається для checkout і майбутніх накладних.',
                'Увімкніть відстеження і відкрийте track-np.php з номером ТТН (11–14 цифр).',
                'Опційно: увімкніть вибір відділення на checkout для доставки по Україні.',
            ],
            'links' => [
                ['label' => 'Документація API Нова пошта', 'url' => 'https://developers.novaposhta.ua/'],
            ],
            'note' => 'Без API-ключа демо-режим показує зразкові міста, відділення та статуси відстеження.',
        ],
        'sections' => [
            'nova-poshta-main' => 'API Нова пошта',
            'nova-poshta-sender' => 'Склад відправника',
        ],
        'hints' => [
            'nova_poshta_api_key' => 'Ключ з бізнес-кабінету — залиште порожнім, щоб зберегти поточний.',
            'nova_poshta_sender_phone' => 'Телефон відправника допомагає API відстеження повертати повний статус.',
        ],
    ],
    'languages' => [
        'intro' => 'Мови вітрини та AI-переклад файлів lang/*.php.',
        'guide' => [
            'title' => 'Мультимовність',
            'steps' => [
                'Увімкніть потрібні мови.',
                'Перекладайте товари в редакторі.',
                'Мультимовність → «Перекласти UI через AI».',
            ],
            'links' => [['label' => 'xAI Docs', 'url' => 'https://docs.x.ai/']],
            'note' => 'Перевіряйте текст AI перед публікацією.',
        ],
        'sections' => ['languages-list' => 'Мови сайту'],
        'hints' => [],
    ],
];

return [
    'en' => $en,
    'uk' => $uk,
    'no' => $en,
    'ru' => $uk,
    'sv' => $en,
];