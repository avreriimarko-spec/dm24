<?php

/**
 * Component: SEO Head
 * Подключать в начале <head>, ПЕРЕД wp_head().
 */

if (!defined('ABSPATH')) exit;
if (defined('SEO_HEAD_PRINTED')) return;
define('SEO_HEAD_PRINTED', true);
if (!defined('SEO_SITE_BRAND')) define('SEO_SITE_BRAND', 'dosugmoskva24');

/* ================= helpers ================= */

function _seo_site_brand(): string
{
    $name = trim((string) wp_strip_all_tags(get_bloginfo('name', 'display')));
    if ($name === '') return SEO_SITE_BRAND;

    // Если в настройках осталось старое имя проекта, принудительно используем актуальный бренд.
    if (preg_match('~almaty|kyzdarki~iu', $name)) return SEO_SITE_BRAND;

    return $name;
}

function _seo_normalize_brand_text(string $s): string
{
    $s = trim($s);
    if ($s === '') return '';

    $s = preg_replace('~almaty\s*kyzdarki~iu', SEO_SITE_BRAND, $s);
    $s = preg_replace('~almaty\.?kyzdarki(?:\.net|\.kz)?~iu', SEO_SITE_BRAND, $s);
    $s = preg_replace('~kyzdarki~iu', SEO_SITE_BRAND, $s);
    $s = preg_replace('~\s+~u', ' ', (string) $s);

    return trim((string) $s);
}

function _seo_normalize_descr_text(string $s): string
{
    $s = trim($s);
    if ($s === '') return '';

    // В description не используем домен/бренд, заменяем на нейтральное "сайте".
    $s = preg_replace('~almaty\s*kyzdarki~iu', 'сайте', $s);
    $s = preg_replace('~almaty\.?kyzdarki(?:\.net|\.kz)?~iu', 'сайте', $s);
    $s = preg_replace('~dosugmoskva24~iu', 'сайте', $s);
    $s = preg_replace('~kyzdarki~iu', 'сайте', $s);
    $s = preg_replace('~\bсайте\s+сайте\b~iu', 'сайте', $s);
    $s = preg_replace('~\s+~u', ' ', (string) $s);

    return trim((string) $s);
}

function _seo_is_individualki_page(array $ctx): bool
{
    if (function_exists('is_page') && is_page('individualki')) {
        return true;
    }

    if (!empty($ctx['id'])) {
        $slug = (string) get_post_field('post_name', (int) $ctx['id']);
        if ($slug === 'individualki') {
            return true;
        }
    }

    $pagename = trim((string) get_query_var('pagename'));
    return trim($pagename, '/') === 'individualki';
}

function _seo_strip_individualki_mentions(string $s): string
{
    if ($s === '') return '';

    $map = [
        'Индивидуалки'  => 'Проститутки',
        'индивидуалки'  => 'проститутки',
        'Индивидуалок'  => 'Проституток',
        'индивидуалок'  => 'проституток',
        'Индивидуалка'  => 'Проститутка',
        'индивидуалка'  => 'проститутка',
        'Индивидуалке'  => 'Проститутке',
        'индивидуалке'  => 'проститутке',
        'Индивидуалкам' => 'Проституткам',
        'индивидуалкам' => 'проституткам',
        'Индивидуалками' => 'Проститутками',
        'индивидуалками' => 'проститутками',
        'Индивидуалках' => 'Проститутках',
        'индивидуалках' => 'проститутках',
    ];

    return strtr($s, $map);
}

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

function _seo_min_price_num_by_term($term, string $taxonomy): int
{
    $label = _seo_min_price_label_by_term($term, $taxonomy);
    if ($label === '') return 0;
    return (int) preg_replace('~\D+~', '', $label);
}

function _seo_find_related_term_name($term, string $source_taxonomy, string $target_taxonomy): string
{
    if (!$term) return '';

    $q = new WP_Query([
        'post_type'      => 'models',
        'post_status'    => 'publish',
        'posts_per_page' => 140,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'tax_query'      => [[
            'taxonomy' => $source_taxonomy,
            'field'    => 'term_id',
            'terms'    => [(int) $term->term_id],
            'operator' => 'IN',
        ]],
    ]);

    $stat = [];
    foreach ((array) $q->posts as $pid) {
        $terms = get_the_terms((int) $pid, $target_taxonomy);
        if (empty($terms) || is_wp_error($terms)) continue;
        foreach ($terms as $t) {
            $tid = (int) $t->term_id;
            if (!isset($stat[$tid])) {
                $stat[$tid] = ['name' => (string) $t->name, 'cnt' => 0];
            }
            $stat[$tid]['cnt']++;
        }
    }
    wp_reset_postdata();

    if (empty($stat)) return '';
    uasort($stat, static function ($a, $b): int {
        return ((int) $b['cnt']) <=> ((int) $a['cnt']);
    });

    $top = reset($stat);
    return !empty($top['name']) ? _seo_decode_entities((string) $top['name']) : '';
}

