<?php

/**
 * Market / locale profile for shop/site product pages.
 *
 * @return array{
 *   country: string,
 *   place_en: string,
 *   area: string,
 *   region: string,
 *   currency: string,
 *   currency_name: string,
 *   origin: string,
 *   in_country: string,
 *   for_country: string,
 *   for_market: string,
 *   service_en: string
 * }
 */
function shs_market(string $lang): array
{
    static $cache = [];
    if (isset($cache[$lang])) {
        return $cache[$lang];
    }

    $cache[$lang] = match ($lang) {
        'no' => [
            'country'       => 'Norge',
            'place_en'      => 'Norway',
            'area'          => 'Skandinavia',
            'region'        => 'NO',
            'currency'      => 'NOK',
            'currency_name' => 'norske kroner',
            'origin'        => 'Norge',
            'in_country'    => 'i Norge',
            'for_country'   => 'for Norge og Skandinavia',
            'for_market'    => 'for Norge',
            'service_en'    => 'Custom PHP e-commerce websites for fashion, electronics, food and B2B catalogues in Norway and Scandinavia.',
        ],
        'sv' => [
            'country'       => 'Sverige',
            'place_en'      => 'Sweden',
            'area'          => 'Norden',
            'region'        => 'SE',
            'currency'      => 'SEK',
            'currency_name' => 'kronor',
            'origin'        => 'Norge',
            'in_country'    => 'i Sverige',
            'for_country'   => 'för Sverige och Norden',
            'for_market'    => 'för Sverige',
            'service_en'    => 'Custom PHP e-commerce websites for fashion, electronics, food and B2B catalogues in Sweden and the Nordics.',
        ],
        'uk' => [
            'country'       => 'Україна',
            'place_en'      => 'Ukraine',
            'area'          => 'Європа',
            'region'        => 'UA',
            'currency'      => 'UAH',
            'currency_name' => 'гривня',
            'origin'        => 'Норвегії',
            'in_country'    => 'в Україні',
            'for_country'   => 'для України та Європи',
            'for_market'    => 'для України',
            'service_en'    => 'Custom PHP e-commerce websites for fashion, electronics, food and B2B catalogues in Ukraine.',
        ],
        'ru' => [
            'country'       => 'Россия',
            'place_en'      => 'Russia',
            'area'          => 'Европа',
            'region'        => 'RU',
            'currency'      => 'RUB',
            'currency_name' => 'рубль',
            'origin'        => 'Норвегии',
            'in_country'    => 'в России',
            'for_country'   => 'для России и Европы',
            'for_market'    => 'для России',
            'service_en'    => 'Custom PHP e-commerce websites for fashion, electronics, food and B2B catalogues in Russia and Europe.',
        ],
        'lt' => [
            'country'       => 'Lietuva',
            'place_en'      => 'Lithuania',
            'area'          => 'Europa',
            'region'        => 'LT',
            'currency'      => 'EUR',
            'currency_name' => 'euras',
            'origin'        => 'Norvegijos',
            'in_country'    => 'Lietuvoje',
            'for_country'   => 'Lietuvai ir Europai',
            'for_market'    => 'Lietuvai',
            'service_en'    => 'Custom PHP e-commerce websites for fashion, electronics, food and B2B catalogues in Lithuania and Europe.',
        ],
        default => [
            'country'       => 'Europe',
            'place_en'      => 'Europe',
            'area'          => 'Europe',
            'region'        => 'EU',
            'currency'      => 'EUR',
            'currency_name' => 'euro',
            'origin'        => 'Norway',
            'in_country'    => 'in Europe',
            'for_country'   => 'for Europe',
            'for_market'    => 'for Europe',
            'service_en'    => 'Custom PHP e-commerce websites for fashion, electronics, food and B2B catalogues across Europe.',
        ],
    };

    return $cache[$lang];
}

/** @param array<string, mixed> $data */
function shs_apply_market_placeholders(array $data, array $market): array
{
    $replacements = [
        '{country}'       => $market['country'],
        '{area}'          => $market['area'],
        '{currency}'      => $market['currency'],
        '{currency_name}' => $market['currency_name'],
        '{origin}'        => $market['origin'],
        '{in_country}'    => $market['in_country'],
        '{for_country}'   => $market['for_country'],
        '{for_market}'    => $market['for_market'],
    ];

    $out = [];
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $out[$key] = shs_apply_market_placeholders($value, $market);
            continue;
        }
        if (!is_string($value)) {
            $out[$key] = $value;
            continue;
        }
        $out[$key] = strtr($value, $replacements);
    }
    return $out;
}

/** @param array<string, mixed> $t */
function shs_apply_market_translations(array $t, string $lang): array
{
    return shs_apply_market_placeholders($t, shs_market($lang));
}