<?php
/**
 * Build lang/admin/{no,ru,sv}.php overlays from uk.php (complete admin) + locale partials.
 * CLI: php scripts/build-admin-overlays.php
 */
$root = dirname(__DIR__);
$outDir = $root . '/lang/admin';
if (!is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

$uk = require $root . '/lang/uk.php';
$no = require $root . '/lang/no.php';
$ru = require $root . '/lang/ru.php';
$sv = require $root . '/lang/sv.php';
$en = require $root . '/lang/en.php';

$ukAdmin = is_array($uk['admin'] ?? null) ? $uk['admin'] : [];

/** Walk array and apply string replacements (leaf strings only). */
function sh_admin_apply_replacements(array $data, array $map): array
{
    foreach ($data as $k => $v) {
        if (is_array($v)) {
            $data[$k] = sh_admin_apply_replacements($v, $map);
        } elseif (is_string($v)) {
            $data[$k] = strtr($v, $map);
        }
    }
    return $data;
}

/** Norwegian UI terms (uk → no) for admin overlay base. */
$noFromUk = [
    'Адмін-панель' => 'Adminpanel',
    'Демо-панель адміністрування' => 'Demo-administrasjonspanel',
    'Ім\'я користувача' => 'Brukernavn',
    'Пароль' => 'Passord',
    'Увійти' => 'Logg inn',
    'Невірне ім\'я користувача або пароль' => 'Ugyldig brukernavn eller passord',
    'Вийти' => 'Logg ut',
    'Демо-вхід: demo / demo2026' => 'Demo-innlogging: demo / demo2026',
    'Панель керування' => 'Dashboard',
    'Товари' => 'Produkter',
    'Імпорт / Експорт' => 'Import / Eksport',
    'Переглянути сайт' => 'Se nettsted',
    'З поверненням' => 'Velkommen tilbake',
    'Активні товари' => 'Aktive produkter',
    'Рекомендовані' => 'Utvalgte',
    'Категорії' => 'Kategorier',
    'Швидкі дії' => 'Hurtighandlinger',
    'Зберегти' => 'Lagre',
    'Скасувати' => 'Avbryt',
    'Назад' => 'Tilbake',
    'Редагувати' => 'Rediger',
    'Додати' => 'Legg til',
    'Видалити' => 'Slett',
    'Дії' => 'Handlinger',
    'Статус' => 'Status',
    'Активний' => 'Aktiv',
    'Неактивний' => 'Inaktiv',
    'Налаштування збережено.' => 'Innstillinger lagret.',
    'Не вдалося зберегти.' => 'Kunne ikke lagre.',
    'Оплата' => 'Betaling',
    'Платежі' => 'Betalinger',
    'Доставка' => 'Levering',
    'Кошик' => 'Handlekurv',
    'Каса' => 'Kasse',
    'Мова' => 'Språk',
    'SEO' => 'SEO',
    'Згенерувати' => 'Generer',
    'Генерація…' => 'Genererer…',
    'Назва' => 'Navn',
    'Опис' => 'Beskrivelse',
    'Ціна' => 'Pris',
    'Зображення' => 'Bilder',
    'Категорія' => 'Kategori',
    'Пошук' => 'Søk',
    'Фільтр' => 'Filter',
    'Так' => 'Ja',
    'Ні' => 'Nei',
    'Увімкнено' => 'Aktivert',
    'Вимкнено' => 'Deaktivert',
    'Демо' => 'Demo',
    'Помилка' => 'Feil',
    'Успішно' => 'Vellykket',
    'Закрити' => 'Lukk',
    'Показати' => 'Vis',
    'Приховати' => 'Skjul',
    'Підказки' => 'Tips',
    'Допомога' => 'Hjelp',
    'Клієнт' => 'Kunde',
    'Замовлення' => 'Ordre',
    'Рахунок' => 'Faktura',
    'Відправити' => 'Send',
    'Оновити' => 'Oppdater',
    'Завантажити' => 'Last opp',
    'Файл' => 'Fil',
    'Ключ API' => 'API-nøkkel',
    'Секретний ключ' => 'Hemmelig nøkkel',
    'Вебхук' => 'Webhook',
    'Інтеграції' => 'Integrasjoner',
    'Пошта' => 'Post',
    'SMS' => 'SMS',
    'Телефон' => 'Telefon',
    'Країна' => 'Land',
    'Місто' => 'By',
    'Адреса' => 'Adresse',
    'Індекс' => 'Postnummer',
];

/** Swedish UI terms (uk → sv). */
$svFromUk = [
    'Адмін-панель' => 'Adminpanel',
    'Товари' => 'Produkter',
    'Категорії' => 'Kategorier',
    'Зберегти' => 'Spara',
    'Скасувати' => 'Avbryt',
    'Назад' => 'Tillbaka',
    'Редагувати' => 'Redigera',
    'Додати' => 'Lägg till',
    'Дії' => 'Åtgärder',
    'Статус' => 'Status',
    'Активний' => 'Aktiv',
    'Неактивний' => 'Inaktiv',
    'Налаштування збережено.' => 'Inställningar sparade.',
    'Оплата' => 'Betalning',
    'Доставка' => 'Leverans',
    'Кошик' => 'Varukorg',
    'Каса' => 'Kassa',
    'Назва' => 'Namn',
    'Опис' => 'Beskrivning',
    'Ціна' => 'Pris',
    'Категорія' => 'Kategori',
    'Демо' => 'Demo',
    'Закрити' => 'Stäng',
    'Підказки' => 'Tips',
    'Замовлення' => 'Order',
    'Телефон' => 'Telefon',
    'Країна' => 'Land',
];

function sh_export_admin_php(string $path, array $admin): void
{
    $body = "<?php\n/** Auto-generated admin overlay — run scripts/build-admin-overlays.php */\nreturn "
        . var_export($admin, true) . ";\n";
    file_put_contents($path, $body);
}

// RU: Ukrainian admin (Cyrillic — readable for RU admins) + ru partial overrides
$ruAdmin = array_replace_recursive($ukAdmin, is_array($ru['admin'] ?? null) ? $ru['admin'] : []);
sh_export_admin_php($outDir . '/ru.php', $ruAdmin);

// NO: uk base → Norwegian term pass → existing no partial wins
$noAdmin = sh_admin_apply_replacements($ukAdmin, $noFromUk);
$noAdmin = array_replace_recursive($noAdmin, is_array($no['admin'] ?? null) ? $no['admin'] : []);
sh_export_admin_php($outDir . '/no.php', $noAdmin);

// SV: uk base → Swedish term pass → existing sv partial wins
$svAdmin = sh_admin_apply_replacements($ukAdmin, $svFromUk);
$svAdmin = array_replace_recursive($svAdmin, is_array($sv['admin'] ?? null) ? $sv['admin'] : []);
sh_export_admin_php($outDir . '/sv.php', $svAdmin);

// Fill uk missing keys from en
$ukFixed = $uk;
$ukFixed['admin'] = array_replace_recursive($en['admin'] ?? [], $ukAdmin);
$ukMissing = [
    'orders_page' => [
        'delete_error' => 'Не вдалося видалити замовлення.',
        'deleted' => 'Замовлення видалено.',
        'save_status' => 'Зберегти статус',
        'status' => 'Статус',
        'update_error' => 'Не вдалося оновити замовлення.',
        'updated' => 'Замовлення оновлено.',
    ],
    'payments_page' => [
        'fields' => [
            'paysera_project_id' => 'Project ID',
            'paysera_sign_password' => 'Sign password',
            'revolut_public_key' => 'Public API key (optional)',
        ],
    ],
];
$ukFixed['admin'] = array_replace_recursive($ukFixed['admin'], $ukMissing);

echo "Built: lang/admin/no.php, ru.php, sv.php\n";
echo 'RU keys: ' . count($ruAdmin, COUNT_RECURSIVE) . "\n";
echo 'NO keys: ' . count($noAdmin, COUNT_RECURSIVE) . "\n";
echo 'SV keys: ' . count($svAdmin, COUNT_RECURSIVE) . "\n";