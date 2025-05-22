<?php
global $post;

if (!isset($post)) return;

$post_id = $post->ID;
$title = get_the_title($post_id);
$url = get_permalink($post_id);
$site_url = home_url('/') . '';

$breadcrumbs = [
    "@context" => "https://schema.org",
    "@type" => "BreadcrumbList",
    "@id" => $url . "#breadcrumb",
    "itemListElement" => []
];

// Уровень 1: Главная
$breadcrumbs["itemListElement"][] = [
    "@type" => "ListItem",
    "position" => 1,
    "item" => [
        "@type" => "Thing",
        "@id" => $site_url,
        "name" => "Главная"
    ]
];

// Если не главная — добавляем второй уровень
if (!is_front_page()) {
    $breadcrumbs["itemListElement"][] = [
        "@type" => "ListItem",
        "position" => 2,
        "item" => [
            "@type" => "Thing",
            "@id" => $url,
            "name" => $title
        ]
    ];
}

// Выводим JSON-LD
echo '<script type="application/ld+json">' .
     wp_json_encode($breadcrumbs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) .
     '</script>';
