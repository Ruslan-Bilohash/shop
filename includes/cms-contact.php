<?php
/**
 * Booking CMS / Auction CMS / Freelance CMS / Shop CMS — contact form with reCAPTCHA v2
 * Mail to email@bilohash.com
 */

define('CMS_CONTACT_EMAIL', 'email@bilohash.com');
define('CMS_CONTACT_FROM', 'no-reply@bilohash.com');

if (!function_exists('bh_send_mail')) {
    if (is_file(__DIR__ . '/ecosystem-load.php')) {
        require_once __DIR__ . '/ecosystem-load.php';
        sh_require_ecosystem('bh-mail.php');
    } else {
        require_once __DIR__ . '/bh-mail.php';
    }
}

function cms_recaptcha_site_key(): string
{
    if (!empty($GLOBALS['bh_cms_recaptcha_settings']) && is_array($GLOBALS['bh_cms_recaptcha_settings'])) {
        require_once __DIR__ . '/bh-cms-site-settings.php';
        $key = bh_cms_recaptcha_site_key($GLOBALS['bh_cms_recaptcha_settings']);
        if ($key !== '') {
            return $key;
        }
        if (!bh_cms_recaptcha_enabled($GLOBALS['bh_cms_recaptcha_settings'])) {
            return '';
        }
    }
    return '6LdcvzYtAAAAAEuYDp5URZ_yIvoSUNtagQFLnpOV';
}

function cms_recaptcha_secret_key(): string
{
    if (!empty($GLOBALS['bh_cms_recaptcha_settings']) && is_array($GLOBALS['bh_cms_recaptcha_settings'])) {
        require_once __DIR__ . '/bh-cms-site-settings.php';
        $key = bh_cms_recaptcha_secret_key($GLOBALS['bh_cms_recaptcha_settings']);
        if ($key !== '') {
            return $key;
        }
    }
    return '6LdcvzYtAAAAAA-24iHgxj4Weo-HtJGOnRQdWR_w';
}

function cms_verify_recaptcha(?string $response): bool
{
    if (!empty($GLOBALS['bh_cms_recaptcha_settings']) && is_array($GLOBALS['bh_cms_recaptcha_settings'])) {
        require_once __DIR__ . '/bh-cms-site-settings.php';
        return bh_cms_verify_recaptcha($response, $GLOBALS['bh_cms_recaptcha_settings']);
    }

    $response = trim((string) $response);
    if ($response === '') {
        return false;
    }

    $payload = http_build_query([
        'secret'   => cms_recaptcha_secret_key(),
        'response' => $response,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
    ]);

    $ctx = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => $payload,
            'timeout' => 12,
        ],
    ]);

    $raw = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $ctx);
    if ($raw === false) {
        return false;
    }

    $data = json_decode($raw, true);
    return !empty($data['success']);
}

function cms_contact_lang_code(string $lang): string
{
    $map = [
        'en' => 'en', 'uk' => 'uk', 'ua' => 'uk', 'no' => 'no',
        'lt' => 'lt', 'pl' => 'pl', 'de' => 'de', 'sv' => 'sv', 'ru' => 'ru',
    ];
    return $map[$lang] ?? 'en';
}