function _seo_landing_kind_by_taxonomy(string $taxonomy): string
{
    $tax_to_kind = [
        'metro_tax' => 'metro',
        'rayonu_tax' => 'rajon',
        'uslugi_tax' => 'uslugi',
        'vozrast_tax' => 'appearance',
        'rost_tax' => 'appearance',
        'grud_tax' => 'appearance',
        'ves_tax' => 'appearance',
        'cvet-volos_tax' => 'appearance',
        'nationalnost_tax' => 'nationality',
        'price_tax' => 'price',
    ];
    return $tax_to_kind[$taxonomy] ?? '';
}

function _seo_build_landing_title_by_kind(string $kind, string $cat_name, string $price_txt = ''): string
{
    if ($kind === 'metro') {
        if ($price_txt !== '') {
            return "Проститутки метро {$cat_name} — интим услуги рядом с метро (цены от {$price_txt} руб.)";
        }
        return "Проститутки метро {$cat_name} — интим услуги рядом с метро (цены по договоренности)";
    }

    if ($kind === 'rajon') {
        return "Индивидуалки {$cat_name} — интим услуги в районе {$cat_name} (фото и цены)";
    }

    if ($kind === 'uslugi') {
        if ($price_txt !== '') {
            return "{$cat_name} Москва — заказать интим услуги в Москве (цены от {$price_txt} руб.)";
        }
        return "{$cat_name} Москва — заказать интим услуги в Москве (цены по договоренности)";
    }

    if ($kind === 'appearance') {
        return "{$cat_name} индивидуалки Москва — девушки с внешностью {$cat_name} в Москве";
    }

    if ($kind === 'nationality') {
        return "Проститутки национальности {$cat_name} в Москве — анкеты с фото и ценами";
    }

    if ($kind === 'price') {
        if ($price_txt !== '') {
            return "Проститутки по цене {$cat_name} в Москве — анкеты с фото (от {$price_txt} руб.)";
        }
        return "Проститутки по цене {$cat_name} в Москве — анкеты с фото и фильтрами";
    }

    return '';
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
        'site'        => _seo_site_brand(),
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
 * TITLE (по ТЗ):
 * - uslugi:       {Service_Name} Москва — заказать интим услуги в Москве (цены от {Price})
 * - appearance:   {Appearance_Type} индивидуалки Москва — девушки с внешностью {Appearance_Type} в Москве
 * - nationality:  Проститутки национальности {Nationality} в Москве — анкеты с фото и ценами
 * - rajon:        Индивидуалки {District} — интим услуги в районе {District} (фото и цены)
 * - metro:        Проститутки метро {Station} — интим услуги рядом с метро (цены от {Price})
 * - models/прочие: прежняя логика.
 */
function _seo_build_title(array $ctx): string
{
    $site = $ctx['site'];
    $pt   = $ctx['post_type'];
    $slug = $ctx['slug'];

    // Карта CPT -> taxonomy для посадочных страниц по термам.
    $tx_map = [
        'metro'   => 'metro_tax',
        'rajon'   => 'rayonu_tax',
        'uslugi'  => 'uslugi_tax',
        'vozrast' => 'vozrast_tax',
        'rost'    => 'rost_tax',
        'price'   => 'price_tax',
        'tsena'   => 'price_tax',
        'nacionalnost' => 'nationalnost_tax',
        'grud'    => 'grud_tax',
        'ves'     => 'ves_tax',
        'tsvet-volos' => 'cvet-volos_tax',
    ];
    // Иерархические "страничные" CPT
    if ($ctx['is_singular'] && $pt && isset($tx_map[$pt]) && $ctx['id']) {
        $tax = $tx_map[$pt];
        $term = _seo_find_term_by_slug($tax, $slug);
        $cat_name = $term ? $term->name : get_the_title($ctx['id']);
        $cat_name = _seo_decode_entities($cat_name);

        $price_num = _seo_min_price_num_by_term($term, $tax);
        $price_txt = $price_num > 0 ? number_format_i18n($price_num) : '';
        $kind = _seo_landing_kind_by_taxonomy($tax);
        if ($kind !== '') {
            $built = _seo_build_landing_title_by_kind($kind, $cat_name, $price_txt);
            if ($built !== '') return $built;
        }
    }

    // Архивы таксономий (services/slug, metro/slug и т.д.)
    if (is_tax()) {
        $qo = get_queried_object();
        if ($qo instanceof WP_Term && !empty($qo->taxonomy)) {
            $tax = (string) $qo->taxonomy;
            $kind = _seo_landing_kind_by_taxonomy($tax);
            if ($kind !== '') {
                $cat_name = _seo_decode_entities((string) $qo->name);
                $price_num = _seo_min_price_num_by_term($qo, $tax);
                $price_txt = $price_num > 0 ? number_format_i18n($price_num) : '';
                $built = _seo_build_landing_title_by_kind($kind, $cat_name, $price_txt);
                if ($built !== '') return $built;
            }
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
            : "{$name} - проститутка в Москве.";

        $parts = [];
        if ($age !== '')    $parts[] = "Возраст - {$age}";
        if ($height !== '') $parts[] = "рост - {$height}";
        if ($bust !== '')   $parts[] = "размер груди - {$bust}";

        $details = $parts ? ' ' . implode(', ', $parts) : '';

        return $first . $details;
    }

    // Прочие singular: ручной title или заголовок записи
    if ($ctx['is_singular'] && $ctx['id']) {
        $manual = _seo_normalize_brand_text(_seo_get_meta_str($ctx['id'], 'title'));
        if ($manual !== '') return _seo_decode_entities($manual);
        $t = _seo_decode_entities(_seo_normalize_brand_text(get_the_title($ctx['id'])));
        return $t;
    }

    if (is_search()) {
        $q = trim((string) get_search_query());
        return $q !== '' ? "Поиск «{$q}» — {$site}" : "Поиск по сайту — {$site}";
    }

    if (is_404()) {
        return "Страница не найдена — {$site}";
    }

    if (is_post_type_archive()) {
        $pt_obj = get_queried_object();
        $label = (is_object($pt_obj) && !empty($pt_obj->labels->name))
            ? (string) $pt_obj->labels->name
            : 'Каталог';
        return _seo_decode_entities("{$label} — {$site}");
    }

    if (is_tax() || is_category() || is_tag()) {
        $term_title = trim((string) single_term_title('', false));
        if ($term_title !== '') {
            return _seo_decode_entities("{$term_title} — {$site}");
        }
    }

    if (is_author()) {
        $author = trim((string) get_the_author_meta('display_name', (int) get_query_var('author')));
        if ($author !== '') return _seo_decode_entities("{$author} — {$site}");
    }

    if (is_date()) {
        return "Архив публикаций — {$site}";
    }

    if (is_archive()) {
        return "Каталог — {$site}";
    }

    return $site !== '' ? $site : SEO_SITE_BRAND;
}

/**
 * DESCRIPTION (по ТЗ):
 * - uslugi:     Ищете {Service_Name} в Москве? ... {Count} анкет ... от {Price}
 * - appearance: Сексуальные {Appearance_Type} в Москве ... {Count} ... от {Price}
 * - nationality: Анкеты с фильтром «национальность {Nationality}» в Москве ...
 * - rajon:      Ищете досуг в районе {District}? ... {Count} ... от {Price} ... метро {Station}
 * - metro:      Секс услуги у метро {Station} ... {Count} ... от {Price}
 * - models/прочие: прежняя логика.
 */
function _seo_build_descr(array $ctx): string
{
    $pt = $ctx['post_type'];
    $tx_map = [
        'metro'   => 'metro_tax',
        'rajon'   => 'rayonu_tax',
        'uslugi'  => 'uslugi_tax',
        'vozrast' => 'vozrast_tax',
        'rost'    => 'rost_tax',
        'price'   => 'price_tax',
        'tsena'   => 'price_tax',
        'nacionalnost' => 'nationalnost_tax',
        'grud'    => 'grud_tax',
        'ves'     => 'ves_tax',
        'tsvet-volos' => 'cvet-volos_tax',
    ];
    $appearance_pts = ['vozrast', 'rost', 'grud', 'ves', 'tsvet-volos'];

    if ($ctx['is_singular'] && $pt && isset($tx_map[$pt]) && $ctx['id']) {
        // Ручное поле descr всегда в приоритете.
        $d = _seo_normalize_descr_text(_seo_get_meta_str($ctx['id'], 'descr'));
        if ($d !== '') return _seo_trim_170($d);

        $tax = $tx_map[$pt];
        $slug = $ctx['slug'];
        $term = _seo_find_term_by_slug($tax, $slug);
        $cat_name = $term ? _seo_decode_entities((string) $term->name) : _seo_decode_entities(get_the_title($ctx['id']));
        $count = _seo_count_models_by_term($term, $tax);
        $count_txt = number_format_i18n(max(0, $count));
        $price_num = _seo_min_price_num_by_term($term, $tax);
        $price_txt = $price_num > 0 ? number_format_i18n($price_num) : '';

        if ($pt === 'metro') {
            if ($price_txt !== '') {
                return _seo_trim_170("💋 Секс услуги у метро {$cat_name}. На сайте {$count_txt} девушек с реальными фото. Проверенные индивидуалки в 5 минутах от метро. Цены от {$price_txt} руб. за час!");
            }
            return _seo_trim_170("💋 Секс услуги у метро {$cat_name}. На сайте {$count_txt} девушек с реальными фото. Проверенные индивидуалки в 5 минутах от метро. Условия встречи уточняйте в анкете.");
        }

        if ($pt === 'rajon') {
            $station = _seo_find_related_term_name($term, $tax, 'metro_tax');
            if ($station === '') $station = 'центра Москвы';
            if ($price_txt !== '') {
                return _seo_trim_170("🔥 Ищете досуг в районе {$cat_name}? У нас {$count_txt} проверенных анкет индивидуалок. Реальные фото, цены от {$price_txt} руб. Секс услуги рядом с метро {$station}!");
            }
            return _seo_trim_170("🔥 Ищете досуг в районе {$cat_name}? У нас {$count_txt} проверенных анкет индивидуалок. Реальные фото и анкеты с актуальными условиями рядом с метро {$station}.");
        }

        if ($pt === 'uslugi') {
            if ($price_txt !== '') {
                return _seo_trim_170("Ищете {$cat_name} в Москве? 💋 На сайте {$count_txt} анкет с реальными фото. Цены от {$price_txt} руб. за час. Проверенные индивидуалки в Москве!");
            }
            return _seo_trim_170("Ищете {$cat_name} в Москве? 💋 На сайте {$count_txt} анкет с реальными фото. Проверенные индивидуалки в Москве, условия встречи указаны в карточках.");
        }

        if ($pt === 'nacionalnost') {
            if ($price_txt !== '') {
                return _seo_trim_170("Анкеты с фильтром «национальность {$cat_name}» в Москве: {$count_txt} проверенных профилей. Реальные фото и цены от {$price_txt} руб. за час.");
            }
            return _seo_trim_170("Анкеты с фильтром «национальность {$cat_name}» в Москве: {$count_txt} проверенных профилей с реальными фото и актуальными условиями встреч.");
        }

        if (in_array($pt, $appearance_pts, true)) {
            if ($price_txt !== '') {
                return _seo_trim_170("💋 Сексуальные {$cat_name} в Москве. Посмотрите {$count_txt} проверенных профилей. Реальные фото, честные цены от {$price_txt} руб. Интим услуги рядом с вами!");
            }
            return _seo_trim_170("💋 Сексуальные {$cat_name} в Москве. Посмотрите {$count_txt} проверенных профилей с реальными фото и актуальными условиями встреч.");
        }

        if (($pt === 'price' || $pt === 'tsena') && $price_txt !== '') {
            return _seo_trim_170("Актуальные анкеты по цене {$cat_name} в Москве. Сравните условия, реальные фото и предложения от {$price_txt} руб. за час.");
        }
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
        $d = _seo_normalize_descr_text(_seo_get_meta_str($ctx['id'], 'descr'));
        if ($d !== '') return _seo_trim_170($d);

        $raw = get_post_field('post_excerpt', $ctx['id']) ?: get_post_field('post_content', $ctx['id']);
        if ($raw) return _seo_trim_170($raw);
    }

    if ($ctx['is_home']) {
        return 'Эскорт-модели с фото, видео и ценами. Фильтры по возрасту, району, метро, национальности. Обновляем ежедневно.';
    }

    if (is_search()) {
        return _seo_trim_170("Результаты поиска по сайту. Используйте фильтры, чтобы быстрее найти подходящие анкеты.");
    }

    if (is_404()) {
        return _seo_trim_170("Страница не найдена. Перейдите в каталог и воспользуйтесь фильтрами по району, метро и цене.");
    }

    if (is_post_type_archive() || is_archive() || is_tax() || is_category() || is_tag()) {
        return _seo_trim_170("Актуальный каталог анкет с фото, ценами и фильтрами по району, метро, услугам и другим параметрам.");
    }

    return _seo_trim_170("Каталог анкет с фото, описаниями и удобными фильтрами для быстрого подбора.");
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
$descr     = _seo_normalize_descr_text($descr);

if (!_seo_is_individualki_page($ctx)) {
    $title = _seo_strip_individualki_mentions($title);
    $descr = _seo_strip_individualki_mentions($descr);
}

$title     = _seo_append_page_suffix($title, $ctx['paged']);
$descr     = _seo_append_page_suffix($descr, $ctx['paged']);

if (trim($title) === '') {
    $title = _seo_site_brand();
}
if (trim($descr) === '') {
    $descr = _seo_trim_170('Актуальные анкеты с фото, ценами и фильтрами по району, метро и услугам.');
    $descr = _seo_append_page_suffix($descr, $ctx['paged']);
}
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
