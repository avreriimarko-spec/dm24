<?php
// json-ld/person-list.php

// 1) Забираем модели и нормализуем
$models_data = get_query_var('models', []);
$models = [];

if (isset($models_data['models']) && is_array($models_data['models'])) {
    $models = $models_data['models'];
} else {
    $models = (array) $models_data;
}
if (empty($models) && !empty($GLOBALS['site_ldjson_models'])) {
    $models = (array) $GLOBALS['site_ldjson_models'];
}
if (empty($models)) return;

// 2) Хелперы
if (!function_exists('site_pl_first_image_from_gallery_field')) {
    /**
     * Извлекает первый URL из ACF-галереи (значение поля `photo`), принимая строку-URL,
     * ассоц.массив с ключом 'url', либо числовой массив из массивов/ID-шников.
     */
    function site_pl_first_image_from_gallery_field($val): string
    {
        // Строка-URL
        if (is_string($val) && filter_var($val, FILTER_VALIDATE_URL)) {
            return $val;
        }
        // Ассоц.массив вида ['url' => '...'] или с sizes
        if (is_array($val) && isset($val['url']) && is_string($val['url'])) {
            return $val['url'];
        }
        // Массив элементов: [ ['url'=>...], ['sizes'=>['medium'=>...]], 123, ... ]
        if (is_array($val) && !isset($val['url'])) {
            $first = reset($val);
            // Если первый элемент — массив
            if (is_array($first)) {
                if (isset($first['url']) && is_string($first['url'])) {
                    return $first['url'];
                }
                if (isset($first['sizes']['medium']) && is_string($first['sizes']['medium'])) {
                    return $first['sizes']['medium'];
                }
                if (isset($first['sizes']['large']) && is_string($first['sizes']['large'])) {
                    return $first['sizes']['large'];
                }
            }
            // Если это ID вложения
            if (is_int($first) || (is_string($first) && ctype_digit($first))) {
                $u = wp_get_attachment_image_url((int)$first, 'medium');
                if ($u) return $u;
            }
            // Пройдём все элементы на всякий случай
            foreach ($val as $it) {
                if (is_string($it) && filter_var($it, FILTER_VALIDATE_URL)) return $it;
                if (is_array($it) && isset($it['url']) && is_string($it['url'])) return $it['url'];
                if (is_array($it) && isset($it['sizes']['medium']) && is_string($it['sizes']['medium'])) return $it['sizes']['medium'];
                if (is_int($it) || (is_string($it) && ctype_digit($it))) {
                    $u = wp_get_attachment_image_url((int)$it, 'medium');
                    if ($u) return $u;
                }
            }
        }
        return '';
    }
}

if (!function_exists('site_pl_resolve_image')) {
    /**
     * Выбирает лучший URL картинки для модели:
     *   1) $m['photo'] (галерея ACF)
     *   2) $m['modelGalleryThumbnail']
     *   3) $m['image']
     *   4) по ID поста: ACF 'photo' -> миниатюра
     *   5) плейсхолдер
     */
    function site_pl_resolve_image(array $m): string
    {
        $placeholder = get_stylesheet_directory_uri() . '/assets/images/placeholder-thumbs.webp';

        // 1) Явно переданная галерея 'photo'
        if (isset($m['photo'])) {
            $u = site_pl_first_image_from_gallery_field($m['photo']);
            if ($u) return $u;
        }

        // 2) Старое поле
        if (isset($m['modelGalleryThumbnail'])) {
            $u = site_pl_first_image_from_gallery_field($m['modelGalleryThumbnail']);
            if ($u) return $u;
        }

        // 3) Уже подготовленное поле 'image'
        if (!empty($m['image']) && is_string($m['image']) && filter_var($m['image'], FILTER_VALIDATE_URL)) {
            return $m['image'];
        }

        // 4) Попытка достать по ID поста
        $pid = 0;
        if (isset($m['ID'])) $pid = (int)$m['ID'];
        if (!$pid && isset($m['id'])) $pid = (int)$m['id'];

        if ($pid > 0) {
            if (function_exists('get_field')) {
                $gal = get_field('photo', $pid);
                $u = site_pl_first_image_from_gallery_field($gal);
                if ($u) return $u;
            }
            $thumb = get_the_post_thumbnail_url($pid, 'medium');
            if ($thumb) return $thumb;
        }

        // 5) Плейсхолдер
        return $placeholder;
    }
}

// 3) Сборка ItemList
$personList = [
    "@context" => "https://schema.org",
    "@type"    => "ItemList",
    "@id"      => get_permalink() . "#person-list",
    "itemListElement" => [],
];

$seen = [];
$pos  = 1;

foreach ($models as $m) {
    $name = isset($m['name']) ? (string)$m['name'] : '';
    $uri  = isset($m['uri'])  ? (string)$m['uri']  : '';

    // если чего-то нет — попробуем добрать из ID
    if ((!$name || !$uri) && (isset($m['ID']) || isset($m['id']))) {
        $pid  = isset($m['ID']) ? (int)$m['ID'] : (int)$m['id'];
        if ($pid > 0) {
            if (!$name) $name = get_the_title($pid);
            if (!$uri)  $uri  = get_permalink($pid);
        }
    }
    if ($name === '' || $uri === '') continue;
    if (isset($seen[$uri])) continue;
    $seen[$uri] = true;

    $img = site_pl_resolve_image($m);

    $personList['itemListElement'][] = [
        "@type"    => "ListItem",
        "position" => $pos++,
        "item"     => [
            "@type" => "Person",
            "@id"   => $uri . "#person",
            "name"  => $name,
            "url"   => $uri,
            "image" => $img,
        ],
    ];
}

// 4) Вывод
if (!empty($personList['itemListElement'])) {
    echo "\n<script type=\"application/ld+json\">\n";
    echo wp_json_encode($personList, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    echo "\n</script>\n";
}
