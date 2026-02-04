<?php

/**
 * Component: SEO Head
 * Подключать в начале <head>, ПЕРЕД wp_head().
 */

if (!defined('ABSPATH')) exit;
if (defined('SEO_HEAD_PRINTED')) return;
define('SEO_HEAD_PRINTED', true);

/* ================= helpers ================= */

function _seo_decode_entities(string $s): string
{
    $s = html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function _seo_trim_170(string $s): string
{
    $s = trim(preg_replace('~\s+~u', ' ', wp_strip_all_tags($s)));
    $s = _seo_decode_entities($s);
    if (mb_strlen($s, 'UTF-8') > 170) $s = mb_substr($s, 0, 169, 'UTF-8') . '…';
    return $s;
}

/** Взять первые 170 символов из HTML ПОСЛЕ первого </h1> */
function _seo_take_after_h1_170(string $html): string
{
    if ($html === '') return '';
    if (preg_match('~</h1>~iu', $html, $m, PREG_OFFSET_CAPTURE)) {
        $pos  = $m[0][1] + strlen($m[0][0]);
        $html = substr($html, $pos);
    }
    return _seo_trim_170($html);
}

function _seo_ru_years($n): string
{
    $n  = abs((int)$n);
    $n1 = $n % 10;
    $n2 = $n % 100;
    if ($n1 == 1 && $n2 != 11) return 'год';
    if ($n1 >= 2 && $n1 <= 4 && ($n2 < 10 || $n2 >= 20)) return 'года';
    return 'лет';
}

function _seo_get_meta_str(int $post_id, string $key): string
{
    if (function_exists('get_field')) {
        $v = get_field($key, $post_id);
        if (is_string($v) && $v !== '') return $v;
    }
    $v = get_post_meta($post_id, $key, true);
    return is_string($v) ? $v : '';
}

/** Найти соответствующий терм по slug в таксономии */
function _seo_find_term_by_slug(string $taxonomy, string $slug)
{
    $slug = sanitize_title($slug);
    if (!$slug) return null;
    $t = get_term_by('slug', $slug, $taxonomy);
    return ($t && !is_wp_error($t)) ? $t : null;
}

/** Посчитать количество анкет models, привязанных к терму */
function _seo_count_models_by_term($term, string $taxonomy): int
{
    if (!$term) return 0;

    $q = new WP_Query([
        'post_type'      => 'models',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'no_found_rows'  => false,
        'tax_query'      => [[
            'taxonomy' => $taxonomy,
            'field'    => 'term_id',
            'terms'    => [(int)$term->term_id],
            'operator' => 'IN',
        ]],
    ]);

    $n = (int) $q->found_posts;
    wp_reset_postdata();
    return $n;
}

/** Найти минимальную цену среди моделей, привязанных к терму, и вернуть её как строку */
function _seo_min_price_label_by_term($term, string $taxonomy): string
{
    if (!$term) return '';

    $q = new WP_Query([
        'post_type'      => 'models',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'tax_query'      => [[
            'taxonomy' => $taxonomy,
            'field'    => 'term_id',
            'terms'    => [(int)$term->term_id],
            'operator' => 'IN',
        ]],
    ]);

    $min_num   = null;
    $min_label = '';

    foreach ((array) $q->posts as $pid) {
        $label = '';

        if (function_exists('get_field')) {
            $label = (string) get_field('price', $pid);
            if ($label === '') $label = (string) get_field('price_from', $pid);
        }
        if ($label === '') {
            $label = (string) get_post_meta($pid, 'price', true);
            if ($label === '') $label = (string) get_post_meta($pid, 'price_from', true);
        }

        $label = trim($label);
        if ($label === '') continue;

        $num = (int) preg_replace('~\D+~', '', $label);
        if ($num <= 0) continue;

        if ($min_num === null || $num < $min_num) {
            $min_num   = $num;
            $min_label = $label;
        }
    }

    wp_reset_postdata();
    return $min_label !== '' ? _seo_decode_entities($min_label) : '';
}

/** Список станций метро для анкеты models */
function _seo_get_model_metro_list(int $post_id): string
{
    $taxes = ['metro_tax', 'metro'];
    foreach ($taxes as $tax) {
        $terms = get_the_terms($post_id, $tax);
        if (!empty($terms) && !is_wp_error($terms)) {
            // Берем первый элемент массива терминов
            $first_term = reset($terms);
            
            if (!empty($first_term->name)) {
                // Возвращаем очищенное имя сразу же
                return _seo_decode_entities($first_term->name);
            }
        }
    }
    return '';
}

/** Текущий контекст */
function _seo_ctx(): array
{
    $id  = (int) get_queried_object_id();
    $obj = get_queried_object();
    $paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));

    return [
        'id'          => $id,
        'obj'         => $obj,
        'post_type'   => $id ? get_post_type($id) : '',
        'is_home'     => (is_front_page() || is_home()),
        'is_singular' => is_singular(),
        'site'        => wp_strip_all_tags(get_bloginfo('name', 'display')),
        'slug'        => (is_singular() && is_object($obj) && !empty($obj->post_name)) ? $obj->post_name : '',
        'paged'       => $paged > 0 ? $paged : 1,
    ];
}