function cms_contact_texts(string $product, string $lang): array
{
    $lang = cms_contact_lang_code($lang);
    $productLabel = match ($product) {
        'auction'   => 'Auction CMS',
        'freelance' => 'Freelance CMS',
        'shop'      => 'Shop CMS',
        'pizza'     => 'Pizza CMS',
        'gamehub'   => 'GameHub CMS',
        'faktura'   => 'Faktura CMS',
        'today'     => 'Today CMS',
        default     => 'Booking CMS',
    };

    $subjects = [
        'no' => $productLabel . ' — prosjektforespørsel',
        'en' => $productLabel . ' — project inquiry',
        'uk' => $productLabel . ' — запит щодо проєкту',
        'ru' => $productLabel . ' — запрос по проекту',
    ];

    $all = [
        'no' => [
            'page_title'       => 'Diskuter prosjektet | ' . $productLabel,
            'meta_description' => 'Send forespørsel om skreddersydd ' . $productLabel . '-løsning. reCAPTCHA-beskyttet skjema — svar innen noen timer.',
            'h1'               => 'Diskuter prosjektet',
            'subtitle'         => 'Beskriv behovene dine — vi svarer på email@bilohash.com innen kort tid.',
            'name'             => 'Navn',
            'email'            => 'E-post',
            'phone'            => 'Telefon (valgfritt)',
            'subject'          => 'Emne',
            'message'          => 'Melding',
            'submit'           => 'Send forespørsel',
            'default_subject'  => $subjects['no'],
            'success'          => '✅ Takk! Forespørselen er sendt til email@bilohash.com.',
            'error_fields'     => '❌ Fyll ut navn, e-post og melding.',
            'error_email'      => '❌ Ugyldig e-postadresse.',
            'error_message'    => '❌ Meldingen må være mellom 20 og 5000 tegn.',
            'error_captcha'    => '❌ Bekreft reCAPTCHA.',
            'error_csrf'       => '❌ Sikkerhetsfeil. Oppdater siden og prøv igjen.',
            'error_mail'       => '❌ Kunne ikke sende. Skriv direkte til email@bilohash.com',
            'nav_discuss'      => 'Diskuter prosjekt',
            'privacy_note'     => 'Data sendes kun for å behandle forespørselen. Beskyttet med reCAPTCHA.',
        ],
        'en' => [
            'page_title'       => 'Discuss your project | ' . $productLabel,
            'meta_description' => 'Request a custom ' . $productLabel . ' build. reCAPTCHA-protected form — reply within hours.',
            'h1'               => 'Discuss your project',
            'subtitle'         => 'Tell us about your needs — we reply via email@bilohash.com shortly.',
            'name'             => 'Name',
            'email'            => 'Email',
            'phone'            => 'Phone (optional)',
            'subject'          => 'Subject',
            'message'          => 'Message',
            'submit'           => 'Send inquiry',
            'default_subject'  => $subjects['en'],
            'success'          => '✅ Thank you! Your message was sent to email@bilohash.com.',
            'error_fields'     => '❌ Please fill in name, email and message.',
            'error_email'      => '❌ Invalid email address.',
            'error_message'    => '❌ Message must be between 20 and 5000 characters.',
            'error_captcha'    => '❌ Please complete reCAPTCHA.',
            'error_csrf'       => '❌ Security error. Refresh the page and try again.',
            'error_mail'       => '❌ Could not send. Email email@bilohash.com directly.',
            'nav_discuss'      => 'Discuss project',
            'privacy_note'     => 'Data is used only to handle your inquiry. Protected by reCAPTCHA.',
        ],
        'uk' => [
            'page_title'       => 'Обговорити проєкт | ' . $productLabel,
            'meta_description' => 'Запит на індивідуальне рішення ' . $productLabel . '. Форма з reCAPTCHA — відповідь протягом кількох годин.',
            'h1'               => 'Обговорити проєкт',
            'subtitle'         => 'Опишіть потреби — відповімо на email@bilohash.com найближчим часом.',
            'name'             => 'Ім\'я',
            'email'            => 'Email',
            'phone'            => 'Телефон (необов\'язково)',
            'subject'          => 'Тема',
            'message'          => 'Повідомлення',
            'submit'           => 'Надіслати запит',
            'default_subject'  => $subjects['uk'],
            'success'          => '✅ Дякуємо! Повідомлення надіслано на email@bilohash.com.',
            'error_fields'     => '❌ Заповніть ім\'я, email і повідомлення.',
            'error_email'      => '❌ Невірна email-адреса.',
            'error_message'    => '❌ Повідомлення: від 20 до 5000 символів.',
            'error_captcha'    => '❌ Підтвердіть reCAPTCHA.',
            'error_csrf'       => '❌ Помилка безпеки. Оновіть сторінку.',
            'error_mail'       => '❌ Не вдалося надіслати. Напишіть на email@bilohash.com',
            'nav_discuss'      => 'Обговорити проєкт',
            'privacy_note'     => 'Дані використовуються лише для обробки запиту. Захист reCAPTCHA.',
        ],
        'lt' => [
            'page_title'       => 'Aptarkite projektą | ' . $productLabel,
            'meta_description' => 'Užklausa dėl individualaus ' . $productLabel . ' sprendimo. reCAPTCHA forma — atsakymas per kelias valandas.',
            'h1'               => 'Aptarkite projektą',
            'subtitle'         => 'Aprašykite poreikius — atsakysime į email@bilohash.com netrukus.',
            'name'             => 'Vardas',
            'email'            => 'El. paštas',
            'phone'            => 'Telefonas (neprivaloma)',
            'subject'          => 'Tema',
            'message'          => 'Žinutė',
            'submit'           => 'Siųsti užklausą',
            'default_subject'  => $productLabel . ' — projekto užklausa',
            'success'          => '✅ Ačiū! Žinutė išsiųsta į email@bilohash.com.',
            'error_fields'     => '❌ Užpildykite vardą, el. paštą ir žinutę.',
            'error_email'      => '❌ Neteisingas el. pašto adresas.',
            'error_message'    => '❌ Žinutė turi būti nuo 20 iki 5000 simbolių.',
            'error_captcha'    => '❌ Patvirtinkite reCAPTCHA.',
            'error_csrf'       => '❌ Saugumo klaida. Atnaujinkite puslapį.',
            'error_mail'       => '❌ Nepavyko išsiųsti. Rašykite tiesiai į email@bilohash.com',
            'nav_discuss'      => 'Aptarti projektą',
            'privacy_note'     => 'Duomenys naudojami tik užklausai apdoroti. Apsaugota reCAPTCHA.',
        ],
        'pl' => [
            'page_title'       => 'Omów projekt | ' . $productLabel,
            'meta_description' => 'Zapytanie o indywidualne rozwiązanie ' . $productLabel . '. Formularz z reCAPTCHA — odpowiedź w kilka godzin.',
            'h1'               => 'Omów projekt',
            'subtitle'         => 'Opisz potrzeby — odpowiemy na email@bilohash.com wkrótce.',
            'name'             => 'Imię',
            'email'            => 'E-mail',
            'phone'            => 'Telefon (opcjonalnie)',
            'subject'          => 'Temat',
            'message'          => 'Wiadomość',
            'submit'           => 'Wyślij zapytanie',
            'default_subject'  => $productLabel . ' — zapytanie o projekt',
            'success'          => '✅ Dziękujemy! Wiadomość wysłana na email@bilohash.com.',
            'error_fields'     => '❌ Wypełnij imię, e-mail i wiadomość.',
            'error_email'      => '❌ Nieprawidłowy adres e-mail.',
            'error_message'    => '❌ Wiadomość: od 20 do 5000 znaków.',
            'error_captcha'    => '❌ Potwierdź reCAPTCHA.',
            'error_csrf'       => '❌ Błąd bezpieczeństwa. Odśwież stronę.',
            'error_mail'       => '❌ Nie udało się wysłać. Napisz na email@bilohash.com',
            'nav_discuss'      => 'Omów projekt',
            'privacy_note'     => 'Dane służą tylko do obsługi zapytania. Chronione reCAPTCHA.',
        ],
        'de' => [
            'page_title'       => 'Projekt besprechen | ' . $productLabel,
            'meta_description' => 'Anfrage für eine individuelle ' . $productLabel . '-Lösung. reCAPTCHA-Formular — Antwort innerhalb weniger Stunden.',
            'h1'               => 'Projekt besprechen',
            'subtitle'         => 'Beschreiben Sie Ihre Anforderungen — wir antworten über email@bilohash.com in Kürze.',
            'name'             => 'Name',
            'email'            => 'E-Mail',
            'phone'            => 'Telefon (optional)',
            'subject'          => 'Betreff',
            'message'          => 'Nachricht',
            'submit'           => 'Anfrage senden',
            'default_subject'  => $productLabel . ' — Projektanfrage',
            'success'          => '✅ Danke! Ihre Nachricht wurde an email@bilohash.com gesendet.',
            'error_fields'     => '❌ Bitte Name, E-Mail und Nachricht ausfüllen.',
            'error_email'      => '❌ Ungültige E-Mail-Adresse.',
            'error_message'    => '❌ Nachricht muss zwischen 20 und 5000 Zeichen lang sein.',
            'error_captcha'    => '❌ Bitte reCAPTCHA bestätigen.',
            'error_csrf'       => '❌ Sicherheitsfehler. Seite aktualisieren.',
            'error_mail'       => '❌ Senden fehlgeschlagen. Schreiben Sie an email@bilohash.com',
            'nav_discuss'      => 'Projekt besprechen',
            'privacy_note'     => 'Daten werden nur zur Bearbeitung Ihrer Anfrage verwendet. Geschützt durch reCAPTCHA.',
        ],
        'sv' => [
            'page_title'       => 'Diskutera projekt | ' . $productLabel,
            'meta_description' => 'Förfrågan om skräddarsydd ' . $productLabel . '-lösning. reCAPTCHA-formulär — svar inom några timmar.',
            'h1'               => 'Diskutera projekt',
            'subtitle'         => 'Beskriv dina behov — vi svarar via email@bilohash.com inom kort.',
            'name'             => 'Namn',
            'email'            => 'E-post',
            'phone'            => 'Telefon (valfritt)',
            'subject'          => 'Ämne',
            'message'          => 'Meddelande',
            'submit'           => 'Skicka förfrågan',
            'default_subject'  => $productLabel . ' — projektförfrågan',
            'success'          => '✅ Tack! Meddelandet skickades till email@bilohash.com.',
            'error_fields'     => '❌ Fyll i namn, e-post och meddelande.',
            'error_email'      => '❌ Ogiltig e-postadress.',
            'error_message'    => '❌ Meddelandet måste vara mellan 20 och 5000 tecken.',
            'error_captcha'    => '❌ Bekräfta reCAPTCHA.',
            'error_csrf'       => '❌ Säkerhetsfel. Uppdatera sidan.',
            'error_mail'       => '❌ Kunde inte skicka. Mejla email@bilohash.com direkt.',
            'nav_discuss'      => 'Diskutera projekt',
            'privacy_note'     => 'Data används endast för att hantera din förfrågan. Skyddad med reCAPTCHA.',
        ],
        'ru' => [
            'page_title'       => 'Обсудить проект | ' . $productLabel,
            'meta_description' => 'Запрос на индивидуальное решение ' . $productLabel . '. Форма с reCAPTCHA — ответ в течение нескольких часов.',
            'h1'               => 'Обсудить проект',
            'subtitle'         => 'Опишите задачу — ответим на email@bilohash.com в ближайшее время.',
            'name'             => 'Имя',
            'email'            => 'Email',
            'phone'            => 'Телефон (необязательно)',
            'subject'          => 'Тема',
            'message'          => 'Сообщение',
            'submit'           => 'Отправить запрос',
            'default_subject'  => $subjects['ru'],
            'success'          => '✅ Спасибо! Сообщение отправлено на email@bilohash.com.',
            'error_fields'     => '❌ Заполните имя, email и сообщение.',
            'error_email'      => '❌ Неверный email.',
            'error_message'    => '❌ Сообщение: от 20 до 5000 символов.',
            'error_captcha'    => '❌ Подтвердите reCAPTCHA.',
            'error_csrf'       => '❌ Ошибка безопасности. Обновите страницу.',
            'error_mail'       => '❌ Не удалось отправить. Напишите на email@bilohash.com',
            'nav_discuss'      => 'Обсудить проект',
            'privacy_note'     => 'Данные используются только для обработки запроса. Защита reCAPTCHA.',
        ],
    ];

    return $all[$lang];
}

