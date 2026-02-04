<?php
// json-ld/index.php — диспетчер JSON-LD разметки (без дебагов)

// ===== 0) Гейты: не запускаем LD-JSON вне HTML-контекста
$uri = $_SERVER['REQUEST_URI'] ?? '';

if (
    is_admin()
    || wp_doing_ajax()
    || (defined('REST_REQUEST') && REST_REQUEST)
    || is_feed()
    || is_search()
    || is_404()
) {
    return;
}

// если веб-сервер по каким-то причинам проксирует ассеты через WP — отсечь
if ($uri && (strpos($uri, '/wp-content/') !== false || strpos($uri, '/wp-includes/') !== false)) {
    return;
}
// ===== 2) WebPage + Breadcrumb для одиночек
if (is_singular()) {
    get_template_part('json-ld/webpage');
    get_template_part('json-ld/breadcrumb');
}

// ===== 3) Организация — только на главной
if (is_front_page()) {
    get_template_part('json-ld/organization');
}


// ===== 5) Персона — для single('models')
if (is_singular('models')) {
    get_template_part('json-ld/person');
}