/** Каноникал */
function _seo_canonical(array $ctx): string
{
    $paged = max(1, (int) $ctx['paged']);
    $base  = ($ctx['is_singular'] && $ctx['id']) ? get_permalink($ctx['id']) : home_url(add_query_arg([]));

    if ($ctx['is_home'] && $paged === 1) return home_url('/');
    if ($paged > 1) return get_pagenum_link($paged);

    return $base;
}

function _seo_append_page_suffix(string $text, int $paged): string
{
    if ($paged <= 1) return $text;
    $text = trim($text);
    if ($text === '') return '';
    return _seo_trim_170($text . ' — страница ' . $paged);
}

/* ================= генерация заголовков/descr ================= */

/**
 * TITLE:
 * - metro:  Проститутки {станция} Ⓜ️ - {N} доступных анкет, шлюхи метро {станция} от {мин. цена} 24/7! Конфиденциально!
 * - rajon:  Проститутки в {название} районе - {N} доступных анкет, шлюхи район {название района} от {мин. цена} 24/7! Конфиденциально!
 * - uslugi: Снять проститутку с услугой {услуга} в Алматы - доступно {N} анкет - 24/7 конфиденциально!
 * - models: {имя} - проститутка у метро {станции}. Возраст - {возраст}, рост - {рост}, размер груди - {размер груди}
 * - прочие: meta "title" или стандартный заголовок записи.
 */
