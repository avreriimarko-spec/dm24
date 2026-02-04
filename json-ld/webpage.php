<?php
// json-ld/webpage.php — использует готовые SEO из хедера

$id       = get_the_ID();
$url      = get_permalink($id);
$site_url = home_url('/');

// 1) Тянем Готовые SEO из хедера/шаблона
$seo_title = (string) get_query_var('seo_title', '');
$seo_descr = (string) get_query_var('seo_descr', '');

if ($seo_title === '' && !empty($GLOBALS['seo_title'])) {
    $seo_title = (string) $GLOBALS['seo_title'];
}
if ($seo_descr === '' && !empty($GLOBALS['seo_descr'])) {
    $seo_descr = (string) $GLOBALS['seo_descr'];
}

// 2) Фолбэки (на всякий): аккуратно подставим, если вдруг не прокинули
if ($seo_title === '') {
    // wp_get_document_title() уже учитывает текущий запрос и фильтры
    $seo_title = function_exists('wp_get_document_title')
        ? wp_get_document_title()
        : get_the_title($id);
}

if ($seo_descr === '') {
    // берём до 170 символов из поля WYSIWYG "content" (если есть) или из excerpt
    $raw = function_exists('get_field') ? (get_field('content', $id) ?: '') : '';
    if ($raw === '') {
        $raw = get_the_excerpt($id) ?: '';
    }
    $txt = wp_strip_all_tags((string) $raw);
    $txt = html_entity_decode($txt, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $txt = trim(preg_replace('/\s+/u', ' ', $txt));
    if (mb_strlen($txt, 'UTF-8') > 170) {
        $txt = rtrim(mb_substr($txt, 0, 170, 'UTF-8'), " \t\n\r\0\x0B,.;:!?") . '…';
    }
    $seo_descr = $txt;
}

// 3) Даты и mainEntity
$date_published = get_the_date('Y-m-d', $id);
$date_modified  = get_the_modified_date('Y-m-d', $id);
$main_entity_id = is_singular('models') ? ($url . '#person') : ($url . '#person-list');

// 4) Сборка JSON-LD WebPage
$webpage = [
    "@context"       => "https://schema.org",
    "@type"          => "WebPage",
    "@id"            => $url . '#webpage',
    "name"           => $seo_title,
    "description"    => wp_strip_all_tags($seo_descr),
    "url"            => $url,
    "datePublished"  => $date_published,
    "dateModified"   => $date_modified,
    "publisher"      => ["@id" => $site_url . '#organization'],
    "mainEntity"     => ["@id" => $main_entity_id],
];

// 5) Вывод
echo '<script type="application/ld+json">' .
    wp_json_encode($webpage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) .
    '</script>';
