<?php
// json-ld/person.php — разметка Person для одиночной модели
if (!is_singular('models')) return;

$id  = get_the_ID();
$url = get_permalink($id);

// ===== Базовые поля =====
$name = function_exists('get_field') ? (get_field('name', $id) ?: get_the_title($id)) : get_the_title($id);

$desc_raw  = get_the_excerpt($id);
if (!$desc_raw && function_exists('get_field')) {
    $desc_raw = get_field('description', $id) ?: '';
}
$desc_text = html_entity_decode(wp_strip_all_tags($desc_raw), ENT_QUOTES | ENT_HTML5, 'UTF-8');

// Пол
$gender = 'https://schema.org/Female';
if (function_exists('get_field')) {
    $g = trim((string) get_field('gender', $id));
    if ($g) {
        $gl = mb_strtolower($g, 'UTF-8');
        if (in_array($gl, ['m', 'male', 'муж', 'мужчина'], true)) {
            $gender = 'https://schema.org/Male';
        }
    }
}

// ===== Изображения =====
$images = [];
$placeholder = get_stylesheet_directory_uri() . '/assets/images/placeholder-thumbs.webp';

if (function_exists('get_field')) {
    $gallery = get_field('photo', $id);
    if (is_array($gallery)) {
        foreach ($gallery as $item) {
            if (!empty($item['url'])) $images[] = esc_url($item['url']);
        }
    }
}
if (empty($images)) {
    $thumb = get_the_post_thumbnail_url($id, 'large');
    if ($thumb) $images[] = esc_url($thumb);
}
if (empty($images)) $images[] = $placeholder;

// ===== Параметры =====
$age    = function_exists('get_field') ? (int) get_field('age', $id) : 0;
$height = function_exists('get_field') ? (int) get_field('height', $id) : 0;
$weight = function_exists('get_field') ? (int) get_field('weight', $id) : 0;
$bust   = function_exists('get_field') ? (string) get_field('bust', $id) : '';

// ===== Цены (без «услуг» таксы) =====
$currency = 'RUB';
$price_apart_1h = function_exists('get_field') ? (float) get_field('price', $id) : 0.0;
$price_out_1h   = function_exists('get_field') ? (float) get_field('price_outcall', $id) : 0.0;

$price_apart_2h = $price_apart_1h > 0 ? $price_apart_1h * 2 : 0.0;
$price_out_2h   = $price_out_1h   > 0 ? $price_out_1h   * 2 : 0.0;

$offer_items = [];
if ($price_apart_1h > 0) {
    $offer_items[] = [
        "@type" => "Offer",
        "itemOffered" => ["@type" => "Service", "name" => "Апартаменты: 1 час"],
        "priceSpecification" => ["@type" => "UnitPriceSpecification", "price" => $price_apart_1h, "priceCurrency" => $currency, "unitText" => "час"],
        "url" => $url . "#apartments-1h"
    ];
}
if ($price_apart_2h > 0) {
    $offer_items[] = [
        "@type" => "Offer",
        "itemOffered" => ["@type" => "Service", "name" => "Апартаменты: 2 часа"],
        "priceSpecification" => ["@type" => "UnitPriceSpecification", "price" => $price_apart_2h, "priceCurrency" => $currency, "unitText" => "2 часа"],
        "url" => $url . "#apartments-2h"
    ];
}
if ($price_out_1h > 0) {
    $offer_items[] = [
        "@type" => "Offer",
        "itemOffered" => ["@type" => "Service", "name" => "Выезд: 1 час"],
        "priceSpecification" => ["@type" => "UnitPriceSpecification", "price" => $price_out_1h, "priceCurrency" => $currency, "unitText" => "час"],
        "url" => $url . "#outcall-1h"
    ];
}
if ($price_out_2h > 0) {
    $offer_items[] = [
        "@type" => "Offer",
        "itemOffered" => ["@type" => "Service", "name" => "Выезд: 2 часа"],
        "priceSpecification" => ["@type" => "UnitPriceSpecification", "price" => $price_out_2h, "priceCurrency" => $currency, "unitText" => "2 часа"],
        "url" => $url . "#outcall-2h"
    ];
}

// ===== Related models =====
$related_json = [];
$related_ids  = get_post_meta($id, 'related_models_ids', true);
if (is_array($related_ids)) {
    $related_ids = array_values(array_unique(array_filter(array_map('intval', $related_ids))));
    $related_ids = array_slice(array_diff($related_ids, [$id]), 0, 4);
}
if (empty($related_ids)) {
    $prio_tax = ['rayonu_tax', 'vozrast_tax']; // uslugi_tax убрали из приоритета
    $picked_tax = '';
    $picked_terms = [];

    foreach ($prio_tax as $tx) {
        $tids = wp_get_object_terms($id, $tx, ['fields' => 'ids']);
        if (!is_wp_error($tids) && !empty($tids)) {
            $picked_tax = $tx;
            $picked_terms = $tids;
            break;
        }
    }

    if ($picked_tax && $picked_terms) {
        $ids = get_posts([
            'post_type'      => 'models',
            'post_status'    => 'publish',
            'posts_per_page' => 4,
            'post__not_in'   => [$id],
            'no_found_rows'  => true,
            'fields'         => 'ids',
            'orderby'        => 'date',
            'order'          => 'DESC',
            'tax_query'      => [[
                'taxonomy' => $picked_tax,
                'field'    => 'term_id',
                'terms'    => $picked_terms,
                'operator' => 'IN',
            ]],
        ]);
        $related_ids = is_array($ids) ? $ids : [];
    }
}

if (!empty($related_ids)) {
    foreach ($related_ids as $rid) {
        if (get_post_status($rid) !== 'publish') continue;

        $r_name = function_exists('get_field') ? (get_field('name', $rid) ?: get_the_title($rid)) : get_the_title($rid);
        $r_url  = get_permalink($rid);
        if (!$r_name || !$r_url) continue;

        $r_img = '';
        if (function_exists('get_field')) {
            $r_gallery = get_field('photo', $rid);
            if (is_array($r_gallery) && !empty($r_gallery[0]['url'])) {
                $r_img = esc_url($r_gallery[0]['url']);
            }
        }
        if (!$r_img) {
            $r_img = get_the_post_thumbnail_url($rid, 'medium') ?: $placeholder;
        }

        $related_json[] = [
            "@type" => "Person",
            "@id"   => $r_url . "#person",
            "name"  => $r_name,
            "url"   => $r_url,
            "image" => $r_img,
        ];
    }
}

// ===== Сборка JSON-LD =====
$schema = [
    "@context" => "https://schema.org",
    "@type"    => "Person",
    "@id"      => $url . "#person",
    "name"     => $name,
    "url"      => $url,
    "image"    => $images,
    "description" => $desc_text,
    "gender"   => $gender,
];

if ($height > 0) {
    $schema['height'] = ["@type" => "QuantitativeValue", "value" => $height, "unitCode" => "CMT"];
}
if ($weight > 0) {
    $schema['weight'] = ["@type" => "QuantitativeValue", "value" => $weight, "unitCode" => "KGM"];
}
if ($bust !== '') {
    $schema['additionalProperty'] = ["@type" => "PropertyValue", "name" => "Размер груди", "value" => $bust];
}
// Только цены (если есть)
if (!empty($offer_items)) {
    $schema['hasOfferCatalog'] = [
        "@type" => "OfferCatalog",
        "name"  => "Цены",
        "itemListElement" => $offer_items
    ];
}
if (!empty($related_json)) {
    $schema['relatedTo'] = $related_json;
}

// Вывод
echo '<script type="application/ld+json">' .
    wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) .
    '</script>';