function _seo_build_title(array $ctx): string
{
    $site = $ctx['site'];
    $pt   = $ctx['post_type'];
    $slug = $ctx['slug'];

    // Карта CPT -> taxonomy (для metro/rajon/uslugi/vozrast/rost/price)
    $tx_map = [
        'metro'   => 'metro_tax',
        'rajon'   => 'rayonu_tax',
        'uslugi'  => 'uslugi_tax',
        'vozrast' => 'vozrast_tax',
        'rost'    => 'rost_tax',
        'price'   => 'price_tax',
    ];

    // Иерархические "страничные" CPT
    if ($ctx['is_singular'] && $pt && isset($tx_map[$pt]) && $ctx['id']) {
        $title_field = _seo_get_meta_str($ctx['id'], 'title');
        if ($title_field !== '') return _seo_decode_entities($title_field);

        $tax = $tx_map[$pt];
        $term = _seo_find_term_by_slug($tax, $slug);
        $cat_name = $term ? $term->name : get_the_title($ctx['id']);
        $cat_name = _seo_decode_entities($cat_name);

        if ($pt === 'metro') {
            $n       = _seo_count_models_by_term($term, $tax);
            $min_str = _seo_min_price_label_by_term($term, $tax);

            $base = "Проститутки {$cat_name} Ⓜ️";
            if ($n > 0) {
                $base .= " - {$n} доступных анкет";
            }

            $tail = "шлюхи метро {$cat_name}";
            if ($min_str !== '') {
                $tail .= " от {$min_str}";
            }
            $tail .= " 24/7! Конфиденциально!";

            return $base . ', ' . $tail;
        }

        if ($pt === 'rajon') {
            $n       = _seo_count_models_by_term($term, $tax);
            $min_str = _seo_min_price_label_by_term($term, $tax);

            $base = "Проститутки в {$cat_name} районе";
            if ($n > 0) {
                $base .= " - {$n} доступных анкет";
            }

            $tail = "шлюхи район {$cat_name}";
            if ($min_str !== '') {
                $tail .= " от {$min_str}";
            }
            $tail .= " 24/7! Конфиденциально!";

            return $base . ', ' . $tail;
        }

        if ($pt === 'uslugi') {
            $n     = _seo_count_models_by_term($term, $tax);
            $title = "Снять проститутку с услугой {$cat_name} в Алматы";
            if ($n > 0) {
                $title .= " - доступно {$n} анкет";
            }
            $title .= " - 24/7 конфиденциально!";
            return $title;
        }

        if ($pt === 'vozrast') {
            return "{$cat_name} Эскорт модели в Алматы по возрасту - снять эскортницу анонимно 24/7";
        }

        if ($pt === 'rost') {
            return "Эскортницы {$cat_name} - анкеты эскорт моделей по росту в Алматы";
        }

        if ($pt === 'price') {
            return "{$cat_name} эскорт модели Алматы. Подобрать эскортницу по цене 24/7";
        }
    }

    // Страница анкеты models
    if ($ctx['is_singular'] && $pt === 'models' && $ctx['id']) {
        if (function_exists('get_field')) {
            $name = get_field('name', $ctx['id']) ?: get_the_title($ctx['id']);
        } else {
            $name = get_the_title($ctx['id']);
        }
        $name = _seo_decode_entities($name);

        $metro_list = _seo_get_model_metro_list($ctx['id']);

        $age    = function_exists('get_field') ? trim((string) get_field('age', $ctx['id'])) : '';
        $height = function_exists('get_field') ? trim((string) (get_field('height', $ctx['id']) ?: get_field('rost', $ctx['id']))) : '';
        $bust   = function_exists('get_field') ? trim((string) get_field('bust', $ctx['id'])) : '';

        $first = $metro_list !== ''
            ? "{$name} - проститутка у метро {$metro_list}."
            : "{$name} - проститутка в Алматы.";

        $parts = [];
        if ($age !== '')    $parts[] = "Возраст - {$age}";
        if ($height !== '') $parts[] = "рост - {$height}";
        if ($bust !== '')   $parts[] = "размер груди - {$bust}";

        $details = $parts ? ' ' . implode(', ', $parts) : '';

        return $first . $details;
    }

    // Прочие singular: ручной title или заголовок записи
    if ($ctx['is_singular'] && $ctx['id']) {
        $manual = _seo_get_meta_str($ctx['id'], 'title');
        if ($manual !== '') return _seo_decode_entities($manual);
        $t = _seo_decode_entities(get_the_title($ctx['id']));
        return $t;
    }

    return $site;
}

/**
 * DESCRIPTION:
 * - metro/rajon/uslugi: 170 символов текста ПОСЛЕ </h1> из p_atc
 * - models: 170 символов из ACF 'description'
 * - прочие: meta 'descr' или excerpt/content
 */
