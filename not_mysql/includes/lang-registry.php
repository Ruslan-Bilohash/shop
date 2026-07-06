<?php
/**
 * World language registry for AI translation & admin language picker.
 * Source UI strings always translate from lang/en.php.
 *
 * @return array<string, array{name:string,label:string,flag:string,locale:string,html:string,region:string}>
 */
function sh_world_languages(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $rows = [
        ['en', 'English', 'EN', '🇬🇧', 'en-GB', 'en', 'Europe'],
        ['no', 'Norsk', 'NO', '🇳🇴', 'nb-NO', 'no', 'Europe'],
        ['nb', 'Norwegian Bokmål', 'NB', '🇳🇴', 'nb-NO', 'nb', 'Europe'],
        ['nn', 'Norsk Nynorsk', 'NN', '🇳🇴', 'nn-NO', 'nn', 'Europe'],
        ['sv', 'Svenska', 'SV', '🇸🇪', 'sv-SE', 'sv', 'Europe'],
        ['da', 'Dansk', 'DA', '🇩🇰', 'da-DK', 'da', 'Europe'],
        ['fi', 'Suomi', 'FI', '🇫🇮', 'fi-FI', 'fi', 'Europe'],
        ['is', 'Íslenska', 'IS', '🇮🇸', 'is-IS', 'is', 'Europe'],
        ['uk', 'Українська', 'UA', '🇺🇦', 'uk-UA', 'uk', 'Europe'],
        ['ru', 'Русский', 'RU', '🇷🇺', 'ru-RU', 'ru', 'Europe'],
        ['be', 'Беларуская', 'BE', '🇧🇾', 'be-BY', 'be', 'Europe'],
        ['pl', 'Polski', 'PL', '🇵🇱', 'pl-PL', 'pl', 'Europe'],
        ['cs', 'Čeština', 'CS', '🇨🇿', 'cs-CZ', 'cs', 'Europe'],
        ['sk', 'Slovenčina', 'SK', '🇸🇰', 'sk-SK', 'sk', 'Europe'],
        ['de', 'Deutsch', 'DE', '🇩🇪', 'de-DE', 'de', 'Europe'],
        ['de-at', 'Deutsch (Österreich)', 'AT', '🇦🇹', 'de-AT', 'de', 'Europe'],
        ['de-ch', 'Deutsch (Schweiz)', 'CH', '🇨🇭', 'de-CH', 'de', 'Europe'],
        ['fr', 'Français', 'FR', '🇫🇷', 'fr-FR', 'fr', 'Europe'],
        ['fr-ca', 'Français (Canada)', 'CA', '🇨🇦', 'fr-CA', 'fr', 'Americas'],
        ['es', 'Español', 'ES', '🇪🇸', 'es-ES', 'es', 'Europe'],
        ['es-mx', 'Español (México)', 'MX', '🇲🇽', 'es-MX', 'es', 'Americas'],
        ['pt', 'Português', 'PT', '🇵🇹', 'pt-PT', 'pt', 'Europe'],
        ['pt-br', 'Português (Brasil)', 'BR', '🇧🇷', 'pt-BR', 'pt', 'Americas'],
        ['it', 'Italiano', 'IT', '🇮🇹', 'it-IT', 'it', 'Europe'],
        ['nl', 'Nederlands', 'NL', '🇳🇱', 'nl-NL', 'nl', 'Europe'],
        ['ro', 'Română', 'RO', '🇷🇴', 'ro-RO', 'ro', 'Europe'],
        ['hu', 'Magyar', 'HU', '🇭🇺', 'hu-HU', 'hu', 'Europe'],
        ['bg', 'Български', 'BG', '🇧🇬', 'bg-BG', 'bg', 'Europe'],
        ['el', 'Ελληνικά', 'EL', '🇬🇷', 'el-GR', 'el', 'Europe'],
        ['tr', 'Türkçe', 'TR', '🇹🇷', 'tr-TR', 'tr', 'Europe'],
        ['sr', 'Српски', 'SR', '🇷🇸', 'sr-RS', 'sr', 'Europe'],
        ['hr', 'Hrvatski', 'HR', '🇭🇷', 'hr-HR', 'hr', 'Europe'],
        ['sl', 'Slovenščina', 'SL', '🇸🇮', 'sl-SI', 'sl', 'Europe'],
        ['lt', 'Lietuvių', 'LT', '🇱🇹', 'lt-LT', 'lt', 'Europe'],
        ['lv', 'Latviešu', 'LV', '🇱🇻', 'lv-LV', 'lv', 'Europe'],
        ['et', 'Eesti', 'ET', '🇪🇪', 'et-EE', 'et', 'Europe'],
        ['ga', 'Gaeilge', 'GA', '🇮🇪', 'ga-IE', 'ga', 'Europe'],
        ['mt', 'Malti', 'MT', '🇲🇹', 'mt-MT', 'mt', 'Europe'],
        ['sq', 'Shqip', 'SQ', '🇦🇱', 'sq-AL', 'sq', 'Europe'],
        ['mk', 'Македонски', 'MK', '🇲🇰', 'mk-MK', 'mk', 'Europe'],
        ['bs', 'Bosanski', 'BS', '🇧🇦', 'bs-BA', 'bs', 'Europe'],
        ['ca', 'Català', 'CA', '🇪🇸', 'ca-ES', 'ca', 'Europe'],
        ['eu', 'Euskara', 'EU', '🇪🇸', 'eu-ES', 'eu', 'Europe'],
        ['gl', 'Galego', 'GL', '🇪🇸', 'gl-ES', 'gl', 'Europe'],
        ['ar', 'العربية', 'AR', '🇸🇦', 'ar-SA', 'ar', 'Middle East'],
        ['he', 'עברית', 'HE', '🇮🇱', 'he-IL', 'he', 'Middle East'],
        ['fa', 'فارسی', 'FA', '🇮🇷', 'fa-IR', 'fa', 'Middle East'],
        ['ur', 'اردو', 'UR', '🇵🇰', 'ur-PK', 'ur', 'Middle East'],
        ['hi', 'हिन्दी', 'HI', '🇮🇳', 'hi-IN', 'hi', 'Asia'],
        ['bn', 'বাংলা', 'BN', '🇧🇩', 'bn-BD', 'bn', 'Asia'],
        ['ta', 'தமிழ்', 'TA', '🇮🇳', 'ta-IN', 'ta', 'Asia'],
        ['te', 'తెలుగు', 'TE', '🇮🇳', 'te-IN', 'te', 'Asia'],
        ['mr', 'मराठी', 'MR', '🇮🇳', 'mr-IN', 'mr', 'Asia'],
        ['gu', 'ગુજરાતી', 'GU', '🇮🇳', 'gu-IN', 'gu', 'Asia'],
        ['kn', 'ಕನ್ನಡ', 'KN', '🇮🇳', 'kn-IN', 'kn', 'Asia'],
        ['ml', 'മലയാളം', 'ML', '🇮🇳', 'ml-IN', 'ml', 'Asia'],
        ['pa', 'ਪੰਜਾਬੀ', 'PA', '🇮🇳', 'pa-IN', 'pa', 'Asia'],
        ['ne', 'नेपाली', 'NE', '🇳🇵', 'ne-NP', 'ne', 'Asia'],
        ['si', 'සිංහල', 'SI', '🇱🇰', 'si-LK', 'si', 'Asia'],
        ['zh', '中文 (简体)', 'ZH', '🇨🇳', 'zh-CN', 'zh', 'Asia'],
        ['zh-tw', '中文 (繁體)', 'TW', '🇹🇼', 'zh-TW', 'zh', 'Asia'],
        ['ja', '日本語', 'JA', '🇯🇵', 'ja-JP', 'ja', 'Asia'],
        ['ko', '한국어', 'KO', '🇰🇷', 'ko-KR', 'ko', 'Asia'],
        ['vi', 'Tiếng Việt', 'VI', '🇻🇳', 'vi-VN', 'vi', 'Asia'],
        ['th', 'ไทย', 'TH', '🇹🇭', 'th-TH', 'th', 'Asia'],
        ['id', 'Bahasa Indonesia', 'ID', '🇮🇩', 'id-ID', 'id', 'Asia'],
        ['ms', 'Bahasa Melayu', 'MS', '🇲🇾', 'ms-MY', 'ms', 'Asia'],
        ['tl', 'Filipino', 'TL', '🇵🇭', 'fil-PH', 'fil', 'Asia'],
        ['my', 'မြန်မာ', 'MY', '🇲🇲', 'my-MM', 'my', 'Asia'],
        ['km', 'ខ្មែរ', 'KM', '🇰🇭', 'km-KH', 'km', 'Asia'],
        ['lo', 'ລາວ', 'LO', '🇱🇦', 'lo-LA', 'lo', 'Asia'],
        ['mn', 'Монгол', 'MN', '🇲🇳', 'mn-MN', 'mn', 'Asia'],
        ['kk', 'Қазақ', 'KK', '🇰🇿', 'kk-KZ', 'kk', 'Asia'],
        ['uz', 'Oʻzbek', 'UZ', '🇺🇿', 'uz-UZ', 'uz', 'Asia'],
        ['az', 'Azərbaycan', 'AZ', '🇦🇿', 'az-AZ', 'az', 'Asia'],
        ['ka', 'ქართული', 'KA', '🇬🇪', 'ka-GE', 'ka', 'Asia'],
        ['hy', 'Հայերեն', 'HY', '🇦🇲', 'hy-AM', 'hy', 'Asia'],
        ['sw', 'Kiswahili', 'SW', '🇰🇪', 'sw-KE', 'sw', 'Africa'],
        ['am', 'አማርኛ', 'AM', '🇪🇹', 'am-ET', 'am', 'Africa'],
        ['ha', 'Hausa', 'HA', '🇳🇬', 'ha-NG', 'ha', 'Africa'],
        ['yo', 'Yorùbá', 'YO', '🇳🇬', 'yo-NG', 'yo', 'Africa'],
        ['ig', 'Igbo', 'IG', '🇳🇬', 'ig-NG', 'ig', 'Africa'],
        ['zu', 'isiZulu', 'ZU', '🇿🇦', 'zu-ZA', 'zu', 'Africa'],
        ['af', 'Afrikaans', 'AF', '🇿🇦', 'af-ZA', 'af', 'Africa'],
        ['so', 'Soomaali', 'SO', '🇸🇴', 'so-SO', 'so', 'Africa'],
        ['rw', 'Kinyarwanda', 'RW', '🇷🇼', 'rw-RW', 'rw', 'Africa'],
        ['mg', 'Malagasy', 'MG', '🇲🇬', 'mg-MG', 'mg', 'Africa'],
        ['en-us', 'English (US)', 'US', '🇺🇸', 'en-US', 'en', 'Americas'],
        ['en-ca', 'English (Canada)', 'CA', '🇨🇦', 'en-CA', 'en', 'Americas'],
        ['en-au', 'English (Australia)', 'AU', '🇦🇺', 'en-AU', 'en', 'Oceania'],
        ['en-nz', 'English (New Zealand)', 'NZ', '🇳🇿', 'en-NZ', 'en', 'Oceania'],
        ['es-ar', 'Español (Argentina)', 'AR', '🇦🇷', 'es-AR', 'es', 'Americas'],
        ['es-co', 'Español (Colombia)', 'CO', '🇨🇴', 'es-CO', 'es', 'Americas'],
        ['es-cl', 'Español (Chile)', 'CL', '🇨🇱', 'es-CL', 'es', 'Americas'],
        ['qu', 'Quechua', 'QU', '🇵🇪', 'qu-PE', 'qu', 'Americas'],
        ['ht', 'Kreyòl ayisyen', 'HT', '🇭🇹', 'ht-HT', 'ht', 'Americas'],
        ['mi', 'Te Reo Māori', 'MI', '🇳🇿', 'mi-NZ', 'mi', 'Oceania'],
        ['sm', 'Gagana Samoa', 'SM', '🇼🇸', 'sm-WS', 'sm', 'Oceania'],
        ['fj', 'Na Vosa Vakaviti', 'FJ', '🇫🇯', 'fj-FJ', 'fj', 'Oceania'],
        ['cy', 'Cymraeg', 'CY', '🇬🇧', 'cy-GB', 'cy', 'Europe'],
        ['lb', 'Lëtzebuergesch', 'LB', '🇱🇺', 'lb-LU', 'lb', 'Europe'],
        ['fo', 'Føroyskt', 'FO', '🇫🇴', 'fo-FO', 'fo', 'Europe'],
    ];
    $cache = [];
    foreach ($rows as $r) {
        $cache[$r[0]] = [
            'name'   => $r[1],
            'label'  => $r[2],
            'flag'   => $r[3],
            'locale' => $r[4],
            'html'   => $r[5],
            'region' => $r[6],
        ];
    }
    return $cache;
}

/** @return array<string, list<array{code:string,name:string,flag:string}>> */
function sh_world_languages_by_region(): array
{
    $grouped = [];
    foreach (sh_world_languages() as $code => $info) {
        $region = $info['region'] ?? 'Other';
        $grouped[$region][] = [
            'code' => $code,
            'name' => $info['name'],
            'flag' => $info['flag'],
        ];
    }
    ksort($grouped);
    return $grouped;
}

function sh_world_language_meta(string $code): ?array
{
    $code = strtolower(trim($code));
    $all = sh_world_languages();
    if (isset($all[$code])) {
        return array_merge(['code' => $code], $all[$code]);
    }
    if (preg_match('/^[a-z]{2,5}$/', $code)) {
        return [
            'code'   => $code,
            'name'   => ucfirst($code),
            'label'  => strtoupper($code),
            'flag'   => '🌐',
            'locale' => $code . '-' . strtoupper($code),
            'html'   => $code,
            'region' => 'Other',
        ];
    }
    return null;
}

function sh_lang_file_path(string $code): string
{
    return dirname(__DIR__) . '/lang/' . $code . '.php';
}