/**
 * @return array{alert: string, alert_type: string, values: array{name: string, email: string, phone: string, subject: string, message: string}}
 */
function cms_contact_handle_post(string $product, string $lang, string $source, bool $skip_recaptcha = false): array
{
    $t = cms_contact_texts($product, $lang);
    $values = [
        'name'    => trim(strip_tags($_POST['name'] ?? '')),
        'email'   => trim($_POST['email'] ?? ''),
        'phone'   => trim(strip_tags($_POST['phone'] ?? '')),
        'subject' => trim(strip_tags($_POST['subject'] ?? '')),
        'message' => trim(strip_tags($_POST['message'] ?? '')),
    ];

    if (empty($_SESSION['cms_contact_csrf']) || !isset($_POST['csrf_token'])
        || !hash_equals($_SESSION['cms_contact_csrf'], $_POST['csrf_token'])) {
        return ['alert' => $t['error_csrf'], 'alert_type' => 'error', 'values' => $values];
    }

    if (!empty($_POST['website'])) {
        return ['alert' => $t['success'], 'alert_type' => 'success', 'values' => ['name' => '', 'email' => '', 'phone' => '', 'subject' => '', 'message' => '']];
    }

    if (!$skip_recaptcha && !cms_verify_recaptcha($_POST['g-recaptcha-response'] ?? '')) {
        return ['alert' => $t['error_captcha'], 'alert_type' => 'error', 'values' => $values];
    }

    if ($values['name'] === '' || $values['email'] === '' || $values['message'] === '') {
        return ['alert' => $t['error_fields'], 'alert_type' => 'error', 'values' => $values];
    }

    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        return ['alert' => $t['error_email'], 'alert_type' => 'error', 'values' => $values];
    }

    if (strlen($values['message']) < 20 || strlen($values['message']) > 5000) {
        return ['alert' => $t['error_message'], 'alert_type' => 'error', 'values' => $values];
    }

    if ($values['subject'] === '') {
        $values['subject'] = $t['default_subject'];
    }

    $productLabel = match ($product) {
        'auction'   => 'Auction CMS',
        'freelance' => 'Freelance CMS',
        'shop'      => 'Shop CMS',
        'pizza'     => 'Pizza CMS',
        'gamehub'   => 'GameHub CMS',
        'faktura'   => 'Faktura CMS',
        'today'     => 'Today CMS',
        default     => 'Booking CMS',
    };
    $ref = strtoupper(substr($product, 0, 1)) . 'CMS-' . date('YmdHis');

    $html = '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body style="font-family:system-ui,sans-serif;background:#0f172a;color:#e2e8f0;padding:32px">'
        . '<div style="max-width:640px;margin:0 auto;background:#1e293b;border-radius:16px;padding:32px;border:1px solid rgba(34,211,238,.25)">'
        . '<h1 style="color:#22d3ee;margin:0 0 16px">📩 ' . htmlspecialchars($productLabel) . ' — project inquiry</h1>'
        . '<p><strong>Ref:</strong> ' . htmlspecialchars($ref) . '</p>'
        . '<p><strong>Name:</strong> ' . htmlspecialchars($values['name']) . '</p>'
        . '<p><strong>Email:</strong> ' . htmlspecialchars($values['email']) . '</p>'
        . '<p><strong>Phone:</strong> ' . htmlspecialchars($values['phone'] !== '' ? $values['phone'] : '—') . '</p>'
        . '<p><strong>Subject:</strong> ' . htmlspecialchars($values['subject']) . '</p>'
        . '<p><strong>Source:</strong> ' . htmlspecialchars($source) . '</p>'
        . '<hr style="border-color:#334155">'
        . '<p style="white-space:pre-wrap">' . nl2br(htmlspecialchars($values['message'])) . '</p>'
        . '<p style="color:#64748b;font-size:12px;margin-top:24px">' . date('d.m.Y H:i:s')
        . ' · IP ' . htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? '') . ' · lang=' . htmlspecialchars($lang) . '</p></div></body></html>';

    $sent = bh_send_mail(
        CMS_CONTACT_EMAIL,
        '[' . $productLabel . '] ' . $values['subject'],
        $html,
        $values['email'],
        $values['name']
    );

    $_SESSION['cms_contact_csrf'] = bin2hex(random_bytes(32));

    if (!$sent) {
        return ['alert' => $t['error_mail'], 'alert_type' => 'error', 'values' => $values];
    }

    return [
        'alert'      => $t['success'],
        'alert_type' => 'success',
        'values'     => ['name' => '', 'email' => '', 'phone' => '', 'subject' => '', 'message' => ''],
    ];
}

function cms_contact_ensure_csrf(): string
{
    if (empty($_SESSION['cms_contact_csrf'])) {
        $_SESSION['cms_contact_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['cms_contact_csrf'];
}

function cms_contact_stylesheet_href(): string
{
    $path = 'includes/cms-contact.css?v=4';
    return function_exists('sh_url') ? sh_url($path) : '/' . $path;
}