function _seo_build_descr(array $ctx): string
{
    $pt = $ctx['post_type'];

    // metro / rajon / uslugi — p_atc, текст после H1
    if ($ctx['is_singular'] && in_array($pt, ['metro', 'rajon', 'vozrast', 'rost', 'price', 'uslugi'], true) && $ctx['id']) {
        $p_atc = _seo_get_meta_str($ctx['id'], 'p_atc');
        if ($p_atc !== '') return _seo_take_after_h1_170($p_atc);

        $d = _seo_get_meta_str($ctx['id'], 'descr');
        if ($d !== '') return _seo_trim_170($d);
    }

    // models: ACF description
    if ($ctx['is_singular'] && $pt === 'models' && $ctx['id']) {
        $raw = function_exists('get_field') ? (string) get_field('description', $ctx['id']) : '';
        if ($raw !== '') return _seo_trim_170($raw);

        $raw = get_post_field('post_excerpt', $ctx['id']) ?: get_post_field('post_content', $ctx['id']);
        if ($raw) return _seo_trim_170($raw);
    }

    // Прочие singular: descr / excerpt / content
    if ($ctx['is_singular'] && $ctx['id']) {
        $d = _seo_get_meta_str($ctx['id'], 'descr');
        if ($d !== '') return _seo_trim_170($d);

        $raw = get_post_field('post_excerpt', $ctx['id']) ?: get_post_field('post_content', $ctx['id']);
        if ($raw) return _seo_trim_170($raw);
    }

    if ($ctx['is_home']) {
        return 'Эскорт-модели с фото, видео и ценами. Фильтры по возрасту, району, метро, национальности. Обновляем ежедневно.';
    }

    return '';
}

/** OG картинка */
function _seo_og_image(): array
{
    if (is_singular() && has_post_thumbnail()) {
        $id  = get_post_thumbnail_id();
        $src = wp_get_attachment_image_src($id, 'large');
        return [
            'url'    => $src[0] ?? '',
            'width'  => $src[1] ?? '',
            'height' => $src[2] ?? '',
            'alt'    => trim(wp_strip_all_tags(get_post_meta($id, '_wp_attachment_image_alt', true))),
        ];
    }
    return ['url' => home_url('/apple-touch-icon.png')];
}

/* ================= build & print ================= */

$ctx       = _seo_ctx();
$title     = _seo_build_title($ctx);
$descr     = _seo_build_descr($ctx);
$title     = _seo_append_page_suffix($title, $ctx['paged']);
$descr     = ($ctx['paged'] > 1) ? '' : _seo_append_page_suffix($descr, $ctx['paged']);
$canonical = _seo_canonical($ctx);
if ($ctx['paged'] > 1) {
    $canonical = untrailingslashit($canonical); // для пагинации убираем закрывающий слэш
}
$og        = _seo_og_image();
$og_type   = (is_singular() ? 'article' : 'website');

set_query_var('seo_title', $title);
set_query_var('seo_descr', $descr);
$GLOBALS['seo_title'] = $title;
$GLOBALS['seo_descr'] = $descr;

remove_action('wp_head', 'rel_canonical');

echo '<title>' . esc_html($title) . "</title>\n";
if ($descr !== '') {
    echo '<meta name="description" content="' . esc_attr($descr) . "\" />\n";
}
echo '<link rel="canonical" href="' . esc_url($canonical) . "\" />\n";
echo '<meta name="robots" content="index,follow,max-snippet:-1,max-video-preview:-1" />' . "\n";

echo '<meta property="og:type" content="' . esc_attr($og_type) . "\" />\n";
echo '<meta property="og:title" content="' . esc_attr($title) . "\" />\n";
if ($descr !== '') {
    echo '<meta property="og:description" content="' . esc_attr($descr) . "\" />\n";
}
echo '<meta property="og:url" content="' . esc_url($canonical) . "\" />\n";
if (!empty($og['url'])) {
    echo '<meta property="og:image" content="' . esc_url($og['url']) . "\" />\n";
    if (!empty($og['alt']))    echo '<meta property="og:image:alt" content="' . esc_attr($og['alt']) . "\" />\n";
    if (!empty($og['width']))  echo '<meta property="og:image:width" content="' . (int) $og['width'] . "\" />\n";
    if (!empty($og['height'])) echo '<meta property="og:image:height" content="' . (int) $og['height'] . "\" />\n";
}
