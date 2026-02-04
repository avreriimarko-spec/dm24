<?php
global $post;

if (is_front_page() || is_home() || ! $post) {
    return;
}

$breadcrumbs = [
    '@context'       => 'https://schema.org',
    '@type'          => 'BreadcrumbList',
    '@id'            => get_permalink($post) . '#breadcrumb',
    'itemListElement' => [],
];

$pos = 1;

// Главная
$breadcrumbs['itemListElement'][] = [
    '@type'    => 'ListItem',
    'position' => $pos++,
    'name'     => 'Главная',
    'item'     => home_url('/'),
];

// Родители (если есть)
$parents = array_reverse(get_post_ancestors($post));
foreach ($parents as $parent_id) {
    $breadcrumbs['itemListElement'][] = [
        '@type'    => 'ListItem',
        'position' => $pos++,
        'name'     => get_the_title($parent_id),
        'item'     => get_permalink($parent_id),
    ];
}

// Текущая страница
$breadcrumbs['itemListElement'][] = [
    '@type'    => 'ListItem',
    'position' => $pos++,
    'name'     => get_the_title($post),
    'item'     => get_permalink($post),
];

// Вывод JSON-LD
echo '<script type="application/ld+json">' .
    wp_json_encode($breadcrumbs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) .
    '</script>';
